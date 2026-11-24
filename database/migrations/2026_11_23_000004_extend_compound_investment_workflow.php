<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendCompoundInvestmentWorkflow extends Migration
{
    public function up()
    {
        if (\App\Support\SchemaInspector::hasTable('compound_investments')) {
            Schema::table('compound_investments', function (Blueprint $table) {
                if (!\App\Support\SchemaInspector::hasColumn('compound_investments', 'start_date')) {
                    $table->timestamp('start_date')->nullable();
                }
                if (!\App\Support\SchemaInspector::hasColumn('compound_investments', 'expected_completion_at')) {
                    $table->timestamp('expected_completion_at')->nullable();
                }
                if (!\App\Support\SchemaInspector::hasColumn('compound_investments', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable();
                }
                if (!\App\Support\SchemaInspector::hasColumn('compound_investments', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable();
                }
                if (!\App\Support\SchemaInspector::hasColumn('compound_investments', 'completed_by')) {
                    $table->unsignedBigInteger('completed_by')->nullable();
                }
            });
        }

        if (\App\Support\SchemaInspector::hasTable('compound_investment_topups')) {
            if (!\App\Support\SchemaInspector::hasColumn('compound_investment_topups', 'admin_id')) {
                Schema::table('compound_investment_topups', function (Blueprint $table) {
                    $table->unsignedBigInteger('admin_id')->nullable();
                });
            }
            if (!\App\Support\SchemaInspector::hasColumn('compound_investment_topups', 'idempotency_key')) {
                Schema::table('compound_investment_topups', function (Blueprint $table) {
                    $table->string('idempotency_key', 64)->nullable()->unique();
                });
            }
        }
    }

    public function down()
    {
        // Preserve approved investment and top-up history on rollback.
    }
}
