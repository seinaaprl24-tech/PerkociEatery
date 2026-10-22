<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun staf (Admin, Kasir, Manager) untuk pengujian backend dan integrasi frontend.
 * Password diambil dari SEED_STAFF_PASSWORD, default "password".
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_STAFF_PASSWORD', 'password');

        $staff = [
            [
                'nama' => 'Admin Perkoci',
                'username' => 'admin',
                'role' => 'admin',
            ],
            [
                'nama' => 'Kasir Perkoci',
                'username' => 'kasir',
                'role' => 'kasir',
            ],
            [
                'nama' => 'Manager Perkoci',
                'username' => 'manager',
                'role' => 'manager',
            ],
        ];

        foreach ($staff as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'nama' => $data['nama'],
                    'role' => $data['role'],
                    'password' => $password,
                ],
            );
        }
    }
}
