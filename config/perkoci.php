<?php

/*
|--------------------------------------------------------------------------
| Perkoci Eatery — Data Statis (Frontend Only)
|--------------------------------------------------------------------------
| Seluruh isi halaman (katalog, keranjang, status, meja) bersumber
| dari file ini. Belum ada database / auth logic. Nilai turunan (subtotal,
| total) dihitung dari data di bawah oleh App\Support\Perkoci.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */
    'brand' => [
        'name' => 'Perkoci Eatery',
        'tagline' => 'Artisanal Coffee & Kitchen',
        'footer' => 'Perkoci Preserving Culinary Warmth',
        // Salinan PNG dari logo asli (public/images/logo-perkoci.png.jpeg)
        // dengan area transparan dipangkas. Logo asli tidak diubah.
        'logo' => 'images/logo-perkoci-trim.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meja Aktif
    |--------------------------------------------------------------------------
    | Satu sumber teks lokasi untuk header, katalog, cover, keranjang, status.
    */
    'table' => [
        'location' => 'Meja 08 • Indoor AC',
        'session_label' => 'Sesi Meja Aktif',
        'connected_note' => 'Terhubung otomatis via QR Meja',
        'locked_pill' => 'Terkunci Otomatis via QR',
        'locked_desc' => 'Pesanan kamu terikat otomatis ke meja ini. Barista akan membawa pesanan langsung ke meja tanpa perlu nomor antrean.',
        'order_mode' => 'Dine In',
    ],

    /*
    |--------------------------------------------------------------------------
    | Halaman Cover
    |--------------------------------------------------------------------------
    */
    'cover' => [
        'greeting' => 'Selamat Datang di',
        'title' => 'Perkoci Eatery',
        'subtitle' => 'Artisanal Coffee & Kitchen',
        'progress' => 70,
        'progress_label' => 'Menyiapkan rekomendasi barista hari ini',
        'redirect_to' => '/menu',
        'redirect_delay' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori Menu
    |--------------------------------------------------------------------------
    */
    'categories' => [
        ['id' => 'all', 'name' => 'Semua'],
        ['id' => 'minuman', 'name' => 'Minuman'],
        ['id' => 'makanan', 'name' => 'Makanan'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Katalog Menu
    |--------------------------------------------------------------------------
    | Sumber: foto menu "Today's Menu" Perkoci Eatery.
    | unit  : satuan jual ("gelas" minuman, "porsi" makanan, "tusuk" sate).
    | image : TIDAK ditulis di sini. App\Support\Perkoci::menu() otomatis
    |         mencari public/images/menu/{slug}.jpg|jpeg|png|webp, lalu
    |         memakai aset lama bila ada; tanpa file = placeholder ikon.
    */
    'menu' => [

        /* ------------------------------ MINUMAN ------------------------------ */
        [
            'slug' => 'kopsuksar',
            'name' => 'Kopsuksar',
            'price' => 18000,
            'description' => 'Es Kopi Susu Gula Aren',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'americans-pep',
            'name' => "American's Pep",
            'price' => 15000,
            'description' => 'Ice Americano',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'amci',
            'name' => 'Amci',
            'price' => 18000,
            'description' => 'Ice Americano dengan Syrup Mix Berry',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'tambakguruyung-sc',
            'name' => 'Tambakguruyung SC',
            'price' => 25000,
            'description' => 'Kopi Filter Houseblend',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'patuha-sc',
            'name' => 'Patuha SC',
            'price' => 25000,
            'description' => 'Kopi Filter Full Arabica',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'matcha-latte',
            'name' => 'Matcha Latte',
            'price' => 18000,
            'description' => '',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'es-coklat',
            'name' => 'Es Coklat',
            'price' => 13000,
            'description' => '',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'es-jeruk',
            'name' => 'Es Jeruk',
            'price' => 8000,
            'description' => '',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],
        [
            'slug' => 'teh-manis',
            'name' => 'Teh Manis',
            'price' => 5000,
            'description' => '',
            'category' => 'minuman',
            'unit' => 'gelas',
        ],

        /* ------------------------------ MAKANAN ------------------------------ */
        [
            'slug' => 'sate-maranggi',
            'name' => 'Sate Maranggi',
            'price' => 3000,
            'description' => '',
            'category' => 'makanan',
            'unit' => 'tusuk',
        ],
        [
            'slug' => 'nasi-lontong',
            'name' => 'Nasi/Lontong',
            'price' => 4000,
            'description' => '',
            'category' => 'makanan',
            'unit' => 'porsi',
        ],
        [
            'slug' => 'nasi-ayam-goreng',
            'name' => 'Nasi Ayam Goreng',
            'price' => 22000,
            'description' => '',
            'category' => 'makanan',
            'unit' => 'porsi',
        ],
        [
            'slug' => 'dimsum',
            'name' => 'Dimsum',
            'price' => 18000,
            'description' => '',
            'category' => 'makanan',
            'unit' => 'porsi',
        ],
        [
            'slug' => 'tahu-cabe-garam',
            'name' => 'Tahu Cabe Garam',
            'price' => 10000,
            'description' => '',
            'category' => 'makanan',
            'unit' => 'porsi',
        ],
        [
            'slug' => 'mix-platte',
            'name' => 'Mix Platte',
            'price' => 18000,
            'description' => 'Kentang Goreng, Sosis dan Nugget',
            'category' => 'makanan',
            'unit' => 'porsi',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Keranjang
    |--------------------------------------------------------------------------
    | price = harga satuan, qty = jumlah porsi/tusuk.
    | Subtotal dihitung otomatis oleh App\Support\Perkoci.
    */
    'cart' => [

        'items' => [
            [
                'slug' => 'kopsuksar',
                'name' => 'Kopsuksar',
                'category' => 'minuman',
                'unit' => 'gelas',
                'note' => 'Gula aren dipisah',
                'price' => 18000,
                'qty' => 1,
            ],
            [
                'slug' => 'sate-maranggi',
                'name' => 'Sate Maranggi',
                'category' => 'makanan',
                'unit' => 'tusuk',
                'note' => 'Sambal oncom dipisah',
                'price' => 3000,
                'qty' => 6,
            ],
            [
                'slug' => 'matcha-latte',
                'name' => 'Matcha Latte',
                'category' => 'minuman',
                'unit' => 'gelas',
                'note' => null,
                'price' => 18000,
                'qty' => 1,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Status Pesanan (Mobile)
    |--------------------------------------------------------------------------
    | Data contoh untuk halaman /status. Belum terhubung ke backend: nomor
    | antrean, progres, dan kode pembayaran masih statis. Saat backend siap,
    | ganti sumbernya di App\Support\Perkoci::statusOrder().
    */
    'status' => [

        'queue' => [
            'label' => 'NOMOR ANTREAN',
            'number' => '#A-24',
            'eta' => '± 10 - 15 Menit',
        ],

        'order' => [
            'id' => '#PK-9821',
            'customer' => 'Budi Santoso',
        ],

        'current' => [
            'label' => 'STATUS TERKINI',
            'title' => 'Sedang Diracik oleh Barista',
        ],

        'auto_update' => 'Pembaruan otomatis',

        'steps' => [
            [
                'title' => 'Pesanan Diterima',
                'time' => '14:15 WIB',
                'desc' => 'Pesanan tercatat dan diteruskan ke barista serta dapur.',
                'state' => 'done',
                'icon' => 'check',
            ],
            [
                'title' => 'Sedang Diracik / Dimasak',
                'time' => '14:18 WIB',
                'desc' => 'Espresso diseduh dan hidangan sedang dimasak di dapur.',
                'state' => 'active',
                'icon' => 'local_cafe',
            ],
            [
                'title' => 'Siap Disajikan',
                'time' => 'Est. 14:28 WIB',
                'desc' => 'Pesanan siap dan akan diantar ke meja kamu.',
                'state' => 'pending',
                'icon' => 'restaurant',
            ],
            [
                'title' => 'Selesai Menikmati',
                'time' => 'Est. 14:40 WIB',
                'desc' => 'Selamat menikmati hidangan Perkoci Eatery.',
                'state' => 'pending',
                'icon' => 'done_all',
            ],
        ],

        'payment' => [
            'title' => 'Metode Pembayaran',
            'method' => 'Bayar di Kasir Perkoci',
            'status' => 'Menunggu Kasir',
            'hint' => 'Tunjukkan kode ini saat kasir menghampiri meja kamu.',
            'code' => 'PK-8820-T08',
            'total_label' => 'Total Tagihan',
        ],

        'items' => [
            'title' => 'Rincian Item',
            'total' => 'Total Akhir',
        ],

        'actions' => [
            [
                'key' => 'waiter',
                'label' => 'Panggil Waiter',
                'icon' => 'notifications_active',
                'note' => 'Estimasi 1 - 2 menit',
                'done' => 'Waiter menuju meja',
            ],
            [
                'key' => 'cashier',
                'label' => 'Hubungi Kasir',
                'icon' => 'support_agent',
                'note' => 'Kasir segera datang',
                'done' => 'Kasir dihubungi',
            ],
        ],

        'farewell' => 'Silakan bersantai menikmati suasana Perkoci Eatery...',
        'demo_note' => 'Data contoh — halaman frontend belum terhubung ke backend.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pembayaran
    |--------------------------------------------------------------------------
    | Total transaksi mengikuti harga menu (tanpa biaya tambahan).
    */
    'payment_methods' => [
        [
            'value' => 'cashier',
            'label' => 'Bayar di Kasir',
            'meta' => 'Tunai, kartu debit/kredit, atau QRIS di meja',
            'default' => true,
        ],
        [
            'value' => 'qris',
            'label' => 'QRIS / E-Wallet Dinamis',
            'meta' => 'Scan QR dinamis yang berlaku 5 menit',
            'default' => false,
        ],
    ],

    'customer' => [
        'name_label' => 'Nama Pemesan',
        'name_placeholder' => 'Contoh: Rani',
        'name_required' => true,
        'phone_label' => 'Nomor WhatsApp',
        'phone_placeholder' => '08xx-xxxx-xxxx',
        'phone_required' => false,
        'phone_hint' => 'Opsional. Dipakai hanya bila perlu menghubungi kamu soal pesanan.',
        'info' => 'Nama pemesan akan tercetak di struk dan di panggil barista saat pesanan siap.',
    ],

    'note' => [
        'label' => 'Catatan untuk Barista',
        'placeholder' => 'Contoh: kurangi es, tambah gula aren, atau info alergi...',
        'max' => 150,
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigasi Customer
    |--------------------------------------------------------------------------
    */
    'nav' => [
        [
            'key' => 'menu',
            'label' => 'Katalog',
            'icon' => 'restaurant_menu',
            'href' => '/menu',
            'route' => 'menu.*',
        ],
        [
            'key' => 'keranjang',
            'label' => 'Keranjang',
            'icon' => 'shopping_bag',
            'href' => '/keranjang',
            'route' => 'keranjang',
        ],
        [
            'key' => 'status',
            'label' => 'Status',
            'icon' => 'receipt_long',
            'href' => '/status',
            'route' => 'status',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigasi Staff (Kasir / Admin / Manager)
    |--------------------------------------------------------------------------
    */
    'staff' => [
        'roles' => [
            ['key' => 'kasir', 'label' => 'Kasir'],
            ['key' => 'admin', 'label' => 'Admin'],
            ['key' => 'manager', 'label' => 'Manager'],
        ],

        // Data contoh pengguna yang sedang login (presentasi saja).
        'user' => [
            'name' => 'Rani Puspita',
            'initials' => 'RP',
            'shift' => 'Shift Pagi • 08.00 - 16.00',
        ],

        // Menu sidebar per role. "roles" = role yang melihat menu tersebut.
        'nav' => [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'dashboard',
                'href' => '#',
                'roles' => ['kasir', 'admin', 'manager'],
            ],
            [
                'key' => 'orders',
                'label' => 'Pesanan Masuk',
                'icon' => 'receipt_long',
                'href' => '#',
                'badge' => 8,
                'roles' => ['kasir'],
            ],
            [
                'key' => 'payments',
                'label' => 'Pembayaran',
                'icon' => 'payments',
                'href' => '#',
                'roles' => ['kasir'],
            ],
            [
                'key' => 'menu',
                'label' => 'Kelola Menu',
                'icon' => 'restaurant_menu',
                'href' => '#',
                'roles' => ['admin'],
            ],
            [
                'key' => 'tables',
                'label' => 'Meja & QR Code',
                'icon' => 'table_restaurant',
                'href' => '#',
                'roles' => ['kasir', 'admin'],
            ],
            [
                'key' => 'inventory',
                'label' => 'Stok Produk',
                'icon' => 'inventory_2',
                'href' => '#',
                'roles' => ['admin'],
            ],
            [
                'key' => 'reports',
                'label' => 'Laporan Penjualan',
                'icon' => 'monitoring',
                'href' => '#',
                'roles' => ['manager'],
            ],
            [
                'key' => 'finance',
                'label' => 'Laporan Keuangan',
                'icon' => 'account_balance_wallet',
                'href' => '#',
                'roles' => ['manager'],
            ],
            [
                'key' => 'export',
                'label' => 'Export/Cetak Laporan',
                'icon' => 'print',
                'href' => '#',
                'roles' => ['manager'],
            ],
            [
                'key' => 'users',
                'label' => 'Pengguna',
                'icon' => 'group',
                'href' => '#',
                'roles' => ['admin'],
            ],
            [
                'key' => 'settings',
                'label' => 'Pengaturan',
                'icon' => 'settings',
                'href' => '#',
                'roles' => ['admin'],
            ],
        ],
    ],

];
