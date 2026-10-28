<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPesananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_detail' => $this->id_detail,
            'id_pesanan' => $this->id_pesanan,
            'id_produk' => $this->id_produk,
            'produk' => $this->whenLoaded('produk', fn () => [
                'id_produk' => $this->produk->id_produk,
                'nama_produk' => $this->produk->nama_produk,
                'kategori' => $this->produk->kategori,
                'harga' => (float) $this->produk->harga,
                'status' => $this->produk->status,
            ]),
            'jumlah' => $this->jumlah,
            'harga' => (float) $this->harga,
            'subtotal' => (float) $this->subtotal,
            'topping' => $this->topping,
            'gula' => $this->gula,
            'es' => $this->es,
            'catatan' => $this->catatan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
