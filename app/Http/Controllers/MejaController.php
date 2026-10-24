<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMejaRequest;
use App\Http\Requests\UpdateMejaRequest;
use App\Http\Resources\MejaResource;
use App\Models\Meja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MejaController extends Controller
{
    /**
     * Menampilkan daftar meja dengan pagination.
     */
    public function index(): AnonymousResourceCollection
    {
        $meja = Meja::orderBy('no_meja')->paginate(10);

        return MejaResource::collection($meja);
    }

    /**
     * Menampilkan detail meja.
     */
    public function show(Meja $meja): MejaResource
    {
        return new MejaResource($meja);
    }

    /**
     * Menyimpan meja baru.
     */
    public function store(StoreMejaRequest $request): JsonResponse
    {
        $meja = Meja::create($request->validated());

        return (new MejaResource($meja))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Memperbarui data meja.
     */
    public function update(UpdateMejaRequest $request, Meja $meja): MejaResource
    {
        $meja->update($request->validated());

        return new MejaResource($meja);
    }

    /**
     * Menghapus meja.
     */
    public function destroy(Meja $meja): JsonResponse
    {
        $meja->delete();

        return response()->json([
            'message' => 'Meja berhasil dihapus.',
        ]);
    }
}
