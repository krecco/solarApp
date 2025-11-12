<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseHelper
{
    /**
     * Check if the database supports UUID functions.
     */
    public static function supportsUuid(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['pgsql', 'mysql']);
    }

    /**
     * Enable UUID support for the current database connection.
     */
    public static function enableUuidSupport(): void
    {
        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'pgsql':
                DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
                break;
            case 'mysql':
                // MySQL 8+ has built-in UUID support
                break;
            case 'sqlite':
                // SQLite doesn't have UUID extension, we'll handle it differently
                break;
        }
    }

    /**
     * Get the UUID generation expression for the current database.
     */
    public static function uuidExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'pgsql':
                return 'uuid_generate_v4()';
            case 'mysql':
                return 'UUID()';
            case 'sqlite':
                // For SQLite, we'll use a trigger or generate in PHP
                return "''"; // Placeholder, will be replaced by trigger
            default:
                throw new \Exception("Unsupported database driver for UUID: {$driver}");
        }
    }

    /**
     * Create a UUID column with appropriate defaults.
     */
    public static function addUuidColumn($table, string $column = 'uuid'): void
    {
        if (static::supportsUuid() && DB::connection()->getDriverName() !== 'sqlite') {
            $table->uuid($column)->default(DB::raw(static::uuidExpression()))->unique();
        } else {
            // For SQLite or other databases, use string and generate UUID in PHP
            $table->uuid($column)->nullable()->unique();
        }
    }
}
