<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('pesanan', 'nama_pemesan')) {
                $table->string('nama_pemesan', 100)->nullable()->after('nomor_antrean');
            }

            if (! Schema::hasColumn('pesanan', 'no_whatsapp')) {
                $table->string('no_whatsapp', 20)->nullable()->after('nama_pemesan');
            }

            if (! Schema::hasColumn('pesanan', 'kode_akses')) {
                // Kode acak yang hanya diketahui pemesan; dipakai membuka status pesanan tanpa login.
                $table->string('kode_akses', 32)->nullable()->unique()->after('no_whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('pesanan', 'kode_akses')) {
                $table->dropUnique(['kode_akses']);
                $table->dropColumn('kode_akses');
            }

            if (Schema::hasColumn('pesanan', 'no_whatsapp')) {
                $table->dropColumn('no_whatsapp');
            }

            if (Schema::hasColumn('pesanan', 'nama_pemesan')) {
                $table->dropColumn('nama_pemesan');
            }
        });
    }
};
