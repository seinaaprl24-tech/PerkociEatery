# Dokumentasi API — PerkociEatery (KlikKopi)

Dokumen ini untuk tim frontend. Isinya endpoint yang sudah jadi di backend (Laravel 12 + Sanctum), siapa yang boleh mengakses, dan bentuk request/respons.

Tanda di tabel: ✅ = sudah diuji langsung dengan data nyata, 📄 = dibuat dari membaca kode, belum diuji.

---

## 1. Dasar

**Base URL (lokal):** `http://127.0.0.1:8000/api`

**Header untuk semua request:**

```
Accept: application/json
Content-Type: application/json
```

**Header tambahan untuk endpoint staff:**

```
Authorization: Bearer <token dari /api/login>
```

**Role:** `admin`, `kasir`, `manager`.

**Uang** selalu berupa angka (bukan string), contoh `16000`.

**Status pesanan:** `menunggu`, `diproses`, `selesai`, `batal`.

**Pagination:** daftar dibagi 10 data per halaman. Respons berbentuk `{ "data": [...], "links": {...}, "meta": {...} }`. Ambil halaman lain dengan `?page=2`. Gunakan `meta.last_page` untuk tahu jumlah halaman.

**Batas request (throttle):**

| Endpoint | Batas |
|---|---|
| `POST /api/login` | 5 per menit |
| `POST /api/pesanan` | 20 per menit |

Kalau terlampaui, respons `429 Too Many Requests`.

### Format error

| Status | Arti | Contoh body |
|---|---|---|
| 401 | Belum login / token salah | `{"message":"Unauthenticated."}` |
| 403 | Login, tapi role tidak boleh | `{"message":"Anda tidak memiliki akses ke halaman ini."}` |
| 404 | Data tidak ditemukan | `{"message":"..."}` |
| 422 | Validasi gagal | `{"message":"...","errors":{"field":["pesan"]}}` |
| 429 | Terlalu banyak request | — |

---

## 2. Alur Customer (self order via QR)

Semua endpoint di bagian ini **tanpa login**.

1. Customer scan QR meja → panggil `GET /api/meja/validate/{qr_code}` untuk mendapatkan `id_meja`.
2. Ambil menu → `GET /api/produk` (ambil semua halaman).
3. Kirim pesanan → `POST /api/pesanan`. Simpan `id_pesanan` dari respons.
4. Pantau status → `GET /api/pesanan/{id_pesanan}` (polling berkala).
5. Setelah dibayar kasir → `GET /api/pesanan/{id_pesanan}/struk`.

### Validasi QR meja

`GET /api/meja/validate/{qr_code}` (alias: `GET /api/meja/qr/{qr_code}`) 📄

Nilai `{qr_code}` adalah kolom `qr_code` pada meja, contoh `MEJA-01`.

Respons 200:

```json
{
  "status": "success",
  "data": { "id_meja": 1, "no_meja": "01", "qr_code": "MEJA-01", "status": "tersedia" }
}
```

Respons 404: `{"message":"Meja tidak ditemukan atau QR tidak valid."}`

### Daftar menu

`GET /api/produk` ✅ · `GET /api/produk/{id_produk}` 📄

Total saat ini 16 produk, jadi menu ada di `?page=1` dan `?page=2`.

```json
{
  "data": [
    {
      "id_produk": 11,
      "nama_produk": "Nasi",
      "kategori": "Makanan Utama",
      "harga": 4000,
      "deskripsi": null,
      "status": "aktif",
      "stok": { "id_stok": 11, "jumlah_stok": 50, "min_stok": 0 }
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 10, "total": 16 }
}
```

Kategori yang ada di data saat ini: `Signature Coffee`, `Non-Kopi & Teh`, `Makanan Utama`, `Snack & Dessert`.

Catatan: `status` produk bisa bukan `aktif`; sembunyikan produk yang tidak aktif atau stoknya 0 di tampilan customer.

### Membuat pesanan

