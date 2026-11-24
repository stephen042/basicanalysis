<?php

namespace Tests\Unit;

use App\Http\Controllers\User\ManualTradingController;
use App\Models\ManualTrade;
use App\Models\TradingAsset;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class ManualTradingMarketsTest extends TestCase
{
    private $marketAssets = [
        'BTC' => 100000,
        'ETH' => 4000,
        'SOL' => 175.25,
        'XRP' => 2.25,
        'BNB' => 900,
        'ADA' => 0.75,
        'DOGE' => 0.2,
        'AVAX' => 35,
        'LINK' => 18,
        'LTC' => 110,
        'DOT' => 7,
        'TRX' => 0.3,
        'BCH' => 500,
        'TON' => 6,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->decimal('account_bal', 15, 2)->default(0);
            $table->decimal('roi', 15, 2)->default(0);
            $table->boolean('trading_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('trading_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol');
            $table->string('type');
            $table->decimal('current_price', 15, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('manual_trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('category');
            $table->string('instrument');
            $table->string('direction');
            $table->decimal('amount', 15, 2);
            $table->unsignedSmallInteger('leverage');
            $table->unsignedInteger('duration_minutes');
            $table->decimal('entry_price', 20, 8)->nullable();
            $table->decimal('result_amount', 15, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tp__transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user');
            $table->string('plan');
            $table->decimal('amount', 15, 2);
            $table->string('type');
            $table->timestamps();
        });

        foreach ($this->marketAssets as $symbol => $price) {
            TradingAsset::create([
                'name' => $symbol,
                'symbol' => $symbol,
                'type' => 'crypto',
                'current_price' => $price,
                'is_active' => true,
                'status' => 'active',
            ]);
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tp__transactions');
        Schema::dropIfExists('manual_trades');
        Schema::dropIfExists('trading_assets');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_crypto_pair_list_has_at_least_twenty_pairs_backed_by_market_assets(): void
    {
        $method = new ReflectionMethod(ManualTradingController::class, 'availableInstruments');
        $method->setAccessible(true);
        $instruments = $method->invoke(new ManualTradingController());
        $pairs = $instruments['crypto'];

        $this->assertGreaterThanOrEqual(20, count($pairs));
        $this->assertSame(count($pairs), count(array_unique($pairs)));

        foreach ($pairs as $pair) {
            [$base, $quote] = explode('/', $pair);
            $this->assertArrayHasKey($base, $this->marketAssets);
            $this->assertContains($quote, ['USD', 'USDT']);
        }
    }

    public function test_selected_crypto_pair_is_saved_and_uses_its_base_market_reference(): void
    {
        $this->withoutMiddleware();
        $user = User::create(['name' => 'Trader', 'email' => 'trader@example.test']);
        $user->account_bal = 500;
        $user->save();

        $this->actingAs($user)
            ->from('/dashboard/manual-trading')
            ->post(route('manual-trading.store'), [
                'category' => 'crypto',
                'instrument' => 'SOL/USDT',
                'direction' => 'BUY',
                'amount' => 100,
                'leverage' => 2,
                'duration_minutes' => 15,
                'submission_key' => 'selected-sol-usdt-pair',
            ])
            ->assertRedirect(route('manual-trading.index'));

        $trade = ManualTrade::firstOrFail();
        $this->assertSame('SOL/USDT', $trade->instrument);
        $this->assertSame('175.25000000', $trade->entry_price);
        $this->assertSame('400.00', $user->fresh()->account_bal);
        $this->assertSame(1, DB::table('tp__transactions')->count());
    }
}
