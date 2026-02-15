<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PredictionTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trading_asset_id', 
        'prediction',
        'trade_type',
        'trade_amount',
        'entry_price',
        'current_price',
        'exit_price',
        'potential_payout',
        'actual_payout',
        'duration_minutes',
        'start_time',
        'end_time',
        'closed_manually',
        'status',
        'profit_loss',
        'admin_manipulated',
        'manipulation_reason'
    ];

    protected $casts = [
        'trade_amount' => 'decimal:2',
        'entry_price' => 'decimal:8',
        'current_price' => 'decimal:8',
        'exit_price' => 'decimal:8',
        'potential_payout' => 'decimal:2',
        'actual_payout' => 'decimal:2',
        'profit_loss' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'closed_manually' => 'boolean',
        'admin_manipulated' => 'boolean'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tradingAsset(): BelongsTo
    {
        return $this->belongsTo(TradingAsset::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(TradeOverride::class, 'trade_id');
    }

    // Business Logic Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        if ($this->trade_type === 'fixed_time' && $this->end_time) {
            return Carbon::now()->isAfter($this->end_time);
        }
        return false;
    }

    public function canClose(): bool
    {
        return $this->isActive() && ($this->trade_type === 'flexible' || !$this->isExpired());
    }

    public function calculateCurrentProfit(): float
    {
        if (!$this->isActive()) {
            return (float) $this->profit_loss;
        }

        $priceChange = $this->current_price - $this->entry_price;
        $isWinning = ($this->prediction === 'UP' && $priceChange > 0) || 
                    ($this->prediction === 'DOWN' && $priceChange < 0);

        if ($isWinning) {
            return (float) $this->potential_payout - (float) $this->trade_amount;
        } else {
            return -(float) $this->trade_amount;
        }
    }

    public function updateCurrentPrice(float $newPrice): void
    {
        $this->update(['current_price' => $newPrice]);
    }

    public function closeTrade(float $exitPrice = null, bool $manualClose = false): void
    {
        $this->exit_price = $exitPrice ?? $this->current_price;
        $this->closed_manually = $manualClose;
        $this->end_time = Carbon::now();

        // Calculate if trade won or lost
        $priceChange = $this->exit_price - $this->entry_price;
        $isWinning = ($this->prediction === 'UP' && $priceChange > 0) || 
                    ($this->prediction === 'DOWN' && $priceChange < 0);

        if ($isWinning) {
            $this->status = 'won';
            $this->actual_payout = $this->potential_payout;
            $this->profit_loss = number_format((float) $this->potential_payout - (float) $this->trade_amount, 2, '.', '');
        } else {
            $this->status = 'lost';
            $this->actual_payout = '0.00';
            $this->profit_loss = number_format(-(float) $this->trade_amount, 2, '.', '');
        }

        $this->save();

        // Update user statistics
        $this->updateUserStats();
    }

    public function cancelTrade(): void
    {
        if ($this->canClose()) {
            $this->status = 'cancelled';
            $this->end_time = Carbon::now();
            $this->actual_payout = $this->trade_amount; // Return investment
            $this->profit_loss = '0.00';
            $this->save();
        }
    }

    public function forceWin(string $reason = 'Admin override'): void
    {
        $this->status = 'won';
        $this->actual_payout = $this->potential_payout;
        $this->profit_loss = number_format((float) $this->potential_payout - (float) $this->trade_amount, 2, '.', '');
        $this->admin_manipulated = true;
        $this->manipulation_reason = $reason;
        $this->end_time = Carbon::now();
        $this->save();

        $this->updateUserStats();
    }

    public function forceLoss(string $reason = 'Admin override'): void
    {
        $this->status = 'lost';
        $this->actual_payout = '0.00';
        $this->profit_loss = number_format(-(float) $this->trade_amount, 2, '.', '');
        $this->admin_manipulated = true;
        $this->manipulation_reason = $reason;
        $this->end_time = Carbon::now();
        $this->save();

        $this->updateUserStats();
    }

    private function updateUserStats(): void
    {
        $user = $this->user;
        
        // Update trading balance
        $user->trading_balance += $this->actual_payout ?? 0;
        
        // Update statistics
        $user->total_trades += 1;
        $user->total_profit_loss += $this->profit_loss;
        $user->last_trade_at = Carbon::now();
        
        if ($this->status === 'won') {
            $user->winning_trades += 1;
        } elseif ($this->status === 'lost') {
            $user->losing_trades += 1;
        }
        
        // Calculate win rate
        $totalDecisiveTrades = $user->winning_trades + $user->losing_trades;
        $user->win_rate = $totalDecisiveTrades > 0 ? 
            ($user->winning_trades / $totalDecisiveTrades) * 100 : 0;
        
        $user->save();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAsset($query, $assetId)
    {
        return $query->where('trading_asset_id', $assetId);
    }

    public function scopeExpired($query)
    {
        return $query->where('trade_type', 'fixed_time')
                    ->where('end_time', '<=', Carbon::now())
                    ->where('status', 'active');
    }
}
