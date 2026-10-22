<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_staf_login_berhasil_menggunakan_username(): void
    {
        $kasir = User::factory()->kasir()->create(['username' => 'kasir.test']);

        $berhasil = auth()->attempt([
            'username' => 'kasir.test',
            'password' => 'password',
        ]);

        $this->assertTrue($berhasil);
        $this->assertAuthenticatedAs($kasir);
        $this->assertSame('kasir', auth()->user()->role);
    }

    public function test_login_gagal_jika_password_salah(): void
    {
        User::factory()->create(['username' => 'kasir.test']);

        $berhasil = auth()->attempt([
            'username' => 'kasir.test',
            'password' => 'password-salah',
        ]);

        $this->assertFalse($berhasil);
        $this->assertGuest();
    }

    public function test_login_gagal_jika_username_tidak_terdaftar(): void
    {
        $berhasil = auth()->attempt([
            'username' => 'tidak.ada',
            'password' => 'password',
        ]);

        $this->assertFalse($berhasil);
        $this->assertGuest();
    }

    public function test_password_tersimpan_sebagai_hash(): void
    {
        $user = User::factory()->create();

        $this->assertNotSame('password', $user->password);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_akun_hanya_bisa_memiliki_role_staf(): void
    {
        $this->expectException(QueryException::class);

        User::factory()->create(['role' => 'customer']);
    }

    public function test_role_staf_dapat_dicek_melalui_helper_has_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole('admin', 'manager'));
        $this->assertFalse($admin->hasRole('kasir'));
    }
}
