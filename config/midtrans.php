<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    // Proyek ini menggunakan akun Midtrans production. Ubah ke false hanya
    // ketika sengaja menguji dengan kredensial Sandbox.
    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', true), FILTER_VALIDATE_BOOL),

    'is_sanitized' => true,
    'is_3ds' => true,

    'snap_url' => filter_var(env('MIDTRANS_IS_PRODUCTION', true), FILTER_VALIDATE_BOOL)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    'snap_api_url' => filter_var(env('MIDTRANS_IS_PRODUCTION', true), FILTER_VALIDATE_BOOL)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'api_base' => filter_var(env('MIDTRANS_IS_PRODUCTION', true), FILTER_VALIDATE_BOOL)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',
];
