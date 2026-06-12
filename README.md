# NIVICO Electronic Mart — Laravel 12

Aplikasi e-commerce toko elektronik (kabel, microphone, adaptor, baterai, tools, audio, lampu/LED, rumah tangga) yang dikonversi 1:1 dari template HTML ke Laravel 12 + MySQL, lengkap dengan panel admin.

## Fitur

**Frontend (toko)**
- Beranda: hero slider, grid kategori, produk terbaru, flash sale + countdown, banner promo, best seller
- Katalog produk dengan filter kategori, pencarian, dan sorting (terbaru / termurah / tertinggi / terlaris)
- Halaman detail produk (galeri thumbnail, qty stepper, beli sekarang / + keranjang, produk terkait)
- Keranjang (guest via session & user login, auto-merge saat login), penerapan kode promo
- Checkout (alamat, pilihan kurir, metode pembayaran, ringkasan) → halaman sukses
- Halaman promo (tab filter), tentang kami, kontak (form tersimpan ke DB)
- Popup promo otomatis, login & register, responsif + bottom nav mobile

**Backend / Admin (`/admin`)**
- Dashboard: statistik pesanan, pendapatan, produk, pelanggan, stok menipis
- CRUD Produk (upload gambar / URL), Kategori, Promo/Voucher
- Manajemen Pesanan + ubah status, detail pengiriman
- Kotak masuk pesan kontak

**Teknis**
- Stock booking anti-race-condition (`lockForUpdate` di dalam transaksi) saat membuat order
- Scheduler melepas order `pending` kedaluwarsa (24 jam) & mengembalikan stok (`php artisan schedule:run`)
- Promo: tipe potongan tetap / persen (dengan cap) / gratis ongkir, validasi minimal belanja
- **Ongkir real-time** via RajaOngkir/Komerce (cari kota/kecamatan + hitung tarif per kurir, fallback ke tarif statis bila API belum diisi)
- **Pembayaran**: Midtrans Snap (otomatis: kartu, e-wallet, VA, QRIS) + Transfer bank manual (upload bukti, verifikasi admin)
- Webhook Midtrans (verifikasi signature SHA-512, update status pembayaran, restock otomatis bila expired/failed)

## Persyaratan
- PHP >= 8.2
- Composer
- MySQL >= 5.7 / MariaDB

## Cara Setup (Lokal)

```bash
# 1. Masuk folder & install dependency
cd nivico
composer install

# 2. Generate app key (file .env sudah disertakan)
php artisan key:generate

# 3. Sesuaikan kredensial database di .env
#    DB_DATABASE=nivico
#    DB_USERNAME=root
#    DB_PASSWORD=
#    (buat database `nivico` lebih dulu di MySQL)

# 4. Migrasi + seed data contoh (kategori, 12 produk, 6 promo, admin)
php artisan migrate --seed

# 5. Symlink storage (untuk gambar upload admin)
php artisan storage:link

# 6. Jalankan
php artisan serve
# buka http://localhost:8000
```

## Akun Default (dari seeder)

| Peran    | Email               | Password   |
|----------|---------------------|------------|
| Admin    | admin@nivico.id     | password   |
| Customer | customer@nivico.id  | password   |

Panel admin: `http://localhost:8000/admin`

## Konfigurasi RajaOngkir & Midtrans

Isi di `.env` (lihat `.env.example`):

```
# RajaOngkir / Komerce — daftar di https://collaborator.komerce.id
RAJAONGKIR_API_KEY=xxxxx
RAJAONGKIR_ORIGIN=          # ID kota/kecamatan asal toko
RAJAONGKIR_ORIGIN_TYPE=city # city | subdistrict
RAJAONGKIR_COURIERS=jne:sicepat:jnt

# Midtrans — dashboard.midtrans.com (pakai Sandbox dulu)
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_IS_PRODUCTION=false
```

- Jika **RajaOngkir kosong**, checkout otomatis memakai tarif statis (JNE/SiCepat/J&T) sebagai fallback.
- Jika **Midtrans kosong**, opsi pembayaran otomatis disembunyikan; transfer bank manual tetap berfungsi.
- Set **URL notifikasi** di dashboard Midtrans ke: `https://domain-anda.com/midtrans/notify`
- Rekening bank untuk transfer manual dikelola di panel admin → **Rekening Bank**.

## Catatan Deploy (cPanel / shared hosting)
- Arahkan document root ke folder `public/`, atau gunakan teknik symlink `public_html` → `public`.
- Pastikan folder `storage/` dan `bootstrap/cache/` writable (755/775).
- Jalankan `php artisan config:cache route:cache view:cache` setelah deploy.
- Untuk scheduler order kedaluwarsa, tambahkan cron:
  `* * * * * cd /path/ke/nivico && php artisan schedule:run >> /dev/null 2>&1`

## Struktur Penting
```
app/
  Http/Controllers/      # frontend, Auth, Admin
  Models/                # Product, Category, Cart, Order, Promo, ...
  Services/              # CartService, OrderService (stock booking)
  Http/Middleware/       # AdminMiddleware, CartCount
database/
  migrations/            # skema lengkap
  seeders/               # data contoh
public/
  css/app.css            # CSS template (dikonversi 1:1)
  js/app.js              # slider, countdown, toast, popup, dll
resources/views/
  layouts/{app,admin}    # layout toko & admin
  partials/              # header, nav, footer, popup, mobile-nav
  pages/                 # halaman toko
  admin/                 # panel admin
  auth/                  # login & register
routes/web.php           # semua route
```

---
Dibuat oleh 1017Studios.