`POST /api/pesanan` ✅ (status 201)

```json
{
  "id_meja": 1,
  "items": [
    { "id_produk": 11, "jumlah": 2 },
    { "id_produk": 9, "jumlah": 1 }
  ]
}
```

**Penting tentang harga:** jangan kirim atau hitung harga dari sisi client. Server selalu mengambil harga dari tabel produk dan menghitung `total_harga` sendiri. `harga_satuan` boleh dihilangkan; kalau tetap dikirim, nilainya diabaikan (sudah diuji dengan `harga_satuan: 1`, total tetap benar). Tampilkan total dari respons server.

Batas: maksimal 50 baris item, `jumlah` 1–99 per item. Field opsional per item: `topping`, `gula`, `es`, `catatan`, `customisasi`. Formatnya mengikuti validasi `StorePesananRequest` (belum didokumentasikan di sini).

Respons 201:

```json
{
  "data": {
    "id_pesanan": 1,
    "id_meja": 1,
    "meja": { "id_meja": 1, "no_meja": "01", "status": "tersedia" },
    "nomor_antrean": "ORD-20261026-001",
    "tanggal_pesan": "2026-10-26T08:28:53.000000Z",
    "total_harga": 16000,
    "status_pesanan": "menunggu",
    "detail_pesanan": [
      {
        "id_detail": 1,
        "id_produk": 11,
        "nama_produk": "Nasi",
        "harga_satuan": 4000,
        "jumlah": 2,
        "subtotal": 8000,
        "topping": null, "gula": null, "es": null, "catatan": null,
        "produk": { "id_produk": 11, "nama_produk": "Nasi", "harga": 4000 }
      }
    ],
    "items": [ "...sama persis dengan detail_pesanan..." ]
  }
}
```

Catatan bentuk data: `items` isinya sama dengan `detail_pesanan`. Di setiap item, `qty` = `jumlah` dan `harga` = `harga_satuan`. Pakai salah satu saja secara konsisten.

Error yang mungkin:

| Status | Penyebab | Body |
|---|---|---|
| 422 | Stok tidak cukup | `{"errors":{"items":["Stok untuk Nasi tidak mencukupi."]}}` |
| 422 | Field tidak valid | `errors` berisi nama field |
| 429 | Lebih dari 20 pesanan per menit | — |

Stok otomatis berkurang saat pesanan dibuat dan dikembalikan jika pesanan dibatalkan.

### Status pesanan & struk

`GET /api/pesanan/{id_pesanan}` 📄 — bentuk respons sama dengan respons pembuatan pesanan (`data` berisi pesanan lengkap).

`GET /api/pesanan/{id_pesanan}/struk` 📄

```json
{
  "status": "success",
  "data": {
    "info_coffee_shop": { "nama": "PerkociEatery", "alamat": "-", "telepon": "-" },
    "nomor_antrean": "ORD-20261026-001",
    "nama_meja": "01",
    "daftar_item": [ "..." ],
    "total_harga": 16000,
    "metode_pembayaran": "Tunai",
    "kembalian": 4000,
    "tanggal": "2026-10-26T08:28:53.000000Z",
    "pembayaran": { "...": "null jika belum dibayar" }
  }
}
```

Sebelum dibayar, `metode_pembayaran` dan `kembalian` bernilai `null`.

---

## 3. Autentikasi Staff

### Login

`POST /api/login` ✅

```json
{ "username": "kasir", "password": "...", "device_name": "kasir-1" }
```

`device_name` opsional.

Respons 200:

```json
{
  "message": "Login berhasil.",
  "user": { "id_user": 2, "nama": "Kasir Perkoci", "username": "kasir", "role": "kasir" },
  "token": "<simpan, kirim sebagai Bearer token>"
}
```

Salah username/password → `422` dengan `errors.username`. Lebih dari 5 percobaan per menit → `429`.

### Lainnya

