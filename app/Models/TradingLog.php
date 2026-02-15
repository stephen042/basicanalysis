<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_trading_bot_id',
        'trading_asset_id',
        'amount',
        'type',
        'asset_price',
        'quantity',
    ];

    public function userTradingBot()
    {
        return $this->belongsTo(UserTradingBot::class);
    }

    public function tradingAsset()
    {
        return $this->belongsTo(TradingAsset::class);
    }
}
