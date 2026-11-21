<?php

namespace Database\Seeders;

use App\Models\Meja;
use Illuminate\Database\Seeder;

/**
 * Data awal meja untuk uji alur pemesanan customer melalui QR Code.
 * qr_code adalah token yang divalidasi backend saat customer memindai QR.
 */
class MejaSeeder extends Seeder
{
    public function run(): void
    {
        for ($nomor = 1; $nomor <= 10; $nomor++) {
            $noMeja = str_pad((string) $nomor, 2, '0', STR_PAD_LEFT);

            Meja::updateOrCreate(
                ['qr_code' => 'MEJA-'.$noMeja],
                [
                    'no_meja' => $noMeja,
                    'status' => 'tersedia',
                ],
            );
        }

        // Meja virtual untuk pesanan takeaway yang dipesan lewat QR kasir.
        Meja::updateOrCreate(
            ['qr_code' => Meja::TAKEAWAY_QR_CODE],
            [
                'no_meja' => Meja::TAKEAWAY_NAME,
                'status' => 'tersedia',
            ],
        );
    }
}
