<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SeparateCompoundContributionsAndEarnings extends Migration
{
    public function up()
    {
        if (\App\Support\SchemaInspector::hasTable('compound_investments')) {
            foreach ([
                'currency' => function (Blueprint $table) {
                    $table->string('currency', 3)->default('USD');
                },
                'payment_source' => function (Blueprint $table) {
                    $table->string('payment_source', 32)->nullable();
                },
                'next_recurring_due_at' => function (Blueprint $table) {
                    $table->timestamp('next_recurring_due_at')->nullable();
                },
            ] as $column => $definition) {
                if (!\App\Support\SchemaInspector::hasColumn('compound_investments', $column)) {
                    Schema::table('compound_investments', $definition);
                }
            }
        }

        if (\App\Support\SchemaInspector::hasTable('compound_investment_topups')
            && !\App\Support\SchemaInspector::hasColumn('compound_investment_topups', 'currency')) {
            Schema::table('compound_investment_topups', function (Blueprint $table) {
                $table->string('currency', 3)->default('USD');
            });
        }

        if (!\App\Support\SchemaInspector::hasTable('compound_investment_contributions')) {
            Schema::create('compound_investment_contributions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('compound_investment_id');
                $table->unsignedBigInteger('deposit_id')->nullable();
                $table->string('idempotency_key', 64);
                $table->decimal('amount', 15, 2);
                $table->string('currency', 3)->default('USD');
                $table->timestamp('scheduled_for');
                $table->string('status', 24)->default('pending');
                $table->text('admin_note')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamps();
                $table->foreign('compound_investment_id', 'cci_investment_fk')
                    ->references('id')->on('compound_investments')->onDelete('cascade');
                $table->unique('deposit_id', 'cci_deposit_uq');
                $table->unique('idempotency_key', 'cci_idempotency_uq');
                $table->index(['compound_investment_id', 'status'], 'cci_investment_status_idx');
                $table->index(['compound_investment_id', 'scheduled_for'], 'cci_schedule_idx');
            });
        }

        if (!\App\Support\SchemaInspector::hasTable('compound_investment_reminders')) {
            Schema::create('compound_investment_reminders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('compound_investment_id');
                $table->timestamp('scheduled_for');
                $table->timestamp('sent_at')->useCurrent();
                $table->timestamps();
                $table->foreign('compound_investment_id', 'cir_investment_fk')
                    ->references('id')->on('compound_investments')->onDelete('cascade');
                $table->unique(['compound_investment_id', 'scheduled_for'], 'cir_period_uq');
            });
        }

        // Preserve active legacy schedules that predate next_recurring_due_at.
        if (\App\Support\SchemaInspector::hasTable('compound_investments')
            && \App\Support\SchemaInspector::hasColumn('compound_investments', 'payment_source')
            && \App\Support\SchemaInspector::hasColumn('compound_investments', 'payment_method')) {
            DB::table('compound_investments')
                ->whereNull('payment_source')
                ->where('payment_method', 'Subscription Balance')
                ->update(['payment_source' => 'subscription_balance']);
        }

        $backfillColumns = [
            'status', 'recurring_amount', 'recurring_frequency',
            'next_recurring_due_at', 'start_date', 'approved_at', 'created_at',
        ];
        $canBackfill = \App\Support\SchemaInspector::hasTable('compound_investments');
        foreach ($backfillColumns as $column) {
            $canBackfill = $canBackfill && \App\Support\SchemaInspector::hasColumn('compound_investments', $column);
        }
        if ($canBackfill) {
            DB::table('compound_investments')
                ->where('status', 'active')
                ->where('recurring_amount', '>', 0)
                ->whereNull('next_recurring_due_at')
                ->orderBy('id')
                ->chunk(100, function ($investments) {
                    foreach ($investments as $investment) {
                        $anchor = $investment->start_date ?: ($investment->approved_at ?: $investment->created_at);
                        if (!$anchor) {
                            continue;
                        }
                        $date = \Carbon\Carbon::parse($anchor);
                        if ($investment->recurring_frequency === 'daily') {
                            $date->addDay();
                        } elseif ($investment->recurring_frequency === 'monthly') {
                            $date->addMonth();
                        } else {
                            $date->addWeek();
                        }
                        DB::table('compound_investments')
                            ->where('id', $investment->id)
                            ->whereNull('next_recurring_due_at')
                            ->update(['next_recurring_due_at' => $date]);
                    }
                });
        }
    }

    public function down()
    {
        // Keep financial and reminder history when rolling application code back.
    }
}
