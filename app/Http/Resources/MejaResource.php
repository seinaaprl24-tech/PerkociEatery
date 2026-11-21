<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MejaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_meja' => $this->id_meja,
            'no_meja' => $this->no_meja,
            'is_takeaway' => $this->resource->isTakeaway(),
            'qr_code' => $this->qr_code,
            // Meja takeaway selalu dilaporkan tersedia ke UI.
            'status' => $this->resource->isTakeaway() ? 'tersedia' : $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
