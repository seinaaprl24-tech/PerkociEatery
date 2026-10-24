<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_produk' => $this->id_produk,
            'nama_produk' => $this->nama_produk,
            'kategori' => $this->kategori,
            'harga' => (float) $this->harga,
            'deskripsi' => $this->deskripsi,
            'status' => $this->status,
            'stok' => $this->whenLoaded('stok', fn () => [
                'id_stok' => $this->stok->id_stok,
                'jumlah_stok' => $this->stok->jumlah_stok,
                'min_stok' => $this->stok->min_stok,
                'updated_at' => $this->stok->updated_at,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
