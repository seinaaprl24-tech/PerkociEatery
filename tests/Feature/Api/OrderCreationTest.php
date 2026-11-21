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

    public function test_meja_takeaway_kasir_otomatis_menyimpan_tipe_takeaway_dan_catatan(): void
    {
        // Meja takeaway sudah dibuat oleh migration.
        $meja = Meja::firstOrCreate(
            ['qr_code' => Meja::TAKEAWAY_QR_CODE],
            [
                'no_meja' => Meja::TAKEAWAY_NAME,
                'status' => 'tersedia',
            ],
        );
        $produk = $this->buatProduk();

        $response = $this->postJson('/api/pesanan', [
            'id_meja' => $meja->id_meja,
            'nama_pemesan' => 'Pemesan Takeaway',
            'catatan' => 'Sambal dipisah, bungkus terpisah.',
            'items' => [
                ['id_produk' => $produk->id_produk, 'jumlah' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tipe_pesanan', 'takeaway')
            ->assertJsonPath('data.is_takeaway', true)
            ->assertJsonPath('data.catatan', 'Sambal dipisah, bungkus terpisah.');

        $this->assertDatabaseHas('pesanan', [
            'id_meja' => $meja->id_meja,
            'tipe_pesanan' => 'takeaway',
            'catatan' => 'Sambal dipisah, bungkus terpisah.',
        ]);
    }

    public function test_meja_takeaway_selalu_tersedia_dan_bisa_dipakai_bersamaan(): void
    {
        $meja = Meja::firstOrCreate(
            ['qr_code' => Meja::TAKEAWAY_QR_CODE],
            [
                'no_meja' => Meja::TAKEAWAY_NAME,
                'status' => 'tersedia',
            ],
        );

        // Simulasikan status lama yang sempat "terisi"; QR takeaway tetap
        // harus bisa dipakai banyak pelanggan.
        $meja->update(['status' => 'terisi']);

        $produk = $this->buatProduk(10);

        foreach (['Pemesan Takeaway A', 'Pemesan Takeaway B'] as $nama) {
            $this->postJson('/api/pesanan', [
                'id_meja' => $meja->id_meja,
                'nama_pemesan' => $nama,
                'items' => [
                    ['id_produk' => $produk->id_produk, 'jumlah' => 1],
                ],
            ])
                ->assertCreated()
                ->assertJsonPath('data.tipe_pesanan', 'takeaway');
        }

        $this->assertDatabaseHas('meja', [
            'id_meja' => $meja->id_meja,
            'status' => 'tersedia',
        ]);
        $this->assertDatabaseCount('pesanan', 2);
    }

    public function test_qr_takeaway_tetap_valid_dan_tersedia(): void
    {
        $meja = Meja::firstOrCreate(
            ['qr_code' => Meja::TAKEAWAY_QR_CODE],
            [
                'no_meja' => Meja::TAKEAWAY_NAME,
                'status' => 'tersedia',
            ],
        );

        $meja->update(['status' => 'terisi']);

        $this->getJson('/api/meja/validate/'.Meja::TAKEAWAY_QR_CODE)
            ->assertOk()
            ->assertJsonPath('data.is_takeaway', true)
            ->assertJsonPath('data.status', 'tersedia');

        $this->assertDatabaseHas('meja', [
            'id_meja' => $meja->id_meja,
            'status' => 'tersedia',
        ]);
    }

    public function test_tipe_pesanan_dine_in_tersimpan_dari_form(): void
    {
        $meja = $this->buatMeja();
        $produk = $this->buatProduk();

        $response = $this->postJson('/api/pesanan', [
            'id_meja' => $meja->id_meja,
            'tipe_pesanan' => 'dine_in',
            'nama_pemesan' => 'Pemesan Dine In',
            'items' => [
                ['id_produk' => $produk->id_produk, 'jumlah' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tipe_pesanan', 'dine_in')
            ->assertJsonPath('data.is_takeaway', false);

        $this->assertDatabaseHas('pesanan', [
            'id_meja' => $meja->id_meja,
            'tipe_pesanan' => 'dine_in',
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

        // Halaman /status hanya kerangka; data pesanan diambil lewat API
        // memakai kode akses, jadi proteksinya diuji pada endpoint tersebut.
        $this->get('/status')->assertOk()->assertSee('Status Pesanan');

        $this->getJson('/api/pesanan/'.$order['id_pesanan'].'?kode='.$order['kode_akses'])
            ->assertOk()
            ->assertJsonPath('data.nomor_antrean', $order['nomor_antrean'])
            ->assertJsonPath('data.nama_pemesan', 'Tes Verifikasi');

        $this->getJson('/api/pesanan/'.$order['id_pesanan'].'?kode=kode-salah')
            ->assertNotFound();
    }
}
