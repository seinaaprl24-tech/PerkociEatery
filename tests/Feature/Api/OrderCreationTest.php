<?php

namespace Tests\Feature\Api;

use App\Models\Meja;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alur inti customer: kirim pesanan dari keranjang lalu buka halaman status.
 */
class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    private function buatProduk(int $jumlahStok = 5): Produk
    {
        $produk = Produk::create([
            'nama_produk' => 'Kopi Susu',
            'kategori' => 'Signature Coffee',
            'harga' => 18000,
            'status' => 'aktif',
        ]);

        // Produk baru otomatis mendapat baris stok (AppServiceProvider).
        Stok::updateOrCreate(
            ['id_produk' => $produk->id_produk],
            ['jumlah_stok' => $jumlahStok, 'min_stok' => 0, 'updated_at' => now()],
        );

        return $produk;
    }

    private function buatMeja(): Meja
    {
        return Meja::create([
            'no_meja' => '01',
            'qr_code' => 'MEJA-01',
            'status' => 'tersedia',
        ]);
    }

    public function test_pesanan_bisa_dibuat_tanpa_harga_satuan_dari_client(): void
    {
        $meja = $this->buatMeja();
        $produk = $this->buatProduk(5);

        $response = $this->postJson('/api/pesanan', [
            'id_meja' => $meja->id_meja,
            'nama_pemesan' => 'Tes Verifikasi',
            'no_whatsapp' => '081234567890',
            'items' => [
                ['id_produk' => $produk->id_produk, 'jumlah' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_harga', 36000)
            ->assertJsonPath('data.nama_pemesan', 'Tes Verifikasi');

        $this->assertNotEmpty($response->json('data.kode_akses'));

        // Stok berkurang dan meja otomatis berstatus terisi.
        $this->assertDatabaseHas('stok', [
            'id_produk' => $produk->id_produk,
            'jumlah_stok' => 3,
        ]);
        $this->assertDatabaseHas('meja', [
            'id_meja' => $meja->id_meja,
            'status' => 'terisi',
        ]);
    }

    public function test_status_pesanan_hanya_bisa_dibuka_dengan_kode_akses_yang_benar(): void
    {
        $meja = $this->buatMeja();
        $produk = $this->buatProduk();

        $order = $this->postJson('/api/pesanan', [
            'id_meja' => $meja->id_meja,
            'nama_pemesan' => 'Tes Verifikasi',
            'items' => [
                ['id_produk' => $produk->id_produk, 'jumlah' => 1],
            ],
        ])->json('data');

        $this->get('/status?id='.$order['id_pesanan'].'&kode='.$order['kode_akses'])
            ->assertOk()
            ->assertSee($order['nomor_antrean'])
            ->assertSee('Tes Verifikasi');

        $this->get('/status?id='.$order['id_pesanan'].'&kode=kode-salah')
            ->assertNotFound();
    }
}
