<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompoundInvestmentSubmissionKey extends Migration
{
    public function up()
    {
        if (!\App\Support\SchemaInspector::hasTable('compound_investments')
            || \App\Support\SchemaInspector::hasColumn('compound_investments', 'submission_key')) {
            return;
        }

        Schema::table('compound_investments', function (Blueprint $table) {
            $table->string('submission_key', 64)->nullable()->unique();
        });
    }

    public function down()
    {
        // Preserve payment and investment idempotency records on rollback.
    }
}
