<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertPerformanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_trader_id',
        'date',
        'portfolio_value',
        'daily_pnl',
        'total_trades',
        'winning_trades',
        'roi_percentage',
        'drawdown_percentage',
        'volume_traded',
        'followers_count'
    ];

    protected $casts = [
        'date' => 'date',
        'portfolio_value' => 'decimal:2',
        'daily_pnl' => 'decimal:2',
        'roi_percentage' => 'decimal:2',
        'drawdown_percentage' => 'decimal:2',
        'volume_traded' => 'decimal:2',
    ];

    // Relationships
    public function expertTrader()
    {
        return $this->belongsTo(ExpertTrader::class);
    }

    // Scopes
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function getWinRate()
    {
        return $this->total_trades > 0 ? ($this->winning_trades / $this->total_trades) * 100 : 0;
    }

    public function isProfitableDay()
    {
        return $this->daily_pnl > 0;
    }

    public function getFormattedPnl()
    {
        $sign = $this->daily_pnl >= 0 ? '+' : '';
        return $sign . number_format($this->daily_pnl, 2);
    }

    public function getPnlColorClass()
    {
        if ($this->daily_pnl > 0) return 'text-green-400';
        if ($this->daily_pnl < 0) return 'text-red-400';
        return 'text-gray-400';
    }
}