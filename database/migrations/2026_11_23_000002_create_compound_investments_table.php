<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompoundInvestmentsTable extends Migration
{
    public function up()
    {
        if (!\App\Support\SchemaInspector::hasTable('compound_investments')) {
            Schema::create('compound_investments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('payment_method_id')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_proof')->nullable();
                $table->decimal('amount', 15, 2);
                $table->unsignedInteger('duration_months');
                $table->decimal('monthly_return', 7, 3)->default(27.8);
                $table->decimal('performance_rate', 7, 3)->default(0);
                $table->decimal('recurring_amount', 15, 2)->default(0);
                $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly'])->default('monthly');
                $table->enum('deposit_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->enum('status', ['pending', 'active', 'rejected', 'completed'])->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('start_date')->nullable();
                $table->timestamp('expected_completion_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('completed_by')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
            });
        }

        if (!\App\Support\SchemaInspector::hasTable('compound_investment_topups')) {
            Schema::create('compound_investment_topups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('compound_investment_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->string('idempotency_key', 64)->nullable()->unique();
                $table->decimal('amount', 15, 2);
                $table->string('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Preserve investment requests, receipts, and earnings on rollback.
    }
}
