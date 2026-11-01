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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id_pembayaran');
            $table->unsignedBigInteger('id_pesanan')->unique();
            $table->unsignedBigInteger('id_metode');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->decimal('kembalian', 15, 2)->nullable();
            $table->enum('status_pembayaran', ['belum_bayar', 'lunas', 'gagal', 'batal']);
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->timestamps();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan');
            $table->foreign('id_metode')->references('id_metode')->on('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
