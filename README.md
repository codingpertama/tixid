# 🎟️ TIX ID Clone (Laravel 12)

Sebuah aplikasi pemesanan tiket bioskop berbasis web yang terinspirasi dari TIX ID. Proyek ini dibangun menggunakan **Laravel 12** dan mengimplementasikan berbagai fitur esensial seperti pemilihan kursi, pembayaran, pembuatan barcode tiket, hingga manajemen bioskop dengan berbagai hak akses (Admin, Staff, dan User).

---

## 🚀 Fitur Utama

Aplikasi ini memiliki sistem otentikasi dan otorisasi dengan tiga peran pengguna (Role):

### 1. 👤 User (Pengguna Biasa)
- **Beranda & Jadwal**: Melihat daftar film yang sedang tayang (aktif) beserta jadwal pemutarannya di berbagai bioskop.
- **Pemesanan Tiket**: Memilih kursi (seat selection) yang tersedia untuk jadwal tertentu.
- **Pembayaran**: Melakukan proses pembayaran, di mana sistem akan diintegrasikan dengan pembuatan barcode pembayaran.
- **Manajemen Tiket**: Melihat riwayat atau daftar tiket yang telah dipesan.
- **Export PDF**: Mengunduh tiket yang sudah dibayar dalam format PDF.

### 2. 🛡️ Admin (Administrator)
- **Dashboard & Analitik**: Melihat grafik (chart) penjualan tiket.
- **Kelola Data Bioskop (Cinemas)**: CRUD data bioskop, export ke Excel, soft-deletes (Trash/Restore/Permanent Delete).
- **Kelola Pengguna (Users)**: CRUD data pengguna (admin dan staff), export data, soft-deletes.
- **Kelola Film (Movies)**: CRUD data film, toggle status aktif/tidak aktif, export data, soft-deletes.
- Menggunakan **Yajra DataTables** untuk manajemen data yang cepat dan dinamis.

### 3. 🎬 Staff
- **Kelola Promo**: CRUD data promo, export Excel, soft-deletes.
- **Kelola Jadwal (Schedules)**: Membuat dan mengatur jadwal tayang film di bioskop tertentu, beserta export dan soft-deletes.

---

## 🛠️ Teknologi & Library yang Digunakan

- **Framework:** Laravel 12 (PHP ^8.2)
- **Frontend:** Blade Templates, TailwindCSS v4, Vite
- **Database:** MySQL / SQLite
- **Package Tambahan:**
  - `barryvdh/laravel-dompdf` (Export Tiket ke PDF)
  - `maatwebsite/excel` (Export Data ke Excel)
  - `simplesoftwareio/simple-qrcode` (Pembuatan Barcode / QR Code Tiket)
  - `yajra/laravel-datatables-oracle` (Integrasi Server-side DataTables)

---

## ⚙️ Panduan Instalasi (Local Development)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di perangkat lokal:

1. **Clone Repository**
   ```bash
   git clone <url-repo-anda>
   cd tixid
   ```

2. **Install Dependencies (PHP & Node.js)**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   Salin file konfigurasi bawaan dan sesuaikan kredensial database Anda.
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` dan atur bagian database (misal MySQL):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tixid_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database & Seeder** (Jika ada data dummy)
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Aplikasi**
   Untuk kemudahan, proyek ini menggunakan *Vite* sebagai build tool. Anda perlu menjalankan server PHP dan Vite secara bersamaan. (Atau gunakan script `npm run dev` jika menggunakan perintah concurrently bawaan).

   *Terminal 1:*
   ```bash
   php artisan serve
   ```
   *Terminal 2:*
   ```bash
   npm run dev
   ```

7. **Akses Aplikasi**
   Buka browser dan navigasi ke: `http://localhost:8000`

---

## 📂 Struktur Direktori Penting

- `app/Http/Controllers/` : Berisi controller utama (MovieController, TicketController, CinemaController, dll).
- `routes/web.php` : Berisi rute aplikasi yang sudah dibagi berdasarkan middleware (`isUser`, `isAdmin`, `isStaff`).
- `resources/views/` : File template antarmuka, dibagi menjadi direktori `admin`, `staff`, `auth`, `ticket`, `schedule`, dll.

---

## 📝 Lisensi
Dibuat untuk tujuan pembelajaran dan pengembangan portfolio. (MIT License - sesuai bawaan Laravel).
