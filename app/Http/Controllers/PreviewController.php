<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * PREVIEW ONLY — Tahap 2 (kerangka frontend).
 *
 * TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi.
 *
 * Controller ini hanya menampilkan halaman login dan dashboard staff
 * menggunakan data contoh dari config/preview.php. Tidak ada autentikasi,
 * sesi login, query database, middleware, ataupun alur transaksi di sini.
 *
 * Hapus controller beserta route /login dan /preview/* setelah desain final
 * dan backend tersedia.
 */
class PreviewController extends Controller
{
    /** Halaman login (tampilan saja, belum terhubung autentikasi). */
    public function login(): View
    {
        return view('auth.login');
    }

    public function manager(): View
    {
        return $this->dashboard('manager');
    }

    public function kasir(): View
    {
        return $this->dashboard('kasir');
    }

    public function admin(): View
    {
        return $this->dashboard('admin');
    }

    public function kasirOrders(): View
    {
        return $this->page('kasir', 'orders');
    }

    public function kasirPayments(): View
    {
        return $this->page('kasir', 'payments');
    }

    public function kasirTables(): View
    {
        return $this->page('kasir', 'tables');
    }

    public function adminMenu(): View
    {
        return $this->page('admin', 'menu');
    }

    public function adminTables(): View
    {
        return $this->page('admin', 'tables');
    }

    public function adminInventory(): View
    {
        return $this->page('admin', 'inventory');
    }

    public function adminUsers(): View
    {
        return $this->page('admin', 'users');
    }

    public function adminSettings(): View
    {
        return $this->page('admin', 'settings');
    }

    public function managerReports(): View
    {
        return $this->page('manager', 'reports');
    }

    public function managerFinance(): View
    {
        return $this->page('manager', 'finance');
    }

    public function managerExport(): View
    {
        return $this->page('manager', 'export');
    }

    private function dashboard(string $role): View
    {
        $data = config('preview.roles.'.$role);

        abort_if($data === null, 404);

        return view('staff.'.$role, [
            'role' => $data,
            'preview' => config('preview'),
        ]);
    }

    private function page(string $role, string $page): View
    {
        $data = config('preview.roles.'.$role);
        $view = 'staff.'.$role.'.'.$page;

        abort_if($data === null || ! view()->exists($view), 404);

        return view($view, [
            'role' => $data,
            'preview' => config('preview'),
        ]);
    }
}
