<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMejaRequest extends FormRequest
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
            'no_meja' => ['required', 'string', 'max:50', 'unique:meja,no_meja'],
            'qr_code' => ['required', 'string', 'max:255', 'unique:meja,qr_code'],
            'status' => ['required', Rule::in(['tersedia', 'terisi', 'reserved'])],
        ];
    }
}
