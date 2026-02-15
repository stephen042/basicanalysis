<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TradingBot;
use App\Models\TradingAsset;
use Illuminate\Support\Str;

class TradingBotPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define 10 comprehensive trading bot plans
        $tradingBots = [
            [
                'name' => 'Bitcoin Pro Scalper',
                'description' => 'High-frequency Bitcoin trading bot designed for experienced traders. Uses advanced scalping strategies to capture small price movements with high accuracy.',
                'min_amount' => 500.00,
                'max_amount' => 10000.00,
                'duration_hours' => 24,
                'profit_rate' => 2.5,
                'risk_level' => 'high',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'BTC', 'allocation' => 70],
                    ['symbol' => 'ETH', 'allocation' => 20],
                    ['symbol' => 'USDT', 'allocation' => 10],
                ]
            ],
            [
                'name' => 'Conservative Growth',
                'description' => 'Low-risk, steady growth bot perfect for beginners. Focuses on stable assets with consistent returns over longer periods.',
                'min_amount' => 100.00,
                'max_amount' => 5000.00,
                'duration_hours' => 168, // 7 days
                'profit_rate' => 1.2,
                'risk_level' => 'low',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'AAPL', 'allocation' => 30],
                    ['symbol' => 'GOOGL', 'allocation' => 25],
                    ['symbol' => 'MSFT', 'allocation' => 25],
                    ['symbol' => 'USDT', 'allocation' => 20],
                ]
            ],
            [
                'name' => 'Crypto Momentum Hunter',
                'description' => 'Aggressive cryptocurrency momentum trading bot that capitalizes on trending altcoins and major crypto movements.',
                'min_amount' => 250.00,
                'max_amount' => 15000.00,
                'duration_hours' => 72,
                'profit_rate' => 4.2,
                'risk_level' => 'high',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'BTC', 'allocation' => 40],
                    ['symbol' => 'ETH', 'allocation' => 35],
                    ['symbol' => 'BNB', 'allocation' => 25],
                ]
            ],
            [
                'name' => 'Forex Swing Master',
                'description' => 'Professional forex swing trading bot that trades major currency pairs with technical analysis and market sentiment.',
                'min_amount' => 1000.00,
                'max_amount' => 25000.00,
                'duration_hours' => 120, // 5 days
                'profit_rate' => 1.8,
                'risk_level' => 'medium',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'EUR/USD', 'allocation' => 40],
                    ['symbol' => 'GBP/USD', 'allocation' => 30],
                    ['symbol' => 'USD/JPY', 'allocation' => 30],
                ]
            ],
            [
                'name' => 'Tech Stock Wizard',
                'description' => 'Specialized bot for trading technology stocks using AI-powered analysis of market trends, earnings, and tech sector movements.',
                'min_amount' => 300.00,
                'max_amount' => 8000.00,
                'duration_hours' => 96, // 4 days
                'profit_rate' => 2.1,
                'risk_level' => 'medium',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'TSLA', 'allocation' => 35],
                    ['symbol' => 'AAPL', 'allocation' => 30],
                    ['symbol' => 'GOOGL', 'allocation' => 25],
                    ['symbol' => 'MSFT', 'allocation' => 10],
                ]
            ],
            [
                'name' => 'Gold Rush Trader',
                'description' => 'Commodity-focused bot specializing in precious metals trading during market volatility and economic uncertainty.',
                'min_amount' => 500.00,
                'max_amount' => 12000.00,
                'duration_hours' => 144, // 6 days
                'profit_rate' => 1.5,
                'risk_level' => 'low',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'GOLD', 'allocation' => 60],
                    ['symbol' => 'SILVER', 'allocation' => 25],
                    ['symbol' => 'OIL', 'allocation' => 15],
                ]
            ],
            [
                'name' => 'Day Trading Beast',
                'description' => 'Aggressive day trading bot for active traders. Executes multiple trades daily across various assets with quick profit-taking.',
                'min_amount' => 750.00,
                'max_amount' => 20000.00,
                'duration_hours' => 12,
                'profit_rate' => 3.8,
                'risk_level' => 'high',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'BTC', 'allocation' => 25],
                    ['symbol' => 'ETH', 'allocation' => 25],
                    ['symbol' => 'TSLA', 'allocation' => 25],
                    ['symbol' => 'AAPL', 'allocation' => 25],
                ]
            ],
            [
                'name' => 'Stable Income Generator',
                'description' => 'Ultra-conservative bot designed for passive income generation with minimal risk. Perfect for retirement accounts and long-term savings.',
                'min_amount' => 50.00,
                'max_amount' => 3000.00,
                'duration_hours' => 240, // 10 days
                'profit_rate' => 0.8,
                'risk_level' => 'low',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'USDT', 'allocation' => 50],
                    ['symbol' => 'AAPL', 'allocation' => 20],
                    ['symbol' => 'MSFT', 'allocation' => 20],
                    ['symbol' => 'GOOGL', 'allocation' => 10],
                ]
            ],
            [
                'name' => 'Multi-Asset Balanced',
                'description' => 'Diversified trading bot that spreads risk across multiple asset classes including crypto, stocks, forex, and commodities.',
                'min_amount' => 400.00,
                'max_amount' => 18000.00,
                'duration_hours' => 192, // 8 days
                'profit_rate' => 2.0,
                'risk_level' => 'medium',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'BTC', 'allocation' => 20],
                    ['symbol' => 'AAPL', 'allocation' => 20],
                    ['symbol' => 'EUR/USD', 'allocation' => 20],
                    ['symbol' => 'GOLD', 'allocation' => 20],
                    ['symbol' => 'ETH', 'allocation' => 10],
                    ['symbol' => 'TSLA', 'allocation' => 10],
                ]
            ],
            [
                'name' => 'Volatility Crusher',
                'description' => 'Advanced bot that thrives in volatile markets using sophisticated algorithms to profit from price swings and market chaos.',
                'min_amount' => 1000.00,
                'max_amount' => 30000.00,
                'duration_hours' => 48,
                'profit_rate' => 5.5,
                'risk_level' => 'high',
                'is_active' => true,
                'assets' => [
                    ['symbol' => 'BTC', 'allocation' => 30],
                    ['symbol' => 'ETH', 'allocation' => 25],
                    ['symbol' => 'TSLA', 'allocation' => 20],
                    ['symbol' => 'OIL', 'allocation' => 15],
                    ['symbol' => 'GOLD', 'allocation' => 10],
                ]
            ],
        ];

        foreach ($tradingBots as $botData) {
            // Extract assets data before creating bot
            $assetsData = $botData['assets'];
            unset($botData['assets']);

            // Create the trading bot
            $bot = TradingBot::create([
                'name' => $botData['name'],
                'description' => $botData['description'],
                'min_amount' => $botData['min_amount'],
                'max_amount' => $botData['max_amount'],
                'duration_hours' => $botData['duration_hours'],
                'profit_rate' => $botData['profit_rate'],
                'risk_level' => $botData['risk_level'],
                'is_active' => $botData['is_active'],
            ]);

            // Attach assets with allocation percentages
            foreach ($assetsData as $assetData) {
                $asset = TradingAsset::where('symbol', $assetData['symbol'])->first();
                if ($asset) {
                    $bot->tradingAssets()->attach($asset->id, [
                        'allocation_percentage' => $assetData['allocation'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $this->command->warn("Asset with symbol '{$assetData['symbol']}' not found. Skipping allocation for bot '{$bot->name}'.");
                }
            }

            $this->command->info("Created trading bot: {$bot->name} with " . count($assetsData) . " asset allocations.");
        }

        $this->command->info('Successfully created 10 trading bot plans with asset allocations!');
    }
}
