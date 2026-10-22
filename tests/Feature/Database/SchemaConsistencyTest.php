<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Memastikan struktur database tetap konsisten dengan ERD:
 * primary key, foreign key, dan kolom wajib tiap tabel.
 */
class SchemaConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seluruh_tabel_erd_tersedia(): void
    {
        $tables = [
            'users', 'meja', 'produk', 'stok', 'pesanan', 'detail_pesanan',
            'metode_pembayaran', 'pembayaran', 'transaksi', 'pemasukan', 'pengeluaran',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} tidak ada.");
        }
    }

    public function test_primary_key_users_menggunakan_id_user(): void
    {
        $this->assertSame(['id_user'], $this->primaryKey('users'));
        $this->assertTrue(Schema::hasColumn('users', 'nama'));
        $this->assertTrue(Schema::hasColumn('users', 'username'));
        $this->assertTrue(Schema::hasColumn('users', 'password'));
        $this->assertTrue(Schema::hasColumn('users', 'role'));
    }

    public function test_primary_key_tabel_lain_sesuai_erd(): void
    {
        $expected = [
            'meja' => 'id_meja',
            'produk' => 'id_produk',
            'stok' => 'id_stok',
            'pesanan' => 'id_pesanan',
            'detail_pesanan' => 'id_detail',
            'metode_pembayaran' => 'id_metode',
            'pembayaran' => 'id_pembayaran',
            'transaksi' => 'id_transaksi',
            'pemasukan' => 'id_pemasukan',
            'pengeluaran' => 'id_pengeluaran',
        ];

        foreach ($expected as $table => $primaryKey) {
            $this->assertSame([$primaryKey], $this->primaryKey($table), "Primary key {$table} tidak sesuai.");
        }
    }

    public function test_foreign_key_menunjuk_ke_tabel_yang_benar(): void
    {
        $expected = [
            'stok' => [['id_produk', 'produk', 'id_produk']],
            'pesanan' => [['id_meja', 'meja', 'id_meja']],
            'detail_pesanan' => [['id_pesanan', 'pesanan', 'id_pesanan'], ['id_produk', 'produk', 'id_produk']],
            'pembayaran' => [['id_pesanan', 'pesanan', 'id_pesanan'], ['id_metode', 'metode_pembayaran', 'id_metode']],
            'transaksi' => [['id_pembayaran', 'pembayaran', 'id_pembayaran'], ['id_user', 'users', 'id_user']],
            'pemasukan' => [['id_transaksi', 'transaksi', 'id_transaksi'], ['id_user', 'users', 'id_user']],
            'pengeluaran' => [['id_user', 'users', 'id_user']],
        ];

        foreach ($expected as $table => $relations) {
            $actual = collect($this->foreignKeys($table))
                ->map(fn (array $fk) => [$fk['columns'][0], $fk['foreign_table'], $fk['foreign_columns'][0]])
                ->all();

            foreach ($relations as $relation) {
                $this->assertContains($relation, $actual, "Foreign key {$table} -> {$relation[1]} tidak sesuai.");
            }
        }
    }

    public function test_kolom_unik_sesuai_erd(): void
    {
        $this->assertTrue($this->hasUniqueColumn('users', 'username'));
        $this->assertTrue($this->hasUniqueColumn('meja', 'qr_code'));
        $this->assertTrue($this->hasUniqueColumn('stok', 'id_produk'));
        $this->assertTrue($this->hasUniqueColumn('pesanan', 'nomor_antrean'));
        $this->assertTrue($this->hasUniqueColumn('pembayaran', 'id_pesanan'));
        $this->assertTrue($this->hasUniqueColumn('transaksi', 'id_pembayaran'));
    }

    public function test_pemasukan_wajib_terisi_transaksi(): void
    {
        $this->assertFalse(Schema::getColumnListing('pemasukan') === []);

        $kolom = collect(Schema::getColumns('pemasukan'))
            ->firstWhere('name', 'id_transaksi');

        $this->assertNotNull($kolom);
        $this->assertFalse((bool) $kolom['nullable'], 'Kolom pemasukan.id_transaksi harus NOT NULL.');
    }

    public function test_pesanan_tidak_wajib_login_pelanggan(): void
    {
        // Customer tidak memiliki akun, sehingga pesanan tidak boleh terikat ke tabel users.
        $this->assertFalse(Schema::hasColumn('pesanan', 'id_user'));
        $this->assertFalse(Schema::hasColumn('detail_pesanan', 'id_user'));
    }

    /**
     * @return array<int, string>
     */
    protected function primaryKey(string $table): array
    {
        foreach (Schema::getIndexes($table) as $index) {
            if ($index['primary'] ?? false) {
                return $index['columns'];
            }
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function foreignKeys(string $table): array
    {
        return Schema::getForeignKeys($table);
    }

    protected function hasUniqueColumn(string $table, string $column): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false) && $index['columns'] === [$column]) {
                return true;
            }
        }

        return false;
    }
}
