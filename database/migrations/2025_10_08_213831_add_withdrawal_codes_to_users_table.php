<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWithdrawalCodesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('withdrawal_code_enabled')->default(false)->after('notification_message');
            $table->string('withdrawal_code')->nullable()->after('withdrawal_code_enabled');
            $table->string('withdrawal_code_name')->default('Withdrawal Code')->after('withdrawal_code');
            $table->text('withdrawal_code_message')->nullable()->after('withdrawal_code_name');
            
            $table->boolean('tax_code_enabled')->default(false)->after('withdrawal_code_message');
            $table->string('tax_code')->nullable()->after('tax_code_enabled');
            $table->string('tax_code_name')->default('Tax Code')->after('tax_code');
            $table->text('tax_code_message')->nullable()->after('tax_code_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['withdrawal_code_enabled', 'withdrawal_code', 'withdrawal_code_name', 'withdrawal_code_message', 'tax_code_enabled', 'tax_code', 'tax_code_name', 'tax_code_message']);
        });
    }
}
