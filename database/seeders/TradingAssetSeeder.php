<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TradingAsset;

class TradingAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $assets = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC/USD',
                'type' => 'crypto',
                'coingecko_id' => 'bitcoin',
                'icon_url' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
                'current_price' => 43250.00,
                'change_24h' => 2.4,
                'market_cap' => 847000000000,
                'market_cap_rank' => 1,
                'total_volume' => 15300000000,
                'high_24h' => 44200.00,
                'low_24h' => 42800.00,
                'description' => 'Bitcoin is a decentralized digital currency.',
                'is_active' => true,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH/USD',
                'type' => 'crypto',
                'coingecko_id' => 'ethereum',
                'icon_url' => 'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
                'current_price' => 2650.00,
                'change_24h' => -1.2,
                'market_cap' => 318000000000,
                'market_cap_rank' => 2,
                'total_volume' => 8200000000,
                'high_24h' => 2720.00,
                'low_24h' => 2620.00,
                'description' => 'Ethereum is a decentralized platform for smart contracts.',
                'is_active' => true,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Apple Inc',
                'symbol' => 'AAPL',
                'type' => 'stock',
                'coingecko_id' => null,
                'icon_url' => 'https://logo.clearbit.com/apple.com',
                'current_price' => 175.84,
                'change_24h' => 0.8,
                'market_cap' => 2750000000000,
                'market_cap_rank' => null,
                'total_volume' => 52000000,
                'high_24h' => 177.20,
                'low_24h' => 174.50,
                'description' => 'Apple Inc. designs, manufactures, and markets smartphones and computers.',
                'is_active' => true,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tesla Inc',
                'symbol' => 'TSLA',
                'type' => 'stock',
                'coingecko_id' => null,
                'icon_url' => 'https://logo.clearbit.com/tesla.com',
                'current_price' => 238.45,
                'change_24h' => -2.1,
                'market_cap' => 760000000000,
                'market_cap_rank' => null,
                'total_volume' => 95000000,
                'high_24h' => 245.80,
                'low_24h' => 235.90,
                'description' => 'Tesla, Inc. designs, develops, and manufactures electric vehicles.',
                'is_active' => true,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EUR/USD',
                'symbol' => 'EURUSD',
                'type' => 'forex',
                'coingecko_id' => null,
                'icon_url' => null,
                'current_price' => 1.0845,
                'change_24h' => 0.3,
                'market_cap' => null,
                'market_cap_rank' => null,
                'total_volume' => null,
                'high_24h' => 1.0865,
                'low_24h' => 1.0820,
                'description' => 'Euro to US Dollar exchange rate.',
                'is_active' => true,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GBP/USD',
                'symbol' => 'GBPUSD',
                'type' => 'forex',
                'coingecko_id' => null,
                'icon_url' => null,
                'current_price' => 1.2635,
                'change_24h' => -0.1,
                'market_cap' => null,
                'market_cap_rank' => null,
                'total_volume' => null,
                'high_24h' => 1.2670,
                'low_24h' => 1.2615,
                'description' => 'British Pound to US Dollar exchange rate.',
                'is_active' => true,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($assets as $asset) {
            TradingAsset::create($asset);
        }
    }
}
