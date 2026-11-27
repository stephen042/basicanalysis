<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompoundInvestmentContribution extends Model
{
    protected $fillable = [
        'compound_investment_id', 'deposit_id', 'idempotency_key', 'amount', 'currency',
        'scheduled_for', 'status', 'payment_method', 'balance_debited', 'admin_note', 'approved_at', 'rejected_at',
        'reviewed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_debited' => 'boolean',
        'scheduled_for' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function investment()
    {
        return $this->belongsTo(CompoundInvestment::class, 'compound_investment_id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }
}
