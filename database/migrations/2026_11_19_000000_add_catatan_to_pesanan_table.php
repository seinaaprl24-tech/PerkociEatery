<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('pesanan', 'catatan')) {
                // Catatan umum pesanan dari pelanggan, diteruskan ke kasir.
                $table->text('catatan')->nullable()->after('no_whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('pesanan', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
    }
};
