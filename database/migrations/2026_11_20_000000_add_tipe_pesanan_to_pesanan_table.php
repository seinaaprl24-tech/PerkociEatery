<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan tipe pesanan (dine in / takeaway) dan meja khusus kasir
     * yang dipakai untuk alur takeaway via QR Code.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pesanan', 'tipe_pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                // VARCHAR (bukan ENUM) agar mudah menambah tipe baru di masa depan.
                $table->string('tipe_pesanan', 20)
                    ->default('dine_in')
                    ->after('id_meja');
            });
        }

        // Meja virtual untuk pesanan takeaway yang dibuat lewat QR kasir.
        $sudahAda = DB::table('meja')
            ->where('qr_code', 'TAKEAWAY-KASIR')
            ->exists();

        if (! $sudahAda) {
            DB::table('meja')->insert([
                'no_meja' => 'Takeaway Kasir',
                'qr_code' => 'TAKEAWAY-KASIR',
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pesanan', 'tipe_pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropColumn('tipe_pesanan');
            });
        }

        // Jangan hapus meja bila masih dipakai pesanan lama (foreign key).
        $idMeja = DB::table('meja')
            ->where('qr_code', 'TAKEAWAY-KASIR')
            ->value('id_meja');

        if ($idMeja && ! DB::table('pesanan')->where('id_meja', $idMeja)->exists()) {
            DB::table('meja')->where('id_meja', $idMeja)->delete();
        }
    }
};
