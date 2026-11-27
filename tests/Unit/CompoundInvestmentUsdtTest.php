<?php

namespace Tests\Unit;

use App\Models\CompoundInvestment;
use App\Models\User;
use App\Http\Controllers\Admin\CompoundInvestmentController;
use App\Http\Controllers\User\CompoundInvestmentController as UserCompoundInvestmentController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompoundInvestmentUsdtTest extends TestCase
{
    protected $usdtMethodId;
    protected $bitcoinMethodId;

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
            $table->decimal('trading_balance', 15, 2)->default(0);
            $table->decimal('demo_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wdmethods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('methodtype')->nullable();
            $table->string('network')->nullable();
            $table->text('wallet_address')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('compound_investments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_source')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('submission_key', 64)->nullable()->unique();
            $table->string('payment_proof')->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('duration_months');
            $table->decimal('monthly_return', 8, 3);
            $table->decimal('performance_rate', 8, 3);
            $table->decimal('recurring_amount', 15, 2)->default(0);
            $table->string('recurring_frequency');
            $table->string('deposit_status')->default('pending');
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('expected_completion_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('next_recurring_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('compound_investment_topups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compound_investment_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 15, 2);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('compound_investment_contributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compound_investment_id');
            $table->unsignedBigInteger('deposit_id')->nullable()->unique();
            $table->string('idempotency_key', 64)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('scheduled_for');
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
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

        $this->usdtMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'USDT',
            'methodtype' => 'crypto',
            'network' => 'TRC20',
            'wallet_address' => 'T-test-usdt-wallet',
            'type' => 'both',
            'status' => 'enabled',
        ]);
        $this->bitcoinMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'Bitcoin',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1-test-wallet',
            'type' => 'deposit',
            'status' => 'enabled',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('compound_investment_contributions');
        Schema::dropIfExists('compound_investment_topups');
        Schema::dropIfExists('compound_investments');
        Schema::dropIfExists('tp__transactions');
        Schema::dropIfExists('wdmethods');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_new_compound_investment_rejects_non_usdt_methods_and_saves_usdt(): void
    {
        $this->withoutMiddleware();
        $user = User::create(['name' => 'Investor', 'email' => 'investor@example.test']);

        $payload = [
            'amount' => 250,
            'duration_months' => 3,
            'recurring_amount' => 25,
            'recurring_frequency' => 'weekly',
            'payment_source' => 'direct_deposit',
            'submission_key' => 'unique-submit-key',
        ];

        $this->actingAs($user)
            ->from('/dashboard/compound-investment')
            ->post(route('compound-investment.store'), $payload + [
                'payment_method_id' => $this->bitcoinMethodId,
            ])
            ->assertNotFound();

        $this->assertSame(0, CompoundInvestment::count());

        $this->actingAs($user)
            ->from('/dashboard/compound-investment')
            ->post(route('compound-investment.store'), array_merge($payload, [
                'payment_method_id' => $this->usdtMethodId,
                'submission_key' => 'unique-usdt-submit-key',
            ]))
            ->assertRedirect();

        $investment = CompoundInvestment::firstOrFail();
        $this->assertSame('USDT', $investment->payment_method);
        $this->assertSame((int) $this->usdtMethodId, (int) $investment->payment_method_id);
        $this->assertSame('250.00', $investment->amount);
    }

    public function test_investment_form_uses_usd_while_usdt_remains_a_separate_payment_method(): void
    {
        $user = User::create(['name' => 'Investor', 'email' => 'investor@example.test']);
        $this->actingAs($user);

        $view = (new UserCompoundInvestmentController())->create();

        $this->assertSame('$', $view->getData()['currency']);
        $this->assertSame('USDT', $view->getData()['methods']->first()->name);
    }

    public function test_admin_cannot_approve_a_non_usdt_compound_receipt(): void
    {
        $this->withoutMiddleware();
        $user = User::create(['name' => 'Investor']);
        $investment = CompoundInvestment::create([
            'user_id' => $user->id,
            'payment_method_id' => $this->bitcoinMethodId,
            'payment_method' => 'Bitcoin',
            'payment_proof' => 'compound-investment-proofs/legacy-receipt.jpg',
            'amount' => 250,
            'duration_months' => 3,
            'monthly_return' => 27.8,
            'performance_rate' => 0,
            'recurring_amount' => 25,
            'recurring_frequency' => 'weekly',
            'deposit_status' => 'pending',
            'status' => 'pending',
        ]);

        $request = Request::create('/admin/admin/compound-investments/' . $investment->id, 'POST', [
            'decision' => 'approve',
        ]);

        $response = app(CompoundInvestmentController::class)->decide($request, $investment);
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame('pending', $investment->fresh()->status);
        $this->assertSame('pending', $investment->fresh()->deposit_status);
    }

    public function test_admin_can_approve_a_usdt_compound_receipt(): void
    {
        $user = User::create(['name' => 'Investor']);
        $investment = CompoundInvestment::create([
            'user_id' => $user->id,
            'payment_method_id' => $this->usdtMethodId,
            'payment_method' => 'USDT',
            'payment_proof' => 'compound-investment-proofs/usdt-receipt.jpg',
            'amount' => 250,
            'duration_months' => 3,
            'monthly_return' => 27.8,
            'performance_rate' => 0,
            'recurring_amount' => 25,
            'recurring_frequency' => 'weekly',
            'deposit_status' => 'pending',
            'status' => 'pending',
        ]);
        $request = Request::create('/admin/admin/compound-investments/' . $investment->id, 'POST', [
            'decision' => 'approve',
        ]);

        app(CompoundInvestmentController::class)->decide($request, $investment);

        $this->assertSame('active', $investment->fresh()->status);
        $this->assertSame('approved', $investment->fresh()->deposit_status);
        $this->assertNotNull($investment->fresh()->approved_at);
    }

    public function test_subscription_balance_payment_is_atomic_and_cannot_be_replayed(): void
    {
        $this->withoutMiddleware();
        $user = User::create(['name' => 'Investor', 'email' => 'investor@example.test']);
        $user->account_bal = 600;
        $user->roi = 20;
        $user->trading_balance = 300;
        $user->demo_balance = 400;
        $user->save();
        $payload = [
            'amount' => 250,
            'duration_months' => 3,
            'recurring_amount' => 25,
            'recurring_frequency' => 'weekly',
            'payment_source' => 'subscription_balance',
            'submission_key' => 'subscription-balance-payment-key',
        ];

        $this->actingAs($user)
            ->from('/dashboard/compound-investment')
            ->post(route('compound-investment.store'), $payload)
            ->assertRedirect(route('compound-investment.show', 1));

        $investment = CompoundInvestment::firstOrFail();
        $this->assertSame('Subscription Balance', $investment->payment_method);
        $this->assertNull($investment->payment_method_id);
        $this->assertSame('pending', $investment->status);
        $this->assertSame('pending', $investment->deposit_status);
        $this->assertSame('350.00', $user->fresh()->account_bal);
        $this->assertSame('20.00', $user->fresh()->roi);
        $this->assertSame('300.00', $user->fresh()->trading_balance);
        $this->assertSame('400.00', $user->fresh()->demo_balance);
        $this->assertSame(-250.0, (float) DB::table('tp__transactions')->value('amount'));

        Cache::flush();
        $this->actingAs($user)
            ->from('/dashboard/compound-investment')
            ->post(route('compound-investment.store'), $payload)
            ->assertRedirect('/dashboard/compound-investment');

        $this->assertSame(1, CompoundInvestment::count());
        $this->assertSame('350.00', $user->fresh()->account_bal);
        $this->assertSame(1, DB::table('tp__transactions')->count());
    }

    public function test_insufficient_subscription_balance_creates_no_investment_or_deduction(): void
    {
        $this->withoutMiddleware();
        $user = User::create(['name' => 'Investor', 'email' => 'investor@example.test']);
        $user->account_bal = 249.99;
        $user->save();

        $this->actingAs($user)
            ->from('/dashboard/compound-investment')
            ->post(route('compound-investment.store'), [
                'amount' => 250,
                'duration_months' => 3,
                'recurring_amount' => 0,
                'recurring_frequency' => 'weekly',
                'payment_source' => 'subscription_balance',
                'submission_key' => 'insufficient-subscription-balance-key',
            ])
            ->assertRedirect('/dashboard/compound-investment')
            ->assertSessionHas('error', 'Insufficient Subscription Balance. Fund your account or choose Direct Deposit.');

        $this->assertSame(0, CompoundInvestment::count());
        $this->assertSame(0, DB::table('tp__transactions')->count());
        $this->assertSame('249.99', $user->fresh()->account_bal);
    }

    public function test_pending_submission_key_migration_fails_safely_without_any_debit(): void
    {
        Schema::dropIfExists('compound_investments');
        Schema::create('compound_investments', function (Blueprint $table) {
            $table->id();
        });

        $this->withoutMiddleware();
        $user = User::create(['name' => 'Investor', 'email' => 'investor@example.test']);
        $user->account_bal = 600;
        $user->save();

        $this->actingAs($user)
            ->from('/dashboard/compound-investment')
            ->post(route('compound-investment.store'), [
                'amount' => 250,
                'duration_months' => 3,
                'recurring_amount' => 0,
                'recurring_frequency' => 'weekly',
                'payment_source' => 'subscription_balance',
                'submission_key' => 'schema-missing-payment-key',
            ])
            ->assertRedirect('/dashboard/compound-investment')
            ->assertSessionHas('error');

        $this->assertSame(0, CompoundInvestment::count());
        $this->assertSame('600.00', $user->fresh()->account_bal);
        $this->assertSame(0, DB::table('tp__transactions')->count());
    }
}
