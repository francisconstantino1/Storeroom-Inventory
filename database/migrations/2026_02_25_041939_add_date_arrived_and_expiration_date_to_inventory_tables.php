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
        Schema::table('church_property_inventory', function (Blueprint $table) {
            $table->date('date_arrived')->nullable()->after('notes');
            $table->date('expiration_date')->nullable()->after('date_arrived');
        });
        Schema::table('office_supplies_inventory', function (Blueprint $table) {
            $table->date('date_arrived')->nullable()->after('notes');
            $table->date('expiration_date')->nullable()->after('date_arrived');
        });
        Schema::table('technical_equipments_inventory', function (Blueprint $table) {
            $table->date('date_arrived')->nullable()->after('notes');
            $table->date('expiration_date')->nullable()->after('date_arrived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('church_property_inventory', function (Blueprint $table) {
            $table->dropColumn(['date_arrived', 'expiration_date']);
        });
        Schema::table('office_supplies_inventory', function (Blueprint $table) {
            $table->dropColumn(['date_arrived', 'expiration_date']);
        });
        Schema::table('technical_equipments_inventory', function (Blueprint $table) {
            $table->dropColumn(['date_arrived', 'expiration_date']);
        });
    }
};
