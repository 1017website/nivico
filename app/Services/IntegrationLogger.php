<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class IntegrationLogger
{
    private ?bool $available = null;

    public function create(
        string $channel,
        string $event,
        string $status,
        array $attributes = []
    ): ?IntegrationLog {
        if (! $this->isAvailable()) {
            return null;
        }

        try {
            return IntegrationLog::create(array_merge([
                'channel' => $channel,
                'event' => $event,
                'status' => $status,
                'context' => [],
            ], $attributes, [
                'context' => $this->sanitize((array) ($attributes['context'] ?? [])),
            ]));
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan log integrasi', [
                'channel' => $channel,
                'event' => $event,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function finish(?IntegrationLog $log, string $status, array $attributes = []): void
    {
        if (! $log) {
            return;
        }

        try {
            if (array_key_exists('context', $attributes)) {
                $attributes['context'] = $this->sanitize(array_merge(
                    (array) $log->context,
                    (array) $attributes['context']
                ));
            }

            $log->update(array_merge($attributes, ['status' => $status]));
        } catch (\Throwable $e) {
            Log::warning('Gagal memperbarui log integrasi', [
                'log_id' => $log->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function sanitize(array $context): array
    {
        $blocked = [
            'signature', 'api_key', 'apikey', 'password', 'token',
            'authorization', 'secret', 'private_key',
        ];

        foreach ($context as $key => $value) {
            if (in_array(strtolower((string) $key), $blocked, true)) {
                $context[$key] = '[DISEMBUNYIKAN]';
            } elseif (is_array($value)) {
                $context[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                $context[$key] = mb_substr($value, 0, 2000);
            }
        }

        return $context;
    }

    private function isAvailable(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if ($this->available !== null) {
            return $this->available;
        }

        try {
            return $this->available = Schema::hasTable('integration_logs');
        } catch (\Throwable) {
            return $this->available = false;
        }
    }
}
