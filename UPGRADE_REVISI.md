# Catatan Revisi — Hak Akses, Audit, Activity Log, SEO

Revisi ini menambah migration & fitur baru. Setelah menimpa folder dengan versi ini:

```bash
php artisan migrate:fresh --seed
php artisan storage:link   # bila belum
```

> Gunakan `migrate:fresh` karena ada perubahan kolom & tabel baru. Hapus file migration usang bila masih ada (lihat catatan di bawah).

## Yang Baru

1. **Icon sosmed asli** — Instagram, WhatsApp, Tokopedia, Shopee, Facebook kini berupa SVG brand (komponen `<x-social-icon>`), bukan emoji.

2. **Soft delete di semua tabel bisnis** — categories, products, product_images, promos, orders, order_items, bank_accounts, contact_messages, users, roles. Data tidak benar-benar terhapus (kolom `deleted_at`).

3. **Audit kolom** — `created_by`, `updated_by`, `deleted_by` terisi otomatis (trait `Auditable`) di semua tabel bisnis. `created_at`/`updated_at` sudah ada sejak awal.

4. **Activity Log** — tabel `activity_logs` mencatat tambah/ubah/hapus + login/logout staf admin. Lihat di menu **Log Aktivitas**.

5. **Manajemen Pengguna + Hak Akses Menu**
   - Menu **Pengguna**: CRUD staf admin, set role & status aktif.
   - Menu **Hak Akses**: buat role dan centang menu mana yang boleh diakses (per-permission).
   - Sidebar admin otomatis menyembunyikan menu yang tidak diizinkan untuk role tsb.
   - Role **Super Admin** terkunci & selalu punya akses penuh.

6. **Halaman SEO** — menu **Pengaturan SEO**: atur meta title, description, keywords, Open Graph (title/description/image), canonical, noindex — global & per halaman (beranda, produk, promo, tentang, kontak). Meta otomatis tampil di `<head>`.

## Akun Default (seeder)

| Peran        | Email              | Password | Role        |
|--------------|--------------------|----------|-------------|
| Super Admin  | admin@nivico.id    | password | Super Admin |
| Staf Toko    | staf@nivico.id     | password | Staf Toko   |
| Customer     | customer@nivico.id | password | —           |

Role bawaan: **Super Admin** (full), **Staf Toko** (katalog & pesanan), **Customer Service** (pesanan & pesan).

## Penting saat update dari zip
Hapus file migration lama yang sudah tidak dipakai bila tertinggal di mesin Anda:
- `2024_01_02_100002_create_bank_accounts_table.php` (sudah diganti `2024_01_02_099999_...`)

Cara aman: hapus folder `database/migrations` lama sebelum ekstrak, atau ekstrak ke folder baru.
