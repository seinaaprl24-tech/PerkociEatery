<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Publik - untuk customer self order
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/meja/validate/{kode_qr}', [MejaController::class, 'validateQR']);
Route::get('/meja/qr/{kode_qr}', [MejaController::class, 'validateQR']);
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{produk}', [ProdukController::class, 'show']);
Route::get('/meja', [MejaController::class, 'index']);
Route::get('/meja/{meja}', [MejaController::class, 'show']);

// Customer bisa buat pesanan tanpa login (self order via QR)
Route::post('/pesanan', [PesananController::class, 'store']);
Route::get('/pesanan/{pesanan}', [PesananController::class, 'show']);
Route::get('/pesanan/{id}/struk', [PesananController::class, 'cetakStruk']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('produk', ProdukController::class)->except(['index', 'show']);
    Route::apiResource('meja', MejaController::class)->except(['index', 'show']);
    Route::apiResource('pesanan', PesananController::class)->except(['store', 'show']);
    Route::patch('pesanan/{pesanan}/status', [PesananController::class, 'update_status']);
    Route::apiResource('pembayaran', PembayaranController::class);
    Route::apiResource('users', UserController::class);

    Route::prefix('laporan')->group(function () {
        Route::get('/daily-stats', [LaporanController::class, 'getDailyStats']);
        Route::get('/top-products', [LaporanController::class, 'getTopProducts']);
        Route::get('/monthly-income', [LaporanController::class, 'getMonthlyIncome']);
        Route::get('/penjualan', [LaporanController::class, 'laporanPenjualan']);
    });
});
