<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'Super Admin')->update(['role' => 'Store Room Supervisor']);
        DB::table('users')
            ->whereIn('role', ['Administrator', 'Admin', 'Member'])
            ->update(['role' => 'Store Room Assistant']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'Store Room Supervisor')->update(['role' => 'Super Admin']);
        DB::table('users')->where('role', 'Store Room Assistant')->update(['role' => 'Member']);
    }
};
