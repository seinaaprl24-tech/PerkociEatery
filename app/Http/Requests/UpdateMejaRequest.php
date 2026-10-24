<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMejaRequest extends FormRequest
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
        $meja = $this->route('meja');

        return [
            'no_meja' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('meja', 'no_meja')->ignore($meja)],
            'qr_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('meja', 'qr_code')->ignore($meja)],
            'status' => ['sometimes', 'required', Rule::in(['tersedia', 'terisi', 'reserved'])],
        ];
    }
}
