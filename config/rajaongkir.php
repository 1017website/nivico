<?php

return [
    // Komerce / RajaOngkir API v2 (collaborator)
    // Dapatkan API key di https://collaborator.komerce.id
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
    'api_key'  => env('RAJAONGKIR_API_KEY', ''),

    // ID kota/kecamatan asal pengiriman (origin) toko
    'origin'      => env('RAJAONGKIR_ORIGIN', ''),
    'origin_type' => env('RAJAONGKIR_ORIGIN_TYPE', 'city'), // city | subdistrict

    // Kurir yang ditawarkan (pisah koma): jne, sicepat, jnt, anteraja, pos, tiki, dll
    'couriers' => env('RAJAONGKIR_COURIERS', 'jne:sicepat:jnt'),

    // Berat default per item (gram) bila produk belum punya berat
    'default_weight' => (int) env('RAJAONGKIR_DEFAULT_WEIGHT', 1000),
];
