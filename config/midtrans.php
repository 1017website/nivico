<?php

return [
    'merchant_id'  => env('MIDTRANS_MERCHANT_ID', ''),
    'client_key'   => env('MIDTRANS_CLIENT_KEY', ''),
    'server_key'   => env('MIDTRANS_SERVER_KEY', ''),

    // true = produksi, false = sandbox
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    // sanitasi & 3DS
    'is_sanitized'  => true,
    'is_3ds'        => true,

    // URL Snap berdasarkan environment
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    'api_base' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',
];