| Endpoint | Fungsi | Status |
|---|---|---|
| `POST /api/logout` | Hapus token yang sedang dipakai | 📄 |
| `GET /api/me` atau `GET /api/user` | Data user yang sedang login | 📄 |

Akun staff dibuat oleh admin lewat `POST /api/users`. Tidak ada pendaftaran publik.

Akun bawaan dari seeder: `admin`, `kasir`, `manager`. Password-nya diatur di `.env` (`SEED_STAFF_PASSWORD`); tanyakan ke backend.

---

## 4. Endpoint Staff

Semua butuh header `Authorization: Bearer <token>`.

### Kasir & Admin

| Method | Endpoint | Fungsi | Status |
|---|---|---|---|
| GET | `/api/meja` | Daftar meja (10 per halaman) | ✅ |
| GET | `/api/meja/{id_meja}` | Detail meja | 📄 |
| GET | `/api/pesanan` | Daftar pesanan, terbaru dulu. Opsional: `?status=menunggu,diproses` dan `?per_page=1..100` | 📄 |
| PATCH | `/api/pesanan/{id}/status` | Ubah status pesanan | 📄 |
| DELETE | `/api/pesanan/{id}` | Batalkan pesanan (stok dikembalikan); panggilan kedua pada pesanan batal akan menghapusnya | 📄 |
| GET | `/api/pembayaran` | Riwayat pembayaran | 📄 |
| GET | `/api/pembayaran/{id}` | Detail pembayaran | 📄 |
| POST | `/api/pembayaran` | Bayar pesanan | ✅ |
| DELETE | `/api/pembayaran/{id}` | Batalkan pembayaran (pesanan kembali `diproses`) | 📄 |

**Ubah status pesanan:**

```json
{ "status_pesanan": "diproses" }
```

Nilai yang diterima: `menunggu`, `diproses`, `selesai`, `batal`. Pesanan yang sudah `batal` tidak bisa diubah lagi (422). Mengubah ke `batal` otomatis mengembalikan stok.

**Bayar pesanan:**

```json
{ "pesanan_id": 1, "jumlah_bayar": 20000, "metode_pembayaran": "tunai" }
```

`metode_pembayaran` yang diterima: `tunai`, `qris`, `transfer`, `e-wallet`.

Respons 201:

```json
{
  "data": {
    "id_pembayaran": 1,
    "id_pesanan": 1,
    "pesanan": { "id_pesanan": 1, "nomor_antrean": "ORD-20261026-001", "total_harga": 16000, "status_pesanan": "selesai" },
    "metode_pembayaran": "Tunai",
    "jumlah_bayar": 20000,
    "kembalian": 4000,
    "status_pembayaran": "lunas",
    "tanggal_pembayaran": "2026-10-26T08:29:38.000000Z"
  }
}
```

Setelah dibayar, status pesanan otomatis menjadi `selesai`.

Error 422 yang mungkin (di `errors`):

| Field | Pesan |
|---|---|
| `pesanan_id` | Pesanan tidak ditemukan / Pesanan tidak dapat dibayar / Pembayaran untuk pesanan ini sudah ada |
| `jumlah_bayar` | Jumlah bayar kurang dari total harga |
| `metode_pembayaran` | Metode pembayaran tidak dikenal |

### Admin saja

| Method | Endpoint | Fungsi | Status |
|---|---|---|---|
| POST | `/api/produk` | Tambah produk | 📄 |
| PUT/PATCH | `/api/produk/{id}` | Ubah produk | 📄 |
| DELETE | `/api/produk/{id}` | Hapus produk | 📄 |
| POST | `/api/meja` | Tambah meja | 📄 |
| PUT/PATCH | `/api/meja/{id}` | Ubah meja | 📄 |
| DELETE | `/api/meja/{id}` | Hapus meja | 📄 |
| GET | `/api/users` | Daftar user | ✅ |
| POST | `/api/users` | Tambah user | 📄 |
| GET | `/api/users/{id}` | Detail user | 📄 |
| PUT/PATCH | `/api/users/{id}` | Ubah user | ✅ |
| DELETE | `/api/users/{id}` | Hapus user | 📄 |

