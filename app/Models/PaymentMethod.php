<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'qr_image',
        'type',
        'is_online',
        'xendit_channel_type',
        'xendit_channel_code',
        'xendit_channel_properties',
        'fee_type',
        'fee_value',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_online'                  => 'boolean',
        'is_active'                  => 'boolean',
        'xendit_channel_properties'  => 'array',
        'fee_value'                  => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Calculate the fee amount for a given transaction total.
     */
    public function calculateFee(float $amount): float
    {
        if (! $this->fee_type || ! $this->fee_value) {
            return 0;
        }

        return match ($this->fee_type) {
            'flat'       => (float) $this->fee_value,
            'percentage' => round($amount * ((float) $this->fee_value / 100), 2),
            default      => 0,
        };
    }

    /**
     * Human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'cash'             => 'Cash',
            'card'             => 'Credit / Debit Card',
            'qr_code'          => 'QR Code',
            'virtual_account'  => 'Virtual Account',
            'ewallet'          => 'E-Wallet',
            'over_the_counter' => 'Over the Counter',
            default            => ucfirst($this->type),
        };
    }
}
