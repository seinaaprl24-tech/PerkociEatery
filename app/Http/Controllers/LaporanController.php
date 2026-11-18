<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function getDailyStats(): JsonResponse
    {
        $today = Carbon::today();
        $start = $today->copy()->startOfDay();
        $end = $today->copy()->endOfDay();

        $pesananDiproses = Pesanan::whereBetween('tanggal_pesan', [$start, $end])
            ->where('status_pesanan', 'diproses')
            ->count();

        $pesananSelesai = Pesanan::whereBetween('tanggal_pesan', [$start, $end])
            ->where('status_pesanan', 'selesai')
            ->count();

        return response()->json([
            'tanggal' => $today->toDateString(),
            'total_pemasukan' => $this->hitungPemasukan($start, $end),
            'jumlah_pesanan_diproses' => $pesananDiproses,
            'jumlah_pesanan_selesai' => $pesananSelesai,
        ]);
    }

    public function getTopProducts(): JsonResponse
    {
        $topProducts = DetailPesanan::select(
            'detail_pesanan.id_produk',
            'produk.nama_produk',
            'produk.kategori',
            'produk.harga',
            DB::raw('SUM(detail_pesanan.jumlah) as total_terjual'),
            DB::raw('SUM(detail_pesanan.subtotal) as total_pendapatan')
        )
            ->join('produk', 'produk.id_produk', '=', 'detail_pesanan.id_produk')
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'detail_pesanan.id_pesanan')
            ->where('pesanan.status_pesanan', 'selesai')
            ->groupBy('detail_pesanan.id_produk', 'produk.nama_produk', 'produk.kategori', 'produk.harga')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $topProducts,
        ]);
    }

    public function getMonthlyIncome(): JsonResponse
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        return response()->json([
            'bulan' => Carbon::now()->format('Y-m'),
            'total_pemasukan' => $this->hitungPemasukan($start, $end),
        ]);
    }

    public function laporanPenjualan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'metode' => ['nullable', 'string'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();
        $metode = $validated['metode'] ?? null;

        // Laporan penjualan = pesanan yang sudah selesai (sudah dibayar).
        $pesananSelesai = Pesanan::whereBetween('tanggal_pesan', [$start, $end])
            ->where('status_pesanan', 'selesai');

        if ($metode && strtolower($metode) !== 'all') {
            $pesananSelesai->whereHas('pembayaran.metodePembayaran', function ($query) use ($metode) {
                $query->whereRaw('LOWER(nama_metode) LIKE ?', ['%' . strtolower($metode) . '%']);
            });
        }

        $totalTransaksi = (clone $pesananSelesai)->count();

        $transaksi = (clone $pesananSelesai)
            ->with(['meja', 'detailPesanan.produk', 'pembayaran.metodePembayaran'])
            ->orderByDesc('tanggal_pesan')
            ->get()
            ->map(function (Pesanan $pesanan) {
                $item = $pesanan->toArray();
                $item['bukti_bayar_url'] = $pesanan->bukti_bayar
                    ? asset('storage/' . ltrim($pesanan->bukti_bayar, '/'))
                    : null;

                return $item;
            });

        return response()->json([
            'status' => 'success',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'metode' => $metode ?: 'all',
            'total_transaksi' => $totalTransaksi,
            'total_pendapatan' => $this->hitungPemasukan($start, $end, $metode),
            'data' => $transaksi,
        ]);
    }

    /**
     * Pemasukan bersih dari pembayaran lunas: jumlah_bayar dikurangi kembalian.
     * (jumlah_bayar adalah uang yang diserahkan customer, belum dipotong kembalian.)
     */
    private function hitungPemasukan(Carbon $start, Carbon $end, ?string $metode = null): float
    {
        $query = Pembayaran::whereBetween('tanggal_pembayaran', [$start, $end])
            ->where('status_pembayaran', 'lunas');

        if ($metode && strtolower($metode) !== 'all') {
            $query->whereHas('metodePembayaran', function ($q) use ($metode) {
                $q->whereRaw('LOWER(nama_metode) LIKE ?', ['%' . strtolower($metode) . '%']);
            });
        }

        return (float) $query->sum(DB::raw('jumlah_bayar - COALESCE(kembalian, 0)'));
    }
}
