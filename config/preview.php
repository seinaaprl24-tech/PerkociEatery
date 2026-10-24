<?php

/*
|--------------------------------------------------------------------------
| Preview Frontend Staff — Data Contoh (Tahap 2)
|--------------------------------------------------------------------------
| SELURUH data di file ini adalah contoh untuk kerangka tampilan staff.
| Belum ada autentikasi, database, atau endpoint backend. Nilai, nama, dan
| angka di sini tidak berasal dari Figma dan hanya untuk mengisi struktur.
|
| TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi.
|
| File ini (beserta PreviewController dan route /preview/*) bersifat
| sementara dan boleh dihapus saat desain final serta backend tersedia.
*/

return [

    'notice' => 'Kerangka tampilan staff. Data di halaman ini hanya contoh dan autentikasi belum terhubung.',

    'figma_note' => 'Susunan kartu, grafik, dan tabel masih sementara. Detail visual final menunggu frame Figma.',

    'roles' => [

        /* ------------------------------------------------------------------
         | MANAGER
         | ------------------------------------------------------------------ */
        'manager' => [
            'label' => 'Manager',
            'title' => 'Dashboard Manager',
            'eyebrow' => 'Manager Panel',
            'subtitle' => 'Ringkasan operasional dan performa penjualan.',

            'stats' => [
                [
                    'label' => 'Pendapatan Hari Ini',
                    'value' => 'Rp 8.450.000',
                    'icon' => 'payments',
                    'tone' => 'brand',
                    'trend' => '+12,4%',
                    'trend_up' => true,
                    'hint' => 'vs kemarin',
                ],
                [
                    'label' => 'Pesanan Selesai',
                    'value' => '128',
                    'icon' => 'receipt_long',
                    'tone' => 'lime',
                    'trend' => '+8,1%',
                    'trend_up' => true,
                    'hint' => 'vs kemarin',
                ],
                [
                    'label' => 'Rata-rata / Pesanan',
                    'value' => 'Rp 54.000',
                    'icon' => 'trending_up',
                    'tone' => 'butter',
                    'hint' => 'dari 128 transaksi',
                ],
                [
                    'label' => 'Meja Terisi',
                    'value' => '6 / 12',
                    'icon' => 'table_restaurant',
                    'tone' => 'cocoa',
                    'hint' => '50% okupansi',
                ],
            ],

            'chart' => [
                'title' => 'Tren Penjualan 7 Hari',
                'description' => 'Kerangka grafik dengan skala relatif (data contoh).',
                'total' => 'Rp 28.450.000',
                'bars' => [
                    ['label' => 'Sen', 'value' => 32],
                    ['label' => 'Sel', 'value' => 45],
                    ['label' => 'Rab', 'value' => 38],
                    ['label' => 'Kam', 'value' => 60],
                    ['label' => 'Jum', 'value' => 78],
                    ['label' => 'Sab', 'value' => 92],
                    ['label' => 'Min', 'value' => 71],
                ],
            ],

            'orders' => [
                'title' => 'Pesanan Terbaru',
                'description' => 'Contoh empat transaksi terakhir.',
                'rows' => [
                    [
                        'id' => '#PK-9821',
                        'table' => 'Meja 08',
                        'customer' => 'Budi Santoso',
                        'menu' => 'Kopsuksar · Sate Maranggi · Matcha Latte',
                        'total' => 54000,
                        'status' => ['label' => 'Diproses', 'tone' => 'butter'],
                    ],
                    [
                        'id' => '#PK-9820',
                        'table' => 'Meja 03',
                        'customer' => 'Sari Dewi',
                        'menu' => 'Matcha Latte · Es Jeruk',
                        'total' => 26000,
                        'status' => ['label' => 'Selesai', 'tone' => 'lime'],
                    ],
                    [
                        'id' => '#PK-9819',
                        'table' => 'Meja 11',
                        'customer' => 'Andi Pratama',
                        'menu' => 'Nasi Ayam Goreng · Dimsum · Es Coklat',
                        'total' => 53000,
                        'status' => ['label' => 'Menunggu Bayar', 'tone' => 'brand'],
                    ],
                    [
                        'id' => '#PK-9818',
                        'table' => 'Meja 05',
                        'customer' => 'Maya Lestari',
                        'menu' => 'Teh Manis',
                        'total' => 5000,
                        'status' => ['label' => 'Dibatalkan', 'tone' => 'danger'],
                    ],
                ],
            ],

            'top_menu' => [
                'title' => 'Menu Terlaris Hari Ini',
                'description' => 'Berdasarkan jumlah item terjual (data contoh).',
                'rows' => [
                    ['name' => 'Kopsuksar', 'sold' => 42, 'revenue' => 756000],
                    ['name' => 'Matcha Latte', 'sold' => 28, 'revenue' => 504000],
                    ['name' => 'Nasi Ayam Goreng', 'sold' => 21, 'revenue' => 462000],
                ],
            ],

            'attention' => [
                'title' => 'Perlu Perhatian',
                'description' => 'Ringkasan hal yang perlu dicek (contoh).',
                'items' => [
                    ['icon' => 'inventory_2', 'label' => '3 menu mendekati batas stok', 'tone' => 'butter'],
                    ['icon' => 'schedule', 'label' => '2 pesanan melewati estimasi sajian', 'tone' => 'danger'],
                    ['icon' => 'table_restaurant', 'label' => '2 meja menunggu dibersihkan', 'tone' => 'cocoa'],
                ],
            ],

            'reports_page' => [
                'title' => 'Laporan Penjualan',
                'subtitle' => 'Rekap penjualan contoh per hari. Semua total dihitung dari baris data.',
                'daily' => [
                    ['day' => 'Senin', 'orders' => 14, 'revenue' => 3150000],
                    ['day' => 'Selasa', 'orders' => 17, 'revenue' => 3780000],
                    ['day' => 'Rabu', 'orders' => 15, 'revenue' => 3420000],
                    ['day' => 'Kamis', 'orders' => 19, 'revenue' => 4160000],
                    ['day' => 'Jumat', 'orders' => 20, 'revenue' => 4520000],
                    ['day' => 'Sabtu', 'orders' => 23, 'revenue' => 5060000],
                    ['day' => 'Minggu', 'orders' => 20, 'revenue' => 4360000],
                ],
            ],

            'finance_page' => [
                'title' => 'Laporan Keuangan',
                'subtitle' => 'Ringkasan pemasukan dan pengeluaran contoh. Total dihitung dari baris data.',
                'income' => [
                    ['label' => 'Penjualan menu', 'amount' => 28450000],
                    ['label' => 'Pendapatan lain', 'amount' => 450000],
                ],
                'expenses' => [
                    ['label' => 'Bahan baku', 'amount' => 6200000],
                    ['label' => 'Operasional', 'amount' => 1450000],
                    ['label' => 'Gaji staff', 'amount' => 4800000],
                    ['label' => 'Sewa tempat', 'amount' => 1200000],
                ],
            ],

            'export_page' => [
                'title' => 'Export/Cetak Laporan',
                'subtitle' => 'Pilih format laporan contoh. Belum ada proses file atau printer.',
                'formats' => [
                    ['key' => 'pdf', 'label' => 'PDF', 'description' => 'Ringkasan laporan siap cetak.', 'icon' => 'picture_as_pdf'],
                    ['key' => 'excel', 'label' => 'Excel', 'description' => 'Data tabel untuk diolah kembali.', 'icon' => 'table_view'],
                    ['key' => 'print', 'label' => 'Cetak Langsung', 'description' => 'Buka dialog cetak perangkat.', 'icon' => 'print'],
                ],
                'history' => [],
            ],
        ],

        /* ------------------------------------------------------------------
         | KASIR
         | ------------------------------------------------------------------ */
        'kasir' => [
            'label' => 'Kasir',
            'title' => 'Dashboard Kasir',
            'eyebrow' => 'Kasir Panel',
            'subtitle' => 'Pantau pesanan masuk dan antrean pembayaran.',
            'shift' => ['label' => 'Shift Aktif', 'value' => 'Pagi • 08.00 - 16.00'],

            'stats' => [
                [
                    'label' => 'Pesanan Aktif',
                    'value' => '8',
                    'icon' => 'receipt_long',
                    'tone' => 'brand',
                    'hint' => '3 baru masuk',
                ],
                [
                    'label' => 'Siap Dibayar',
                    'value' => '3',
                    'icon' => 'payments',
                    'tone' => 'butter',
                    'hint' => 'menunggu konfirmasi',
                ],
                [
                    'label' => 'Meja Terisi',
                    'value' => '6 / 12',
                    'icon' => 'table_restaurant',
                    'tone' => 'lime',
                    'hint' => '50% okupansi',
                ],
            ],

            'queue' => [
                'title' => 'Pesanan Masuk',
                'description' => 'Daftar contoh pesanan yang perlu ditindaklanjuti.',
                'rows' => [
                    [
                        'id' => '#PK-9821',
                        'table' => 'Meja 08',
                        'menu' => 'Kopsuksar · Sate Maranggi · Matcha Latte',
                        'items' => 3,
                        'total' => 54000,
                        'waited' => '6 menit',
                        'status' => ['label' => 'Baru', 'tone' => 'danger'],
                    ],
                    [
                        'id' => '#PK-9820',
                        'table' => 'Meja 03',
                        'menu' => 'Matcha Latte · Es Jeruk',
                        'items' => 2,
                        'total' => 26000,
                        'waited' => '11 menit',
                        'status' => ['label' => 'Diproses', 'tone' => 'butter'],
                    ],
                    [
                        'id' => '#PK-9819',
                        'table' => 'Meja 11',
                        'menu' => 'Nasi Ayam Goreng · Dimsum · Es Coklat',
                        'items' => 4,
                        'total' => 58000,
                        'waited' => '14 menit',
                        'status' => ['label' => 'Diproses', 'tone' => 'butter'],
                    ],
                    [
                        'id' => '#PK-9817',
                        'table' => 'Meja 01',
                        'menu' => 'Teh Manis',
                        'items' => 1,
                        'total' => 5000,
                        'waited' => '2 menit',
                        'status' => ['label' => 'Baru', 'tone' => 'danger'],
                    ],
                ],
            ],

            'payments' => [
                'title' => 'Menunggu Pembayaran',
                'description' => 'Antrean pembayaran di kasir (contoh).',
                'rows' => [
                    ['id' => '#PK-9819', 'table' => 'Meja 11', 'total' => 58000, 'method' => 'Tunai'],
                    ['id' => '#PK-9820', 'table' => 'Meja 03', 'total' => 26000, 'method' => 'QRIS'],
                    ['id' => '#PK-9816', 'table' => 'Meja 07', 'total' => 44000, 'method' => 'Kartu'],
                ],
            ],

            // Contoh struk tunai: total sesuai harga menu, lengkap dengan
            // nominal dibayar dan kembalian.
            'receipt' => [
                'title' => 'Preview Struk',
                'description' => 'Contoh struk pembayaran tunai, belum terhubung printer.',
                'meta' => ['No. #PK-9821', 'Meja 08', 'Kasir: Dimas'],
                'items' => [
                    ['name' => 'Kopsuksar', 'qty' => 1, 'price' => 18000],
                    ['name' => 'Sate Maranggi', 'qty' => 6, 'price' => 3000],
                    ['name' => 'Matcha Latte', 'qty' => 1, 'price' => 18000],
                ],
                'total' => 54000,
                'paid' => 60000,
                'change' => 6000,
                'note' => 'Total sesuai harga menu. Tidak ada biaya tambahan.',
            ],

            'tables' => [
                'title' => 'Status Meja',
                'description' => 'Ringkasan okupansi meja (contoh).',
                'items' => [
                    ['code' => '01', 'state' => 'Kosong'],
                    ['code' => '02', 'state' => 'Terisi'],
                    ['code' => '03', 'state' => 'Terisi'],
                    ['code' => '04', 'state' => 'Reservasi'],
                    ['code' => '05', 'state' => 'Terisi'],
                    ['code' => '06', 'state' => 'Kosong'],
                    ['code' => '07', 'state' => 'Terisi'],
                    ['code' => '08', 'state' => 'Terisi'],
                    ['code' => '09', 'state' => 'Kosong'],
                    ['code' => '10', 'state' => 'Reservasi'],
                    ['code' => '11', 'state' => 'Terisi'],
                    ['code' => '12', 'state' => 'Kosong'],
                ],
            ],

            'quick_actions' => [
                ['label' => 'Buat Pesanan Baru', 'icon' => 'add_shopping_cart'],
                ['label' => 'Cetak Struk', 'icon' => 'print'],
                ['label' => 'Tutup Shift', 'icon' => 'logout'],
            ],

            'orders_page' => [
                'title' => 'Daftar Pesanan',
                'subtitle' => 'Pantau status pesanan contoh dan saring berdasarkan status.',
                'filters' => [
                    ['key' => 'all', 'label' => 'Semua'],
                    ['key' => 'baru', 'label' => 'Baru'],
                    ['key' => 'diproses', 'label' => 'Diproses'],
                    ['key' => 'selesai', 'label' => 'Selesai'],
                    ['key' => 'dibatalkan', 'label' => 'Dibatalkan'],
                ],
                'rows' => [
                    [
                        'id' => '#PK-9823',
                        'table' => 'Meja 02',
                        'customer' => 'Rani',
                        'menu' => 'Kopsuksar · Es Jeruk',
                        'items' => 2,
                        'total' => 26000,
                        'waited' => '1 menit',
                        'status' => ['key' => 'baru', 'label' => 'Baru', 'tone' => 'danger'],
                    ],
                    [
                        'id' => '#PK-9822',
                        'table' => 'Meja 09',
                        'customer' => 'Adi',
                        'menu' => 'Nasi Ayam Goreng · Dimsum · Es Coklat',
                        'items' => 3,
                        'total' => 53000,
                        'waited' => '4 menit',
                        'status' => ['key' => 'diproses', 'label' => 'Diproses', 'tone' => 'butter'],
                    ],
                    [
                        'id' => '#PK-9821',
                        'table' => 'Meja 08',
                        'customer' => 'Budi Santoso',
                        'menu' => 'Kopsuksar · Sate Maranggi · Matcha Latte',
                        'items' => 3,
                        'total' => 54000,
                        'waited' => '6 menit',
                        'status' => ['key' => 'baru', 'label' => 'Baru', 'tone' => 'danger'],
                    ],
                    [
                        'id' => '#PK-9820',
                        'table' => 'Meja 03',
                        'customer' => 'Sari Dewi',
                        'menu' => 'Matcha Latte · Es Jeruk',
                        'items' => 2,
                        'total' => 26000,
                        'waited' => '11 menit',
                        'status' => ['key' => 'diproses', 'label' => 'Diproses', 'tone' => 'butter'],
                    ],
                    [
                        'id' => '#PK-9819',
                        'table' => 'Meja 11',
                        'customer' => 'Andi Pratama',
                        'menu' => 'Nasi Ayam Goreng · Dimsum · Es Coklat',
                        'items' => 4,
                        'total' => 58000,
                        'waited' => '14 menit',
                        'status' => ['key' => 'selesai', 'label' => 'Selesai', 'tone' => 'lime'],
                    ],
                    [
                        'id' => '#PK-9818',
                        'table' => 'Meja 05',
                        'customer' => 'Maya Lestari',
                        'menu' => 'Teh Manis',
                        'items' => 1,
                        'total' => 5000,
                        'waited' => '18 menit',
                        'status' => ['key' => 'dibatalkan', 'label' => 'Dibatalkan', 'tone' => 'neutral'],
                    ],
                ],
            ],

            'payment_page' => [
                'title' => 'Pembayaran & Cetak Struk',
                'subtitle' => 'Ringkasan pesanan tunai contoh. Struk hanya memunculkan toast.',
                'cash_received' => 60000,
            ],
        ],

        /* ------------------------------------------------------------------
         | ADMIN
         | ------------------------------------------------------------------ */
        'admin' => [
            'label' => 'Admin',
            'title' => 'Dashboard Admin',
            'eyebrow' => 'Admin Panel',
            'subtitle' => 'Kelola menu, meja, stok, dan pengguna internal.',

            'stats' => [
                [
                    'label' => 'Menu Aktif',
                    'value' => '15',
                    'icon' => 'restaurant_menu',
                    'tone' => 'brand',
                    'hint' => '9 minuman · 6 makanan',
                ],
                [
                    'label' => 'Kategori',
                    'value' => '2',
                    'icon' => 'category',
                    'tone' => 'lime',
                    'hint' => 'Minuman & Makanan',
                ],
                [
                    'label' => 'Meja',
                    'value' => '12',
                    'icon' => 'table_restaurant',
                    'tone' => 'butter',
                    'hint' => 'QR aktif',
                ],
                [
                    'label' => 'Stok Menipis',
                    'value' => '3',
                    'icon' => 'inventory_2',
                    'tone' => 'danger',
                    'hint' => 'perlu restock',
                ],
            ],

            'menu' => [
                'title' => 'Kelola Menu',
                'description' => 'Contoh daftar menu dari katalog (data statis).',
                'rows' => [
                    ['slug' => 'kopsuksar', 'name' => 'Kopsuksar', 'category' => 'Minuman', 'price' => 18000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'matcha-latte', 'name' => 'Matcha Latte', 'category' => 'Minuman', 'price' => 18000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'sate-maranggi', 'name' => 'Sate Maranggi', 'category' => 'Makanan', 'price' => 3000, 'unit' => 'tusuk', 'available' => true],
                    ['slug' => 'nasi-ayam-goreng', 'name' => 'Nasi Ayam Goreng', 'category' => 'Makanan', 'price' => 22000, 'unit' => 'porsi', 'available' => true],
                    ['slug' => 'dimsum', 'name' => 'Dimsum', 'category' => 'Makanan', 'price' => 18000, 'unit' => 'porsi', 'available' => false],
                    ['slug' => 'mix-platte', 'name' => 'Mix Platte', 'category' => 'Makanan', 'price' => 18000, 'unit' => 'porsi', 'available' => true],
                ],
            ],

            'low_stock' => [
                'title' => 'Stok Menipis',
                'description' => 'Contoh peringatan stok menu.',
                'rows' => [
                    ['name' => 'Sate Maranggi', 'left' => '12 tusuk', 'tone' => 'danger'],
                    ['name' => 'Dimsum', 'left' => '8 porsi', 'tone' => 'butter'],
                    ['name' => 'Matcha Latte', 'left' => '5 gelas', 'tone' => 'danger'],
                ],
            ],

            'users' => [
                'title' => 'Pengguna Internal',
                'description' => 'Contoh akun staff dan statusnya.',
                'rows' => [
                    ['name' => 'Rani Puspita', 'role' => 'Manager', 'status' => ['label' => 'Aktif', 'tone' => 'lime']],
                    ['name' => 'Dimas Saputra', 'role' => 'Kasir', 'status' => ['label' => 'Aktif', 'tone' => 'lime']],
                    ['name' => 'Nadia Putri', 'role' => 'Admin', 'status' => ['label' => 'Nonaktif', 'tone' => 'neutral']],
                ],
            ],

            'tables' => [
                'title' => 'Meja & QR Code',
                'description' => 'Contoh daftar meja yang terhubung ke QR.',
                'items' => [
                    ['code' => '01', 'area' => 'Indoor AC', 'state' => 'Kosong'],
                    ['code' => '02', 'area' => 'Indoor AC', 'state' => 'Terisi'],
                    ['code' => '03', 'area' => 'Indoor AC', 'state' => 'Terisi'],
                    ['code' => '04', 'area' => 'Outdoor', 'state' => 'Reservasi'],
                    ['code' => '05', 'area' => 'Outdoor', 'state' => 'Terisi'],
                    ['code' => '06', 'area' => 'Outdoor', 'state' => 'Kosong'],
                ],
            ],

            'menu_page' => [
                'title' => 'Kelola Menu',
                'subtitle' => 'Daftar 15 menu asli Perkoci Eatery. Aksi tambah, edit, dan hapus hanya memunculkan toast.',
                'rows' => [
                    ['slug' => 'kopsuksar', 'name' => 'Kopsuksar', 'category' => 'Minuman', 'price' => 18000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'americans-pep', 'name' => "American's Pep", 'category' => 'Minuman', 'price' => 15000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'amci', 'name' => 'Amci', 'category' => 'Minuman', 'price' => 18000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'tambakguruyung-sc', 'name' => 'Tambakguruyung SC', 'category' => 'Minuman', 'price' => 25000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'patuha-sc', 'name' => 'Patuha SC', 'category' => 'Minuman', 'price' => 25000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'matcha-latte', 'name' => 'Matcha Latte', 'category' => 'Minuman', 'price' => 18000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'es-coklat', 'name' => 'Es Coklat', 'category' => 'Minuman', 'price' => 13000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'es-jeruk', 'name' => 'Es Jeruk', 'category' => 'Minuman', 'price' => 8000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'teh-manis', 'name' => 'Teh Manis', 'category' => 'Minuman', 'price' => 5000, 'unit' => 'gelas', 'available' => true],
                    ['slug' => 'sate-maranggi', 'name' => 'Sate Maranggi', 'category' => 'Makanan', 'price' => 3000, 'unit' => 'tusuk', 'available' => true],
                    ['slug' => 'nasi-lontong', 'name' => 'Nasi/Lontong', 'category' => 'Makanan', 'price' => 4000, 'unit' => 'porsi', 'available' => true],
                    ['slug' => 'nasi-ayam-goreng', 'name' => 'Nasi Ayam Goreng', 'category' => 'Makanan', 'price' => 22000, 'unit' => 'porsi', 'available' => true],
                    ['slug' => 'dimsum', 'name' => 'Dimsum', 'category' => 'Makanan', 'price' => 18000, 'unit' => 'porsi', 'available' => true],
                    ['slug' => 'tahu-cabe-garam', 'name' => 'Tahu Cabe Garam', 'category' => 'Makanan', 'price' => 10000, 'unit' => 'porsi', 'available' => true],
                    ['slug' => 'mix-platte', 'name' => 'Mix Platte', 'category' => 'Makanan', 'price' => 18000, 'unit' => 'porsi', 'available' => true],
                ],
            ],

            'inventory_page' => [
                'title' => 'Kelola Stok',
                'subtitle' => 'Contoh stok bahan dan menu. Angka tidak terhubung ke database.',
                'rows' => [
                    ['name' => 'Biji Kopi Houseblend', 'unit' => 'kg', 'stock' => 8, 'minimum' => 5, 'status' => ['label' => 'Aman', 'tone' => 'lime']],
                    ['name' => 'Susu UHT', 'unit' => 'liter', 'stock' => 12, 'minimum' => 8, 'status' => ['label' => 'Aman', 'tone' => 'lime']],
                    ['name' => 'Gula Aren', 'unit' => 'kg', 'stock' => 3, 'minimum' => 4, 'status' => ['label' => 'Menipis', 'tone' => 'butter']],
                    ['name' => 'Matcha Powder', 'unit' => 'kg', 'stock' => 2, 'minimum' => 3, 'status' => ['label' => 'Menipis', 'tone' => 'butter']],
                    ['name' => 'Bubuk Coklat', 'unit' => 'kg', 'stock' => 0, 'minimum' => 2, 'status' => ['label' => 'Habis', 'tone' => 'danger']],
                    ['name' => 'Daging Sate', 'unit' => 'tusuk', 'stock' => 24, 'minimum' => 30, 'status' => ['label' => 'Menipis', 'tone' => 'butter']],
                    ['name' => 'Beras', 'unit' => 'kg', 'stock' => 18, 'minimum' => 10, 'status' => ['label' => 'Aman', 'tone' => 'lime']],
                    ['name' => 'Minyak Goreng', 'unit' => 'liter', 'stock' => 6, 'minimum' => 5, 'status' => ['label' => 'Aman', 'tone' => 'lime']],
                ],
            ],

            'settings_page' => [
                'title' => 'Pengaturan',
                'subtitle' => 'Contoh pengaturan outlet. Perubahan belum disimpan ke mana pun.',
                'groups' => [
                    [
                        'title' => 'Profil Outlet',
                        'fields' => [
                            ['label' => 'Nama outlet', 'value' => 'Perkoci Eatery'],
                            ['label' => 'Alamat', 'value' => 'Contoh alamat outlet'],
                            ['label' => 'Nomor kontak', 'value' => '08xx-xxxx-xxxx'],
                        ],
                    ],
                    [
                        'title' => 'Preferensi Operasional',
                        'fields' => [
                            ['label' => 'Jam buka', 'value' => '08.00 WIB'],
                            ['label' => 'Jam tutup', 'value' => '22.00 WIB'],
                            ['label' => 'Catatan struk', 'value' => 'Terima kasih telah berkunjung.'],
                        ],
                    ],
                ],
            ],
        ],

    ],
];
