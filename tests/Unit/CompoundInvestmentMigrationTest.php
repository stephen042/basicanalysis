<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompoundInvestmentMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('compound_investments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('recurring_amount', 15, 2)->default(0);
            $table->string('recurring_frequency')->default('weekly');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('compound_investment_topups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compound_investment_id');
            $table->decimal('amount', 15, 2);
        });
        DB::table('compound_investments')->insert([
            'id' => 9,
            'amount' => 1000,
            'payment_method' => 'Subscription Balance',
            'status' => 'active',
            'recurring_amount' => 100,
            'recurring_frequency' => 'daily',
            'start_date' => now()->subDay(),
            'approved_at' => now()->subDay(),
            'created_at' => now()->subWeek(),
        ]);
        DB::table('compound_investment_topups')->insert([
            'compound_investment_id' => 9,
            'amount' => 50,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('compound_investment_reminders');
        Schema::dropIfExists('compound_investment_contributions');
        Schema::dropIfExists('compound_investment_topups');
        Schema::dropIfExists('compound_investments');
        parent::tearDown();
    }

    public function test_migration_extends_existing_records_without_rewriting_financial_history(): void
    {
        require_once database_path('migrations/2026_11_25_000001_separate_compound_contributions_and_earnings.php');
        (new \SeparateCompoundContributionsAndEarnings())->up();

        $this->assertTrue(Schema::hasColumn('compound_investments', 'currency'));
        $this->assertTrue(Schema::hasColumn('compound_investments', 'payment_source'));
        $this->assertTrue(Schema::hasColumn('compound_investments', 'next_recurring_due_at'));
        $this->assertTrue(Schema::hasColumn('compound_investment_topups', 'currency'));
        $this->assertTrue(Schema::hasTable('compound_investment_contributions'));
        $this->assertTrue(Schema::hasTable('compound_investment_reminders'));
        $this->assertSame(1000.0, (float) DB::table('compound_investments')->where('id', 9)->value('amount'));
        $this->assertSame(50.0, (float) DB::table('compound_investment_topups')->value('amount'));
        $this->assertSame('USD', DB::table('compound_investments')->where('id', 9)->value('currency'));
        $legacy = DB::table('compound_investments')->where('id', 9)->first();
        $this->assertSame('subscription_balance', $legacy->payment_source);
        $this->assertNotNull($legacy->next_recurring_due_at);

        require_once database_path('migrations/2026_11_26_000001_add_payment_source_to_compound_contributions.php');
        (new \AddPaymentSourceToCompoundContributions())->up();
        $this->assertTrue(Schema::hasColumn('compound_investment_contributions', 'payment_method'));
        $this->assertTrue(Schema::hasColumn('compound_investment_contributions', 'balance_debited'));
    }
}
