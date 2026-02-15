<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_trader_id',
        'trading_asset_id',
        'amount',
        'type',
        'asset_price',
        'quantity',
        'entry_price',
        'exit_price',
        'pnl',
        'trade_direction',
        'status',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'asset_price' => 'decimal:8',
        'quantity' => 'decimal:8',
        'entry_price' => 'decimal:8',
        'exit_price' => 'decimal:8',
        'pnl' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function expertTrader()
    {
        return $this->belongsTo(ExpertTrader::class);
    }

    public function tradingAsset()
    {
        return $this->belongsTo(TradingAsset::class);
    }

    public function copyTrades()
    {
        return $this->hasMany(CopyTrade::class);
    }

    // Scopes
    public function scopeProfit($query)
    {
        return $query->where('type', 'profit');
    }

    public function scopeLoss($query)
    {
        return $query->where('type', 'loss');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Helper Methods
    public function isProfit()
    {
        return $this->type === 'profit';
    }

    public function getPnlPercentage()
    {
        if (!$this->entry_price || $this->entry_price == 0) {
            return 0;
        }

        if ($this->status === 'open') {
            $currentPrice = $this->tradingAsset?->current_price ?? $this->entry_price;
            $priceDiff = $currentPrice - $this->entry_price;
        } else {
            $priceDiff = ($this->exit_price ?? $this->entry_price) - $this->entry_price;
        }

        return ($priceDiff / $this->entry_price) * 100;
    }

    public function getFormattedDuration()
    {
        if (!$this->opened_at) {
            return 'N/A';
        }

        $endTime = $this->closed_at ?? now();
        $duration = $this->opened_at->diffInMinutes($endTime);

        if ($duration < 60) {
            return $duration . 'm';
        } elseif ($duration < 1440) {
            return round($duration / 60, 1) . 'h';
        } else {
            return round($duration / 1440, 1) . 'd';
        }
    }

    public function getTradeDirectionIcon()
    {
        switch ($this->trade_direction) {
            case 'long':
                return 'fa-arrow-up text-green-400';
            case 'short':
                return 'fa-arrow-down text-red-400';
            default:
                return 'fa-exchange-alt text-gray-400';
        }
    }

    public function getStatusColorClass()
    {
        switch ($this->status) {
            case 'open':
                return 'text-blue-400 bg-blue-500/10';
            case 'closed':
                return $this->isProfit() ? 'text-green-400 bg-green-500/10' : 'text-red-400 bg-red-500/10';
            default:
                return 'text-gray-400 bg-gray-500/10';
        }
    }
}