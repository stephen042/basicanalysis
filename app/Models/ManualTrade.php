<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualTrade extends Model
{
    protected $fillable = [
        'user_id', 'category', 'instrument', 'direction', 'amount', 'leverage',
        'duration_minutes', 'entry_price', 'result_amount', 'status', 'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_price' => 'decimal:8',
        'result_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
