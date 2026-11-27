<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompoundInvestmentReminder extends Model
{
    protected $fillable = ['compound_investment_id', 'scheduled_for', 'sent_at'];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
