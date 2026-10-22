<?php

namespace Database\Seeders;

use App\Models\MetodePembayaran;
use Illuminate\Database\Seeder;

/**
 * Metode pembayaran sesuai proposal: Tunai, QRIS, E-Wallet.
 */
class MetodePembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $methods = ['Tunai', 'QRIS', 'E-Wallet'];

        foreach ($methods as $namaMetode) {
            MetodePembayaran::updateOrCreate(['nama_metode' => $namaMetode]);
        }
    }
}
