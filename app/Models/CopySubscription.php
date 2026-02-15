<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CopySubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expert_trader_id',
        'amount',
        'copy_percentage',
        'status',
        'started_at',
        'expires_at',
        'completed_at',
        'auto_renew',
        'max_risk_per_trade',
        'stop_loss_percentage'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'copy_percentage' => 'decimal:2',
        'max_risk_per_trade' => 'decimal:2',
        'stop_loss_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expertTrader()
    {
        return $this->belongsTo(ExpertTrader::class);
    }

    public function copyTrades()
    {
        return $this->hasMany(CopyTrade::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                    ->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Dynamic Calculations
    public function getTotalPnlAttribute()
    {
        return $this->copyTrades->sum('pnl');
    }

    public function getCurrentRoiAttribute()
    {
        if ($this->amount <= 0) return 0;
        return ($this->total_pnl / $this->amount) * 100;
    }

    public function getTotalTradesAttribute()
    {
        return $this->copyTrades->count();
    }

    public function getWinningTradesAttribute()
    {
        return $this->copyTrades->where('pnl', '>', 0)->count();
    }

    public function getWinRateAttribute()
    {
        $total = $this->total_trades;
        return $total > 0 ? ($this->winning_trades / $total) * 100 : 0;
    }

    public function getActiveTradesAttribute()
    {
        return $this->copyTrades->where('status', 'open')->count();
    }

    // Helper Methods
    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function getDaysRemaining()
    {
        if ($this->isExpired()) return 0;
        return now()->diffInDays($this->expires_at);
    }

    public function getProgressPercentage()
    {
        if (!$this->started_at || !$this->expires_at) return 0;
        
        $total = $this->started_at->diffInDays($this->expires_at);
        $elapsed = $this->started_at->diffInDays(now());
        
        return $total > 0 ? min(($elapsed / $total) * 100, 100) : 0;
    }

    public function getStatusColorClass()
    {
        switch ($this->status) {
            case 'active':
                return $this->isExpired() ? 'text-yellow-400' : 'text-green-400';
            case 'paused':
                return 'text-yellow-400';
            case 'cancelled':
                return 'text-red-400';
            case 'completed':
                return 'text-blue-400';
            default:
                return 'text-gray-400';
        }
    }

    public function getStatusText()
    {
        if ($this->status === 'active' && $this->isExpired()) {
            return 'Expired';
        }
        return ucfirst($this->status);
    }

    public function calculateCopyAmount($expertTradeAmount)
    {
        $baseAmount = ($expertTradeAmount * $this->copy_percentage) / 100;
        
        // Apply max risk per trade limit
        if ($this->max_risk_per_trade > 0) {
            $maxRiskAmount = ($this->amount * $this->max_risk_per_trade) / 100;
            $baseAmount = min($baseAmount, $maxRiskAmount);
        }
        
        return $baseAmount;
    }
}