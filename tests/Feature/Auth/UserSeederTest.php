<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_memakai_password_role_dari_config(): void
    {
        config([
            'seed.admin' => 'AdminPass123',
            'seed.kasir' => 'KasirPass123',
            'seed.manager' => 'ManagerPass123',
        ]);

        $this->seed(UserSeeder::class);

        $expected = [
            'admin' => ['admin', 'AdminPass123'],
            'kasir' => ['kasir', 'KasirPass123'],
            'manager' => ['manager', 'ManagerPass123'],
        ];

        foreach ($expected as $username => [$role, $password]) {
            $user = User::where('username', $username)->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check($password, $user->password));
            $this->assertNotSame($password, $user->password);
        }
    }

    public function test_seeder_memakai_fallback_jika_password_role_kosong(): void
    {
        config([
            'seed.admin' => '',
            'seed.kasir' => '',
            'seed.manager' => null,
            'seed.fallback' => 'FallbackPass123',
        ]);

        $this->seed(UserSeeder::class);

        foreach (['admin', 'kasir', 'manager'] as $username) {
            $user = User::where('username', $username)->firstOrFail();

            $this->assertTrue(Hash::check('FallbackPass123', $user->password));
        }
    }
}
