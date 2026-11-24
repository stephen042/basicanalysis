<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompoundInvestmentTopup extends Model
{
    protected $fillable = [
        'compound_investment_id', 'admin_id', 'idempotency_key', 'amount', 'note',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function investment()
    {
        return $this->belongsTo(CompoundInvestment::class, 'compound_investment_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
