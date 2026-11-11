<?php

namespace App\Http\Controllers;

use App\Models\Pesanan; // Sesuaikan jika nama model pesanan kamu berbeda (misal: Order)
use App\Support\Perkoci;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

/**
 * Halaman customer (self order).
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

    /** Status pesanan (Halaman Blade). */
    public function status(): View
    {
        return view('status');
    }

    /**
     * Endpoint API untuk mengambil status pesanan secara dinamis (Polling).
     * Diakses via GET /api/pesanan/{id}?kode={kode_akses}
     */
    public function getStatusApi(Request $request, string $id): JsonResponse
    {
        $kodeAkses = $request->query('kode');

        $pesanan = Pesanan::with(['items', 'meja'])
            ->where('id', $id)
            ->where('kode_akses', $kodeAkses)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan atau kode akses tidak valid.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pesanan->id,
                'nomor_antrean' => $pesanan->nomor_antrean, // Format ORD-20261105-001
                'nama_pemesan' => $pesanan->nama_pemesan,
                'meja' => $pesanan->meja ? $pesanan->meja->nama_meja : 'Meja --',
                'status' => $pesanan->status, // 'menunggu', 'diproses', 'selesai', 'dibatalkan'
                'created_at' => $pesanan->created_at ? $pesanan->created_at->format('H:i') . ' WIB' : '-',
                'updated_at' => $pesanan->updated_at ? $pesanan->updated_at->format('H:i') . ' WIB' : '-',
                'total' => $pesanan->total_harga ?? 0,
                'items' => $pesanan->items ?? [],
            ]
        ]);
    }
}
