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
            $table->string('brand', 100)->nullable()->after('notes');
        });

        Schema::table('office_supplies_inventory', function (Blueprint $table) {
            $table->string('brand', 100)->nullable()->after('notes');
        });

        Schema::table('technical_equipments_inventory', function (Blueprint $table) {
            $table->string('brand', 100)->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('church_property_inventory', function (Blueprint $table) {
            $table->dropColumn('brand');
        });

        Schema::table('office_supplies_inventory', function (Blueprint $table) {
            $table->dropColumn('brand');
        });

        Schema::table('technical_equipments_inventory', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};

