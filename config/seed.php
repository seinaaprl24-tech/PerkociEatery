<?php

/*
|--------------------------------------------------------------------------
| Password Awal Akun Staf
|--------------------------------------------------------------------------
| Nilai diambil dari .env lalu disimpan di config agar tetap terbaca
| walaupun `php artisan config:cache` sedang aktif. Jangan memanggil env()
| langsung dari seeder karena env() mengembalikan null saat config di-cache.
|
| Nilai di bawah adalah password PLAINTEXT. UserSeeder akan meng-hash-nya.
*/

return [
    // `?:` (bukan default argumen env()) supaya nilai kosong di .env
    // tetap jatuh ke fallback, bukan menjadi password string kosong.
    'fallback' => env('SEED_STAFF_PASSWORD') ?: 'password',
    'admin' => env('SEED_ADMIN_PASSWORD') ?: null,
    'kasir' => env('SEED_KASIR_PASSWORD') ?: null,
    'manager' => env('SEED_MANAGER_PASSWORD') ?: null,
];
