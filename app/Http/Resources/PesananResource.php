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
            'user' => $this->whenLoaded('user', fn () => [
                'id_user' => $this->user->id_user ?? null,
                'nama' => $this->user->nama ?? null,
                'username' => $this->user->username ?? null,
                'role' => $this->user->role ?? null,
            ]),
            'pemesan' => $this->whenLoaded('user', fn () => [
                'id_user' => $this->user->id_user ?? null,
                'nama' => $this->user->nama ?? null,
                'username' => $this->user->username ?? null,
                'role' => $this->user->role ?? null,
            ]),
            'kasir' => $this->whenLoaded('user', fn () => [
                'id_user' => $this->user->id_user ?? null,
                'nama' => $this->user->nama ?? null,
                'username' => $this->user->username ?? null,
                'role' => $this->user->role ?? null,
            ]),
            'nomor_antrean' => $this->nomor_antrean,
            'tanggal_pesan' => $this->tanggal_pesan,
            'total_harga' => (float) $this->total_harga,
            'status_pesanan' => $this->status_pesanan,
            'detail_pesanan' => DetailPesananResource::collection($this->whenLoaded('detailPesanan')),
            'items' => DetailPesananResource::collection($this->whenLoaded('detailPesanan')),
            'pembayaran' => $this->whenLoaded('pembayaran', fn () => [
                'id_pembayaran' => $this->pembayaran->id_pembayaran,
                'jumlah_bayar' => (float) $this->pembayaran->jumlah_bayar,
                'kembalian' => $this->pembayaran->kembalian !== null ? (float) $this->pembayaran->kembalian : null,
                'status_pembayaran' => $this->pembayaran->status_pembayaran,
                'tanggal_pembayaran' => $this->pembayaran->tanggal_pembayaran,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
