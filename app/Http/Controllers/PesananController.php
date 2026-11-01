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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.produk'])
            ->orderByDesc('id_pesanan')
            ->paginate(10);

        return PesananResource::collection($pesanan);
    }

    public function show(Request $request, Pesanan $pesanan): PesananResource
    {
        $this->authorizeAccess($request, $pesanan);

        return new PesananResource($pesanan->load(['meja', 'detailPesanan.produk', 'pembayaran']));
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
                'nama_pemesan' => $validated['nama_pemesan'] ?? null,
                'no_whatsapp' => $validated['no_whatsapp'] ?? null,
                'kode_akses' => Str::random(12),
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
        // Pesanan yang sudah dibayar tidak boleh dibatalkan/diubah, supaya laporan
        // pemasukan tidak menyimpang dari status pesanan.
        if ($statusLama === 'selesai' && $statusBaru !== 'selesai') {
            throw ValidationException::withMessages([
                'status_pesanan' => ['Pesanan yang sudah dibayar tidak dapat diubah atau dibatalkan. Batalkan pembayarannya terlebih dahulu.'],
            ]);
        }

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
        if ($pesanan->status_pesanan === 'selesai') {
            return response()->json([
                'message' => 'Pesanan yang sudah dibayar tidak dapat dibatalkan atau dihapus.',
            ], 409);
        }

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

    public function cetakStruk(Request $request, $id): JsonResponse
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.produk', 'pembayaran'])
            ->findOrFail($id);

        $this->authorizeAccess($request, $pesanan);

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
                'nama_pemesan' => $pesanan->nama_pemesan,
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
     * Pesanan hanya boleh dibuka oleh staff yang login, atau oleh pemesan yang
     * membawa kode_akses yang benar (?kode=...). Selain itu dijawab 404 supaya
     * keberadaan pesanan tidak bisa ditebak dari ID.
     */
    private function authorizeAccess(Request $request, Pesanan $pesanan): void
    {
        $user = $request->user('sanctum');

        if ($user && in_array($user->role, ['admin', 'kasir', 'manager'], true)) {
            return;
        }

        $kode = $request->query('kode');

        if (! is_string($kode) || $kode === '' || ! is_string($pesanan->kode_akses)
            || ! hash_equals($pesanan->kode_akses, $kode)) {
            abort(404);
        }
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

    /**
     * Nomor antrean harian: ORD-YYYYMMDD-001, 002, ...
     * Memakai nomor TERBESAR hari ini (bukan jumlah baris), jadi tetap aman
     * walaupun ada pesanan yang pernah dihapus.
     */
    private function generateNomorAntrean(): string
    {
        $prefix = 'ORD-'.Carbon::now()->format('Ymd').'-';

        $terakhir = Pesanan::where('nomor_antrean', 'like', $prefix.'%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(nomor_antrean, '-', -1) AS UNSIGNED) DESC")
            ->value('nomor_antrean');

        $berikutnya = $terakhir ? ((int) Str::afterLast($terakhir, '-')) + 1 : 1;

        return $prefix.str_pad((string) $berikutnya, 3, '0', STR_PAD_LEFT);
    }
}
