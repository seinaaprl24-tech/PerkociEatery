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
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    private const METODE_MAP = [
        'tunai' => 'Tunai',
        'qris' => 'QRIS',
    ];

    public function index(): AnonymousResourceCollection
    {
        $pembayaran = Pembayaran::with(['pesanan.meja', 'metodePembayaran'])
            ->orderByDesc('id_pembayaran')
            ->paginate(10);

        return PembayaranResource::collection($pembayaran);
    }

    public function show(Pembayaran $pembayaran): PembayaranResource
    {
        return new PembayaranResource($pembayaran->load(['pesanan.meja', 'metodePembayaran']));
    }

    public function store(StorePembayaranRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pembayaran = DB::transaction(function () use ($validated) {
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

            if (Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
                ->where('status_pembayaran', 'lunas')
                ->exists()) {
                throw ValidationException::withMessages([
                    'pesanan_id' => ['Pembayaran untuk pesanan ini sudah ada.'],
                ]);
            }

            $kunciMetode = strtolower(trim((string) $validated['metode_pembayaran']));
            $namaMetode = self::METODE_MAP[$kunciMetode] ?? null;

            if ($namaMetode === null) {
                throw ValidationException::withMessages([
                    'metode_pembayaran' => ['Metode pembayaran tidak dikenal.'],
                ]);
            }

            if ($kunciMetode === 'qris'
                && Schema::hasColumn('pesanan', 'status_pembayaran')
                && $pesanan->status_pembayaran === 'belum_bayar') {
                throw ValidationException::withMessages([
                    'pesanan_id' => ['Pelanggan belum mengunggah bukti pembayaran QRIS.'],
                ]);
            }

            $totalHarga = (float) $pesanan->total_harga;
            $jumlahBayar = (float) $validated['jumlah_bayar'];

            if ($jumlahBayar < $totalHarga) {
                throw ValidationException::withMessages([
                    'jumlah_bayar' => ['Jumlah bayar kurang dari total harga.'],
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

            $pesananUpdates = [
                'status_pesanan' => 'selesai',
            ];

            if (Schema::hasColumn('pesanan', 'metode_pembayaran')) {
                $pesananUpdates['metode_pembayaran'] = $kunciMetode;
            }

            if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
                $pesananUpdates['status_pembayaran'] = 'lunas';
            }

            $pesanan->update($pesananUpdates);

            return $pembayaran;
        });

        return (new PembayaranResource($pembayaran->load(['pesanan.meja', 'metodePembayaran'])))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Pembayaran $pembayaran): JsonResponse
    {
        DB::transaction(function () use ($pembayaran) {
            $pembayaran->load('pesanan');

            $pembayaran->update([
                'status_pembayaran' => 'batal',
            ]);

            if ($pembayaran->pesanan) {
                $pesananUpdates = [
                    'status_pesanan' => 'diproses',
                ];

                if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
                    $pesananUpdates['status_pembayaran'] = 'belum_bayar';
                }

                $pembayaran->pesanan->update($pesananUpdates);
            }

            $pembayaran->delete();
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dibatalkan.',
        ]);
    }
}
