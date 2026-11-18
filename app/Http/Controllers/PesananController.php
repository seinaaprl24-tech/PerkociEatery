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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    /**
     * Daftar pesanan, terbaru dulu.
     * Pesanan QRIS yang belum upload bukti bayar tidak ditampilkan ke kasir/dapur.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pesanan::with(['meja', 'detailPesanan.produk'])
            ->orderByDesc('id_pesanan');

        if (Schema::hasColumn('pesanan', 'metode_pembayaran') && Schema::hasColumn('pesanan', 'status_pembayaran')) {
            $query->where(function ($q) {
                $q->where('metode_pembayaran', '!=', 'qris')
                    ->orWhereNull('metode_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 'belum_bayar');
            });
        }

        $status = $request->query('status');

        if (is_string($status) && $status !== '') {
            $query->whereIn('status_pesanan', array_filter(explode(',', $status)));
        }

        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        return PesananResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function show(Request $request, Pesanan $pesanan): PesananResource
    {
        $this->authorizeAccess($request, $pesanan);

        return new PesananResource($pesanan->load(['meja', 'detailPesanan.produk', 'pembayaran']));
    }

    public function store(StorePesananRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! isset($validated['metode_pembayaran'])) {
            $validated['metode_pembayaran'] = $request->input('metode_pembayaran', 'tunai');
        }

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
            $dataPesanan = [
                'id_meja' => $validated['id_meja'],
                'nomor_antrean' => $this->generateNomorAntrean(),
                'nama_pemesan' => $validated['nama_pemesan'] ?? null,
                'no_whatsapp' => $validated['no_whatsapp'] ?? null,
                'kode_akses' => Str::random(12),
                'tanggal_pesan' => Carbon::now(),
                'total_harga' => 0,
                'status_pesanan' => 'menunggu',
            ];

            if (Schema::hasColumn('pesanan', 'metode_pembayaran')) {
                $dataPesanan['metode_pembayaran'] = $validated['metode_pembayaran'] ?? 'tunai';
            }

            if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
                $dataPesanan['status_pembayaran'] = 'belum_bayar';
            }

            $pesanan = Pesanan::create($dataPesanan);

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

    /**
     * Customer wajib mengunggah bukti transfer QRIS agar pesanan diteruskan ke kasir.
     */
    public function konfirmasiQris(Request $request, Pesanan $pesanan): JsonResponse
    {
        $this->authorizeAccess($request, $pesanan);

        $request->validate([
            'bukti_bayar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'bukti_bayar.required' => 'Wajib unggah foto/screenshot bukti pembayaran QRIS terlebih dahulu.',
            'bukti_bayar.image' => 'File bukti pembayaran harus berupa gambar.',
            'bukti_bayar.mimes' => 'Format gambar bukti pembayaran harus JPG, JPEG, PNG, atau WEBP.',
            'bukti_bayar.max' => 'Ukuran file bukti pembayaran maksimal 5 MB.',
        ]);

        $path = $request->file('bukti_bayar')->store('bukti-qris', 'public');

        $updates = [];

        if (Schema::hasColumn('pesanan', 'metode_pembayaran')) {
            $updates['metode_pembayaran'] = 'qris';
        }

        if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
            $updates['status_pembayaran'] = 'menunggu_verifikasi';
        }

        if (Schema::hasColumn('pesanan', 'bukti_bayar')) {
            $updates['bukti_bayar'] = $path;
        }

        if (! empty($updates)) {
            $pesanan->update($updates);
        }

        return response()->json([
            'message' => 'Bukti pembayaran QRIS berhasil dikirim. Menunggu verifikasi kasir.',
            'data' => new PesananResource($pesanan->fresh(['meja', 'detailPesanan.produk'])),
        ]);
    }

    /**
     * Kasir menolak bukti pembayaran QRIS yang tidak valid.
     */
    public function tolakQris(Pesanan $pesanan): JsonResponse
    {
        if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
            $pesanan->update([
                'status_pembayaran' => 'ditolak',
            ]);
        }

        return response()->json([
            'message' => 'Bukti pembayaran QRIS ditolak. Pelanggan diminta mengunggah ulang.',
            'data' => new PesananResource($pesanan->fresh(['meja', 'detailPesanan.produk'])),
        ]);
    }

    public function update_status(Request $request, Pesanan $pesanan): PesananResource
    {
        $validated = $request->validate([
            'status_pesanan' => ['required', Rule::in(['menunggu', 'diproses', 'selesai', 'batal'])],
        ]);

        $statusBaru = $validated['status_pesanan'];
        $statusLama = $pesanan->status_pesanan;

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
        $prefix = 'ORD-'.Carbon::now()->format('Ymd').'-';

        $terakhir = Pesanan::where('nomor_antrean', 'like', $prefix.'%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(nomor_antrean, '-', -1) AS UNSIGNED) DESC")
            ->value('nomor_antrean');

        $berikutnya = $terakhir ? ((int) Str::afterLast($terakhir, '-')) + 1 : 1;

        return $prefix.str_pad((string) $berikutnya, 3, '0', STR_PAD_LEFT);
    }
}
