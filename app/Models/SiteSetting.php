<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'group', 'value', 'type', 'label'];

    protected static function booted(): void
    {
        // bersihkan cache saat ada perubahan
        static::saved(fn () => Cache::forget('site_settings_all'));
        static::deleted(fn () => Cache::forget('site_settings_all'));
    }

    /** Semua setting sebagai map [key => value], di-cache. */
    public static function allMap(): array
    {
        return Cache::rememberForever('site_settings_all', function () {
            return static::query()->get()->mapWithKeys(function ($s) {
                return [$s->key => $s->castValue()];
            })->all();
        });
    }

    /** Ambil satu nilai setting; otomatis decode JSON bila type=json. */
    public static function get(string $key, $default = null)
    {
        $map = static::allMap();
        return $map[$key] ?? $default;
    }

    /** Simpan / perbarui setting. */
    public static function put(string $key, $value, string $type = 'text', string $group = 'umum', ?string $label = null): self
    {
        if (in_array($type, ['json']) && ! is_string($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group, 'label' => $label]
        );
    }

    /** Konversi value mentah sesuai type. */
    public function castValue()
    {
        return match ($this->type) {
            'json'    => json_decode($this->value ?? '[]', true) ?: [],
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number'  => is_numeric($this->value) ? $this->value + 0 : 0,
            default   => $this->value,
        };
    }
}
