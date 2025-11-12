<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create simplified roles for fresh database
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        // Clean out any default complex roles/permissions if they exist
        DB::table($tableNames['permissions'])->delete();
        DB::table($tableNames['roles'])->delete();

        // Create only two simple roles
        DB::table($tableNames['roles'])->insert([
            [
                'name' => 'customer',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // No permissions needed - we use simple role checks
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        DB::table($tableNames['roles'])->whereIn('name', ['customer', 'admin'])->delete();
    }
};
