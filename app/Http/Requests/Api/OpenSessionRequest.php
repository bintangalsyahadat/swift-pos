<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OpenSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terminal_id' => ['required', 'string', 'uuid', 'exists:cashiers,api_id'],
            'opening_balance' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'terminal_id.required' => 'Terminal wajib dipilih.',
            'terminal_id.uuid'     => 'Format terminal_id tidak valid.',
            'terminal_id.exists'   => 'Terminal tidak ditemukan.',
            'opening_balance.required' => 'Saldo awal wajib diisi.',
            'opening_balance.min'      => 'Saldo awal tidak boleh negatif.',
        ];
    }
}
