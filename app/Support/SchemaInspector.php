<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal schema checks that use SHOW on MySQL/MariaDB. This avoids requiring
 * direct SELECT permission on information_schema for limited DB users.
 */
class SchemaInspector
{
    public static function hasTable($table): bool
    {
        if (!self::validIdentifier($table)) {
            return false;
        }

        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                foreach (DB::select('SHOW TABLES') as $row) {
                    if (in_array($table, array_values((array) $row), true)) {
                        return true;
                    }
                }

                return false;
            }

            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function hasColumn($table, $column): bool
    {
        if (!self::validIdentifier($table) || !self::validIdentifier($column)) {
            return false;
        }

        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                foreach (DB::select('SHOW COLUMNS FROM `' . $table . '`') as $row) {
                    if (isset($row->Field) && $row->Field === $column) {
                        return true;
                    }
                }

                return false;
            }

            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function validIdentifier($identifier): bool
    {
        return is_string($identifier) && preg_match('/^[A-Za-z0-9_]+$/', $identifier) === 1;
    }
}
