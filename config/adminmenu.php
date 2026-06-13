<?php

/**
 * Definisi menu admin + permission yang mengikat tiap menu.
 * Dipakai oleh: sidebar (tampil/sembunyi), seeder permission, dan halaman atur hak akses.
 *
 * Tiap item: key => [label, icon, route, permission, group]
 */
return [
    'dashboard'  => ['label' => 'Dashboard',      'icon' => '📊', 'route' => 'admin.dashboard',       'permission' => 'dashboard.view',  'group' => 'Umum'],
    'orders'     => ['label' => 'Pesanan',        'icon' => '🧾', 'route' => 'admin.orders.index',    'permission' => 'orders.manage',   'group' => 'Transaksi'],
    'products'   => ['label' => 'Produk',         'icon' => '📦', 'route' => 'admin.products.index',  'permission' => 'products.manage', 'group' => 'Katalog'],
    'stock'      => ['label' => 'Stok',           'icon' => '📥', 'route' => 'admin.stock.index',     'permission' => 'stock.manage',    'group' => 'Katalog'],
    'categories' => ['label' => 'Kategori',       'icon' => '🗂', 'route' => 'admin.categories.index','permission' => 'categories.manage','group' => 'Katalog'],
    'promos'     => ['label' => 'Promo',          'icon' => '🎁', 'route' => 'admin.promos.index',    'permission' => 'promos.manage',   'group' => 'Katalog'],
    'banks'      => ['label' => 'Rekening Bank',  'icon' => '🏦', 'route' => 'admin.banks.index',     'permission' => 'banks.manage',    'group' => 'Transaksi'],
    'messages'   => ['label' => 'Pesan Masuk',    'icon' => '💬', 'route' => 'admin.messages.index',  'permission' => 'messages.manage', 'group' => 'Umum'],
    'seo'        => ['label' => 'Pengaturan SEO', 'icon' => '🔍', 'route' => 'admin.seo.index',       'permission' => 'seo.manage',      'group' => 'Pengaturan'],
    'users'      => ['label' => 'Pengguna',       'icon' => '👥', 'route' => 'admin.users.index',     'permission' => 'users.manage',    'group' => 'Pengaturan'],
    'roles'      => ['label' => 'Hak Akses',      'icon' => '🔐', 'route' => 'admin.roles.index',     'permission' => 'roles.manage',    'group' => 'Pengaturan'],
    'activity'   => ['label' => 'Log Aktivitas',  'icon' => '📜', 'route' => 'admin.activity.index',  'permission' => 'activity.view',   'group' => 'Pengaturan'],
];
