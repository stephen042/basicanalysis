<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserTradeControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'max_trade_amount',
        'min_trade_amount',
        'max_active_trades',
        'daily_trade_limit',
        'daily_loss_limit',
        'can_trade',
        'force_lose',
        'forced_win_rate',
        'restriction_reason',
        'restricted_until'
    ];

    protected $casts = [
        'max_trade_amount' => 'decimal:2',
        'min_trade_amount' => 'decimal:2',
        'daily_loss_limit' => 'decimal:2',
        'forced_win_rate' => 'decimal:2',
        'can_trade' => 'boolean',
        'force_lose' => 'boolean',
        'restricted_until' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Business Logic Methods
    public function canUserTrade(): bool
    {
        if (!$this->can_trade) {
            return false;
        }

        if ($this->restricted_until && Carbon::now()->isBefore($this->restricted_until)) {
            return false;
        }

        return true;
    }

    public function validateTradeAmount(float $amount): bool
    {
        if ($amount < $this->min_trade_amount) {
            return false;
        }

        if ($this->max_trade_amount && $amount > $this->max_trade_amount) {
            return false;
        }

        return true;
    }

    public function canOpenNewTrade(): bool
    {
        if (!$this->canUserTrade()) {
            return false;
        }

        $activeTrades = $this->user->predictionTrades()->active()->count();
        
        return $activeTrades < $this->max_active_trades;
    }

    public function hasReachedDailyTradeLimit(): bool
    {
        if (!$this->daily_trade_limit) {
            return false;
        }

        $todayTrades = $this->user->predictionTrades()
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $todayTrades >= $this->daily_trade_limit;
    }

    public function hasReachedDailyLossLimit(): bool
    {
        if (!$this->daily_loss_limit) {
            return false;
        }

        $todayLoss = $this->user->predictionTrades()
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'lost')
            ->sum('trade_amount');

        return $todayLoss >= $this->daily_loss_limit;
    }

    public function shouldForceLose(): bool
    {
        return $this->force_lose;
    }

    public function getForcedWinRate(): ?float
    {
        return $this->forced_win_rate ? (float) $this->forced_win_rate : null;
    }

    public function restrictUser(string $reason, Carbon $until = null): void
    {
        $this->update([
            'can_trade' => false,
            'restriction_reason' => $reason,
            'restricted_until' => $until
        ]);
    }

    public function unrestrict(): void
    {
        $this->update([
            'can_trade' => true,
            'restriction_reason' => null,
            'restricted_until' => null
        ]);
    }

    public function setForcedWinRate(float $winRate): void
    {
        $this->update(['forced_win_rate' => $winRate]);
    }

    public function enableForceLose(bool $enable = true): void
    {
        $this->update(['force_lose' => $enable]);
    }

    // Static factory methods
    public static function createForUser(int $userId, array $controls = []): self
    {
        $defaultControls = [
            'user_id' => $userId,
            'max_trade_amount' => TradeSetting::getMaxTradeAmount(),
            'min_trade_amount' => TradeSetting::getMinTradeAmount(),
            'max_active_trades' => TradeSetting::getMaxActiveTrades(),
            'can_trade' => true,
            'force_lose' => false
        ];

        return self::create(array_merge($defaultControls, $controls));
    }

    public static function getForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'max_trade_amount' => TradeSetting::getMaxTradeAmount(),
                'min_trade_amount' => TradeSetting::getMinTradeAmount(),
                'max_active_trades' => TradeSetting::getMaxActiveTrades(),
                'can_trade' => true,
                'force_lose' => false
            ]
        );
    }

    // Validation message helpers
    public function getTradeValidationMessage(): ?string
    {
        if (!$this->can_trade) {
            return $this->restriction_reason ?? 'Trading is disabled for your account.';
        }

        if ($this->restricted_until && Carbon::now()->isBefore($this->restricted_until)) {
            return "Your account is restricted until " . $this->restricted_until->format('Y-m-d H:i:s');
        }

        if ($this->hasReachedDailyTradeLimit()) {
            return "You have reached your daily trade limit of {$this->daily_trade_limit} trades.";
        }

        if ($this->hasReachedDailyLossLimit()) {
            return "You have reached your daily loss limit of $" . number_format($this->daily_loss_limit, 2);
        }

        if (!$this->canOpenNewTrade()) {
            return "You have reached your maximum active trades limit of {$this->max_active_trades}.";
        }

        return null;
    }
}
