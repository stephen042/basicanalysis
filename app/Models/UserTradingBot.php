<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTradingBot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trading_bot_id',
        'amount',
        'status',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tradingBot()
    {
        return $this->belongsTo(TradingBot::class);
    }

    public function tradingLogs()
    {
        return $this->hasMany(TradingLog::class);
    }
}
