# KlikKopi — Perkoci Eatery

> *Artisanal Coffee & Kitchen*

Sistem pemesanan kafe berbasis QR. Customer memesan sendiri dari meja tanpa perlu login, lalu pesanannya dikelola oleh **kasir**, **admin**, dan **manager** lewat dashboard masing-masing.

Proyek Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak, SMK Budi Bakti, Tahun Pelajaran 2026/2027.

| | |
|---|---|
| **Aplikasi online** | https://perkocieatery.my.id |
| **Hosting** | ArenHost (cPanel) |
| **Backend** | Meliza Meilani |
| **Frontend** | Sena |
| **Repository** | https://github.com/seinaaprl24-tech/PerkociEatery.git |
| **Branch utama** | `frontend` |

---

## Daftar isi

- [Aplikasi online](#aplikasi-online)
- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Alur pemesanan](#alur-pemesanan)
- [Instalasi lokal](#instalasi-lokal)
- [Konfigurasi `.env`](#konfigurasi-env)
- [Akun awal](#akun-awal)
- [Struktur proyek](#struktur-proyek)
- [Ringkasan API](#ringkasan-api)
- [Keamanan](#keamanan)
- [Pengujian](#pengujian)
- [Deploy ke hosting](#deploy-ke-hosting)
- [Status pengembangan](#status-pengembangan)

---

## Aplikasi online

Aplikasi sudah di-deploy di **ArenHost** dan dapat dibuka di:

**https://perkocieatery.my.id**

| Halaman | Alamat |
|---|---|
| Menu customer (contoh meja) | `https://perkocieatery.my.id/menu?meja=KODE_QR` |
| Status pesanan customer | `https://perkocieatery.my.id/status` |
| Login staff (kasir, admin, manager) | `https://perkocieatery.my.id/login` |

Kode QR tiap meja dibuat dari menu **Meja & QR Code** (admin) dan mengarah ke alamat di atas. Akun uji untuk penilaian diberikan terpisah oleh tim, tidak dicantumkan di sini.

---

## Fitur

### Customer (tanpa login)
- Scan QR di meja, nomor meja langsung dikenali sistem.
- Katalog menu dari database lengkap dengan foto, harga, dan stok.
- Keranjang belanja (tersimpan di browser), isi nama dan nomor WhatsApp, lalu kirim pesanan.
- Mendapat nomor antrean dan kode akses untuk melihat pesanannya sendiri.

### Kasir
- Dashboard: pesanan aktif, pesanan selesai hari ini, meja terisi (diperbarui otomatis tiap 20 detik).
- Daftar pesanan dengan filter status: proses, bayar, batalkan.
- Pembayaran tunai, QRIS, atau e-wallet dengan perhitungan kembalian otomatis.
- Struk pembayaran siap cetak (kertas thermal 58 mm).
- Status meja dan salin link QR.

### Admin
- Dashboard ringkasan: pemasukan, menu terbaru, pesanan terbaru, stok menipis, pengguna, meja.
- Kelola menu (tambah, ubah, hapus, upload foto).
- Kelola meja dan QR code (gambar QR dibuat otomatis, bisa dicetak).
- Kelola pengguna dan role.
- Kelola stok produk (restock, atur stok, batas minimum).

### Manager
- Dashboard: pemasukan hari ini dan bulan ini, grafik 7 hari, menu terlaris, transaksi terbaru.
- Laporan penjualan per periode.
- Keuangan: pemasukan per metode pembayaran dan per hari.
- Ekspor laporan ke CSV dan cetak PDF.

---

## Teknologi

| Bagian | Teknologi |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL |
| Autentikasi API | Laravel Sanctum (token) |
| Frontend | Blade, Tailwind CSS 4, JavaScript, Vite |
| QR code | qrserver.com (gambar QR) |

---

## Alur pemesanan

```
Customer scan QR  →  /menu?meja=KODE_QR
        ↓
Pilih menu → keranjang → isi nama & WhatsApp → Kirim Pesanan
        ↓
Pesanan tersimpan (status: menunggu), kode akses dikirim sekali ke browser
        ↓
Kasir: Proses → Terima pembayaran → Struk (status: selesai, lunas)
        ↓
Manager: pemasukan tampil di laporan
```

Nomor meja dibaca dari parameter `?meja=` oleh middleware `ResolveCustomerTable`, lalu disimpan di sesi.

---

## Instalasi lokal

**Prasyarat:** PHP 8.2+, Composer, Node.js dan npm, MySQL (misalnya lewat XAMPP).

```bash
# 1. Ambil kode
git clone https://github.com/seinaaprl24-tech/PerkociEatery.git
cd PerkociEatery
git checkout frontend

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan konfigurasi
cp .env.example .env
php artisan key:generate

# 4. Buat database kosong bernama klikkopi_perkoci di phpMyAdmin,
#    lalu sesuaikan DB_* di file .env

# 5. Buat tabel dan data awal
php artisan migrate --seed

# 6. Tautkan folder foto
php artisan storage:link

# 7. Jalankan (dua terminal)
npm run dev
php artisan serve
```

Buka **http://127.0.0.1:8000** di browser.

> Gunakan `127.0.0.1`, bukan `localhost`, supaya URL foto menu tampil benar (sesuai `APP_URL`).
> Jika tampilan terlihat polos atau berantakan, pastikan `npm run dev` sedang berjalan, atau jalankan `npm run build` sekali.

---

## Konfigurasi `.env`

```env
APP_NAME="Perkoci Eatery"
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klikkopi_perkoci
DB_USERNAME=root
DB_PASSWORD=

# Password akun awal (dibaca UserSeeder)
SEED_ADMIN_PASSWORD=
SEED_KASIR_PASSWORD=
SEED_MANAGER_PASSWORD=

# Opsional, hanya untuk pengembangan lokal:
# menganggap semua pengunjung duduk di meja tertentu
DEV_TABLE_QR=MEJA-01
```

Identitas kedai (nama, alamat, telepon untuk struk) diatur di `config/perkoci.php` pada bagian `brand`.

---

## Akun awal

Akun dibuat oleh `database/seeders/UserSeeder.php` untuk tiga role: **admin**, **kasir**, dan **manager**. Password diambil dari `SEED_ADMIN_PASSWORD`, `SEED_KASIR_PASSWORD`, dan `SEED_MANAGER_PASSWORD`. Jika tidak diisi, seeder memakai `SEED_STAFF_PASSWORD`, lalu `password`.

Login staff ada di `/login`. Setelah masuk, pengguna diarahkan ke dashboard sesuai rolenya. **Ganti password bawaan sebelum aplikasi dipakai secara nyata.**

---

## Struktur proyek

```
app/
  Http/
    Controllers/         # API: Pesanan, Pembayaran, Produk, Meja, User, Laporan, Stok, Auth
      Web/               # Login web dan halaman staff
    Middleware/          # ResolveCustomerTable, pembatasan role
    Requests/            # Validasi (StorePesanan, StoreMeja, StoreProduk, ...)
    Resources/           # Bentuk respons JSON
  Models/
config/perkoci.php       # Identitas kedai
database/
  migrations/
  seeders/
resources/views/
  layouts/staff.blade.php
  staff/{kasir,admin,manager}/   # Halaman dashboard per role
  partials/              # Penyimpan keranjang dan data bersama per role
  menu/ , keranjang , status     # Halaman customer
  errors/                # Halaman 403, 404, 500 kustom
routes/
  api.php                # Endpoint API
  web.php                # Halaman web
```

---

## Ringkasan API

Semua endpoint berawalan `/api`. Dokumentasi lengkap ada di [`API.md`](API.md).

**Publik (tanpa token)**

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/login` | Login staff (dibatasi 5 kali per menit) |
| GET | `/meja/validate/{kode_qr}` | Validasi kode QR meja |
| GET | `/produk`, `/produk/{produk}` | Daftar dan detail menu |
| POST | `/pesanan` | Buat pesanan (dibatasi 20 kali per menit) |
| GET | `/pesanan/{pesanan}?kode=...` | Lihat pesanan milik sendiri (butuh `kode_akses`) |
| GET | `/pesanan/{id}/struk?kode=...` | Data struk pesanan |

**Staff (butuh token Bearer, dibatasi per role)**

| Area | Contoh endpoint |
|---|---|
| Pesanan | `GET /pesanan?status=menunggu,diproses&per_page=50`, `PATCH /pesanan/{pesanan}/status` |
| Pembayaran | `GET` dan `POST /pembayaran` |
| Menu dan stok | CRUD `/produk`, `PATCH /produk/{produk}/stok` |
| Meja dan pengguna | CRUD `/meja`, CRUD `/users` |
| Laporan | `/laporan/...` untuk dashboard dan rekap |

---

## Keamanan

- **Role-based access control:** setiap endpoint staff dibatasi per role (admin, kasir, manager).
- **Anti-IDOR:** pesanan customer dilindungi `kode_akses` acak 12 karakter yang diperiksa dengan `hash_equals`. Tanpa kode yang sah, hasilnya 404.
- **Harga dari server:** harga item selalu diambil dari database, bukan dari data yang dikirim browser.
- **Transaksi aman:** stok dan pembayaran dikunci (`lockForUpdate`), nomor antrean unik, pesanan yang sudah selesai atau lunas tidak bisa dibatalkan atau dibayar ulang.
- **Pembatasan percobaan:** login dan pembuatan pesanan memakai throttle.
- **Validasi input dan upload:** foto produk divalidasi tipe dan ukurannya, output di-escape untuk mencegah XSS.
- **Integritas data:** meja atau pengguna yang punya riwayat transaksi tidak bisa dihapus (respons 409), password dan role yang berubah mencabut token lama.
- **Error handling:** dengan `APP_DEBUG=false`, error tidak membocorkan detail teknis dan detailnya hanya masuk ke `storage/logs/laravel.log`.

---

## Pengujian

```bash
php artisan test
```

Pengujian manual (Functional Testing Report, minimal 15 skenario) dan catatan bug didokumentasikan pada dokumen evidence Sprint 3.

---

## Deploy ke hosting

Aplikasi ini berjalan di **ArenHost** (cPanel) pada domain `perkocieatery.my.id`. Ringkasan langkah deploy:

1. Atur **document root** ke folder `public`.
2. Salin `.env.example` menjadi `.env`, lalu isi `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-kamu`, dan data database dari cPanel (`DB_HOST=localhost`).
3. `composer install --no-dev --optimize-autoloader`
4. Build aset di laptop (`npm run build`), lalu upload folder `public/build`. Jangan ikutkan `public/hot`.
5. `php artisan key:generate`, `php artisan migrate --force`, lalu `php artisan db:seed --force` bila perlu.
6. `php artisan storage:link`, dan pastikan `storage` serta `bootstrap/cache` dapat ditulis.
7. `php artisan optimize:clear`, lalu `php artisan config:cache`.

Pastikan file `.env` tidak bisa diakses dari browser (`https://perkocieatery.my.id/.env` harus menghasilkan 403 atau 404).

File `README.md` ini hanya untuk repository GitHub dan tidak perlu diunggah ke folder web di hosting.

---

## Status pengembangan

**Sudah berjalan**
- Alur lengkap customer, kasir, admin, dan manager, dari pesanan hingga laporan.
- Keamanan dasar, validasi, dan penanganan error.
- Foto produk, stok, QR meja, struk, serta ekspor laporan.

**Dalam pengerjaan atau rencana**
- Halaman status pesanan customer (`/status`) sedang disambungkan ke API agar menampilkan data nyata.
- Pengeluaran dan saldo bersih pada laporan keuangan manager.
- Pengaturan admin dan kategori produk sebagai tabel tersendiri.
- Penambahan tes otomatis untuk endpoint inti.

---

## Lisensi

Proyek pembelajaran untuk keperluan UKK. Hak cipta dipegang oleh tim pengembang.
