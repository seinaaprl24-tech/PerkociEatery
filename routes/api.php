<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/meja/validate/{kode_qr}', [MejaController::class, 'validateQR']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('produk', ProdukController::class);
    Route::apiResource('meja', MejaController::class);
    Route::apiResource('pesanan', PesananController::class)->except(['update']);
    Route::patch('pesanan/{pesanan}/status', [PesananController::class, 'update_status']);
    Route::get('pesanan/{id}/struk', [PesananController::class, 'cetakStruk']);
    Route::apiResource('pembayaran', PembayaranController::class);
    Route::apiResource('users', UserController::class);

    Route::prefix('laporan')->group(function () {
        Route::get('/daily-stats', [LaporanController::class, 'getDailyStats']);
        Route::get('/top-products', [LaporanController::class, 'getTopProducts']);
        Route::get('/monthly-income', [LaporanController::class, 'getMonthlyIncome']);
        Route::get('/penjualan', [LaporanController::class, 'laporanPenjualan']);
    });
});
