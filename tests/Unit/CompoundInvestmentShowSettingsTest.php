<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\CompoundInvestmentController;
use App\Models\CompoundInvestment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompoundInvestmentShowSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency')->nullable();
            $table->timestamps();
        });

        Schema::create('wdmethods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('network')->nullable();
            $table->text('wallet_address')->nullable();
            $table->text('barcode')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('compound_investments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('duration_months');
            $table->decimal('monthly_return', 8, 3);
            $table->decimal('performance_rate', 8, 3);
            $table->decimal('recurring_amount', 15, 2)->default(0);
            $table->string('recurring_frequency');
            $table->string('deposit_status')->default('pending');
            $table->string('status')->default('pending');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('expected_completion_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('compound_investment_topups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compound_investment_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('idempotency_key', 64)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert(['id' => 1, 'currency' => '$']);
        $userId = DB::table('users')->insertGetId(['name' => 'Investor', 'email' => 'i@example.test']);
        $methodId = DB::table('wdmethods')->insertGetId([
            'name' => 'USDT',
            'network' => 'TRC20',
            'wallet_address' => 'T-test-wallet',
            'type' => 'both',
            'status' => 'enabled',
        ]);
        DB::table('compound_investments')->insert([
            'user_id' => $userId,
            'payment_method_id' => $methodId,
            'payment_method' => 'USDT',
            'amount' => 1000,
            'duration_months' => 3,
            'monthly_return' => 27.8,
            'performance_rate' => 70,
            'recurring_amount' => 25,
            'recurring_frequency' => 'weekly',
            'deposit_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('compound_investment_topups');
        Schema::dropIfExists('compound_investments');
        Schema::dropIfExists('wdmethods');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_show_resolves_the_settings_model_and_returns_the_currency(): void
    {
        $investment = CompoundInvestment::firstOrFail();

        // Before the fix this threw: Class "App\Http\Controllers\Admin\Settings" not found
        $view = (new CompoundInvestmentController())->show($investment->id);

        $this->assertSame('$', $view->getData()['currency']);
        $this->assertSame('USDT', $view->getData()['investment']->payment_method);
        $this->assertSame('TRC20', $view->getData()['payment']['network']);
    }
}
