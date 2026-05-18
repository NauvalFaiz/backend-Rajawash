# RajaWash - Backend System

## Tentang Proyek
Ini adalah sistem backend (API & Admin Management) untuk ekosistem aplikasi RajaWash. Dibangun menggunakan framework Laravel modern untuk menangani logika bisnis utama, manajemen pengguna (termasuk kurir), perhitungan biaya laundry dinamis, dan sinkronisasi pembayaran.

## Teknologi Utama
- **Framework**: Laravel 13.x (PHP 8.3+)
- **Authentication**: Laravel Sanctum & Tymon JWT Auth (mendukung integrasi dengan otentikasi Supabase)
- **Database**: SQLite (default development) / MySQL / PostgreSQL dengan Eloquent ORM
- **Testing**: Pest PHP
- **Frontend Tools**: Terintegrasi dengan Vite untuk asset bundling web views

## Struktur Folder Utama
- `app/Http/Controllers/`: Menyimpan semua pengontrol logika API dan Web (mis. `PaymentWebController.php`).
- `app/Services/`: Berisi logika bisnis kompleks (mis. `PaymentService.php`) agar pengontrol tetap bersih.
- `app/Models/`: Model Eloquent untuk berinteraksi dengan database (User, Order, Payment, dll).
- `routes/`: Konfigurasi rute aplikasi (`api.php` untuk request dari mobile/web, `web.php` untuk tampilan blade/dashboard lokal).
- `database/`: Migrasi, Seeder, dan Factory untuk pembentukan skema database.

## Cara Menjalankan
1. Pastikan Anda telah menginstal PHP 8.3+ dan Composer.
2. Salin file environment: `cp .env.example .env`
3. Install dependencies: `composer install`
4. Generate key aplikasi: `php artisan key:generate`
5. Jalankan migrasi: `php artisan migrate`
6. Jalankan server lokal: `php artisan serve` atau gunakan `npm run dev` / `composer run dev` untuk menjalankan server, antrean (queue), dan vite secara bersamaan.
