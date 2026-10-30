<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Catatan: harga TIDAK dipercaya dari client. Server selalu memakai harga
     * dari tabel produk, jadi items.*.harga_satuan boleh dihilangkan (jika
     * dikirim, nilainya diabaikan).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_meja' => ['required', 'integer', 'exists:meja,id_meja'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id_produk' => ['required', 'integer', 'exists:produk,id_produk'],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'items.*.topping' => ['nullable', 'string', 'max:255'],
            'items.*.gula' => ['nullable', 'string', 'max:255'],
            'items.*.es' => ['nullable', 'string', 'max:255'],
            'items.*.catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
