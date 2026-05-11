<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    protected $fillable = [
        'terminal_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'expected_balance',
        'actual_balance',
        'difference_amount',
        'closing_notes',
        'state',
    ];

    protected $casts = [
        'opened_at'        => 'datetime',
        'closed_at'        => 'datetime',
        'opening_balance'  => 'integer',
        'expected_balance' => 'integer',
        'actual_balance'   => 'integer',
        'difference_amount' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function terminal()
    {
        return $this->belongsTo(Cashier::class, 'terminal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    /**
     * Total cash payments collected during this session.
     */
    public function cashSales(): int
    {
        return (int) $this->orders()
            ->where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->sum('total_payment');
    }

    /**
     * Expected balance at close: opening balance + cash sales during session.
     */
    public function computeExpectedBalance(): int
    {
        return $this->opening_balance + $this->cashSales();
    }

    /**
     * Find the currently open session for a given terminal (cashier).
     */
    public static function openSessionForTerminal(int $terminalId): ?self
    {
        return static::where('terminal_id', $terminalId)
            ->where('state', 'open')
            ->first();
    }
}
