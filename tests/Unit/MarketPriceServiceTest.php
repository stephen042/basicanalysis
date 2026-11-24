<?php

namespace Tests\Unit;

use App\Services\MarketPriceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketPriceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_usd_prices_batches_all_symbols_in_one_request(): void
    {
        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'bitcoin' => ['usd' => 50000],
                'ethereum' => ['usd' => 2000],
                'tether' => ['usd' => 1],
            ], 200),
        ]);

        $prices = app(MarketPriceService::class)->getUsdPrices(['btc', 'eth', 'usdt']);

        $this->assertSame([
            'btc' => 50000.0,
            'eth' => 2000.0,
            'usdt' => 1.0,
        ], $prices);

        Http::assertSentCount(1);
    }

    public function test_get_usd_prices_falls_back_to_binance_on_coingecko_failure(): void
    {
        Http::fake([
            'api.coingecko.com/*' => Http::response(['status' => ['error_code' => 429]], 429),
            'api.binance.com/*' => Http::response([
                ['symbol' => 'BTCUSDT', 'price' => '50000.00'],
            ], 200),
        ]);

        $price = app(MarketPriceService::class)->getUsdPrice('btc');

        $this->assertSame(50000.0, $price);
    }

    public function test_invalid_symbol_returns_null(): void
    {
        $this->assertNull(app(MarketPriceService::class)->getUsdPrice('notacoin'));
    }

    public function test_usd_price_is_always_one(): void
    {
        $this->assertSame(1.0, app(MarketPriceService::class)->getUsdPrice('usd'));
    }

    public function test_get_rate_uses_common_usd_denominator(): void
    {
        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'bitcoin' => ['usd' => 50000],
                'ethereum' => ['usd' => 2500],
            ], 200),
        ]);

        $service = app(MarketPriceService::class);

        // 1 BTC = 20 ETH
        $this->assertSame(20.0, $service->getRate('btc', 'eth'));
        // 1 ETH = 0.05 BTC
        $this->assertSame(0.05, $service->getRate('eth', 'btc'));
    }
}
