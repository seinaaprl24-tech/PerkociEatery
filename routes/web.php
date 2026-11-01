<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\StaffPageController;
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
| Login & Logout staff (session)
|--------------------------------------------------------------------------
| GET /login bernama "login" supaya middleware auth dan EnsureUserHasRole
| bisa mengarahkan user yang belum login ke halaman ini.
*/

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');

Route::post('/login', [WebAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.attempt');

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Halaman staff (wajib login + role yang sesuai)
|--------------------------------------------------------------------------
| Belum login   -> diarahkan ke /login
| Role tidak cocok -> 403
|
| Nama route: {role}.dashboard dan {role}.{halaman}, mis. kasir.orders.
*/

$staffPages = [
    'kasir' => ['orders', 'payments', 'tables'],
    'admin' => ['menu', 'tables', 'inventory', 'users', 'settings'],
    'manager' => ['reports', 'finance', 'export'],
];

foreach ($staffPages as $role => $pages) {
    Route::middleware(['auth', 'role:'.$role])
        ->prefix($role)
        ->name($role.'.')
        ->group(function () use ($role, $pages) {
            Route::get('/', [StaffPageController::class, 'dashboard'])
                ->defaults('role', $role)
                ->name('dashboard');

            foreach ($pages as $page) {
                Route::get('/'.$page, [StaffPageController::class, 'page'])
                    ->defaults('role', $role)
                    ->defaults('page', $page)
                    ->name($page);
            }
        });
}
