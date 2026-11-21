<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Resources\PesananResource;
use App\Models\DetailPesanan;
use App\Models\Meja;
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
     * Menampilkan daftar pesanan.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pesanan::with([
            'meja',
            'detailPesanan.produk',
        ])->orderByDesc('id_pesanan');

        if (
            Schema::hasColumn('pesanan', 'metode_pembayaran') &&
            Schema::hasColumn('pesanan', 'status_pembayaran')
        ) {
            $query->where(function ($q) {
                $q->where('metode_pembayaran', '!=', 'qris')
                    ->orWhereNull('metode_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 'belum_bayar');
            });
        }

        $status = $request->query('status');

        if (is_string($status) && $status !== '') {
            $query->whereIn(
                'status_pesanan',
                array_filter(explode(',', $status))
            );
        }

        $perPage = min(
            max((int) $request->query('per_page', 10), 1),
            100
        );

        return PesananResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    /**
     * Menampilkan detail pesanan.
     */
    public function show(
        Request $request,
        Pesanan $pesanan
    ): PesananResource {
        $this->authorizeAccess($request, $pesanan);

        return new PesananResource(
            $pesanan->load([
                'meja',
                'detailPesanan.produk',
                'pembayaran',
            ])
        );
    }

    /**
     * Membuat pesanan baru.
     */
    public function store(StorePesananRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!isset($validated['metode_pembayaran'])) {
            $validated['metode_pembayaran'] =
                $request->input('metode_pembayaran', 'tunai');
        }

        // Meja takeaway kasir selalu menghasilkan pesanan takeaway,
        // meskipun client tidak mengirim tipe_pesanan.
        $validated['tipe_pesanan'] =
            $this->resolveTipePesanan($validated);

        $pesanan = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $pesanan = $this->simpanPesanan($validated);
                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($percobaan === 3) {
                    throw $e;
                }
            }
        }

        return (new PesananResource(
            $pesanan->load([
                'meja',
                'detailPesanan.produk',
            ])
        ))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Menyimpan pesanan menggunakan database transaction.
     */
    private function simpanPesanan(array $validated): Pesanan
    {
        return DB::transaction(function () use ($validated) {

            // Pesanan takeaway memakai satu meja virtual. Kunci baris meja
            // ini agar pesanan bersamaan diproses berurutan dan statusnya
            // tetap "tersedia" (tidak pernah terkunci sebagai "terisi").
            if (($validated['tipe_pesanan'] ?? 'dine_in') === 'takeaway') {
                $meja = Meja::whereKey($validated['id_meja'])
                    ->lockForUpdate()
                    ->first();

                $meja?->ensureAvailableIfTakeaway();
            }

            $dataPesanan = [
                'id_meja' => $validated['id_meja'],
                'nomor_antrean' => $this->generateNomorAntrean(),
                'nama_pemesan' => $validated['nama_pemesan'] ?? null,
                'no_whatsapp' => $validated['no_whatsapp'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'kode_akses' => Str::random(12),
                'tanggal_pesan' => Carbon::now(),
                'total_harga' => 0,
                'status_pesanan' => 'menunggu',
            ];

            if (Schema::hasColumn('pesanan', 'tipe_pesanan')) {
                $dataPesanan['tipe_pesanan'] =
                    $validated['tipe_pesanan'] ?? 'dine_in';
            }

            if (Schema::hasColumn('pesanan', 'metode_pembayaran')) {
                $dataPesanan['metode_pembayaran'] =
                    $validated['metode_pembayaran'] ?? 'tunai';
            }

            if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
                $dataPesanan['status_pembayaran'] = 'belum_bayar';
            }

            $pesanan = Pesanan::create($dataPesanan);

            $totalHarga = 0;

            foreach ($validated['items'] as $item) {

                // Mengambil harga produk dari database.
                $produk = Produk::findOrFail($item['id_produk']);

                // Mengunci stok selama transaksi.
                $stok = Stok::where(
                    'id_produk',
                    $produk->id_produk
                )
                    ->lockForUpdate()
                    ->first();

                if (!$stok || $stok->jumlah_stok < $item['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Stok untuk {$produk->nama_produk} tidak mencukupi.",
                        ],
                    ]);
                }

                // Mengurangi stok.
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

            // Memperbarui total harga.
            $pesanan->update([
                'total_harga' => $totalHarga,
            ]);

            // Dine-in menandai meja terisi; takeaway selalu tetap tersedia.
            $this->syncMejaStatus($pesanan);

            return $pesanan;
        });
    }

    /**
     * Konfirmasi pembayaran QRIS.
     */
    public function konfirmasiQris(
        Request $request,
        Pesanan $pesanan
    ): JsonResponse {
        $this->authorizeAccess($request, $pesanan);

        $request->validate([
            'bukti_bayar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $path = $request->file('bukti_bayar')
            ->store('bukti-qris', 'public');

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

        if (!empty($updates)) {
            $pesanan->update($updates);
        }

        return response()->json([
            'message' => 'Bukti pembayaran QRIS berhasil dikirim. Menunggu verifikasi kasir.',
            'data' => new PesananResource(
                $pesanan->fresh([
                    'meja',
                    'detailPesanan.produk',
                ])
            ),
        ]);
    }

    /**
     * Menolak bukti pembayaran QRIS.
     */
    public function tolakQris(
        Request $request,
        Pesanan $pesanan
    ): JsonResponse {
        $this->authorizeStaff($request);

        if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
            $pesanan->update([
                'status_pembayaran' => 'ditolak',
            ]);
        }

        return response()->json([
            'message' => 'Bukti pembayaran QRIS ditolak. Pelanggan diminta mengunggah ulang.',
            'data' => new PesananResource(
                $pesanan->fresh([
                    'meja',
                    'detailPesanan.produk',
                ])
            ),
        ]);
    }

    /**
     * Mengubah status pesanan.
     */
    public function update_status(
        Request $request,
        Pesanan $pesanan
    ): PesananResource {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'status_pesanan' => [
                'required',
                Rule::in([
                    'menunggu',
                    'diproses',
                    'selesai',
                    'batal',
                ]),
            ],
        ]);

        $statusBaru = $validated['status_pesanan'];
        $statusLama = $pesanan->status_pesanan;

        if ($statusLama === 'selesai' && $statusBaru !== 'selesai') {
            throw ValidationException::withMessages([
                'status_pesanan' => [
                    'Pesanan yang sudah selesai tidak dapat diubah atau dibatalkan.',
                ],
            ]);
        }

        if ($statusLama === 'batal' && $statusBaru !== 'batal') {
            throw ValidationException::withMessages([
                'status_pesanan' => [
                    'Pesanan yang sudah dibatalkan tidak dapat diubah.',
                ],
            ]);
        }

        DB::transaction(function () use (
            $pesanan,
            $statusBaru,
            $statusLama
        ) {
            $pesanan = Pesanan::where(
                'id_pesanan',
                $pesanan->id_pesanan
            )
                ->lockForUpdate()
                ->firstOrFail();

            if ($pesanan->status_pesanan !== $statusLama) {
                throw ValidationException::withMessages([
                    'status_pesanan' => [
                        'Status pesanan sudah berubah. Silakan muat ulang.',
                    ],
                ]);
            }

            if (
                $statusBaru === 'batal' &&
                $pesanan->status_pesanan !== 'batal'
            ) {
                $this->kembalikanStok($pesanan);
            }

            $pesanan->update([
                'status_pesanan' => $statusBaru,
            ]);

            // Lepas meja dine-in bila tidak ada lagi pesanan aktif.
            $this->syncMejaStatus($pesanan);
        });

        return new PesananResource(
            $pesanan->fresh([
                'meja',
                'detailPesanan.produk',
            ])
        );
    }

    /**
     * Membatalkan atau menghapus pesanan.
     */
    public function destroy(
        Request $request,
        Pesanan $pesanan
    ): JsonResponse {
        $this->authorizeStaff($request);

        return DB::transaction(function () use ($pesanan) {

            $pesanan = Pesanan::where(
                'id_pesanan',
                $pesanan->id_pesanan
            )
                ->lockForUpdate()
                ->firstOrFail();

            if ($pesanan->status_pesanan === 'selesai') {
                return response()->json([
                    'message' => 'Pesanan yang sudah selesai tidak dapat dibatalkan atau dihapus.',
                ], 409);
            }

            if ($pesanan->status_pesanan !== 'batal') {
                $this->kembalikanStok($pesanan);

                $pesanan->update([
                    'status_pesanan' => 'batal',
                ]);

                $this->syncMejaStatus($pesanan);

                return response()->json([
                    'message' => 'Pesanan berhasil dibatalkan.',
                ]);
            }

            $pesanan->detailPesanan()->delete();
            $pesanan->delete();

            $this->syncMejaStatus($pesanan);

            return response()->json([
                'message' => 'Pesanan berhasil dihapus.',
            ]);
        });
    }

    /**
     * Membuat data struk pesanan.
     */
    public function cetakStruk(
        Request $request,
        $id
    ): JsonResponse {
        $pesanan = Pesanan::with([
            'meja',
            'detailPesanan.produk',
            'pembayaran',
        ])->findOrFail($id);

        $this->authorizeAccess($request, $pesanan);

        $resource = (new PesananResource($pesanan))->resolve();

        $items = $resource['detail_pesanan']
            ?? $resource['items']
            ?? [];

        $pembayaran = $pesanan->pembayaran;

        $metodePembayaran =
            $resource['pembayaran']['metode_pembayaran']
            ?? $pembayaran?->metodePembayaran?->nama_metode
            ?? $pesanan->metode_pembayaran
            ?? null;

        $kembalian =
            $resource['pembayaran']['kembalian']
            ?? $pembayaran?->kembalian
            ?? null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'info_coffee_shop' => [
                    'nama' => 'PerkociEatery',
                    'alamat' => '-',
                    'telepon' => '-',
                ],

                'nomor_antrean' =>
                    $resource['nomor_antrean']
                    ?? $pesanan->nomor_antrean,

                'nama_pemesan' => $pesanan->nama_pemesan,

                'tipe_pesanan' =>
                    $resource['tipe_pesanan']
                    ?? $pesanan->tipe_pesanan
                    ?? 'dine_in',

                'nama_meja' =>
                    $resource['meja']['no_meja'] ?? null,

                'meja' => $resource['meja'] ?? null,

                'daftar_item' => $items,
                'items' => $items,

                'total_harga' =>
                    $resource['total_harga']
                    ?? (float) $pesanan->total_harga,

                'metode_pembayaran' => $metodePembayaran,
                'kembalian' => $kembalian,

                'tanggal' =>
                    $resource['tanggal_pesan']
                    ?? $pesanan->tanggal_pesan,

                'tanggal_pesan' =>
                    $resource['tanggal_pesan']
                    ?? $pesanan->tanggal_pesan,

                'pembayaran' => $resource['pembayaran'] ?? null,
            ],
        ]);
    }

    /**
     * Memeriksa akses staff.
     */
    private function authorizeStaff(Request $request): void
    {
        $user = $request->user('sanctum');

        abort_unless(
            $user && in_array(
                $user->role,
                ['admin', 'kasir', 'manager'],
                true
            ),
            403
        );
    }

    /**
     * Memeriksa hak akses pelanggan terhadap pesanan.
     */
    private function authorizeAccess(
        Request $request,
        Pesanan $pesanan
    ): void {
        $user = $request->user('sanctum');

        if (
            $user &&
            in_array($user->role, ['admin', 'kasir', 'manager'], true)
        ) {
            return;
        }

        $kode = $request->query('kode');

        if (
            !is_string($kode) ||
            $kode === '' ||
            !is_string($pesanan->kode_akses) ||
            !hash_equals($pesanan->kode_akses, $kode)
        ) {
            abort(404);
        }
    }

    /**
     * Mengembalikan stok ketika pesanan dibatalkan.
     */
    private function kembalikanStok(Pesanan $pesanan): void
    {
        $pesanan->loadMissing('detailPesanan');

        foreach ($pesanan->detailPesanan as $detail) {

            $stok = Stok::where(
                'id_produk',
                $detail->id_produk
            )
                ->lockForUpdate()
                ->first();

            if ($stok) {
                $stok->increment('jumlah_stok', $detail->jumlah);
            }
        }
    }

    /**
     * Menyelaraskan status meja dengan pesanan aktif.
     * Meja takeaway tidak pernah berstatus "terisi".
     */
    private function syncMejaStatus(Pesanan $pesanan): void
    {
        $meja = Meja::find($pesanan->id_meja);

        if (! $meja) {
            return;
        }

        if ($meja->isTakeaway() || $pesanan->isTakeaway()) {
            $meja->ensureAvailableIfTakeaway();

            return;
        }

        $adaPesananAktif = Pesanan::where('id_meja', $meja->id_meja)
            ->whereIn('status_pesanan', ['menunggu', 'diproses'])
            ->exists();

        $status = $adaPesananAktif ? 'terisi' : 'tersedia';

        if ($meja->status !== $status) {
            $meja->update(['status' => $status]);
        }
    }

    /**
     * Menentukan tipe pesanan dari meja atau input pelanggan.
     */
    private function resolveTipePesanan(array $validated): string
    {
        $meja = Meja::find($validated['id_meja'] ?? null);

        if ($meja && $meja->isTakeaway()) {
            return 'takeaway';
        }

        return $validated['tipe_pesanan'] ?? 'dine_in';
    }

    /**
     * Membuat nomor antrean otomatis.
     */
    private function generateNomorAntrean(): string
    {
        $prefix = 'ORD-' . Carbon::now()->format('Ymd') . '-';

        // Urutkan berdasarkan id (portable MySQL & SQLite), lalu ambil
        // nomor urut terakhir dari format ORD-YYYYMMDD-XXX.
        $terakhir = Pesanan::where(
            'nomor_antrean',
            'like',
            $prefix . '%'
        )
            ->orderByDesc('id_pesanan')
            ->value('nomor_antrean');

        $berikutnya = $terakhir
            ? ((int) Str::afterLast($terakhir, '-')) + 1
            : 1;

        return $prefix . str_pad(
            (string) $berikutnya,
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}
