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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $meja = $this->route('meja');

        return [
            'no_meja' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('meja', 'no_meja')->ignore($meja?->id_meja, 'id_meja'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_meja.required' => 'Nomor meja wajib diisi.',
            'no_meja.unique' => 'Nomor meja ini sudah dipakai.',
            'no_meja.max' => 'Nomor meja maksimal 20 karakter.',
        ];
    }
}
