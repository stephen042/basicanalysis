<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use App\Models\Settings;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Send the email verification notification.
     *
     * @return void
     */

    public function sendEmailVerificationNotification()
    {
        try {
            $settings = Schema::hasTable('settings') ? Settings::where('id', 1)->first() : null;
            if ($settings && $settings->enable_verification == 'true') {
                $this->notify(new VerifyEmail);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'l_name',
        'email',
        'phone',
        'country',
        'password',
        'ref_by',
        'status',
        'username',
        'email_verified_at',
        'currency',
        'trading_balance',
        'demo_balance',
        'total_trades',
        'winning_trades',
        'losing_trades',
        'total_profit_loss',
        'win_rate',
        'trading_enabled',
        'last_trade_at',
        'trading_profit_rate',
        'copy_trading_win_rate',
        'copy_trading_profit_percentage',
        'copy_trading_loss_percentage',
        'signal_strength_enabled',
        'signal_strength_value',
        'notification_enabled',
        'notification_message',
        'withdrawal_code_enabled',
        'withdrawal_code',
        'withdrawal_code_name',
        'withdrawal_code_message',
        'tax_code_enabled',
        'tax_code',
        'tax_code_name',
        'tax_code_message'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'account_bal' => 'decimal:2',
        'roi' => 'decimal:2',
        'trading_balance' => 'decimal:2',
        'demo_balance' => 'decimal:2',
        'total_profit_loss' => 'decimal:2',
        'win_rate' => 'decimal:2',
        'trading_enabled' => 'boolean',
        'last_trade_at' => 'datetime',
        'trading_profit_rate' => 'decimal:2',
        'copy_trading_win_rate' => 'decimal:2',
        'copy_trading_profit_percentage' => 'decimal:2',
        'copy_trading_loss_percentage' => 'decimal:2',
        'signal_strength_enabled' => 'boolean',
        'signal_strength_value' => 'integer',
        'notification_enabled' => 'boolean',
        'withdrawal_code_enabled' => 'boolean',
        'tax_code_enabled' => 'boolean'
    ];

    /**
     * Legacy installs do not all have the optional username column. Keep
     * email authentication available without issuing SQL against a missing
     * column.
     */
    public static function hasUsernameColumn(): bool
    {
        return in_array('username', static::databaseColumns((new static())->getTable()), true);
    }

    /**
     * Read column names using SHOW on MySQL/MariaDB, so authentication does
     * not require SELECT privileges on information_schema.
     */
    public static function databaseColumns($table = 'users'): array
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return [];
        }

        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                $columns = DB::select('SHOW COLUMNS FROM `' . $table . '`');
                return array_map(function ($column) {
                    return $column->Field;
                }, $columns);
            }

            return Schema::getColumnListing($table);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Find a user by email, and by username only when that column exists.
     */
    public static function findByLoginIdentifier($identifier)
    {
        $query = static::where('email', $identifier);

        if (static::hasUsernameColumn()) {
            $query->orWhere('username', $identifier);
        }

        return $query->first();
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];


    public function dp()
    {
        return $this->hasMany(Deposit::class, 'user');
    }

    public function wd()
    {
        return $this->hasMany(Withdrawal::class, 'user');
    }

    public function tuser()
    {
        return $this->belongsTo(Admin::class, 'assign_to');
    }

    public function dplan()
    {
        return $this->belongsTo(Plans::class, 'plan');
    }

    public function plans()
    {
        return $this->hasMany(User_plans::class, 'user', 'id');
    }

    public function tradingBots()
    {
        return $this->hasMany(UserTradingBot::class);
    }

    public function compoundInvestments()
    {
        return $this->hasMany(CompoundInvestment::class);
    }

    // Trading-related relationships
    public function predictionTrades()
    {
        return $this->hasMany(PredictionTrade::class);
    }

    public function tradeControls()
    {
        return $this->hasOne(UserTradeControl::class);
    }

    public function activeTrades()
    {
        return $this->predictionTrades()->active();
    }

    // Trading helper methods
    public function canTrade(): bool
    {
        if (!$this->trading_enabled) {
            return false;
        }

        $controls = $this->tradeControls;
        if ($controls) {
            return $controls->canUserTrade();
        }

        return true;
    }

    public function getTradeControlsAttribute()
    {
        return $this->tradeControls ?: UserTradeControl::getForUser($this->id);
    }

    public function hasActiveTrades(): bool
    {
        return $this->activeTrades()->exists();
    }

    public function getActiveTradesCount(): int
    {
        return $this->activeTrades()->count();
    }

    public static function search($search): \Illuminate\Database\Eloquent\Builder
    {
        if (empty($search)) {
            return static::query();
        }

        return static::query()->where(function ($query) use ($search) {
            $query->where('id', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
            if (static::hasUsernameColumn()) {
                $query->orWhere('username', 'like', '%' . $search . '%');
            }
        });
    }

    /**
     * Get user notifications
     */
    public function userNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications
     */
    public function unreadNotifications()
    {
        return $this->userNotifications()->unread();
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    public function signalTransactions()
    {
        return $this->hasMany(SignalTransaction::class);
    }

    /**
     * Copy-trading subscriptions owned by this user.
     */
    public function copySubscriptions()
    {
        return $this->hasMany(CopySubscription::class, 'user_id', 'id');
    }

    // Helper to check if user has an active signal
    public function hasActiveSignalFor($signalId)
    {
        return $this->signalTransactions()
            ->where('signal_id', $signalId)
            ->where('status', 'approved')
            ->exists();
    }
}
