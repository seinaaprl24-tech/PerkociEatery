<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------
// Publik - untuk customer self order (via QR, tanpa login)
// ---------------------------------------------------------------------
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/meja/validate/{kode_qr}', [MejaController::class, 'validateQR']);
Route::get('/meja/qr/{kode_qr}', [MejaController::class, 'validateQR']);

Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{produk}', [ProdukController::class, 'show']);

Route::post('/pesanan', [PesananController::class, 'store'])->middleware('throttle:20,1');
Route::get('/pesanan/{pesanan}', [PesananController::class, 'show']);
Route::get('/pesanan/{id}/struk', [PesananController::class, 'cetakStruk']);

// ---------------------------------------------------------------------
// Staff (butuh token Sanctum)
// ---------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);

    // Kasir & admin: operasional harian
    Route::middleware('role:kasir,admin')->group(function () {
        Route::get('meja', [MejaController::class, 'index']);
        Route::get('meja/{meja}', [MejaController::class, 'show']);

        Route::apiResource('pesanan', PesananController::class)->only(['index', 'destroy']);
        Route::patch('pesanan/{pesanan}/status', [PesananController::class, 'update_status']);

        Route::apiResource('pembayaran', PembayaranController::class)
            ->only(['index', 'show', 'store', 'destroy']);
    });

    // Admin saja: kelola master data & akun
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('produk', ProdukController::class)->except(['index', 'show']);
        Route::apiResource('meja', MejaController::class)->except(['index', 'show']);
        Route::apiResource('users', UserController::class);
        Route::patch('produk/{produk}/stok', [\App\Http\Controllers\StokController::class, 'update']);
    });


    // Manager & admin: laporan
    Route::middleware('role:manager,admin')->prefix('laporan')->group(function () {
        Route::get('/daily-stats', [LaporanController::class, 'getDailyStats']);
        Route::get('/top-products', [LaporanController::class, 'getTopProducts']);
        Route::get('/monthly-income', [LaporanController::class, 'getMonthlyIncome']);
        Route::get('/penjualan', [LaporanController::class, 'laporanPenjualan']);
    });
});
