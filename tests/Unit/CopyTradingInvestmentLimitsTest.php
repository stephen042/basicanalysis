<?php

namespace Tests\Unit;

use App\Models\CopySubscription;
use App\Models\ExpertTrader;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CopyTradingInvestmentLimitsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('currency')->default('$');
            $table->decimal('account_bal', 15, 2)->default(0);
            $table->decimal('roi', 15, 2)->default(0);
            $table->boolean('trading_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('expert_traders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('total_followers')->default(0);
            $table->decimal('min_copy_amount', 10, 2)->default(100);
            $table->decimal('max_copy_amount', 10, 2)->default(10000);
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
            $table->timestamp('completed_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->decimal('max_risk_per_trade', 5, 2)->default(10);
            $table->decimal('stop_loss_percentage', 5, 2)->default(20);
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

    public function test_subscription_uses_the_selected_experts_saved_limits(): void
    {
        $this->withoutMiddleware();
        $user = User::create(['name' => 'Follower', 'email' => 'follower@example.test']);
        $user->account_bal = 10000;
        $user->save();
        $expert = ExpertTrader::create([
            'name' => 'Configured Expert',
            'min_copy_amount' => 100,
            'max_copy_amount' => 5000,
        ]);

        foreach ([99.99, 5000.01] as $amount) {
            $this->actingAs($user)
                ->from('/dashboard/copy-trading')
                ->post(route('copy-trading.subscribe'), [
                    'expert_trader_id' => $expert->id,
                    'amount' => $amount,
                    'copy_percentage' => 100,
                    'duration_days' => 30,
                ])
                ->assertRedirect('/dashboard/copy-trading');

            $this->assertSame(0, CopySubscription::count());
        }

        $this->actingAs($user)
            ->from('/dashboard/copy-trading')
            ->post(route('copy-trading.subscribe'), [
                'expert_trader_id' => $expert->id,
                'amount' => 100,
                'copy_percentage' => 100,
                'duration_days' => 30,
            ])
            ->assertRedirect('/dashboard/copy-trading');

        $subscription = CopySubscription::firstOrFail();
        $this->assertSame('100.00', $subscription->amount);
        $this->assertSame((int) $user->id, (int) $subscription->user_id);
        $this->assertSame((int) $expert->id, (int) $subscription->expert_trader_id);
    }
}
