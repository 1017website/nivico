<?php

use App\Services\OrderService;
use Illuminate\Support\Facades\Schedule;

// Lepas order pending kedaluwarsa tiap 10 menit & kembalikan stok
Schedule::call(function () {
    app(OrderService::class)->releaseExpired();
})->everyTenMinutes()->name('release-expired-orders')->withoutOverlapping();
