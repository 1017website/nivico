<?php

namespace Tests\Unit;

use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rajaongkir.base_url' => 'https://rajaongkir.test/api/v1',
            'rajaongkir.api_key' => 'test-key',
            'rajaongkir.origin' => '123',
            'rajaongkir.couriers' => 'jne:jnt',
        ]);
        Cache::flush();
    }

    public function test_destination_results_include_fields_for_address_autofill(): void
    {
        Http::fake([
            'https://rajaongkir.test/api/v1/destination/*' => Http::response([
                'data' => [[
                    'id' => 69318,
                    'label' => 'MEDOKAN SEMAMPIR, SUKOLILO, SURABAYA, JAWA TIMUR, 60119',
                    'province_name' => 'JAWA TIMUR',
                    'city_name' => 'SURABAYA',
                    'district_name' => 'SUKOLILO',
                    'subdistrict_name' => 'MEDOKAN SEMAMPIR',
                    'zip_code' => '60119',
                ]],
            ]),
        ]);

        $result = app(RajaOngkirService::class)->searchDestination('Medokan');

        $this->assertSame('SURABAYA', $result[0]['city']);
        $this->assertSame('SUKOLILO', $result[0]['district']);
        $this->assertSame('60119', $result[0]['postal_code']);
    }

    public function test_non_retail_shipping_services_are_removed_from_shipping_options(): void
    {
        Http::fake([
            'https://rajaongkir.test/api/v1/calculate/*' => Http::response([
                'data' => [
                    ['code' => 'jnt', 'name' => 'J&T Express', 'service' => 'EZ', 'description' => 'Reguler', 'cost' => 23000, 'etd' => '2 hari'],
                    ['code' => 'jne', 'name' => 'Jalur Nugraha Ekakurir (JNE)', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 23000, 'etd' => '2 hari'],
                    ['code' => 'tiki', 'name' => 'Citra Van Titipan Kilat (TIKI)', 'service' => 'REG', 'description' => 'Regular Service', 'cost' => 7000, 'etd' => '2 hari'],
                    ['code' => 'anteraja', 'name' => 'AnterAja', 'service' => 'DOK', 'description' => 'Anteraja Document', 'cost' => 5810, 'etd' => '1-2 hari'],
                    ['code' => 'jne', 'name' => 'Jalur Nugraha Ekakurir (JNE)', 'service' => 'JTR', 'description' => 'JNE Trucking', 'cost' => 85000, 'etd' => '6 hari'],
                    ['code' => 'jne', 'name' => 'Jalur Nugraha Ekakurir (JNE)', 'service' => 'JTR<130', 'description' => 'JNE Trucking', 'cost' => 1100000, 'etd' => '3 hari'],
                    ['code' => 'tiki', 'name' => 'Citra Van Titipan Kilat (TIKI)', 'service' => 'TRC', 'description' => 'Trucking', 'cost' => 34000, 'etd' => '6 hari'],
                    ['code' => 'sicepat', 'name' => 'SiCepat Express', 'service' => 'GOKIL', 'description' => 'Cargo Per Kg (Minimal 10kg)', 'cost' => 30000, 'etd' => '3 hari'],
                    ['code' => 'anteraja', 'name' => 'AnterAja', 'service' => 'MC', 'description' => 'Anteraja Mini Cargo', 'cost' => 35000, 'etd' => '4 hari'],
                    ['code' => 'tiki', 'name' => 'Citra Van Titipan Kilat (TIKI)', 'service' => 'T15', 'description' => 'Motor Di Bawah 150cc/1500watt', 'cost' => 476000, 'etd' => '6 hari'],
                    ['code' => 'tiki', 'name' => 'Citra Van Titipan Kilat (TIKI)', 'service' => 'T25', 'description' => 'Motor Di Bawah 250cc/Di Atas 1500watt', 'cost' => 578000, 'etd' => '6 hari'],
                    ['code' => 'tiki', 'name' => 'Citra Van Titipan Kilat (TIKI)', 'service' => 'T60', 'description' => 'Motor Di Bawah 600cc/Non Standar/Roda 3', 'cost' => 680000, 'etd' => '6 hari'],
                ],
            ]),
        ]);

        $result = app(RajaOngkirService::class)->calculateCost('69318', 1000);

        $this->assertCount(3, $result);
        $this->assertSame(['REG', 'EZ', 'REG'], array_column($result, 'service'));
    }
}
