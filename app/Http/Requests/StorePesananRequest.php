<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'id_meja'           => ['required', 'exists:meja,id_meja'],
        'nama_pemesan'      => ['nullable', 'string', 'max:255'],
        'no_whatsapp'       => ['nullable', 'string', 'max:20'],
        'items'             => ['required', 'array'],
        'items.*.id_produk' => ['required', 'exists:produk,id_produk'],
        'items.*.jumlah'    => ['required', 'integer', 'min:1'],
        'items.*.topping'   => ['nullable', 'string', 'max:255'],
        'items.*.gula'      => ['nullable', 'string', 'max:255'],
        'items.*.es'        => ['nullable', 'string', 'max:255'],
        'items.*.catatan'   => ['nullable', 'string', 'max:500'],
    ];
}
}
