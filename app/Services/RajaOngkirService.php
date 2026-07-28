<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi RajaOngkir (Komerce Collaborator API v1/v2).
 * Menyediakan pencarian tujuan (kota/kecamatan) & perhitungan ongkir.
 *
 * Catatan: struktur endpoint Komerce dapat berbeda per akun/paket.
 * Sesuaikan path bila perlu; method sudah defensif terhadap variasi response.
 */
class RajaOngkirService
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('rajaongkir.base_url'), '/');
        $this->apiKey = (string) config('rajaongkir.api_key');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && config('rajaongkir.origin') !== '';
    }

    protected function client()
    {
        return Http::withHeaders([
            'key' => $this->apiKey,
            'Accept' => 'application/json',
        ])->timeout(15);
    }

    /**
     * Cari tujuan pengiriman berdasarkan kata kunci (nama kota/kecamatan).
     * Mengembalikan array [['id'=>, 'label'=>], ...]
     */
    public function searchDestination(string $keyword): array
    {
        if (! $this->isConfigured() || strlen($keyword) < 3) {
            return [];
        }

        // Sertakan hash API key di cache-key agar pergantian key
        // otomatis menginvalidasi cache lama.
        $cacheKey = 'ro_dest_v2_'.substr(md5($this->apiKey), 0, 8).'_'.md5(strtolower($keyword));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($keyword) {
            try {
                $res = $this->client()->get($this->baseUrl.'/destination/domestic-destination', [
                    'search' => $keyword,
                    'limit' => 20,
                ]);

                if (! $res->successful()) {
                    Log::warning('RajaOngkir destination gagal', ['status' => $res->status()]);

                    return [];
                }

                $rows = $res->json('data') ?? [];

                return collect($rows)->map(function ($r) {
                    $label = $r['label']
                        ?? trim(($r['subdistrict_name'] ?? '').', '.($r['district_name'] ?? '').', '.($r['city_name'] ?? '').', '.($r['province_name'] ?? ''), ', ');

                    return [
                        'id' => (string) ($r['id'] ?? ($r['subdistrict_id'] ?? $r['city_id'] ?? '')),
                        'label' => $label,
                        'province' => (string) ($r['province_name'] ?? ''),
                        'city' => (string) ($r['city_name'] ?? ''),
                        'district' => (string) ($r['district_name'] ?? ''),
                        'subdistrict' => (string) ($r['subdistrict_name'] ?? ''),
                        'postal_code' => (string) ($r['zip_code'] ?? ''),
                    ];
                })->filter(fn ($r) => $r['id'])->values()->all();
            } catch (\Throwable $e) {
                Log::error('RajaOngkir destination exception', ['msg' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Hitung ongkir dari origin (config) ke destination.
     * Mengembalikan array opsi [['courier'=>, 'service'=>, 'description'=>, 'cost'=>, 'etd'=>], ...]
     */
    public function calculateCost(string $destinationId, int $weightGram): array
    {
        if (! $this->isConfigured() || ! $destinationId) {
            return [];
        }

        $weightGram = max(1, $weightGram);

        try {
            $res = $this->client()->asForm()->post($this->baseUrl.'/calculate/domestic-cost', [
                'origin' => config('rajaongkir.origin'),
                'destination' => $destinationId,
                'weight' => $weightGram,
                'courier' => config('rajaongkir.couriers'),
            ]);

            if (! $res->successful()) {
                Log::warning('RajaOngkir cost gagal', ['status' => $res->status(), 'body' => $res->body()]);

                return [];
            }

            $rows = $res->json('data') ?? [];

            return collect($rows)->map(function ($r) {
                return [
                    'courier' => strtolower($r['code'] ?? $r['courier'] ?? ''),
                    'courier_name' => $r['name'] ?? strtoupper($r['code'] ?? ''),
                    'service' => $r['service'] ?? '',
                    'description' => $r['description'] ?? ($r['service'] ?? ''),
                    'cost' => (int) ($r['cost'] ?? $r['value'] ?? 0),
                    'etd' => $r['etd'] ?? '',
                ];
            })->filter(fn ($r) => $r['cost'] > 0)
                ->reject(fn ($r) => $this->isUnsupportedRetailService($r))
                ->sortBy('cost')
                ->values()->all();
        } catch (\Throwable $e) {
            Log::error('RajaOngkir cost exception', ['msg' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Sembunyikan layanan khusus dokumen, muatan besar, trucking,
     * kargo, atau pengiriman kendaraan dari checkout barang retail.
     */
    protected function isUnsupportedRetailService(array $option): bool
    {
        $courier = strtolower((string) ($option['courier'] ?? ''));
        $service = strtoupper(preg_replace('/\s+/', '', (string) ($option['service'] ?? '')));
        $searchable = strtolower(implode(' ', [
            (string) ($option['courier_name'] ?? ''),
            (string) ($option['service'] ?? ''),
            (string) ($option['description'] ?? ''),
        ]));

        $documentOnly = in_array($service, ['DOK', 'DOC', 'DOCUMENT'], true)
            || (
                (str_contains($searchable, 'document') || str_contains($searchable, 'dokumen'))
                && ! str_contains($searchable, 'parcel')
                && ! str_contains($searchable, 'paket')
            );

        if ($documentOnly) {
            return true;
        }

        $excludedKeywords = [
            'cargo',
            'kargo',
            'trucking',
            'mini cargo',
            'sepeda motor',
            'motor di bawah',
            'minimal 10kg',
            'minimal 10 kg',
        ];

        if (collect($excludedKeywords)->contains(fn ($keyword) => str_contains($searchable, $keyword))) {
            return true;
        }

        if (str_starts_with($service, 'JTR') || $service === 'TRC' || $service === 'GOKIL') {
            return true;
        }

        if ($courier === 'anteraja' && $service === 'MC') {
            return true;
        }

        return $courier === 'tiki' && in_array($service, ['T15', 'T25', 'T60'], true);
    }

    /**
     * Opsi ongkir fallback (dipakai bila API belum dikonfigurasi).
     */
    public function fallbackOptions(): array
    {
        return [
            ['courier' => 'jne',     'courier_name' => 'JNE',     'service' => 'REG',  'description' => 'Layanan Reguler',   'cost' => 15000, 'etd' => '2-3 hari'],
            ['courier' => 'jne',     'courier_name' => 'JNE',     'service' => 'YES',  'description' => 'Yakin Esok Sampai', 'cost' => 28000, 'etd' => '1 hari'],
            ['courier' => 'sicepat', 'courier_name' => 'SiCepat', 'service' => 'HALU', 'description' => 'Hemat',             'cost' => 12000, 'etd' => '2-4 hari'],
            ['courier' => 'jnt',     'courier_name' => 'J&T',     'service' => 'EZ',   'description' => 'Reguler',           'cost' => 14000, 'etd' => '2-3 hari'],
        ];
    }
}
