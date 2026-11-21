<?php

namespace Tests\Feature\Customer;

use App\Models\Meja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Memastikan halaman keranjang mendeteksi QR meja takeaway kasir.
 */
class TakeawayCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_keranjang_menampilkan_mode_takeaway_setelah_scan_qr_kasir(): void
    {
        // Meja takeaway sudah dibuat oleh migration.
        $this->get('/keranjang?meja='.Meja::TAKEAWAY_QR_CODE)
            ->assertOk()
            ->assertSee('Mode: Takeaway (Bungkus)')
            ->assertSee('Takeaway Kasir');

        // Sesi meja tetap tersimpan saat halaman dibuka tanpa parameter QR.
        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('Mode: Takeaway (Bungkus)');
    }

    public function test_keranjang_meja_biasa_tidak_menampilkan_mode_takeaway(): void
    {
        Meja::create([
            'no_meja' => '01',
            'qr_code' => 'MEJA-01',
            'status' => 'tersedia',
        ]);

        $this->get('/keranjang?meja=MEJA-01')
            ->assertOk()
            ->assertSee('Meja 01')
            ->assertDontSee('Mode: Takeaway (Bungkus)');
    }
}
