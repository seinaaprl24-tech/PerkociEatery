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
            'nama_pemesan' => $this->nama_pemesan,
            'no_whatsapp' => $this->no_whatsapp,
            // Kode akses hanya dikirim sekali: pada respons pembuatan pesanan.
            'kode_akses' => $this->when($this->wasRecentlyCreated, $this->kode_akses),
            'tanggal_pesan' => $this->tanggal_pesan,
            'total_harga' => (float) $this->total_harga,
            'status_pesanan' => $this->status_pesanan,
            'detail_pesanan' => DetailPesananResource::collection($this->whenLoaded('detailPesanan')),
            'items' => DetailPesananResource::collection($this->whenLoaded('detailPesanan')),
            'pembayaran' => $this->whenLoaded('pembayaran', fn () => $this->pembayaran ? [
                'id_pembayaran' => $this->pembayaran->id_pembayaran,
                'jumlah_bayar' => (float) $this->pembayaran->jumlah_bayar,
                'kembalian' => $this->pembayaran->kembalian !== null ? (float) $this->pembayaran->kembalian : null,
                'status_pembayaran' => $this->pembayaran->status_pembayaran,
                'tanggal_pembayaran' => $this->pembayaran->tanggal_pembayaran,
            ] : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
