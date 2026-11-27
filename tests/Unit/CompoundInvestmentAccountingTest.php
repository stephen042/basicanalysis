<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\CompoundInvestmentController as AdminCompoundController;
use App\Http\Controllers\Admin\ManageDepositController;
use App\Models\Admin;
use App\Models\CompoundInvestment;
use App\Models\CompoundInvestmentContribution;
use App\Models\CompoundInvestmentReminder;
use App\Models\Deposit;
use App\Models\Notification;
use App\Models\User;
use App\Services\CompoundInvestmentReminderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompoundInvestmentAccountingTest extends TestCase
{
    protected $admin;
    protected $user;
    protected $investment;
    protected $cryptoMethodId;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('currency')->default('$');
            $table->decimal('account_bal', 15, 2)->default(0);
            $table->decimal('roi', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('firstName')->nullable();
            $table->timestamps();
        });
        Schema::create('wdmethods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('methodtype')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('network')->nullable();
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('enable_kyc_registration')->default('no');
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
        Schema::create('settings_conts', function (Blueprint $table) {
            $table->id();
            $table->decimal('minamt', 15, 2)->default(1);
            $table->timestamps();
        });
        DB::table('settings')->insert([
            'id' => 1,
            'enable_kyc_registration' => 'no',
            'contact_email' => 'admin@example.test',
        ]);
        DB::table('settings_conts')->insert(['id' => 1, 'minamt' => 10]);
        Schema::create('compound_investments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_source')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('submission_key', 64)->nullable();
            $table->string('payment_proof')->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('duration_months');
            $table->decimal('monthly_return', 8, 3);
            $table->decimal('performance_rate', 8, 3);
            $table->decimal('recurring_amount', 15, 2)->default(0);
            $table->string('recurring_frequency')->default('weekly');
            $table->string('deposit_status')->default('pending');
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('expected_completion_at')->nullable();
            $table->timestamp('next_recurring_due_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
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
            $table->string('payment_method', 64)->nullable();
            $table->boolean('balance_debited')->default(false);
            $table->timestamp('scheduled_for');
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();
        });
        Schema::create('compound_investment_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compound_investment_id');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['compound_investment_id', 'scheduled_for']);
        });
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user')->nullable();
            $table->string('amount')->nullable();
            $table->string('payment_mode')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_network')->nullable();
            $table->string('status')->nullable();
            $table->string('proof')->nullable();
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->string('icon')->nullable();
            $table->string('priority')->nullable();
            $table->timestamps();
        });

        $this->user = User::create([
            'name' => 'Investor',
            'email' => 'investor@example.test',
            'email_verified_at' => now(),
            'roi' => 0,
        ]);
        $this->user->account_bal = 75;
        $this->user->save();
        $adminId = DB::table('admins')->insertGetId([
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'firstName' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->admin = Admin::findOrFail($adminId);
        $this->cryptoMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'USDT',
            'methodtype' => 'crypto',
            'type' => 'both',
            'status' => 'enabled',
            'network' => 'TRC20',
        ]);
        $this->investment = CompoundInvestment::create([
            'user_id' => $this->user->id,
            'payment_method_id' => $this->cryptoMethodId,
            'payment_method' => 'USDT',
            'payment_source' => 'direct_deposit',
            'amount' => 1000,
            'duration_months' => 3,
            'monthly_return' => 27.8,
            'performance_rate' => 10,
            'recurring_amount' => 100,
            'recurring_frequency' => 'weekly',
            'deposit_status' => 'approved',
            'status' => 'active',
            'approved_at' => now()->subWeeks(2),
            'start_date' => now()->subWeeks(2),
            'expected_completion_at' => now()->addMonths(3),
            'next_recurring_due_at' => now()->subDay(),
        ]);
    }

    protected function tearDown(): void
    {
        foreach ([
            'notifications', 'tp__transactions', 'deposits',
            'compound_investment_reminders', 'compound_investment_contributions',
            'compound_investment_topups', 'compound_investments',
            'settings_conts', 'settings', 'wdmethods', 'admins', 'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_user_contribution_approval_is_not_a_wallet_deposit_and_profit_is_total_earning_only(): void
    {
        Mail::fake();
        $depositId = DB::table('deposits')->insertGetId([
            'user' => $this->user->id,
            'amount' => '100.00',
            'payment_mode' => 'USDT',
            'payment_method_id' => $this->cryptoMethodId,
            'payment_network' => 'TRC20',
            'status' => 'Pending',
            'proof' => 'uploads/recurring-proof.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $deposit = Deposit::findOrFail($depositId);
        $contribution = CompoundInvestmentContribution::create([
            'compound_investment_id' => $this->investment->id,
            'deposit_id' => $deposit->id,
            'idempotency_key' => 'recurring-contribution-1',
            'amount' => 100,
            'scheduled_for' => now()->subDay(),
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'admin');
        app(ManageDepositController::class)->pdeposit($deposit->id);

        $this->assertSame('Processed', $deposit->fresh()->status);
        $this->assertSame('approved', $contribution->fresh()->status);
        $this->assertSame('75.00', $this->user->fresh()->account_bal);
        $this->assertSame('0.00', $this->user->fresh()->roi);
        $this->assertTrue($this->investment->fresh()->next_recurring_due_at->isFuture());
        // The schedule advances from the contribution's scheduled period, so a
        // contribution submitted yesterday for a weekly plan is due next in
        // six days, not seven days after the approval time.
        $expectedNextDue = $contribution->fresh()->scheduled_for->copy()->addWeek();
        $this->assertSame($expectedNextDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);
        $this->assertEquals(1100, $this->investment->fresh()->total_user_contributions);
        $this->assertEquals(0, $this->investment->fresh()->total_earnings);

        $request = Request::create('/admin/compound-investments/1/topup', 'POST', [
            'amount' => 50,
            'note' => 'Admin profit',
            'idempotency_key' => 'profit-credit-1',
        ]);
        app(AdminCompoundController::class)->topup($request, $this->investment->fresh());

        $this->assertSame('75.00', $this->user->fresh()->account_bal);
        $this->assertSame('50.00', $this->user->fresh()->roi);
        $this->assertEquals(1100, $this->investment->fresh()->total_user_contributions);
        $this->assertEquals(50, $this->investment->fresh()->total_earnings);
        $this->assertSame(1, DB::table('compound_investment_topups')->count());
        $this->assertSame(0, DB::table('deposits')->where('status', 'Processed')->where('id', '<>', $deposit->id)->count());
        $this->assertDatabaseHas('tp__transactions', [
            'user' => $this->user->id,
            'plan' => 'Compound investment profit #' . $this->investment->id,
            'type' => 'ROI',
            'amount' => 50,
        ]);

        $duplicate = app(AdminCompoundController::class)->topup($request, $this->investment->fresh());
        $this->assertSame('50.00', $this->user->fresh()->roi);
        $this->assertSame(1, DB::table('compound_investment_topups')->count());
    }

    public function test_due_recurring_reminders_are_once_per_period_and_stop_after_completion(): void
    {
        Mail::fake();
        $service = app(CompoundInvestmentReminderService::class);

        $this->assertSame(1, $service->sendDueReminders());
        $this->assertSame(0, $service->sendDueReminders());
        $this->assertSame(1, CompoundInvestmentReminder::count());
        $this->assertSame(1, Notification::where('type', Notification::TYPE_GENERAL)->count());

        $this->investment->status = 'completed';
        $this->investment->completed_at = now();
        $this->investment->save();
        $this->investment->next_recurring_due_at = now()->subDay();
        $this->investment->save();

        $this->assertSame(0, $service->sendDueReminders());
        $this->assertSame(1, CompoundInvestmentReminder::count());
    }

    public function test_user_history_detail_and_recurring_payment_choices_render_for_approved_investment(): void
    {
        $this->investment->next_recurring_due_at = now()->addDays(3);
        $this->investment->save();

        $this->actingAs($this->user, 'sanctum')
            ->get(route('compound-investment.history'))
            ->assertOk()
            ->assertSee(route('compound-investment.show', $this->investment))
            ->assertSee('Compound Investment History')
            ->assertSee('Recurring Top-ups')
            ->assertSee('Top Up Now')
            ->assertSee('$100.00')
            ->assertSee($this->investment->next_recurring_due->format('j F Y'));

        $this->actingAs($this->user, 'sanctum')
            ->get(route('compound-investment.show', $this->investment))
            ->assertOk()
            ->assertSee('Top Up Now')
            ->assertSee('Next due date')
            ->assertSee('$100.00')
            ->assertSee('Total investment earnings');

        $this->actingAs($this->user, 'sanctum')
            ->get(route('compound-investment.recurring-topup', $this->investment))
            ->assertOk()
            ->assertSee('Subscription Balance')
            ->assertSee('Continue to Deposit');
    }

    public function test_user_recurring_contribution_stays_pending_until_deposit_approval(): void
    {
        Mail::fake();
        Storage::fake('public');
        $scheduledDue = now()->addDays(3);
        $this->investment->next_recurring_due_at = $scheduledDue;
        $this->investment->save();

        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_method_id' => $this->cryptoMethodId,
            ])
            ->assertRedirect(route('payment'))
            ->assertSessionHas('compound_contribution_id');

        $contributionId = session('compound_contribution_id');
        $this->assertSame(1, CompoundInvestmentContribution::count());
        $this->assertSame('pending', CompoundInvestmentContribution::findOrFail($contributionId)->status);
        $this->assertSame(100.0, (float) session('amount'));

        $this->actingAs($this->user, 'sanctum')
            ->post(route('savedeposit'), [
                'amount' => 100,
                'payment_method_id' => $this->cryptoMethodId,
                'proof' => UploadedFile::fake()->image('recurring-proof.png'),
            ])
            ->assertRedirect(route('deposits'));

        $contribution = CompoundInvestmentContribution::findOrFail($contributionId);
        $deposit = Deposit::findOrFail($contribution->deposit_id);
        $this->assertSame('Pending', $deposit->status);
        $this->assertSame('pending', $contribution->status);
        $this->assertSame($scheduledDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);
        $this->assertSame('75.00', $this->user->fresh()->account_bal);
        $this->assertSame('0.00', $this->user->fresh()->roi);

        $this->actingAs($this->admin, 'admin');
        app(ManageDepositController::class)->pdeposit($deposit->id);
        $this->assertSame('Processed', $deposit->fresh()->status);
        $this->assertSame('approved', $contribution->fresh()->status);
        // Paying early still advances the schedule from the scheduled period.
        $expectedNextDue = $scheduledDue->copy()->addWeek();
        $this->assertSame($expectedNextDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);
        $this->assertSame('75.00', $this->user->fresh()->account_bal);
        $this->assertSame('0.00', $this->user->fresh()->roi);
    }

    public function test_subscription_balance_contribution_is_deliberate_pending_and_admin_approval_is_not_profit(): void
    {
        Mail::fake();
        $scheduledDue = now()->addDays(4);
        $this->investment->recurring_frequency = 'daily';
        $this->investment->next_recurring_due_at = $scheduledDue;
        $this->investment->save();
        $this->user->account_bal = 175;
        $this->user->save();

        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_source' => 'subscription_balance',
            ])
            ->assertRedirect(route('compound-investment.show', $this->investment->id))
            ->assertSessionHas('success');

        $contribution = CompoundInvestmentContribution::firstOrFail();
        $this->assertSame('pending', $contribution->status);
        $this->assertSame('Subscription Balance', $contribution->payment_method);
        $this->assertTrue($contribution->balance_debited);
        $this->assertNull($contribution->deposit_id);
        $this->assertSame($scheduledDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);
        $this->assertSame('75.00', $this->user->fresh()->account_bal);
        $this->assertSame(0.0, (float) $this->investment->fresh()->total_recurring_contributions);
        $this->assertSame('0.00', $this->user->fresh()->roi);
        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_source' => 'subscription_balance',
            ])
            ->assertRedirect(route('compound-investment.show', $this->investment->id));
        $this->assertSame(1, CompoundInvestmentContribution::count());
        $this->assertSame('75.00', $this->user->fresh()->account_bal);

        $this->actingAs($this->admin, 'admin');
        app(AdminCompoundController::class)->approveContribution(
            Request::create('/admin/compound-investments/contributions/' . $contribution->id . '/approve', 'POST'),
            $contribution
        );
        // Daily plan paid early: next due date is the scheduled period plus one day.
        $expectedNextDue = $scheduledDue->copy()->addDay();
        $this->assertSame($expectedNextDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);
        app(AdminCompoundController::class)->approveContribution(
            Request::create('/admin/compound-investments/contributions/' . $contribution->id . '/approve', 'POST'),
            $contribution->fresh()
        );

        $this->assertSame('approved', $contribution->fresh()->status);
        $this->assertSame(100.0, (float) $this->investment->fresh()->total_recurring_contributions);
        $this->assertSame(1100.0, (float) $this->investment->fresh()->total_user_contributions);
        $this->assertSame('0.00', $this->user->fresh()->roi);
        $this->assertSame(0, DB::table('compound_investment_topups')->count());
    }

    public function test_rejected_subscription_balance_contribution_is_refunded_once(): void
    {
        Mail::fake();
        $this->user->account_bal = 175;
        $this->user->save();
        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_source' => 'subscription_balance',
            ])
            ->assertRedirect(route('compound-investment.show', $this->investment->id));

        $contribution = CompoundInvestmentContribution::firstOrFail();
        $this->actingAs($this->admin, 'admin');
        $reject = function () use ($contribution) {
            return app(AdminCompoundController::class)->rejectContribution(
                Request::create('/admin/compound-investments/contributions/' . $contribution->id . '/reject', 'POST', [
                    'admin_note' => 'Review failed',
                ]),
                $contribution
            );
        };

        $reject();
        $this->assertSame('rejected', $contribution->fresh()->status);
        $this->assertSame('175.00', $this->user->fresh()->account_bal);
        $this->assertSame(1, DB::table('tp__transactions')
            ->where('plan', 'Compound investment recurring contribution #' . $this->investment->id . ' rejected refund')
            ->count());

        $reject();
        $this->assertSame('175.00', $this->user->fresh()->account_bal);
        $this->assertSame(1, DB::table('tp__transactions')
            ->where('plan', 'Compound investment recurring contribution #' . $this->investment->id . ' rejected refund')
            ->count());
    }

    public function test_admin_can_reject_a_pending_user_contribution_without_crediting_balances(): void
    {
        Mail::fake();
        $depositId = DB::table('deposits')->insertGetId([
            'user' => $this->user->id,
            'amount' => '100.00',
            'payment_mode' => 'USDT',
            'payment_method_id' => $this->cryptoMethodId,
            'payment_network' => 'TRC20',
            'status' => 'Pending',
            'proof' => 'uploads/rejected-proof.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contribution = CompoundInvestmentContribution::create([
            'compound_investment_id' => $this->investment->id,
            'deposit_id' => $depositId,
            'idempotency_key' => 'recurring-contribution-rejected',
            'amount' => 100,
            'scheduled_for' => now()->subDay(),
            'status' => 'pending',
        ]);
        $this->actingAs($this->admin, 'admin');

        app(AdminCompoundController::class)->rejectContribution(
            Request::create('/admin/compound-investments/contributions/' . $contribution->id . '/reject', 'POST', [
                'admin_note' => 'Proof is not readable',
            ]),
            $contribution
        );

        $this->assertSame('Rejected', Deposit::findOrFail($depositId)->status);
        $this->assertSame('rejected', $contribution->fresh()->status);
        $this->assertSame('Proof is not readable', $contribution->fresh()->admin_note);
        $this->assertSame('75.00', $this->user->fresh()->account_bal);
        $this->assertSame('0.00', $this->user->fresh()->roi);
    }

    public function test_rejecting_balance_funded_initial_application_refunds_once(): void
    {
        $this->user->account_bal = 0;
        $this->user->save();
        $pending = CompoundInvestment::create([
            'user_id' => $this->user->id,
            'payment_method' => 'Subscription Balance',
            'payment_source' => 'subscription_balance',
            'submission_key' => 'pending-balance-application',
            'amount' => 100,
            'duration_months' => 1,
            'monthly_return' => 27.8,
            'performance_rate' => 10,
            'recurring_amount' => 0,
            'recurring_frequency' => 'daily',
            'deposit_status' => 'pending',
            'status' => 'pending',
        ]);
        $this->actingAs($this->admin, 'admin');
        $reject = function () use ($pending) {
            return app(AdminCompoundController::class)->decide(
                Request::create('/admin/compound-investments/' . $pending->id . '/decision', 'POST', [
                    'decision' => 'reject',
                    'rejection_reason' => 'Not approved',
                ]),
                $pending
            );
        };

        $reject();
        $this->assertSame('rejected', $pending->fresh()->status);
        $this->assertSame('100.00', $this->user->fresh()->account_bal);
        $this->assertSame(1, DB::table('tp__transactions')
            ->where('plan', 'Compound investment #' . $pending->id . ' rejected refund')
            ->count());

        $reject();
        $this->assertSame('100.00', $this->user->fresh()->account_bal);
        $this->assertSame(1, DB::table('tp__transactions')
            ->where('plan', 'Compound investment #' . $pending->id . ' rejected refund')
            ->count());
    }

    public function test_early_recurring_topup_is_allowed_and_button_stays_available_after_approval(): void
    {
        Mail::fake();
        $this->user->account_bal = 500;
        $this->user->save();

        $scheduledDue = now()->addDays(5);
        $this->investment->next_recurring_due_at = $scheduledDue;
        $this->investment->save();

        // The due date is in the future, but the user may already top up.
        $this->actingAs($this->user, 'sanctum')
            ->get(route('compound-investment.show', $this->investment))
            ->assertOk()
            ->assertSee('Top Up Now')
            ->assertDontSee('cursor-not-allowed')
            ->assertSee($scheduledDue->format('j F Y'));

        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_source' => 'subscription_balance',
            ])
            ->assertRedirect(route('compound-investment.show', $this->investment->id))
            ->assertSessionHas('success');

        $contribution = CompoundInvestmentContribution::firstOrFail();
        $this->assertSame('pending', $contribution->status);
        $this->assertSame($scheduledDue->timestamp, $contribution->scheduled_for->timestamp);
        $this->assertSame($scheduledDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);

        // Pending state is visible and duplicate submission is blocked.
        $this->actingAs($this->user, 'sanctum')
            ->get(route('compound-investment.show', $this->investment))
            ->assertOk()
            ->assertSee('Pending admin approval');
        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_source' => 'subscription_balance',
            ]);
        $this->assertSame(1, CompoundInvestmentContribution::count());

        $this->actingAs($this->admin, 'admin');
        app(AdminCompoundController::class)->approveContribution(
            Request::create('/admin/compound-investments/contributions/' . $contribution->id . '/approve', 'POST'),
            $contribution
        );

        $expectedNextDue = $scheduledDue->copy()->addWeek();
        $this->assertSame('approved', $contribution->fresh()->status);
        $this->assertSame($expectedNextDue->timestamp, $this->investment->fresh()->next_recurring_due_at->timestamp);
        $this->assertSame(1100.0, (float) $this->investment->fresh()->total_user_contributions);
        $this->assertSame(0.0, (float) $this->investment->fresh()->total_earnings);

        // The button remains available for the next period immediately.
        $this->actingAs($this->user, 'sanctum')
            ->get(route('compound-investment.show', $this->investment))
            ->assertOk()
            ->assertSee('Top Up Now')
            ->assertDontSee('cursor-not-allowed')
            ->assertSee($expectedNextDue->format('j F Y'));

        $this->actingAs($this->user, 'sanctum')
            ->post(route('compound-investment.recurring-topup.start', $this->investment->id), [
                'payment_source' => 'subscription_balance',
            ])
            ->assertRedirect(route('compound-investment.show', $this->investment->id))
            ->assertSessionHas('success');

        $nextContribution = CompoundInvestmentContribution::where('id', '!=', $contribution->id)->firstOrFail();
        $this->assertSame($expectedNextDue->timestamp, $nextContribution->scheduled_for->timestamp);
        $this->assertSame('pending', $nextContribution->status);
    }
}
