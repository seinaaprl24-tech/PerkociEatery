DOKUMENTASI REST API — PERKOCIEATERY
Spesifikasi Antarmuka Pemrograman Aplikasi (REST API) Sistem Pemesanan dan Manajemen PerkociEatery.

================================================================================
1. INFORMASI DASAR
================================================================================
- Base URL (Produksi)   : https://perkocieatery.my.id/api
- Format Data           : application/json
- Autentikasi           : Laravel Sanctum (Bearer Token)
- Header Wajib          : Accept: application/json
                          Content-Type: application/json
- Header Terproteksi    : Authorization: Bearer <token>

================================================================================
2. ATURAN KHUSUS & KEAMANAN
================================================================================
1. Pembatasan Request (Rate Limiting):
   - Endpoint Login (POST /login): Maksimal 5 percobaan per menit. Melebihi batas mengembalikan status 429 Too Many Requests.
   - Endpoint Buat Pesanan (POST /pesanan): Maksimal 20 pesanan per menit. Melebihi batas mengembalikan status 429 Too Many Requests.

2. Filter & Paginasi:
   - Daftar pesanan (GET /pesanan) mendukung filter query parameter ?status=<status> yang dapat dipisah koma (contoh: ?status=menunggu,diproses).
   - Daftar data mendukung paginasi menggunakan parameter ?per_page=<jumlah> dari 1 sampai 100 (bawaan 10).

3. Keamanan kode_akses Pesanan:
   - Field kode_akses (12 karakter acak) dibuat otomatis saat pesanan disimpan dan hanya dikirimkan satu kali pada respons 201 Created (POST /pesanan).
   - Akses customer ke GET /pesanan/{pesanan} dan GET /pesanan/{id}/struk mewajibkan query parameter ?kode=<kode_akses>. Jika token staf tidak ada dan kode salah atau kosong, server mengembalikan 404 Not Found agar ID pesanan tidak dapat ditebak.

================================================================================
3. DAFTAR ENDPOINT
================================================================================

A. Autentikasi & Profil
- POST      /login                  | Role: Publik (Staf)         | Autentikasi pengguna dan mendapatkan token Sanctum
- POST      /logout                 | Role: Admin, Manager, Kasir | Menghapus token aktif (keluar sesi)
- GET       /me                     | Role: Admin, Manager, Kasir | Mendapatkan data profil pengguna yang sedang login
- GET       /user                   | Role: Admin, Manager, Kasir | Alias dari /me untuk mengambil data pengguna aktif

B. Manajemen Pengguna (Users)
- GET       /users                  | Role: Admin, Manager        | Menampilkan daftar seluruh akun pengguna
- POST      /users                  | Role: Admin, Manager        | Menambahkan akun pengguna baru
- GET       /users/{user}           | Role: Admin, Manager        | Menampilkan detail akun pengguna tertentu
- PUT/PATCH /users/{user}           | Role: Admin, Manager        | Memperbarui data atau role pengguna
- DELETE    /users/{user}           | Role: Admin, Manager        | Menghapus akun pengguna

C. Manajemen Meja & QR Code
- GET       /meja                   | Role: Publik / Semua Role   | Menampilkan daftar seluruh meja
- POST      /meja                   | Role: Admin, Manager        | Menambahkan data meja baru
- GET       /meja/{meja}            | Role: Publik / Semua Role   | Menampilkan detail meja berdasarkan ID
- PUT/PATCH /meja/{meja}            | Role: Admin, Manager        | Memperbarui informasi atau status meja
- DELETE    /meja/{meja}            | Role: Admin, Manager        | Menghapus data meja
- GET       /meja/qr/{kode_qr}      | Role: Customer (Publik)     | Validasi kode QR meja saat customer memindai
- GET       /meja/validate/{kode_qr}| Role: Customer (Publik)     | Alias validasi kode QR meja

D. Produk & Manajemen Stok
- GET       /produk                 | Role: Publik / Semua Role   | Menampilkan daftar menu/produk
- POST      /produk                 | Role: Admin, Manager        | Menambahkan menu/produk baru
- GET       /produk/{produk}        | Role: Publik / Semua Role   | Menampilkan detail menu/produk
- PUT/PATCH /produk/{produk}        | Role: Admin, Manager        | Memperbarui informasi menu/produk
- DELETE    /produk/{produk}        | Role: Admin, Manager        | Menghapus menu/produk
- PATCH     /produk/{produk}/stok   | Role: Admin                 | Memperbarui jumlah_stok, tambah, atau min_stok produk

