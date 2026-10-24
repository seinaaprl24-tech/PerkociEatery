<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('produk', ProdukController::class);
    Route::apiResource('meja', MejaController::class);
    Route::apiResource('pesanan', PesananController::class)->except(['update']);
    Route::patch('pesanan/{pesanan}/status', [PesananController::class, 'update_status']);
});
