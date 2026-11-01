<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Http\Resources\ProdukResource;
use App\Models\Produk;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();
        $foto = $request->file('foto');

        unset($data['foto']);

        if ($foto) {
            $data['foto'] = $foto->store('produk', 'public');
        }

        try {
            $produk = Produk::create($data);
        } catch (\Throwable $e) {
            if (! empty($data['foto'])) {
                $this->hapusFoto($data['foto']);
            }

            throw $e;
        }

        return (new ProdukResource($produk->load('stok')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Memperbarui data produk.
     */
    public function update(UpdateProdukRequest $request, Produk $produk): ProdukResource
    {
        $data = $request->validated();
        $foto = $request->file('foto');
        $fotoLama = null;

        unset($data['foto']);

        if ($foto) {
            $fotoLama = $produk->foto;
            $data['foto'] = $foto->store('produk', 'public');
        }

        try {
            $produk->update($data);
        } catch (\Throwable $e) {
            if (! empty($data['foto'])) {
                $this->hapusFoto($data['foto']);
            }

            throw $e;
        }

        if ($foto) {
            $this->hapusFoto($fotoLama);
        }

        return new ProdukResource($produk->load('stok'));
    }

    /**
     * Menghapus produk.
     */
    public function destroy(Produk $produk): JsonResponse
    {
        $foto = $produk->foto;

        try {
            $produk->delete();
        } catch (QueryException $e) {
            // 23000 = pelanggaran foreign key (produk sudah dipakai di detail pesanan).
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }

            return response()->json([
                'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan.',
            ], 409);
        }

        $this->hapusFoto($foto);

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    /**
     * Hapus file foto lama dari disk public bila ada.
     */
    private function hapusFoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
