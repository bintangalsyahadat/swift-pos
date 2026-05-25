<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CloseSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_balance' => ['required', 'integer', 'min:0'],
            'closing_notes'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Tambahkan validasi kondisional: closing_notes WAJIB jika ada selisih.
     * Selisih dihitung di controller karena butuh data session — cukup jadikan nullable di sini.
     */
    public function messages(): array
    {
        return [
            'actual_balance.required' => 'Saldo aktual wajib diisi.',
            'actual_balance.min'      => 'Saldo aktual tidak boleh negatif.',
        ];
    }
}
