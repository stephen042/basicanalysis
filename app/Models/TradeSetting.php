<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description'
    ];

    // Helper methods for common settings
    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    public static function set(string $key, $value, string $description = null): void
    {
        self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'description' => $description
            ]
        );
    }

    // Specific setting getters
    public static function getGlobalWinRate(): float
    {
        return (float) self::get('global_win_rate', 85);
    }

    public static function getDefaultPayout(): float
    {
        return (float) self::get('default_payout', 180);
    }

    public static function getMinTradeAmount(): float
    {
        return (float) self::get('min_trade_amount', 1.00);
    }

    public static function getMaxTradeAmount(): float
    {
        return (float) self::get('max_trade_amount', 1000.00);
    }

    public static function isManipulationEnabled(): bool
    {
        return (bool) self::get('manipulation_enabled', true);
    }

    public static function getManipulationIntensity(): float
    {
        return (float) self::get('manipulation_intensity', 0.3);
    }

    public static function getMaxActiveTrades(): int
    {
        return (int) self::get('max_active_trades', 5);
    }

    public static function getTradingHours(): array
    {
        $hours = self::get('trading_hours', '00:00-23:59');
        return explode('-', $hours);
    }

    public static function isTradingAllowed(): bool
    {
        return (bool) self::get('trading_enabled', true);
    }

    // Initialize default settings
    public static function initializeDefaults(): void
    {
        $defaults = [
            'global_win_rate' => ['value' => '85', 'description' => 'Global win rate percentage for manipulation'],
            'default_payout' => ['value' => '180', 'description' => 'Default payout percentage for winning trades'],
            'min_trade_amount' => ['value' => '1.00', 'description' => 'Minimum trade amount allowed'],
            'max_trade_amount' => ['value' => '1000.00', 'description' => 'Maximum trade amount allowed'],
            'manipulation_enabled' => ['value' => '1', 'description' => 'Enable admin manipulation features'],
            'manipulation_intensity' => ['value' => '0.3', 'description' => 'Price manipulation intensity (0-1)'],
            'max_active_trades' => ['value' => '5', 'description' => 'Maximum active trades per user'],
            'trading_hours' => ['value' => '00:00-23:59', 'description' => 'Trading hours (24h format)'],
            'trading_enabled' => ['value' => '1', 'description' => 'Global trading enable/disable'],
        ];

        foreach ($defaults as $key => $config) {
            self::firstOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $config['value'],
                    'description' => $config['description']
                ]
            );
        }
    }
}
