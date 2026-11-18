<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
    $table->string('metode_pembayaran', 30)->default('tunai')->after('total_harga');
    $table->string('status_pembayaran', 30)->default('belum_bayar')->after('metode_pembayaran');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            //
        });
    }
};