E. Transaksi Pesanan
- GET       /pesanan                | Role: Admin, Manager, Kasir | Menampilkan daftar pesanan (?status=, ?per_page=)
- POST      /pesanan                | Role: Customer, Kasir       | Membuat pesanan baru (mengembalikan kode_akses sekali)
- GET       /pesanan/{pesanan}      | Role: Customer (?kode=), Staf | Melihat detail & status pesanan
- PATCH     /pesanan/{pesanan}/status | Role: Admin, Manager, Kasir | Mengubah status_pesanan (menunggu, diproses, selesai, batal)
- DELETE    /pesanan/{pesanan}      | Role: Admin, Manager, Kasir | Membatalkan pesanan (mengembalikan stok) atau menghapus pesanan batal
- GET       /pesanan/{id}/struk     | Role: Customer (?kode=), Staf | Mengambil data struk pesanan

F. Pembayaran
- GET       /pembayaran             | Role: Admin, Manager, Kasir | Menampilkan riwayat transaksi pembayaran
- POST      /pembayaran             | Role: Kasir, Admin          | Memproses pembayaran pesanan & mengubah status pesanan menjadi selesai
- GET       /pembayaran/{pembayaran}| Role: Admin, Manager, Kasir | Menampilkan detail transaksi pembayaran
- DELETE    /pembayaran/{pembayaran}| Role: Admin, Manager        | Membatalkan pembayaran & mengembalikan status pesanan ke diproses

G. Laporan & Statistik
- GET       /laporan/daily-stats    | Role: Manager, Admin        | Mengambil ringkasan statistik harian
- GET       /laporan/monthly-income | Role: Manager, Admin        | Mengambil data pendapatan bulanan
- GET       /laporan/penjualan      | Role: Manager, Admin        | Mengambil laporan detail penjualan
- GET       /laporan/top-products   | Role: Manager, Admin        | Mengambil daftar produk terlaris

================================================================================
4. CONTOH REQUEST & RESPONSE ENDPOINT INTI
================================================================================

4.1 Login (POST /login)
Request Body:
{
  "email": "kasir@perkocieatery.my.id",
  "password": "password123"
}

Response (200 OK):
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 2,
      "name": "Kasir Utama",
      "email": "kasir@perkocieatery.my.id",
      "role": "kasir"
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890"
  }
}

--------------------------------------------------------------------------------
4.2 Buat Pesanan (POST /pesanan)
Catatan: Field kode_akses hanya dikirimkan satu kali pada respons ini. Simpan di sisi client customer untuk mengakses status pesanan dan struk.

Request Body:
{
  "id_meja": 4,
  "nama_pemesan": "Budi Santoso",
  "no_whatsapp": "081234567890",
  "items": [
    {
      "id_produk": 1,
      "jumlah": 2,
      "gula": "Less Sugar",
      "es": "Normal Ice",
      "catatan": "Kopi jangan terlalu pahit"
    },
    {
      "id_produk": 5,
      "jumlah": 1
    }
  ]
}

Response (201 Created):
{
  "data": {
    "id_pesanan": 108,
    "id_meja": 4,
    "nomor_antrean": "ORD-20261115-001",
    "nama_pemesan": "Budi Santoso",
    "no_whatsapp": "081234567890",
    "kode_akses": "aB9kLm2PqR7x",
    "tanggal_pesan": "2026-11-15T07:00:00.000000Z",
    "total_harga": 65000,
    "status_pesanan": "menunggu",
    "detail_pesanan": [
      {
        "id_produk": 1,
        "jumlah": 2,
        "harga": 20000,
        "subtotal": 40000,
        "gula": "Less Sugar",
        "es": "Normal Ice",
        "catatan": "Kopi jangan terlalu pahit"
      },
      {
        "id_produk": 5,
        "jumlah": 1,
        "harga": 25000,
        "subtotal": 25000
      }
    ]
  }
}

--------------------------------------------------------------------------------
4.3 Lihat Detail Pesanan (GET /pesanan/{pesanan}?kode=aB9kLm2PqR7x)
Request URL:
GET https://perkocieatery.my.id/api/pesanan/108?kode=aB9kLm2PqR7x

Response (200 OK):
{
  "data": {
    "id_pesanan": 108,
    "id_meja": 4,
    "nomor_antrean": "ORD-20261115-001",
    "nama_pemesan": "Budi Santoso",
    "status_pesanan": "diproses",
    "total_harga": 65000,
    "detail_pesanan": [
      {
        "id_produk": 1,
        "jumlah": 2,
        "harga": 20000,
        "subtotal": 40000
      }
    ],
    "pembayaran": null
  }
}

