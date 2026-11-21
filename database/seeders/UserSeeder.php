<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun staf (Admin, Kasir, Manager) dengan password masing-masing.
 *
 * Password dibaca dari .env (jangan ditulis di kode / di-commit):
 *   SEED_ADMIN_PASSWORD, SEED_KASIR_PASSWORD, SEED_MANAGER_PASSWORD
 * Jika kosong, dipakai SEED_STAFF_PASSWORD, lalu "password" (hanya untuk uji lokal).
 *
 * Nilainya dibaca lewat config/seed.php supaya tetap tersedia saat config
 * di-cache. Menjalankan seeder ini lagi akan MENGATUR ULANG password ketiga
 * akun sesuai nilai di .env.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $fallback = config('seed.fallback', 'password');

        $staff = [
            [
                'nama' => 'Admin Perkoci',
                'username' => 'admin',
                'role' => 'admin',
                'password' => config('seed.admin') ?: $fallback,
            ],
            [
                'nama' => 'Kasir Perkoci',
                'username' => 'kasir',
                'role' => 'kasir',
                'password' => config('seed.kasir') ?: $fallback,
            ],
            [
                'nama' => 'Manager Perkoci',
                'username' => 'manager',
                'role' => 'manager',
                'password' => config('seed.manager') ?: $fallback,
            ],
        ];

        foreach ($staff as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'nama' => $data['nama'],
                    'role' => $data['role'],
                    // Hash eksplisit agar tidak bergantung pada cast model.
                    'password' => Hash::make($data['password']),
                ],
            );
        }
    }
}
