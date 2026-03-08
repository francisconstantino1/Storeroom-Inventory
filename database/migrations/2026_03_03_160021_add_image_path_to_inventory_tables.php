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
        $tables = [
            'mechanical_inventory',
            'office_supplies_inventory',
            'electrical_inventory',
            'chemical_inventory',
            'safety_inventory',
            'cleaning_inventory',
            'power_plant_inventory',
            'industrial_supplies_inventory',
            'production_supplies_inventory',
            'sanitation_inventory',
            'tools_inventory',
            'technical_equipments_inventory',
        ];

        foreach ($tables as $name) {
            if (Schema::hasTable($name) && ! Schema::hasColumn($name, 'image_path')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->string('image_path')->nullable()->after('item_name');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'mechanical_inventory',
            'office_supplies_inventory',
            'electrical_inventory',
            'chemical_inventory',
            'safety_inventory',
            'cleaning_inventory',
            'power_plant_inventory',
            'industrial_supplies_inventory',
            'production_supplies_inventory',
            'sanitation_inventory',
            'tools_inventory',
            'technical_equipments_inventory',
        ];

        foreach ($tables as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'image_path')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('image_path');
                });
            }
        }
    }
};
