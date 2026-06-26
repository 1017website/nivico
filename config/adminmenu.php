<?php

/**
 * Definisi menu admin + permission yang mengikat tiap menu.
 * Dipakai oleh: sidebar (tampil/sembunyi), seeder permission, dan halaman atur hak akses.
 *
 * Tiap item: key => [label, icon (FA class), route, permission, group]
 * 'super_only' => true  : hanya tampil untuk Super Admin (mis. menu Sistem).
 */
return [
    'dashboard'  => ['label' => 'Dashboard',      'icon' => 'fa-solid fa-gauge-high',     'route' => 'admin.dashboard',       'permission' => 'dashboard.view',   'group' => 'Umum'],
    'statistics' => ['label' => 'Statistik',      'icon' => 'fa-solid fa-chart-line',     'route' => 'admin.statistics.index','permission' => 'dashboard.view',   'group' => 'Umum'],
    'orders'     => ['label' => 'Pesanan',        'icon' => 'fa-solid fa-receipt',        'route' => 'admin.orders.index',    'permission' => 'orders.manage',    'group' => 'Transaksi'],
    'products'   => ['label' => 'Produk',         'icon' => 'fa-solid fa-box',            'route' => 'admin.products.index',  'permission' => 'products.manage',  'group' => 'Katalog'],
    'stock'      => ['label' => 'Stok',           'icon' => 'fa-solid fa-warehouse',      'route' => 'admin.stock.index',     'permission' => 'stock.manage',     'group' => 'Katalog'],
    'categories' => ['label' => 'Kategori',       'icon' => 'fa-solid fa-folder-tree',    'route' => 'admin.categories.index','permission' => 'categories.manage','group' => 'Katalog'],
    'promos'     => ['label' => 'Promo',          'icon' => 'fa-solid fa-gift',           'route' => 'admin.promos.index',    'permission' => 'promos.manage',    'group' => 'Katalog'],
    'flashsale'  => ['label' => 'Flash Sale',     'icon' => 'fa-solid fa-bolt',           'route' => 'admin.flashsale.index', 'permission' => 'promos.manage',    'group' => 'Katalog'],
    'banks'      => ['label' => 'Rekening Bank',  'icon' => 'fa-solid fa-building-columns','route' => 'admin.banks.index',     'permission' => 'banks.manage',     'group' => 'Transaksi'],
    'messages'   => ['label' => 'Pesan Masuk',    'icon' => 'fa-solid fa-comment-dots',   'route' => 'admin.messages.index',  'permission' => 'messages.manage',  'group' => 'Umum'],
    'content'    => ['label' => 'Konten Web',     'icon' => 'fa-solid fa-pen-ruler',      'route' => 'admin.content.index',   'permission' => 'content.manage',   'group' => 'Pengaturan'],
    'seo'        => ['label' => 'Pengaturan SEO', 'icon' => 'fa-solid fa-magnifying-glass','route' => 'admin.seo.index',      'permission' => 'seo.manage',       'group' => 'Pengaturan'],
    'users'      => ['label' => 'Pengguna',       'icon' => 'fa-solid fa-users',          'route' => 'admin.users.index',     'permission' => 'users.manage',     'group' => 'Pengaturan'],
    'roles'      => ['label' => 'Hak Akses',      'icon' => 'fa-solid fa-user-shield',    'route' => 'admin.roles.index',     'permission' => 'roles.manage',     'group' => 'Pengaturan'],
    'activity'   => ['label' => 'Log Aktivitas',  'icon' => 'fa-solid fa-clock-rotate-left','route' => 'admin.activity.index', 'permission' => 'activity.view',    'group' => 'Pengaturan'],
    'system'     => ['label' => 'Sistem',         'icon' => 'fa-solid fa-screwdriver-wrench','route' => 'admin.system.index',  'permission' => 'settings.manage',  'group' => 'Pengaturan', 'super_only' => true],
];
