<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'coingecko_id',
        'icon_url',
        'current_price',
        'change_24h',
        'market_cap',
        'market_cap_rank',
        'total_volume',
        'high_24h',
        'low_24h',
        'description',
        'is_active',
        'status',
        'last_updated',
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'change_24h' => 'decimal:4',
        'market_cap' => 'decimal:2',
        'total_volume' => 'decimal:2',
        'high_24h' => 'decimal:8',
        'low_24h' => 'decimal:8',
        'is_active' => 'boolean',
        'last_updated' => 'datetime',
    ];

    public function tradingBots()
    {
        return $this->belongsToMany(TradingBot::class, 'trading_bot_assets')
                    ->withPivot('allocation_percentage')
                    ->withTimestamps();
    }

    public function getFormattedPriceAttribute()
    {
        if (!$this->current_price) {
            return 'N/A';
        }

        $price = (float) $this->current_price;

        if ($this->type === 'crypto' && $price < 1) {
            return '$' . number_format($price, 8);
        }

        return '$' . number_format($price, 2);
    }

    public function getChangeColorAttribute()
    {
        if (!$this->change_24h) {
            return 'text-gray-400';
        }

        return $this->change_24h >= 0 ? 'text-green-400' : 'text-red-400';
    }

    public function getChangeIconAttribute()
    {
        if (!$this->change_24h) {
            return 'fas fa-minus';
        }

        return $this->change_24h >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
    }

    public function getIconUrlAttribute()
    {
        // Prioritize CoinGecko icon_url if available
        if (!empty($this->attributes['icon_url'])) {
            return $this->attributes['icon_url'];
        }

        // Fallback to local icon if available
        if (!empty($this->icon)) {
            return asset('dash/' . $this->icon);
        }

        // Default fallback icon
        return asset('dash/default-crypto.png');
    }

    // Trading relationships
    public function predictionTrades()
    {
        return $this->hasMany(PredictionTrade::class);
    }

    public function activeTrades()
    {
        return $this->predictionTrades()->active();
    }

    // Trading helper methods
    public function getActiveTradesCount(): int
    {
        return $this->activeTrades()->count();
    }

    public function getTotalTradeVolume(): float
    {
        return (float) $this->predictionTrades()
            ->whereIn('status', ['won', 'lost'])
            ->sum('trade_amount');
    }
}
