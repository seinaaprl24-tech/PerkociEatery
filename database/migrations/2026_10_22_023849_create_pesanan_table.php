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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->bigIncrements('id_pesanan');
            $table->unsignedBigInteger('id_meja');
            $table->string('nomor_antrean')->unique();
            $table->dateTime('tanggal_pesan');
            $table->decimal('total_harga', 15, 2);
            $table->enum('status_pesanan', ['menunggu', 'diproses', 'selesai', 'batal']);
            $table->timestamps();
            $table->foreign('id_meja')->references('id_meja')->on('meja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
