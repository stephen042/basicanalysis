<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TradingBot;

class TradingBotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tradingBots = [
            [
                'name' => 'Bitcoin Scalper Pro',
                'description' => 'Advanced Bitcoin scalping bot that executes high-frequency trades to capture small price movements. Perfect for conservative investors seeking steady returns.',
                'min_amount' => 100,
                'max_amount' => 5000,
                'duration' => 24,
                'profit_rate' => 5.5,
                'status' => 'active',
            ],
            [
                'name' => 'Ethereum Swing Trader',
                'description' => 'Medium-term trading bot focused on Ethereum swing patterns. Analyzes market trends and executes strategic trades for optimal profit generation.',
                'min_amount' => 250,
                'max_amount' => 10000,
                'duration' => 48,
                'profit_rate' => 8.2,
                'status' => 'active',
            ],
            [
                'name' => 'DeFi Yield Hunter',
                'description' => 'Specialized bot for DeFi protocols and yield farming opportunities. Automatically finds and capitalizes on high-yield DeFi investments.',
                'min_amount' => 500,
                'max_amount' => 25000,
                'duration' => 72,
                'profit_rate' => 12.8,
                'status' => 'active',
            ],
            [
                'name' => 'Altcoin Momentum Bot',
                'description' => 'High-performance bot that tracks momentum in alternative cryptocurrencies. Uses advanced algorithms to predict price movements.',
                'min_amount' => 150,
                'max_amount' => 7500,
                'duration' => 36,
                'profit_rate' => 9.5,
                'status' => 'active',
            ],
            [
                'name' => 'Stable Arbitrage Master',
                'description' => 'Low-risk arbitrage bot that exploits price differences across multiple exchanges. Ideal for risk-averse investors wanting consistent returns.',
                'min_amount' => 1000,
                'max_amount' => 50000,
                'duration' => 168, // 1 week
                'profit_rate' => 15.5,
                'status' => 'active',
            ],
            [
                'name' => 'AI Prediction Engine',
                'description' => 'Cutting-edge AI-powered trading bot using machine learning algorithms to predict market movements with high accuracy.',
                'min_amount' => 300,
                'max_amount' => 15000,
                'duration' => 96,
                'profit_rate' => 18.7,
                'status' => 'active',
            ],
        ];

        foreach ($tradingBots as $botData) {
            TradingBot::create($botData);
        }
    }
}
