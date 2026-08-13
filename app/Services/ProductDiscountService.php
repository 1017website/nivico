<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SiteSetting;

class ProductDiscountService
{
    public function enabled(): bool
    {
        return (bool) SiteSetting::get('flashsale.discount_enabled', false)
            && $this->percentage() > 0;
    }

    public function scope(): string
    {
        $scope = (string) SiteSetting::get('flashsale.discount_scope', 'selected');

        return in_array($scope, ['all', 'selected'], true) ? $scope : 'selected';
    }

    public function percentage(): int
    {
        return max(0, min(99, (int) SiteSetting::get('flashsale.discount_percent', 10)));
    }

    public function appliesTo(Product $product): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        return $this->scope() === 'all' || (bool) $product->is_flash_sale;
    }

    public function priceFor(Product $product, int $basePrice): int
    {
        if (! $this->appliesTo($product)) {
            return max(0, $basePrice);
        }

        return max(0, (int) round($basePrice * (100 - $this->percentage()) / 100));
    }
}
