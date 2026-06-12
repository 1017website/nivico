<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description', 'type', 'value', 'max_discount',
        'min_purchase', 'badge', 'image', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now());
            });
    }

    public function isValid(): bool
    {
        return $this->is_active
            && (is_null($this->expires_at) || $this->expires_at->gte(now()->startOfDay()));
    }

    /**
     * Hitung diskon untuk subtotal & ongkir tertentu.
     * Mengembalikan ['discount' => int, 'free_shipping' => bool]
     */
    public function calculate(int $subtotal, int $shipping): array
    {
        if (! $this->isValid() || $subtotal < $this->min_purchase) {
            return ['discount' => 0, 'free_shipping' => false];
        }

        return match ($this->type) {
            'fixed'         => ['discount' => min($this->value, $subtotal), 'free_shipping' => false],
            'percent'       => ['discount' => $this->capPercent($subtotal), 'free_shipping' => false],
            'free_shipping' => ['discount' => $shipping, 'free_shipping' => true],
            default         => ['discount' => 0, 'free_shipping' => false],
        };
    }

    private function capPercent(int $subtotal): int
    {
        $disc = (int) floor($subtotal * $this->value / 100);
        if ($this->max_discount) {
            $disc = min($disc, $this->max_discount);
        }
        return $disc;
    }
}