--------------------------------------------------------------------------------
4.4 Ubah Status Pesanan (PATCH /pesanan/{pesanan}/status)
Header: Authorization: Bearer <token>
Nilai status_pesanan yang valid: menunggu, diproses, selesai, batal

Request Body:
{
  "status_pesanan": "diproses"
}

Response (200 OK):
{
  "data": {
    "id_pesanan": 108,
    "nomor_antrean": "ORD-20261115-001",
    "status_pesanan": "diproses",
    "total_harga": 65000
  }
}

--------------------------------------------------------------------------------
4.5 Proses Pembayaran (POST /pembayaran)
Header: Authorization: Bearer <token>
Nilai metode_pembayaran yang valid: tunai, qris, transfer, e-wallet

Request Body:
{
  "pesanan_id": 108,
  "metode_pembayaran": "tunai",
  "jumlah_bayar": 100000
}

Response (201 Created):
{
  "data": {
    "id_pembayaran": 89,
    "id_pesanan": 108,
    "jumlah_bayar": 100000,
    "kembalian": 35000,
    "status_pembayaran": "lunas",
    "tanggal_pembayaran": "2026-11-15T07:12:00.000000Z"
  }
}

--------------------------------------------------------------------------------
4.6 Update Stok Produk (PATCH /produk/{produk}/stok)
Header: Authorization: Bearer <token> (Khusus role admin)
Parameter Body (isi minimal satu): jumlah_stok, tambah, atau min_stok.

Request Body:
{
  "jumlah_stok": 25,
  "min_stok": 5
}

Response (200 OK):
{
  "message": "Stok berhasil diperbarui.",
  "data": {
    "id_produk": 1,
    "nama_produk": "Kopi Susu Perkoci",
    "stok": {
      "id_stok": 1,
      "jumlah_stok": 25,
      "min_stok": 5,
      "updated_at": "2026-11-15 07:15:00"
    }
  }
}

--------------------------------------------------------------------------------
4.7 Laporan Penjualan (GET /laporan/penjualan)
Header: Authorization: Bearer <token>

Request URL:
GET https://perkocieatery.my.id/api/laporan/penjualan?tanggal_mulai=2026-11-01&tanggal_selesai=2026-11-15

Response (200 OK):
{
  "status": "success",
  "data": {
    "periode": {
      "mulai": "2026-11-01",
      "selesai": "2026-11-15"
    },
    "total_transaksi": 142,
    "total_pendapatan": 8450000
  }
}

================================================================================
5. KODE STATUS HTTP & FORMAT ERROR
================================================================================

1. 401 Unauthorized (Tidak Terautentikasi)
   Penyebab: Token Sanctum tidak dikirim, tidak valid, atau sudah kedaluwarsa.
   Contoh Response:
   {
     "message": "Unauthenticated."
   }

2. 403 Forbidden (Akses Ditolak)
   Penyebab: Role (customer, kasir, admin, manager) tidak memiliki hak akses pada endpoint tersebut.
   Contoh Response:
   {
     "message": "Forbidden."
   }

3. 404 Not Found (Tidak Ditemukan)
   Penyebab: Data tidak ditemukan, atau akses customer ke /pesanan/{id} tanpa kode_akses yang valid.
   Contoh Response:
   {
     "message": "No query results for model [App\\Models\\Pesanan]."
   }

4. 409 Conflict (Konflik Data)
   Penyebab: Mencoba menghapus/membatalkan pesanan (DELETE /pesanan/{id}) yang statusnya sudah selesai (sudah dibayar).
   Contoh Response:
   {
     "message": "Pesanan yang sudah dibayar tidak dapat dibatalkan atau dihapus."
   }

5. 422 Unprocessable Entity (Validasi Gagal)
   Penyebab: Input tidak valid, stok tidak mencukupi, jumlah_bayar kurang dari total_harga, atau pesanan sudah lunas.
   Contoh Response:
   {
     "message": "Stok untuk Kopi Susu Perkoci tidak mencukupi.",
     "errors": {
       "items": [
         "Stok untuk Kopi Susu Perkoci tidak mencukupi."
       ]
     }
   }

6. 429 Too Many Requests (Melebihi Batas Request)
   Penyebab: Terkena rate limit (POST /login > 5x/menit atau POST /pesanan > 20x/menit).
   Contoh Response:
   {
     "message": "Too Many Attempts."
   }
