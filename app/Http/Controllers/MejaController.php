<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMejaRequest;
use App\Http\Requests\UpdateMejaRequest;
use App\Http\Resources\MejaResource;
use App\Models\Meja;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class MejaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $meja = Meja::orderBy('no_meja')->paginate(10);

        return MejaResource::collection($meja);
    }

    public function show(Meja $meja): MejaResource
    {
        return new MejaResource($meja);
    }

    /**
     * Tambah meja. Kode QR dibuat otomatis dari nomor meja dan status awalnya "tersedia".
     */
    public function store(StoreMejaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['qr_code'] = $this->generateQrCode($data['no_meja']);
        $data['status'] = 'tersedia';

        $meja = Meja::create($data);

        return (new MejaResource($meja))->response()->setStatusCode(201);
    }

    /**
     * Ubah nomor meja. Kode QR tidak berubah, jadi QR yang sudah dicetak tetap berlaku.
     */
    public function update(UpdateMejaRequest $request, Meja $meja): MejaResource
    {
        $meja->update($request->validated());

        return new MejaResource($meja);
    }

    public function destroy(Meja $meja): JsonResponse
    {
        try {
            $meja->delete();
        } catch (QueryException $e) {
            // 23000 = pelanggaran foreign key (meja sudah punya riwayat pesanan).
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }

            return response()->json([
                'message' => 'Meja tidak dapat dihapus karena sudah memiliki riwayat pesanan.',
            ], 409);
        }

        return response()->json(['message' => 'Meja berhasil dihapus.']);
    }

    public function validateQR($kode_qr): JsonResponse
    {
        $meja = Meja::where('qr_code', $kode_qr)->first();

        if (! $meja) {
            return response()->json(['message' => 'Meja tidak ditemukan atau QR tidak valid.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => new MejaResource($meja)]);
    }

    private function generateQrCode(string $noMeja): string
    {
        $base = 'MEJA-'.Str::upper(Str::slug($noMeja, ''));
        $kode = $base;

        while (Meja::where('qr_code', $kode)->exists()) {
            $kode = $base.'-'.Str::upper(Str::random(4));
        }

        return $kode;
    }
}
