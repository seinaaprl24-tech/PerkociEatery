<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\Seeder;

/**
 * Data awal menu beserta stoknya, dipakai frontend saat integrasi.
 */
class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produk = [
            ['nama_produk' => 'Amci', 'kategori' => 'Signature Coffee', 'harga' => 18000, 'deskripsi' => 'Ice Americano dengan Syrup Mix Berry', 'jumlah_stok' => 38, 'min_stok' => 10],
            ['nama_produk' => 'Es Coklat', 'kategori' => 'Non-Kopi & Teh', 'harga' => 13000, 'deskripsi' => null, 'jumlah_stok' => 25, 'min_stok' => 8],
            ['nama_produk' => 'Matcha Latte', 'kategori' => 'Non-Kopi & Teh', 'harga' => 18000, 'deskripsi' => null, 'jumlah_stok' => 30, 'min_stok' => 8],
            ['nama_produk' => 'Patuha SC', 'kategori' => 'Signature Coffee', 'harga' => 25000, 'deskripsi' => 'Kopi Filter Full Arabica', 'jumlah_stok' => 14, 'min_stok' => 8],
            ['nama_produk' => "American's Pep", 'kategori' => 'Signature Coffee', 'harga' => 15000, 'deskripsi' => 'Ice Americano', 'jumlah_stok' => 18, 'min_stok' => 8],
            ['nama_produk' => 'Tambakguruyung SC', 'kategori' => 'Signature Coffee', 'harga' => 25000, 'deskripsi' => 'Kopi Filter Houseblend', 'jumlah_stok' => 10, 'min_stok' => 5],
            ['nama_produk' => 'Teh Manis', 'kategori' => 'Non-Kopi & Teh', 'harga' => 5000, 'deskripsi' => null, 'jumlah_stok' => 12, 'min_stok' => 5],
            ['nama_produk' => 'Kopsuksar', 'kategori' => 'Signature Coffee', 'harga' => 18000, 'deskripsi' => 'Es Kopi Susu Gula Aren', 'jumlah_stok' => 10, 'min_stok' => 5],
            ['nama_produk' => 'Es Jeruk', 'kategori' => 'Non-Kopi & Teh', 'harga' => 8000, 'deskripsi' => null, 'jumlah_stok' => 10, 'min_stok' => 0],
            ['nama_produk' => 'Sate Maranggi', 'kategori' => 'Makanan Utama', 'harga' => 3000, 'deskripsi' => '3k/tusuk', 'jumlah_stok' => 100, 'min_stok' => 0],
            ['nama_produk' => 'Nasi', 'kategori' => 'Makanan Utama', 'harga' => 4000, 'deskripsi' => null, 'jumlah_stok' => 50, 'min_stok' => 0],
            ['nama_produk' => 'Lontong', 'kategori' => 'Makanan Utama', 'harga' => 4000, 'deskripsi' => null, 'jumlah_stok' => 50, 'min_stok' => 0],
            ['nama_produk' => 'Nasi Ayam Goreng', 'kategori' => 'Makanan Utama', 'harga' => 22000, 'deskripsi' => null, 'jumlah_stok' => 50, 'min_stok' => 0],
            ['nama_produk' => 'Dimsum', 'kategori' => 'Snack & Dessert', 'harga' => 18000, 'deskripsi' => null, 'jumlah_stok' => 20, 'min_stok' => 0],
            ['nama_produk' => 'Tahu Cabe Garam', 'kategori' => 'Snack & Dessert', 'harga' => 10000, 'deskripsi' => null, 'jumlah_stok' => 20, 'min_stok' => 0],
            ['nama_produk' => 'Mix Platte', 'kategori' => 'Snack & Dessert', 'harga' => 18000, 'deskripsi' => 'Kentang Goreng, Sosis dan Nugget', 'jumlah_stok' => 20, 'min_stok' => 0],
        ];

        foreach ($produk as $data) {
            $produkModel = Produk::updateOrCreate(
                ['nama_produk' => $data['nama_produk'], 'kategori' => $data['kategori']],
                [
                    'harga' => $data['harga'],
                    'deskripsi' => $data['deskripsi'],
                    'status' => 'aktif',
                ],
            );

            Stok::updateOrCreate(
                ['id_produk' => $produkModel->id_produk],
                [
                    'jumlah_stok' => $data['jumlah_stok'],
                    'min_stok' => $data['min_stok'],
                    'updated_at' => now(),
                ],
            );
        }
    }
}
