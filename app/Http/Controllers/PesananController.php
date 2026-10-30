<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Resources\PesananResource;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\UniqueConstraintViolationException;
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
     * Daftar pesanan, terbaru dulu.
     *
     * Parameter opsional:
     * - status   : satu atau beberapa status dipisah koma, mis. ?status=menunggu,diproses
     * - per_page : jumlah per halaman, 1 sampai 100 (bawaan 10)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pesanan::with(['meja', 'detailPesanan.produk'])
            ->orderByDesc('id_pesanan');

        $status = $request->query('status');

        if (is_string($status) && $status !== '') {
            $query->whereIn('status_pesanan', array_filter(explode(',', $status)));
        }

        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        return PesananResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function show(Pesanan $pesanan): PesananResource
    {
        return new PesananResource($pesanan->load(['meja', 'detailPesanan.produk']));
    }

    public function store(StorePesananRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pesanan = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $pesanan = $this->simpanPesanan($validated);
                break;
            } catch (UniqueConstraintViolationException $e) {
                // Nomor antrean bentrok dengan pesanan lain yang masuk bersamaan: coba lagi.
                if ($percobaan === 3) {
                    throw $e;
                }
            }
        }

        return (new PesananResource($pesanan->load(['meja', 'detailPesanan.produk'])))
            ->response()
            ->setStatusCode(201);
    }

    private function simpanPesanan(array $validated): Pesanan
    {
        return DB::transaction(function () use ($validated) {
            $pesanan = Pesanan::create([
                'id_meja' => $validated['id_meja'],
                'nomor_antrean' => $this->generateNomorAntrean(),
                'tanggal_pesan' => Carbon::now(),
                'total_harga' => 0,
                'status_pesanan' => 'menunggu',
            ]);

            $totalHarga = 0;

            foreach ($validated['items'] as $item) {
                // Harga SELALU dari database, bukan dari request client.
                $produk = Produk::findOrFail($item['id_produk']);

                $stok = Stok::where('id_produk', $produk->id_produk)
                    ->lockForUpdate()
                    ->first();

                if (! $stok || $stok->jumlah_stok < $item['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stok untuk {$produk->nama_produk} tidak mencukupi."],
                    ]);
                }

                $stok->decrement('jumlah_stok', $item['jumlah']);

                $harga = (float) $produk->harga;
                $subtotal = $harga * $item['jumlah'];
                $totalHarga += $subtotal;

                $detailData = [
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_produk' => $produk->id_produk,
                    'jumlah' => $item['jumlah'],
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                    'topping' => $item['topping'] ?? null,
                    'gula' => $item['gula'] ?? null,
                    'es' => $item['es'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                ];

                if (array_key_exists('customisasi', $item)) {
                    $detailData['customisasi'] = $item['customisasi'];
                }

                DetailPesanan::create($detailData);
            }

            $pesanan->update(['total_harga' => $totalHarga]);

            return $pesanan;
        });
    }

    public function update_status(Request $request, Pesanan $pesanan): PesananResource
    {
        $validated = $request->validate([
            'status_pesanan' => ['required', Rule::in(['menunggu', 'diproses', 'selesai', 'batal'])],
        ]);

        $statusBaru = $validated['status_pesanan'];
        $statusLama = $pesanan->status_pesanan;

        // Pesanan yang sudah batal tidak boleh diubah lagi
        // (mencegah stok dikembalikan dua kali).
        if ($statusLama === 'batal' && $statusBaru !== 'batal') {
            throw ValidationException::withMessages([
                'status_pesanan' => ['Pesanan yang sudah dibatalkan tidak dapat diubah.'],
            ]);
        }

        DB::transaction(function () use ($pesanan, $statusBaru, $statusLama) {
            if ($statusBaru === 'batal' && $statusLama !== 'batal') {
                $this->kembalikanStok($pesanan);
            }

            $pesanan->update(['status_pesanan' => $statusBaru]);
        });

        return new PesananResource($pesanan->load(['meja', 'detailPesanan.produk']));
    }

    public function destroy(Pesanan $pesanan): JsonResponse
    {
        DB::transaction(function () use ($pesanan) {
            if ($pesanan->status_pesanan !== 'batal') {
                $this->kembalikanStok($pesanan);

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

    public function cetakStruk($id): JsonResponse
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.produk', 'pembayaran'])
            ->findOrFail($id);

        $resource = (new PesananResource($pesanan))->resolve();

        $items = [];
        if (isset($resource['detail_pesanan'])) {
            $items = $resource['detail_pesanan'];
        } elseif (isset($resource['items'])) {
            $items = $resource['items'];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'info_coffee_shop' => [
                    'nama' => 'PerkociEatery',
                    'alamat' => '-',
                    'telepon' => '-',
                ],
                'nomor_antrean' => $resource['nomor_antrean'] ?? $pesanan->nomor_antrean,
                'nama_meja' => $resource['meja']['no_meja'] ?? null,
                'meja' => $resource['meja'] ?? null,
                'daftar_item' => $items,
                'items' => $items,
                'total_harga' => $resource['total_harga'] ?? (float) $pesanan->total_harga,
                'metode_pembayaran' => $resource['pembayaran']['metode_pembayaran'] ?? $pesanan->pembayaran->metodePembayaran->nama_metode ?? null,
                'kembalian' => $resource['pembayaran']['kembalian'] ?? $pesanan->pembayaran->kembalian ?? null,
                'tanggal' => $resource['tanggal_pesan'] ?? $pesanan->tanggal_pesan,
                'tanggal_pesan' => $resource['tanggal_pesan'] ?? $pesanan->tanggal_pesan,
                'pembayaran' => $resource['pembayaran'] ?? null,
            ],
        ]);
    }

    /**
     * Kembalikan stok semua item pada pesanan (dipakai saat pesanan dibatalkan).
     */
    private function kembalikanStok(Pesanan $pesanan): void
    {
        $pesanan->loadMissing('detailPesanan');

        foreach ($pesanan->detailPesanan as $detail) {
            $stok = Stok::where('id_produk', $detail->id_produk)->lockForUpdate()->first();

            if ($stok) {
                $stok->increment('jumlah_stok', $detail->jumlah);
            }
        }
    }

    private function generateNomorAntrean(): string
    {
        $date = Carbon::now()->format('Ymd');
        $count = Pesanan::whereDate('tanggal_pesan', Carbon::now()->toDateString())->count() + 1;

        return 'ORD-' . $date . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }
}
