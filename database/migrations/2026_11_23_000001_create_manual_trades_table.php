<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualTradesTable extends Migration
{
    public function up()
    {
        if (!\App\Support\SchemaInspector::hasTable('manual_trades')) {
            Schema::create('manual_trades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('category', 20);
                $table->string('instrument', 40);
                $table->string('direction', 4);
                $table->decimal('amount', 15, 2);
                $table->unsignedSmallInteger('leverage');
                $table->unsignedInteger('duration_minutes');
                $table->decimal('entry_price', 20, 8)->nullable();
                $table->decimal('result_amount', 15, 2)->nullable();
                $table->enum('status', ['active', 'completed'])->default('active');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down()
    {
        // Preserve real trading records during rollback; table removal must
        // be a separately reviewed operational decision.
    }
}
