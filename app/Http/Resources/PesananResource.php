<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_pesanan' => $this->id_pesanan,
            'id_meja' => $this->id_meja,
            'nomor_antrean' => $this->nomor_antrean,
            'nama_pemesan' => $this->nama_pemesan,
            'no_whatsapp' => $this->no_whatsapp,
            'kode_akses' => $this->kode_akses,
            'tanggal_pesan' => $this->tanggal_pesan,
            'updated_at' => $this->updated_at,
            'total_harga' => (float) $this->total_harga,
            'status_pesanan' => $this->status_pesanan,
            'metode_pembayaran' => $this->metode_pembayaran ?? 'tunai',
            'status_pembayaran' => $this->status_pembayaran ?? 'belum_bayar',
            'bukti_bayar' => $this->bukti_bayar,
            'bukti_bayar_url' => $this->bukti_bayar ? asset('storage/' . ltrim($this->bukti_bayar, '/')) : null,
            'meja' => $this->whenLoaded('meja'),
            'detail_pesanan' => $this->whenLoaded('detailPesanan'),
            'pembayaran' => $this->whenLoaded('pembayaran'),
        ];
    }
}
