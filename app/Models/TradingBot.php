<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingBot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'duration',
        'profit_rate',
        'status',
    ];

    public function userTradingBots()
    {
        return $this->hasMany(UserTradingBot::class);
    }

    public function tradingAssets()
    {
        return $this->belongsToMany(TradingAsset::class, 'trading_bot_assets')
                    ->withPivot('allocation_percentage')
                    ->withTimestamps();
    }
}
