<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePembayaranRequest extends FormRequest
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
            'pesanan_id' => ['required', 'integer', 'exists:pesanan,id_pesanan'],
            'metode_pembayaran' => ['required', Rule::in(['tunai', 'qris', 'transfer', 'e-wallet'])],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
        ];
    }
}
