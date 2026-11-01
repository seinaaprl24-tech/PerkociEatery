<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       \App\Models\Produk::created(function ($produk) {
       \App\Models\Stok::firstOrCreate(
         ['id_produk' => $produk->id_produk],
           ['jumlah_stok' => 0, 'min_stok' => 0, 'updated_at' => now()->toDateTimeString()]
       );
   });
    }
}