Isi body produk dan meja mengikuti `StoreProdukRequest` dan `StoreMejaRequest` (belum dicantumkan di sini). Untuk meja, `kode_qr`/`qr_code` boleh dikosongkan, server membuat kode acak.

**Tambah user:**

```json
{ "nama": "Budi", "username": "budi", "password": "minimal6", "role": "kasir" }
```

`role`: `admin`, `kasir`, atau `manager`. Password minimal 6 karakter.

**Ubah user:** semua field opsional. `password` kosong berarti password tidak diganti. Mengganti password atau role membuat semua token user itu dihapus (harus login ulang).

**Hapus user:** `422` jika menghapus akun sendiri, `409` jika user sudah punya data transaksi.

### Manager & Admin — Laporan

| Method | Endpoint | Fungsi | Status |
|---|---|---|---|
| GET | `/api/laporan/daily-stats` | Statistik hari ini | ✅ |
| GET | `/api/laporan/top-products` | 5 produk terlaris | 📄 |
| GET | `/api/laporan/monthly-income` | Pemasukan bulan ini | 📄 |
| GET | `/api/laporan/penjualan?start_date=...&end_date=...` | Laporan per periode | 📄 |

Contoh `daily-stats`:

```json
{ "tanggal": "2026-10-26", "total_pemasukan": 16000, "jumlah_pesanan_diproses": 0, "jumlah_pesanan_selesai": 1 }
```

`top-products`: `{ "data": [{ "id_produk", "nama_produk", "kategori", "harga", "total_terjual", "total_pendapatan" }] }`

`monthly-income`: `{ "bulan": "2026-10", "total_pemasukan": 16000 }`

`penjualan`: format tanggal `YYYY-MM-DD`; `end_date` tidak boleh sebelum `start_date`. Respons:

```json
{
  "status": "success",
  "start_date": "2026-10-01",
  "end_date": "2026-10-26",
  "total_transaksi": 1,
  "total_pendapatan": 16000,
  "data": [ "...pesanan selesai lengkap dengan meja, detail_pesanan, pembayaran..." ]
}
```

**Cara hitung:** pemasukan = `jumlah_bayar` dikurangi `kembalian` dari pembayaran berstatus `lunas`. Hanya pesanan `selesai` yang dihitung di laporan.

---

## 5. Login Halaman Web (Blade) vs API

Ada dua sistem login yang berdiri terpisah:

| | Halaman web (Blade) | API |
|---|---|---|
| Login | `POST /login` (form, session) | `POST /api/login` (token) |
| Field | `username`, `password`, `remember` | `username`, `password` |
| Setelah login | Redirect ke `/admin`, `/kasir`, atau `/manager` sesuai role | Mengembalikan `token` |
| Logout | `POST /logout` (form dengan `@csrf`) | `POST /api/logout` |

Login lewat halaman web **belum otomatis** memberi akses ke API. Cara halaman dashboard memanggil API (memakai token Bearer atau cookie session) **belum diputuskan** dan perlu disepakati antara backend dan frontend sebelum dashboard dihubungkan ke data nyata.

Dashboard `/admin`, `/kasir`, `/manager` saat ini masih menampilkan data contoh dari `config/preview.php`.

---

## 6. Hal yang Belum Lengkap

- Isi body `POST/PUT /api/produk` dan `/api/meja` (tergantung Form Request).
- Format `topping`, `gula`, `es`, `customisasi` pada item pesanan.
- Status meja (`tersedia`/terisi) belum berubah otomatis saat ada pesanan.
- Struk dan status pesanan masih bisa dibuka siapa saja yang tahu `id_pesanan`.
- Tabel `transaksi`, `pemasukan`, `pengeluaran` dari ERD belum terisi oleh alur pembayaran.
