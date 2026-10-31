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

    public function store(StoreMejaRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['kode_qr'])) {
            $data['kode_qr'] = $this->generateKodeQr($data['no_meja'] ?? null);
        }
        if (empty($data['qr_code']) && ! empty($data['kode_qr'])) {
            $data['qr_code'] = $data['kode_qr'];
        }
        $meja = Meja::create($data);
        return (new MejaResource($meja))->response()->setStatusCode(201);
    }

    public function update(UpdateMejaRequest $request, Meja $meja): MejaResource
    {
        $data = $request->validated();
        if (array_key_exists('kode_qr', $data) && empty($data['kode_qr'])) {
            $data['kode_qr'] = $this->generateKodeQr($data['no_meja'] ?? $meja->no_meja);
        }
        if (array_key_exists('qr_code', $data) && empty($data['qr_code']) && ! empty($data['kode_qr'])) {
            $data['qr_code'] = $data['kode_qr'];
        }
        $meja->update($data);
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
        $meja = Meja::where('kode_qr', $kode_qr)->orWhere('qr_code', $kode_qr)->first();
        if (! $meja) {
            return response()->json(['message' => 'Meja tidak ditemukan atau QR tidak valid.'], 404);
        }
        return response()->json(['status' => 'success', 'data' => new MejaResource($meja)]);
    }

    private function generateKodeQr($noMeja = null): string
    {
        $prefix = $noMeja ? 'MEJA-' . str_replace(' ', '', $noMeja) : 'MEJA';
        do {
            $kode = $prefix . '-QR' . strtoupper(Str::random(6));
        } while (Meja::where('kode_qr', $kode)->orWhere('qr_code', $kode)->exists());
        return $kode;
    }
}
