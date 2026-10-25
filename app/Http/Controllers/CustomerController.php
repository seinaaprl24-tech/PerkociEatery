<?php

namespace App\Http\Controllers;

use App\Support\Perkoci;
use Illuminate\Contracts\View\View;

/**
 * Halaman customer (self order). Seluruh data berasal dari config/perkoci.php.
 */
class CustomerController extends Controller
{
    /** Cover / splash screen, redirect otomatis ke katalog. */
    public function cover(): View
    {
        return view('cover');
    }

    /** Katalog menu. */
    public function menu(): View
    {
        return view('menu.index', [
            'menu' => Perkoci::menu(),
        ]);
    }

    /** Detail menu + varian. */
    public function menuShow(string $slug): View
    {
        $item = Perkoci::menuBySlug($slug);

        abort_if($item === null, 404);

        return view('menu.show', [
            'item' => $item,
        ]);
    }

    /** Keranjang / pesanan saya. */
    public function cart(): View
    {
        $items = Perkoci::cartItems();

        return view('keranjang', [
            'items' => $items,
            'totals' => Perkoci::cartTotals($items),
        ]);
    }

    /** Status pesanan (placeholder, diisi pada bagian berikutnya). */
    public function status(): View
    {
        return view('status');
    }
}
