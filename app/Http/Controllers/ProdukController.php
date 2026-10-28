<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Http\Resources\ProdukResource;
use App\Models\Produk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar produk dengan pagination.
     */
    public function index(): AnonymousResourceCollection
    {
        $produk = Produk::with('stok')
            ->orderByDesc('id_produk')
            ->paginate(10);

        return ProdukResource::collection($produk);
    }

    /**
     * Menampilkan detail produk.
     */
    public function show(Produk $produk): ProdukResource
    {
        return new ProdukResource($produk->load('stok'));
    }

    /**
     * Menyimpan produk baru.
     */
    public function store(StoreProdukRequest $request): JsonResponse
    {
        $produk = Produk::create($request->validated());

        return (new ProdukResource($produk->load('stok')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Memperbarui data produk.
     */
    public function update(UpdateProdukRequest $request, Produk $produk): ProdukResource
    {
        $produk->update($request->validated());

        return new ProdukResource($produk->load('stok'));
    }

    /**
     * Menghapus produk.
     */
    public function destroy(Produk $produk): JsonResponse
    {
        $produk->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
