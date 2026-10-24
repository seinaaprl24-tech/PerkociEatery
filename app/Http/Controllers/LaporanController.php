<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Ringkasan data hari ini.
     */
    public function getDailyStats(): JsonResponse
    {
        $today = Carbon::today();

        $totalPemasukan = Pembayaran::whereDate('tanggal_pembayaran', $today)
            ->where('status_pembayaran', 'lunas')
            ->sum('jumlah_bayar');

        if ($totalPemasukan == 0) {
            $totalPemasukan = Pesanan::whereDate('tanggal_pesan', $today)
                ->where('status_pesanan', 'selesai')
                ->sum('total_harga');
        }

        $pesananDiproses = Pesanan::whereDate('tanggal_pesan', $today)
            ->where('status_pesanan', 'diproses')
            ->count();

        $pesananSelesai = Pesanan::whereDate('tanggal_pesan', $today)
            ->whereIn('status_pesanan', ['selesai', 'lunas'])
            ->count();

        if ($pesananSelesai == 0) {
            $pesananSelesai = Pesanan::whereDate('tanggal_pesan', $today)
                ->where('status_pesanan', 'selesai')
                ->count();
        }

        return response()->json([
            'tanggal' => $today->toDateString(),
            'total_pemasukan' => (float) $totalPemasukan,
            'jumlah_pesanan_diproses' => $pesananDiproses,
            'jumlah_pesanan_selesai' => $pesananSelesai,
        ]);
    }

    /**
     * 5 produk terlaris berdasarkan total qty terjual.
     */
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
            ->whereIn('pesanan.status_pesanan', ['selesai', 'lunas'])
            ->groupBy('detail_pesanan.id_produk', 'produk.nama_produk', 'produk.kategori', 'produk.harga')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $topProducts,
        ]);
    }

    /**
     * Total pemasukan bulan berjalan.
     */
    public function getMonthlyIncome(): JsonResponse
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalPemasukan = Pembayaran::whereBetween('tanggal_pembayaran', [$startOfMonth, $endOfMonth])
            ->where('status_pembayaran', 'lunas')
            ->sum('jumlah_bayar');

        if ($totalPemasukan == 0) {
            $totalPemasukan = Pesanan::whereBetween('tanggal_pesan', [$startOfMonth, $endOfMonth])
                ->where('status_pesanan', 'selesai')
                ->sum('total_harga');
        }

        return response()->json([
            'bulan' => Carbon::now()->format('Y-m'),
            'total_pemasukan' => (float) $totalPemasukan,
        ]);
    }
}
