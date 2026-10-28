<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePembayaranRequest;
use App\Http\Resources\PembayaranResource;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    /**
     * Metode pembayaran yang diizinkan (kunci dari client => nama di tabel).
     */
    private const METODE_MAP = [
        'tunai' => 'Tunai',
        'qris' => 'QRIS',
        'transfer' => 'E-Wallet',
        'e-wallet' => 'E-Wallet',
    ];

    /**
     * Menampilkan semua riwayat pembayaran.
     */
    public function index(): AnonymousResourceCollection
    {
        $pembayaran = Pembayaran::with(['pesanan.meja', 'metodePembayaran'])
            ->orderByDesc('id_pembayaran')
            ->paginate(10);

        return PembayaranResource::collection($pembayaran);
    }

    /**
     * Menampilkan detail pembayaran.
     */
    public function show(Pembayaran $pembayaran): PembayaranResource
    {
        return new PembayaranResource($pembayaran->load(['pesanan.meja', 'metodePembayaran']));
    }

    /**
     * Proses pembayaran untuk sebuah pesanan.
     */
    public function store(StorePembayaranRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pembayaran = DB::transaction(function () use ($validated) {
            // Kunci baris pesanan agar dua kasir tidak bisa membayar pesanan yang sama bersamaan.
            $pesanan = Pesanan::lockForUpdate()->find($validated['pesanan_id']);

            if (! $pesanan) {
                throw ValidationException::withMessages([
                    'pesanan_id' => ['Pesanan tidak ditemukan.'],
                ]);
            }

            if ($pesanan->status_pesanan === 'selesai' || $pesanan->status_pesanan === 'batal') {
                throw ValidationException::withMessages([
                    'pesanan_id' => ['Pesanan tidak dapat dibayar.'],
                ]);
            }

            // Hanya pembayaran LUNAS yang menghalangi; percobaan yang dibatalkan boleh diulang
            // (sesuai relasi Pesanan 1 : N Pembayaran pada ERD).
            if (Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
                ->where('status_pembayaran', 'lunas')
                ->exists()) {
                throw ValidationException::withMessages([
                    'pesanan_id' => ['Pembayaran untuk pesanan ini sudah ada.'],
                ]);
            }

            $totalHarga = (float) $pesanan->total_harga;
            $jumlahBayar = (float) $validated['jumlah_bayar'];

            if ($jumlahBayar < $totalHarga) {
                throw ValidationException::withMessages([
                    'jumlah_bayar' => ['Jumlah bayar kurang dari total harga.'],
                ]);
            }

            $kunciMetode = strtolower(trim((string) $validated['metode_pembayaran']));
            $namaMetode = self::METODE_MAP[$kunciMetode] ?? null;

            if ($namaMetode === null) {
                throw ValidationException::withMessages([
                    'metode_pembayaran' => ['Metode pembayaran tidak dikenal.'],
                ]);
            }

            $metode = MetodePembayaran::firstOrCreate(['nama_metode' => $namaMetode]);

            $kembalian = $jumlahBayar - $totalHarga;

            $pembayaran = Pembayaran::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'id_metode' => $metode->id_metode,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'status_pembayaran' => 'lunas',
                'tanggal_pembayaran' => Carbon::now(),
            ]);

            $pesanan->update([
                'status_pesanan' => 'selesai',
            ]);

            return $pembayaran;
        });

        return (new PembayaranResource($pembayaran->load(['pesanan.meja', 'metodePembayaran'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Batalkan pembayaran dan kembalikan status pesanan.
     */
    public function destroy(Pembayaran $pembayaran): JsonResponse
    {
        DB::transaction(function () use ($pembayaran) {
            $pembayaran->load('pesanan');

            $pembayaran->update([
                'status_pembayaran' => 'batal',
            ]);

            if ($pembayaran->pesanan) {
                $pembayaran->pesanan->update([
                    'status_pesanan' => 'diproses',
                ]);
            }

            $pembayaran->delete();
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dibatalkan.',
        ]);
    }
}
