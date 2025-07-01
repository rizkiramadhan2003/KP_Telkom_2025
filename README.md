# Sistem Pelaporan Fallout

Ini adalah aplikasi Laravel yang dirancang untuk mengelola laporan fallout, menampilkan bot Telegram untuk pengiriman laporan dan panel admin untuk manajemen data.

## Fitur

*   **Integrasi Bot Telegram**: Pengguna dapat mengirim laporan fallout langsung melalui bot Telegram.
*   **Penomoran Laporan Harian**: Pembuatan nomor laporan otomatis yang diatur ulang setiap hari (misalnya, `001`, `002`, dll., per hari). ID unik lengkap (`FR-YYYYMMDD-XXX`) disimpan di database.
*   **Peran Super Admin**: Peran super admin khusus dengan hak istimewa yang ditingkatkan.
*   **CRUD untuk Data Master**:
    *   Mengelola HD Daman (Kepala Daman).
    *   Mengelola Tipe Pesanan.
    *   Mengelola Status Fallout.
*   **Manajemen Pengguna HD Daman**: Pembuatan akun pengguna otomatis untuk setiap HD Daman, dengan email dan kata sandi yang dibuat secara otomatis berdasarkan nama dan tanggal lahir mereka.
*   **UI Responsif**: Panel admin dirancang dengan Tailwind CSS untuk pengalaman yang bersih dan responsif.
*   **Perbaikan Konten Campuran (Mixed Content)**: Dikonfigurasi untuk menangani HTTPS dengan benar saat di-deploy di belakang proxy seperti Ngrok.

## Prasyarat

Sebelum memulai, pastikan Anda memiliki hal-hal berikut yang terinstal di sistem Anda:

*   **PHP** (>= 8.4)
*   **Composer**
*   **Node.js** (>= 24.3)
*   **npm** 
*   **Aiven for MySQL** (udah ada di .env tidak perlu di migrate lagi)
*   **Ngrok** (untuk pengembangan lokal dengan webhook Telegram)

## Instalasi

1.  **Clone repositori:**
    ```bash
    git clone <repository_url>
    cd kp
    ```

2.  **Instal dependensi Composer:**
    ```bash
    composer install
    ```

3.  **Instal dependensi Node.js:**
    ```bash
    npm install
    ```

4.  **Salin file environment:**
    ```bash
    cp .env.example .env
    ```

5.  **Buat kunci aplikasi:**
    ```bash
    php artisan key:generate
    ```


6.  **Kompilasi aset front-end untuk produksi:**
    Langkah ini penting agar UI dapat dimuat dengan benar saat diakses melalui Ngrok atau di lingkungan produksi.
    ```bash
    npm run build
    ```
    *Catatan: Jika Anda secara aktif mengembangkan UI, Anda akan menjalankan `npm run dev` di terminal terpisah, tetapi untuk berbagi melalui Ngrok, `npm run build` lebih disukai.*

## Menjalankan Aplikasi

1.  **Mulai server pengembangan Laravel:**
    ```bash
    php artisan serve
    ```
    Ini biasanya akan berjalan di `http://127.0.0.1:8000`.

2.  **Mulai Ngrok (untuk webhook Bot Telegram):**
    Di jendela terminal baru, jalankan Ngrok untuk mengekspos server Laravel Anda ke internet:
    ```bash
    ngrok http 8000
    ```
    Ngrok akan memberi Anda URL HTTPS publik (misalnya, `https://xxxx-xxxx-xxxx-xxxx.ngrok-free.app`). Salin URL ini.

## Pengaturan Bot Telegram

1.  **Atur Webhook Telegram:**
    Ini memberi tahu Telegram ke mana harus mengirim pembaruan untuk bot Anda. **Anda harus menjalankan perintah ini setiap kali URL Ngrok Anda berubah.**
    ```bash
    php artisan telegram:set-webhook YOUR_NGROK_HTTPS_URL/telegram/webhook
    ```
    Ganti `YOUR_NGROK_HTTPS_URL` dengan URL HTTPS yang disediakan oleh Ngrok (misalnya, `https://xxxx-xxxx-xxxx-xxxx.ngrok-free.app`).

2.  **Gunakan Bot:**
    *   Buka bot Anda di Telegram.
    *   Kirim `/start` atau `/newreport` untuk memulai laporan fallout baru.
    *   Bot akan memandu Anda melalui proses pengiriman laporan. Nomor laporan akan dibuat secara otomatis setiap hari (misalnya, `01`, `02`, dll.).

## Akses Panel Admin

Akses panel admin dengan menavigasi ke URL aplikasi Anda (misalnya, `http://127.0.0.1:8000/dashboard` atau URL Ngrok Anda).

**Kredensial Super Admin:**
*   **Email**: `test@example.com`
*   **Kata Sandi**: `password` (default, Anda dapat mengubahnya setelah masuk)

Setelah masuk sebagai super admin, Anda akan melihat tautan "Admin" di bilah sisi:
*   **HD Damans**: `/hd-damans` (untuk mengelola HD Daman dan akun pengguna terkait)
*   **Tipe Pesanan**: `/order-types`
*   **Status Fallout**: `/fallout-statuses`

## Login Pengguna HD Daman

Ketika HD Daman baru dibuat melalui panel admin, akun pengguna yang sesuai akan dibuat secara otomatis:

*   **Email**: Berasal dari nama HD Daman (misalnya, "Daman A" menjadi `damana@tif.com`).
*   **Kata Sandi**: Berasal dari tanggal lahir HD Daman dalam format `YYMMDD` (misalnya, 30-07-2003 menjadi `030730`).

Pengguna ini dapat masuk menggunakan kredensial yang dibuat secara otomatis.

Yang sudah ada di database

*   **Email**: `muhammadrizky@tif.com`
*   **Kata Sandi**: `000830`

## Pemecahan Masalah

*   **Kesalahan `Mixed Content` (UI tidak memuat dengan benar melalui Ngrok HTTPS)**:
    Ini terjadi karena Laravel menghasilkan URL HTTP untuk aset. Perbaikan sudah diimplementasikan di `app/Providers/AppServiceProvider.php`. Pastikan Anda telah menjalankan `php artisan config:clear` setelah perubahan `.env` atau `AppServiceProvider`.
    ```bash
    php artisan config:clear
    ```
*   **Kesalahan `Column not found`**:
    Pastikan semua migrasi telah dijalankan dengan sukses. Jika Anda menambahkan kolom baru, jalankan:
    ```bash
    php artisan migrate
    ```
    Jika Anda mencurigai masalah database, Anda dapat mengatur ulang dan mengisi ulang:
    ```bash
    php artisan migrate:fresh --seed
    ```
*   **Bot Telegram tidak merespons**:
    *   Periksa `TELEGRAM_BOT_TOKEN` Anda di `.env`.
    *   Pastikan `php artisan serve` sedang berjalan.
    *   Pastikan Ngrok sedang berjalan dan menyediakan URL publik.
    *   **Yang terpenting, pastikan Anda telah mengatur webhook dengan URL HTTPS Ngrok yang benar dan terkini.**
    *   Periksa `storage/logs/laravel.log` untuk setiap kesalahan dari webhook Telegram.