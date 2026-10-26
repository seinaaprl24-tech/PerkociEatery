<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Customer / Self Order
|--------------------------------------------------------------------------
*/

Route::get('/', [CustomerController::class, 'cover'])->name('cover');

Route::get('/menu', [CustomerController::class, 'menu'])->name('menu.index');

Route::get('/menu/{slug}', [CustomerController::class, 'menuShow'])->name('menu.show');

Route::get('/keranjang', [CustomerController::class, 'cart'])->name('keranjang');

Route::get('/status', [CustomerController::class, 'status'])->name('status');

/*
|--------------------------------------------------------------------------
| PREVIEW ONLY — kerangka UI staff (Tahap 2)
|--------------------------------------------------------------------------
| TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi.
|
| Route GET sementara untuk melihat tampilan. Bukan autentikasi sungguhan,
| tidak memakai middleware, dan tidak membuat sesi login. Hapus bagian ini
| (beserta PreviewController) saat backend & desain final siap.
*/

Route::get('/login', [PreviewController::class, 'login'])->name('preview.login');

Route::get('/preview/manager', [PreviewController::class, 'manager'])->name('preview.manager');

Route::get('/preview/kasir', [PreviewController::class, 'kasir'])->name('preview.kasir');

Route::get('/preview/admin', [PreviewController::class, 'admin'])->name('preview.admin');

// TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi.
Route::get('/preview/kasir/orders', [PreviewController::class, 'kasirOrders'])->name('preview.kasir.orders');
Route::get('/preview/kasir/payments', [PreviewController::class, 'kasirPayments'])->name('preview.kasir.payments');
Route::get('/preview/kasir/tables', [PreviewController::class, 'kasirTables'])->name('preview.kasir.tables');

// TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi.
Route::get('/preview/admin/menu', [PreviewController::class, 'adminMenu'])->name('preview.admin.menu');
Route::get('/preview/admin/tables', [PreviewController::class, 'adminTables'])->name('preview.admin.tables');
Route::get('/preview/admin/inventory', [PreviewController::class, 'adminInventory'])->name('preview.admin.inventory');
Route::get('/preview/admin/users', [PreviewController::class, 'adminUsers'])->name('preview.admin.users');
Route::get('/preview/admin/settings', [PreviewController::class, 'adminSettings'])->name('preview.admin.settings');

// TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi.
Route::get('/preview/manager/reports', [PreviewController::class, 'managerReports'])->name('preview.manager.reports');
Route::get('/preview/manager/finance', [PreviewController::class, 'managerFinance'])->name('preview.manager.finance');
Route::get('/preview/manager/export', [PreviewController::class, 'managerExport'])->name('preview.manager.export');

Route::post('/login', [WebAuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [\App\Http\Controllers\PreviewController::class, 'admin'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/kasir', [\App\Http\Controllers\PreviewController::class, 'kasir'])->name('kasir.dashboard');
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager', [\App\Http\Controllers\PreviewController::class, 'manager'])->name('manager.dashboard');
});

