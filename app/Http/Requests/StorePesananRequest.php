<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_meja' => ['required', 'integer', 'exists:meja,id_meja'],
<<<<<<< Updated upstream
            'items' => ['required', 'array', 'min:1'],
=======
            'nama_pemesan' => ['nullable', 'string', 'max:100'],
            'no_whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
>>>>>>> Stashed changes
            'items.*.id_produk' => ['required', 'integer', 'exists:produk,id_produk'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'items.*.topping' => ['nullable', 'string', 'max:255'],
            'items.*.gula' => ['nullable', 'string', 'max:255'],
            'items.*.es' => ['nullable', 'string', 'max:255'],
            'items.*.catatan' => ['nullable', 'string'],
        ];
    }
}
