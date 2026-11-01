<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stok per produk (bukan stok bahan baku).
 *
 * PATCH /api/produk/{produk}/stok  (khusus admin)
 *
 * Body (isi minimal satu):
 * - jumlah_stok : atur jumlah stok menjadi nilai ini
 * - tambah      : tambah (atau kurangi, jika negatif) dari stok saat ini; hasil tidak kurang dari 0
 * - min_stok    : batas stok minimum untuk peringatan "menipis"
 */
class StokController extends Controller
{
    public function update(Request $request, Produk $produk): JsonResponse
    {
        $data = $request->validate([
            'jumlah_stok' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'tambah' => ['sometimes', 'integer', 'min:-1000000', 'max:1000000'],
            'min_stok' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
        ]);

        if ($data === []) {
            throw ValidationException::withMessages([
                'jumlah_stok' => ['Isi jumlah stok, jumlah tambahan, atau stok minimum.'],
            ]);
        }

        $stok = DB::transaction(function () use ($produk, $data) {
            $stok = Stok::where('id_produk', $produk->id_produk)->lockForUpdate()->first()
                ?? new Stok([
                    'id_produk' => $produk->id_produk,
                    'jumlah_stok' => 0,
                    'min_stok' => 0,
                ]);

            if (array_key_exists('jumlah_stok', $data)) {
                $stok->jumlah_stok = $data['jumlah_stok'];
            }

            if (array_key_exists('tambah', $data)) {
                $stok->jumlah_stok = max(0, (int) $stok->jumlah_stok + $data['tambah']);
            }

            if (array_key_exists('min_stok', $data)) {
                $stok->min_stok = $data['min_stok'];
            }

            $stok->updated_at = now()->toDateTimeString();
            $stok->save();

            return $stok;
        });

        return response()->json([
            'message' => 'Stok berhasil diperbarui.',
            'data' => [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'stok' => [
                    'id_stok' => $stok->id_stok,
                    'jumlah_stok' => (int) $stok->jumlah_stok,
                    'min_stok' => (int) $stok->min_stok,
                    'updated_at' => (string) $stok->updated_at,
                ],
            ],
        ]);
    }
}
