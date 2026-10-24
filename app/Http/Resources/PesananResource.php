<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pesanan' => $this->id_pesanan,
            'id_meja' => $this->id_meja,
            'meja' => $this->whenLoaded('meja', fn () => [
                'id_meja' => $this->meja->id_meja,
                'no_meja' => $this->meja->no_meja,
                'status' => $this->meja->status,
            ]),
            'nomor_antrean' => $this->nomor_antrean,
            'tanggal_pesan' => $this->tanggal_pesan,
            'total_harga' => (float) $this->total_harga,
            'status_pesanan' => $this->status_pesanan,
            'detail_pesanan' => DetailPesananResource::collection($this->whenLoaded('detailPesanan')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
