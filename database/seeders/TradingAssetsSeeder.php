<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TradingAsset;
use App\Models\TradingBot;

class TradingAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $assets = [
            // Cryptocurrencies
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'type' => 'crypto',
                'icon' => 'bitcoin-btc-logo.png',
                'current_price' => 43250.75,
                'change_24h' => 2.45,
                'status' => 'active',
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'type' => 'crypto',
                'icon' => 'ethereum-eth-logo.png',
                'current_price' => 2680.32,
                'change_24h' => 1.87,
                'status' => 'active',
            ],
            [
                'name' => 'Tether',
                'symbol' => 'USDT',
                'type' => 'crypto',
                'icon' => 'tether-usdt-logo.png',
                'current_price' => 1.0001,
                'change_24h' => 0.01,
                'status' => 'active',
            ],
            [
                'name' => 'Binance Coin',
                'symbol' => 'BNB',
                'type' => 'crypto',
                'icon' => null,
                'current_price' => 312.45,
                'change_24h' => -0.95,
                'status' => 'active',
            ],
            [
                'name' => 'Cardano',
                'symbol' => 'ADA',
                'type' => 'crypto',
                'icon' => null,
                'current_price' => 0.4523,
                'change_24h' => 3.21,
                'status' => 'active',
            ],
            // Stocks
            [
                'name' => 'Apple Inc.',
                'symbol' => 'AAPL',
                'type' => 'stock',
                'icon' => null,
                'current_price' => 175.84,
                'change_24h' => 1.23,
                'status' => 'active',
            ],
            [
                'name' => 'Tesla Inc.',
                'symbol' => 'TSLA',
                'type' => 'stock',
                'icon' => null,
                'current_price' => 251.52,
                'change_24h' => -2.45,
                'status' => 'active',
            ],
            [
                'name' => 'Microsoft Corporation',
                'symbol' => 'MSFT',
                'type' => 'stock',
                'icon' => null,
                'current_price' => 378.85,
                'change_24h' => 0.87,
                'status' => 'active',
            ],
            [
                'name' => 'NVIDIA Corporation',
                'symbol' => 'NVDA',
                'type' => 'stock',
                'icon' => null,
                'current_price' => 465.12,
                'change_24h' => 4.56,
                'status' => 'active',
            ],
            // Forex
            [
                'name' => 'EUR/USD',
                'symbol' => 'EURUSD',
                'type' => 'forex',
                'icon' => null,
                'current_price' => 1.0985,
                'change_24h' => 0.12,
                'status' => 'active',
            ],
            [
                'name' => 'GBP/USD',
                'symbol' => 'GBPUSD',
                'type' => 'forex',
                'icon' => null,
                'current_price' => 1.2743,
                'change_24h' => -0.34,
                'status' => 'active',
            ],
            // Commodities
            [
                'name' => 'Gold',
                'symbol' => 'XAU',
                'type' => 'commodity',
                'icon' => null,
                'current_price' => 1954.32,
                'change_24h' => 0.76,
                'status' => 'active',
            ],
            [
                'name' => 'Crude Oil',
                'symbol' => 'WTI',
                'type' => 'commodity',
                'icon' => null,
                'current_price' => 89.45,
                'change_24h' => -1.23,
                'status' => 'active',
            ],
        ];

        foreach ($assets as $asset) {
            TradingAsset::create($asset);
        }

        // Assign assets to trading bots
        $this->assignAssetsToTradingBots();
    }

    private function assignAssetsToTradingBots()
    {
        try {
            $bots = TradingBot::all();
            $assets = TradingAsset::all();

            if ($bots->isEmpty() || $assets->isEmpty()) {
                return; // No bots or assets to assign
            }

            foreach ($bots as $bot) {
                // Clear existing assignments first
                $bot->tradingAssets()->detach();
                
                // Assign 3-5 random assets to each bot
                $botAssets = $assets->random(min(rand(3, 5), $assets->count()));
                $totalAllocation = 100;
                
                foreach ($botAssets as $index => $asset) {
                    $isLast = $index === ($botAssets->count() - 1);
                    $allocation = $isLast ? $totalAllocation : rand(10, 30);
                    
                    // Ensure we don't exceed 100%
                    if ($allocation > $totalAllocation) {
                        $allocation = $totalAllocation;
                    }
                    
                    $bot->tradingAssets()->attach($asset->id, [
                        'allocation_percentage' => $allocation
                    ]);
                    
                    $totalAllocation -= $allocation;
                    
                    if ($totalAllocation <= 0) break;
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the seeding
            \Log::error('Error assigning assets to trading bots: ' . $e->getMessage());
        }
    }
}
