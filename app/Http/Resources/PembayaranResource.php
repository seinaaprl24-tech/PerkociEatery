<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pembayaran' => $this->id_pembayaran,
            'id_pesanan' => $this->id_pesanan,
            'pesanan' => $this->whenLoaded('pesanan', fn () => [
                'id_pesanan' => $this->pesanan->id_pesanan,
                'nomor_antrean' => $this->pesanan->nomor_antrean,
                'total_harga' => (float) $this->pesanan->total_harga,
                'status_pesanan' => $this->pesanan->status_pesanan,
                'tanggal_pesan' => $this->pesanan->tanggal_pesan,
                'meja' => $this->whenLoaded('pesanan.meja', fn () => [
                    'id_meja' => $this->pesanan->meja->id_meja,
                    'no_meja' => $this->pesanan->meja->no_meja,
                ]),
            ]),
            'id_metode' => $this->id_metode,
            'metode_pembayaran' => $this->whenLoaded('metodePembayaran', fn () => $this->metodePembayaran->nama_metode),
            'jumlah_bayar' => (float) $this->jumlah_bayar,
            'kembalian' => $this->kembalian !== null ? (float) $this->kembalian : null,
            'status_pembayaran' => $this->status_pembayaran,
            'tanggal_pembayaran' => $this->tanggal_pembayaran,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
