<?php

namespace App\Services;

use App\Models\SiteSetting;

class ServiceFeeService
{
    /** @return array{type:string,value:float} */
    public function settings(): array
    {
        $type = (string) SiteSetting::get('checkout.service_fee_type', 'fixed');

        return [
            'type' => in_array($type, ['percent', 'fixed'], true) ? $type : 'fixed',
            'value' => max(0, (float) SiteSetting::get('checkout.service_fee_value', 0)),
        ];
    }

    public function calculate(int $amountBeforeFee): int
    {
        if ($amountBeforeFee <= 0) {
            return 0;
        }

        $settings = $this->settings();

        if ($settings['type'] === 'percent') {
            return (int) round($amountBeforeFee * min(100, $settings['value']) / 100);
        }

        return (int) round($settings['value']);
    }
}
