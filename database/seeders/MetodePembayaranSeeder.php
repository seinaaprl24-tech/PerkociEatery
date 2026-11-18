<?php

namespace Database\Seeders;

use App\Models\MetodePembayaran;
use Illuminate\Database\Seeder;

/**
 * Metode pembayaran: Tunai dan QRIS.
 */
class MetodePembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $methods = ['Tunai', 'QRIS'];

        foreach ($methods as $namaMetode) {
            MetodePembayaran::updateOrCreate(['nama_metode' => $namaMetode]);
        }
    }
}
