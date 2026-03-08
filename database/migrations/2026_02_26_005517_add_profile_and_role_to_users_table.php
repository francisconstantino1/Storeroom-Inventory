<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('contact_number')->nullable()->after('password');
            $table->text('address')->nullable()->after('contact_number');
            $table->string('role', 50)->default('Store Room Assistant')->after('address');
            $table->string('created_by')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'contact_number', 'address', 'role', 'created_by']);
        });
    }
};
