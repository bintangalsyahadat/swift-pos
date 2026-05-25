<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orders'                              => ['required', 'array', 'min:1'],

            // ── Header Order ─────────────────────────────────────────────────
            'orders.*.pos_reference'              => ['required', 'string', 'uuid'],
            'orders.*.created_at'                 => ['required', 'date'],
            'orders.*.order_number'               => ['nullable', 'string', 'max:100'],
            'orders.*.customer_id'                => ['nullable', 'integer', 'exists:customers,id'],
            'orders.*.payment_method_id'          => ['required', 'string', 'uuid', 'exists:payment_methods,api_id'],
            'orders.*.total_price'                => ['required', 'numeric', 'min:0'],
            'orders.*.discount'                   => ['nullable', 'integer', 'min:0', 'max:100'],
            'orders.*.discount_amount'            => ['nullable', 'numeric', 'min:0'],
            'orders.*.total_payment'              => ['required', 'numeric', 'min:0'],
            'orders.*.cash_paid'                  => ['nullable', 'numeric', 'min:0'],
            'orders.*.change_amount'              => ['nullable', 'numeric', 'min:0'],
            'orders.*.payment_status'             => ['required', 'string', 'in:unpaid,paid,failed'],

            // ── Line Items ───────────────────────────────────────────────────
            'orders.*.items'                      => ['required', 'array', 'min:1'],
            'orders.*.items.*.product_id'         => ['required', 'string', 'uuid', 'exists:products,api_id'],
            'orders.*.items.*.quantity'           => ['required', 'integer', 'min:1'],
            'orders.*.items.*.subtotal'           => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'orders.required'                              => 'Array transaksi tidak boleh kosong.',
            'orders.*.pos_reference.required'              => 'pos_reference wajib ada di setiap transaksi.',
            'orders.*.pos_reference.uuid'                  => 'pos_reference harus berformat UUID.',
            'orders.*.created_at.required'                 => 'created_at wajib ada (waktu asli transaksi).',
            'orders.*.payment_method_id.required'      => 'payment_method_id wajib diisi.',
            'orders.*.payment_method_id.exists'        => 'Metode pembayaran tidak ditemukan.',
            'orders.*.items.required'                      => 'Setiap transaksi harus memiliki minimal 1 item.',
            'orders.*.items.*.product_id.required'     => 'product_id wajib diisi untuk setiap item.',
            'orders.*.items.*.product_id.exists'       => 'Produk tidak ditemukan.',
            'orders.*.items.*.quantity.min'                => 'Jumlah item minimal 1.',
        ];
    }
}
