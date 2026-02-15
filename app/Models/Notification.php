<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url',
        'icon',
        'priority'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types constants
    const TYPE_BOT_TRADE = 'bot_trade';
    const TYPE_PROFIT_RETURN = 'profit_return';
    const TYPE_BOT_COMPLETED = 'bot_completed';
    const TYPE_BOT_STARTED = 'bot_started';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_KYC = 'kyc';
    const TYPE_ACCOUNT = 'account';
    const TYPE_SYSTEM = 'system';
    const TYPE_GENERAL = 'general';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific priority
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if ($this->read_at) {
            return true; // Already read
        }
        
        return $this->update(['read_at' => Carbon::now()]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
        return $this;
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread()
    {
        return is_null($this->read_at);
    }

    /**
     * Get the icon class for the notification
     */
    public function getIconClass()
    {
        $iconMap = [
            self::TYPE_BOT_TRADE => 'fas fa-robot',
            self::TYPE_PROFIT_RETURN => 'fas fa-chart-line',
            self::TYPE_BOT_COMPLETED => 'fas fa-check-circle',
            self::TYPE_BOT_STARTED => 'fas fa-play-circle',
            self::TYPE_DEPOSIT => 'fas fa-arrow-down',
            self::TYPE_WITHDRAWAL => 'fas fa-arrow-up',
            self::TYPE_KYC => 'fas fa-id-card',
            self::TYPE_ACCOUNT => 'fas fa-user',
            self::TYPE_SYSTEM => 'fas fa-cog',
            self::TYPE_GENERAL => 'fas fa-bell',
        ];

        return $iconMap[$this->type] ?? $this->icon ?? 'fas fa-bell';
    }

    /**
     * Get the color class for the notification based on priority
     */
    public function getPriorityColor()
    {
        $colorMap = [
            self::PRIORITY_LOW => 'text-gray-400',
            self::PRIORITY_NORMAL => 'text-blue-500',
            self::PRIORITY_HIGH => 'text-orange-500',
            self::PRIORITY_URGENT => 'text-red-500',
        ];

        return $colorMap[$this->priority] ?? 'text-blue-500';
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgo()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification data value by key
     */
    public function getData($key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Static method to create bot trade notification
     */
    public static function createBotTradeNotification($userId, $botName, $action, $amount, $asset = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOT_TRADE,
            'title' => "Bot Trade {$action}",
            'message' => "Your {$botName} bot has {$action} a trade" . ($asset ? " on {$asset}" : ""),
            'data' => [
                'bot_name' => $botName,
                'action' => $action,
                'amount' => $amount,
                'asset' => $asset,
                'timestamp' => Carbon::now()->toISOString(),
            ],
            'icon' => 'fas fa-robot',
            'priority' => self::PRIORITY_NORMAL,
        ]);
    }

    /**
     * Static method to create profit return notification
     */
    public static function createProfitNotification($userId, $botName, $profit, $totalAmount)
    {
        $isProfit = $profit > 0;
        $action = $isProfit ? 'Profit Earned' : 'Loss Incurred';
        $color = $isProfit ? 'success' : 'danger';

        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_PROFIT_RETURN,
            'title' => $action,
            'message' => "Your {$botName} bot has " . ($isProfit ? 'earned' : 'incurred') . " $" . number_format(abs($profit), 2),
            'data' => [
                'bot_name' => $botName,
                'profit_amount' => $profit,
                'total_amount' => $totalAmount,
                'is_profit' => $isProfit,
                'timestamp' => Carbon::now()->toISOString(),
            ],
            'icon' => $isProfit ? 'fas fa-chart-line' : 'fas fa-chart-line-down',
            'priority' => $isProfit ? self::PRIORITY_HIGH : self::PRIORITY_URGENT,
        ]);
    }

    /**
     * Static method to create bot completion notification
     */
    public static function createBotCompletionNotification($userId, $botName, $finalAmount, $totalProfit)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOT_COMPLETED,
            'title' => 'Bot Trading Completed',
            'message' => "Your {$botName} bot has completed trading with a total " . ($totalProfit >= 0 ? 'profit' : 'loss') . " of $" . number_format(abs($totalProfit), 2),
            'data' => [
                'bot_name' => $botName,
                'final_amount' => $finalAmount,
                'total_profit' => $totalProfit,
                'completion_time' => Carbon::now()->toISOString(),
            ],
            'action_url' => route('trading-bots.index'),
            'icon' => 'fas fa-check-circle',
            'priority' => self::PRIORITY_HIGH,
        ]);
    }
}
