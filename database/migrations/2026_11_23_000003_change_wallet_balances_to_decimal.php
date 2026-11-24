<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeWalletBalancesToDecimal extends Migration
{
    public function up()
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql' && \App\Support\SchemaInspector::hasColumn('users', 'account_bal') && \App\Support\SchemaInspector::hasColumn('users', 'roi')) {
            // Keep nullable legacy wallet rows nullable; converting them to
            // NOT NULL could rewrite or reject existing NULL balances.
            DB::statement('ALTER TABLE users MODIFY account_bal DECIMAL(15,2) NULL DEFAULT NULL, MODIFY roi DECIMAL(15,2) NULL DEFAULT NULL');
        } elseif ($driver === 'pgsql' && \App\Support\SchemaInspector::hasColumn('users', 'account_bal') && \App\Support\SchemaInspector::hasColumn('users', 'roi')) {
            DB::statement('ALTER TABLE users ALTER COLUMN account_bal TYPE DECIMAL(15,2) USING account_bal::DECIMAL(15,2), ALTER COLUMN roi TYPE DECIMAL(15,2) USING roi::DECIMAL(15,2)');
        }
        // SQLite retains decimal numeric values even when the legacy columns
        // have INTEGER affinity, so no rebuild of the users table is needed.
    }

    public function down()
    {
        // Deliberately do not narrow financial balances back to whole dollars.
    }
}
