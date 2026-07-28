<?php

return [
    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key' => env('DUITKU_API_KEY', ''),
    'is_production' => (bool) env('DUITKU_IS_PRODUCTION', false),
    'expiry_period' => (int) env('DUITKU_EXPIRY_PERIOD', 60),

    'create_invoice_url' => env(
        'DUITKU_CREATE_INVOICE_URL',
        env('DUITKU_IS_PRODUCTION', false)
            ? 'https://api-prod.duitku.com/api/merchant/createInvoice'
            : 'https://api-sandbox.duitku.com/api/merchant/createInvoice'
    ),

    'payment_method_url' => env(
        'DUITKU_PAYMENT_METHOD_URL',
        env('DUITKU_IS_PRODUCTION', false)
            ? 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod'
            : 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod'
    ),
];
