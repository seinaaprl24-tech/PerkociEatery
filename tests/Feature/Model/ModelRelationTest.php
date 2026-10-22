<?php

namespace Tests\Feature\Model;

use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\MetodePembayaran;
use App\Models\Pemasukan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Memastikan relasi Eloquent pada seluruh model sesuai ERD.
 */
class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_relasi_pesanan_menghubungkan_meja_detail_dan_pembayaran(): void
    {
        $pesanan = $this->buatPesananLengkap();

        $this->assertInstanceOf(Meja::class, $pesanan->meja);
        $this->assertTrue($pesanan->meja->is($this->meja));
        $this->assertTrue($pesanan->detailPesanan->first()->is($this->detail));
        $this->assertTrue($pesanan->pembayaran->is($this->pembayaran));
    }

    public function test_relasi_produk_dan_stok(): void
    {
        $this->assertTrue($this->produk->stok->is($this->stok));
        $this->assertTrue($this->stok->produk->is($this->produk));
        $this->assertTrue($this->produk->detailPesanan->first()->is($this->detail));
    }

    public function test_relasi_transaksi_menghubungkan_pembayaran_user_dan_pemasukan(): void
    {
        $this->assertTrue($this->transaksi->pembayaran->is($this->pembayaran));
        $this->assertTrue($this->transaksi->user->is($this->user));
        $this->assertTrue($this->transaksi->pemasukan->first()->is($this->pemasukan));
    }

    public function test_relasi_melalui_user(): void
    {
        $this->assertTrue($this->user->transaksi->first()->is($this->transaksi));
        $this->assertTrue($this->user->pemasukan->first()->is($this->pemasukan));
        $this->assertTrue($this->user->pengeluaran->first()->is($this->pengeluaran));
    }

    public function test_relasi_melalui_meja_dan_metode_pembayaran(): void
    {
        $this->assertTrue($this->meja->pesanan->first()->is($this->pesanan));
        $this->assertTrue($this->metode->pembayaran->first()->is($this->pembayaran));
    }

    public function test_model_menggunakan_nama_tabel_dan_primary_key_erd(): void
    {
        $this->assertSame('meja', (new Meja)->getTable());
        $this->assertSame('id_meja', (new Meja)->getKeyName());
        $this->assertSame('detail_pesanan', (new DetailPesanan)->getTable());
        $this->assertSame('id_detail', (new DetailPesanan)->getKeyName());
        $this->assertSame('metode_pembayaran', (new MetodePembayaran)->getTable());
        $this->assertSame('id_metode', (new MetodePembayaran)->getKeyName());
        $this->assertSame('pembayaran', (new Pembayaran)->getTable());
        $this->assertSame('id_pembayaran', (new Pembayaran)->getKeyName());
        $this->assertSame('transaksi', (new Transaksi)->getTable());
        $this->assertSame('id_transaksi', (new Transaksi)->getKeyName());
        $this->assertSame('pemasukan', (new Pemasukan)->getTable());
        $this->assertSame('id_pemasukan', (new Pemasukan)->getKeyName());
        $this->assertSame('pengeluaran', (new Pengeluaran)->getTable());
        $this->assertSame('id_pengeluaran', (new Pengeluaran)->getKeyName());
        $this->assertSame('stok', (new Stok)->getTable());
        $this->assertSame('id_stok', (new Stok)->getKeyName());
        $this->assertSame('produk', (new Produk)->getTable());
        $this->assertSame('id_produk', (new Produk)->getKeyName());
        $this->assertSame('pesanan', (new Pesanan)->getTable());
        $this->assertSame('id_pesanan', (new Pesanan)->getKeyName());
    }

    protected User $user;

    protected Meja $meja;

    protected Produk $produk;

    protected Stok $stok;

    protected Pesanan $pesanan;

    protected DetailPesanan $detail;

    protected MetodePembayaran $metode;

    protected Pembayaran $pembayaran;

    protected Transaksi $transaksi;

    protected Pemasukan $pemasukan;

    protected Pengeluaran $pengeluaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->kasir()->create();
        $this->meja = Meja::create(['no_meja' => '01', 'qr_code' => 'MEJA-01', 'status' => 'tersedia']);
        $this->produk = Produk::create(['nama_produk' => 'Kopi Susu', 'kategori' => 'Signature Coffee', 'harga' => 18000, 'status' => 'aktif']);
        $this->stok = Stok::create(['id_produk' => $this->produk->id_produk, 'jumlah_stok' => 10, 'min_stok' => 2, 'updated_at' => now()]);
        $this->pesanan = Pesanan::create([
            'id_meja' => $this->meja->id_meja,
            'nomor_antrean' => 'A-001',
            'tanggal_pesan' => now(),
            'total_harga' => 36000,
            'status_pesanan' => 'menunggu',
        ]);
        $this->detail = DetailPesanan::create([
            'id_pesanan' => $this->pesanan->id_pesanan,
            'id_produk' => $this->produk->id_produk,
            'jumlah' => 2,
            'harga' => 18000,
            'subtotal' => 36000,
        ]);
        $this->metode = MetodePembayaran::create(['nama_metode' => 'Tunai']);
        $this->pembayaran = Pembayaran::create([
            'id_pesanan' => $this->pesanan->id_pesanan,
            'id_metode' => $this->metode->id_metode,
            'jumlah_bayar' => 40000,
            'kembalian' => 4000,
            'status_pembayaran' => 'lunas',
            'tanggal_pembayaran' => now(),
        ]);
        $this->transaksi = Transaksi::create([
            'id_pembayaran' => $this->pembayaran->id_pembayaran,
            'id_user' => $this->user->id_user,
            'tanggal_transaksi' => now(),
            'status_transaksi' => 'berhasil',
        ]);
        $this->pemasukan = Pemasukan::create([
            'id_transaksi' => $this->transaksi->id_transaksi,
            'id_user' => $this->user->id_user,
            'jumlah' => 36000,
            'keterangan' => 'Penjualan menu',
            'tanggal' => now()->toDateString(),
        ]);
        $this->pengeluaran = Pengeluaran::create([
            'id_user' => $this->user->id_user,
            'jumlah' => 15000,
            'keterangan' => 'Beli susu',
            'tanggal' => now()->toDateString(),
        ]);
    }

    protected function buatPesananLengkap(): Pesanan
    {
        return $this->pesanan;
    }
}
