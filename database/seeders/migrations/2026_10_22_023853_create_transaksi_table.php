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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->bigIncrements('id_transaksi');
            $table->unsignedBigInteger('id_pembayaran')->unique();
            $table->unsignedBigInteger('id_user');
            $table->dateTime('tanggal_transaksi');
            $table->enum('status_transaksi', ['berhasil', 'batal']);
            $table->timestamps();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran');
            $table->foreign('id_user')->references('id_user')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
