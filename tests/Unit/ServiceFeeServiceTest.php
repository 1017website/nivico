<?php

namespace Tests\Unit;

use App\Services\ServiceFeeService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ServiceFeeServiceTest extends TestCase
{
    public function test_it_calculates_a_fixed_rupiah_fee(): void
    {
        Cache::forever('site_settings_all', [
            'checkout.service_fee_type' => 'fixed',
            'checkout.service_fee_value' => 3500,
        ]);

        $this->assertSame(3500, app(ServiceFeeService::class)->calculate(100000));
    }

    public function test_it_calculates_a_percentage_fee_from_the_amount_after_discount_and_shipping(): void
    {
        Cache::forever('site_settings_all', [
            'checkout.service_fee_type' => 'percent',
            'checkout.service_fee_value' => 2.5,
        ]);

        $this->assertSame(2500, app(ServiceFeeService::class)->calculate(100000));
    }
}
