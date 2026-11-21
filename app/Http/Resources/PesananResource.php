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
            // Kode akses hanya dikirim saat pesanan BARU dibuat (respons POST /api/pesanan),
            // supaya tidak bocor lewat daftar pesanan kasir/dapur atau endpoint lain.
            'kode_akses' => $this->when($this->resource->wasRecentlyCreated, $this->kode_akses),
            'nomor_antrean' => $this->nomor_antrean,
            'tipe_pesanan' => $this->tipe_pesanan ?? 'dine_in',
            'is_takeaway' => ($this->tipe_pesanan ?? 'dine_in') === 'takeaway',
            'nama_pemesan' => $this->nama_pemesan,
            'no_whatsapp' => $this->no_whatsapp,
            'catatan' => $this->catatan, // <-- Catatan dari pelanggan yang diteruskan ke kasir
            'total_harga' => $this->total_harga,
            'status_pesanan' => $this->status_pesanan,
            'metode_pembayaran' => $this->metode_pembayaran ?? 'tunai',
            'status_pembayaran' => $this->status_pembayaran ?? 'belum_bayar',
            'bukti_bayar' => $this->bukti_bayar ? asset('storage/' . $this->bukti_bayar) : null,
            'tanggal_pesan' => $this->tanggal_pesan,
            'meja' => new MejaResource($this->whenLoaded('meja')),
            'detail_pesanan' => DetailPesananResource::collection($this->whenLoaded('detailPesanan')),
        ];
    }
}
