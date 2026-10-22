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
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->bigIncrements('id_detail');
            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_produk');
            $table->integer('jumlah');
            $table->decimal('harga', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('topping')->nullable();
            $table->string('gula')->nullable();
            $table->string('es')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
