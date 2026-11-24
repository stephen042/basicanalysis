<?php

namespace Tests\Unit;

use App\Models\CopySubscription;
use App\Models\ExpertTrader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CopyTradingRelationshipsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('expert_traders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedInteger('total_followers')->default(0);
            $table->timestamps();
        });

        Schema::create('copy_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('expert_trader_id');
            $table->decimal('amount', 15, 2);
            $table->decimal('copy_percentage', 5, 2)->default(100);
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('copy_subscriptions');
        Schema::dropIfExists('expert_traders');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_copy_trading_relationships_use_the_exported_foreign_keys(): void
    {
        $user = User::create(['name' => 'Follower', 'email' => 'follower@example.test']);
        $expert = ExpertTrader::create(['name' => 'Expert']);

        $subscription = new CopySubscription();

        $this->assertSame('user_id', $user->copySubscriptions()->getForeignKeyName());
        $this->assertSame('expert_trader_id', $expert->copySubscriptions()->getForeignKeyName());
        $this->assertSame('user_id', $subscription->user()->getForeignKeyName());
        $this->assertSame('expert_trader_id', $subscription->expertTrader()->getForeignKeyName());
        $this->assertSame('copy_subscription_id', $subscription->copyTrades()->getForeignKeyName());

        $this->assertSame('id', $subscription->user()->getOwnerKeyName());
        $this->assertSame('id', $subscription->expertTrader()->getOwnerKeyName());

        $createdSubscription = $user->copySubscriptions()->create([
            'expert_trader_id' => $expert->id,
            'amount' => 500,
            'copy_percentage' => 100,
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $this->assertSame($user->id, $createdSubscription->user->id);
        $this->assertSame($expert->id, $createdSubscription->expertTrader->id);
        $this->assertSame($createdSubscription->id, $expert->copySubscriptions()->first()->id);
    }

    public function test_active_subscribers_exclude_paused_and_expired_records(): void
    {
        Carbon::setTestNow('2026-11-24 12:00:00');

        try {
            $user = User::create(['name' => 'Follower']);
            $expert = ExpertTrader::create(['name' => 'Expert', 'total_followers' => 25]);
            $active = $user->copySubscriptions()->create([
                'expert_trader_id' => $expert->id,
                'amount' => 500,
                'status' => 'active',
                'expires_at' => now()->addDay(),
            ]);
            $user->copySubscriptions()->create([
                'expert_trader_id' => $expert->id,
                'amount' => 500,
                'status' => 'paused',
                'expires_at' => now()->addDay(),
            ]);
            $user->copySubscriptions()->create([
                'expert_trader_id' => $expert->id,
                'amount' => 500,
                'status' => 'active',
                'expires_at' => now()->subDay(),
            ]);

            $this->assertSame([$active->id], $expert->copySubscriptions()->active()->pluck('id')->all());
            $this->assertSame(1, $expert->getActiveSubscribersCount());
            $this->assertSame(3, $user->copySubscriptions()->count());
        } finally {
            Carbon::setTestNow();
        }
    }
}
