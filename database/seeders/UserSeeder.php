<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun staf (Admin, Kasir, Manager) dengan password masing-masing.
 *
 * Password dibaca dari .env (jangan ditulis di kode / di-commit):
 *   SEED_ADMIN_PASSWORD, SEED_KASIR_PASSWORD, SEED_MANAGER_PASSWORD
 * Jika kosong, dipakai SEED_STAFF_PASSWORD, lalu "password" (hanya untuk uji lokal).
 *
 * Menjalankan seeder ini lagi akan MENGATUR ULANG password ketiga akun
 * sesuai nilai di .env.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $fallback = env('SEED_STAFF_PASSWORD', 'password');

        $staff = [
            [
                'nama' => 'Admin Perkoci',
                'username' => 'admin',
                'role' => 'admin',
                'password' => env('SEED_ADMIN_PASSWORD') ?: $fallback,
            ],
            [
                'nama' => 'Kasir Perkoci',
                'username' => 'kasir',
                'role' => 'kasir',
                'password' => env('SEED_KASIR_PASSWORD') ?: $fallback,
            ],
            [
                'nama' => 'Manager Perkoci',
                'username' => 'manager',
                'role' => 'manager',
                'password' => env('SEED_MANAGER_PASSWORD') ?: $fallback,
            ],
        ];

        foreach ($staff as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'nama' => $data['nama'],
                    'role' => $data['role'],
                    'password' => $data['password'],
                ],
            );
        }
    }
}
