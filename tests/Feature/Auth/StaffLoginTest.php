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

    public function test_login_web_manager_diarahkan_ke_dashboard_manager(): void
    {
        $manager = User::factory()->manager()->create(['username' => 'manager.test']);

        $response = $this->post('/login', [
            'username' => 'manager.test',
            'password' => 'password',
            'role' => 'manager',
        ]);

        $response->assertRedirect('/manager');
        $this->assertAuthenticatedAs($manager);
    }

    public function test_login_web_tanpa_role_tetap_berhasil(): void
    {
        $kasir = User::factory()->kasir()->create(['username' => 'kasir.test']);

        $response = $this->post('/login', [
            'username' => 'kasir.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/kasir');
        $this->assertAuthenticatedAs($kasir);
    }

    public function test_login_web_menerima_nilai_role_huruf_besar(): void
    {
        $manager = User::factory()->manager()->create(['username' => 'manager.test']);

        $response = $this->post('/login', [
            'username' => 'manager.test',
            'password' => 'password',
            'role' => 'Manager',
        ]);

        $response->assertRedirect('/manager');
        $this->assertAuthenticatedAs($manager);
    }

    public function test_login_web_menolak_role_yang_tidak_sesuai(): void
    {
        User::factory()->manager()->create(['username' => 'manager.test']);

        $response = $this->from('/login')->post('/login', [
            'username' => 'manager.test',
            'password' => 'password',
            'role' => 'kasir',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_halaman_login_menampilkan_pesan_error_kredensial(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => 'tidak.ada',
            'password' => 'salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Username atau password salah.');
    }
}
