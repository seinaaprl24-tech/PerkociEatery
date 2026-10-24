<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Resources\PesananResource;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    /**
     * Menampilkan daftar pesanan dengan pagination.
     */
    public function index(): AnonymousResourceCollection
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.produk'])
            ->orderByDesc('id_pesanan')
            ->paginate(10);

        return PesananResource::collection($pesanan);
    }

    /**
     * Menampilkan detail pesanan lengkap.
     */
    public function show(Pesanan $pesanan): PesananResource
    {
        return new PesananResource($pesanan->load(['meja', 'detailPesanan.produk']));
    }

    /**
     * Membuat pesanan baru.
     */
    public function store(StorePesananRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pesanan = DB::transaction(function () use ($validated) {
            $totalHarga = 0;

            foreach ($validated['items'] as $item) {
                $totalHarga += ($item['harga_satuan'] * $item['jumlah']);
            }

            $nomorAntrean = $this->generateNomorAntrean();

            $pesanan = Pesanan::create([
                'id_meja' => $validated['id_meja'],
                'nomor_antrean' => $nomorAntrean,
                'tanggal_pesan' => Carbon::now(),
                'total_harga' => $totalHarga,
                'status_pesanan' => 'menunggu',
            ]);

            foreach ($validated['items'] as $item) {
                $produk = Produk::find($item['id_produk']);
                $stok = Stok::where('id_produk', $item['id_produk'])->lockForUpdate()->first();

                if (! $stok || $stok->jumlah_stok < $item['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => ['Stok untuk produk tidak mencukupi.'],
                    ]);
                }

                $stok->decrement('jumlah_stok', $item['jumlah']);

                $subtotal = $item['harga_satuan'] * $item['jumlah'];

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga_satuan'],
                    'subtotal' => $subtotal,
                    'topping' => $item['topping'] ?? null,
                    'gula' => $item['gula'] ?? null,
                    'es' => $item['es'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }

            return $pesanan;
        });

        return (new PesananResource($pesanan->load(['meja', 'detailPesanan.produk'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update status pesanan.
     */
    public function update_status(Request $request, Pesanan $pesanan): PesananResource
    {
        $validated = $request->validate([
            'status_pesanan' => ['required', Rule::in(['menunggu', 'diproses', 'selesai', 'batal'])],
        ]);

        $pesanan->update([
            'status_pesanan' => $validated['status_pesanan'],
        ]);

        return new PesananResource($pesanan->load(['meja', 'detailPesanan.produk']));
    }

    /**
     * Batalkan/hapus pesanan dan kembalikan stok.
     */
    public function destroy(Pesanan $pesanan): JsonResponse
    {
        DB::transaction(function () use ($pesanan) {
            if ($pesanan->status_pesanan !== 'batal') {
                $pesanan->load('detailPesanan');

                foreach ($pesanan->detailPesanan as $detail) {
                    $stok = Stok::where('id_produk', $detail->id_produk)->lockForUpdate()->first();

                    if ($stok) {
                        $stok->increment('jumlah_stok', $detail->jumlah);
                    }
                }

                $pesanan->update([
                    'status_pesanan' => 'batal',
                ]);

                return;
            }

            $pesanan->detailPesanan()->delete();
            $pesanan->delete();
        });

        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan/dihapus.',
        ]);
    }

    private function generateNomorAntrean(): string
    {
        $date = Carbon::now()->format('Ymd');
        $count = Pesanan::whereDate('tanggal_pesan', Carbon::now()->toDateString())->count() + 1;

        return 'ANT-' . $date . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
