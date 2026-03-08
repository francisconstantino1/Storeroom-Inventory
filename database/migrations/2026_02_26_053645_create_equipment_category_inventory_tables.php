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
            'electrical_inventory',
            'chemical_inventory',
            'safety_inventory',
            'cleaning_inventory',
            'power_plant_inventory',
            'industrial_supplies_inventory',
            'production_supplies_inventory',
            'sanitation_inventory',
            'tools_inventory',
        ];

        foreach ($tables as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('item_name');
                $table->unsignedInteger('quantity')->default(0);
                $table->unsignedInteger('min_stock')->nullable();
                $table->unsignedInteger('max_stock')->nullable();
                $table->string('status', 50)->default('Working');
                $table->text('notes')->nullable();
                $table->string('brand', 100)->nullable();
                $table->date('date_arrived')->nullable();
                $table->date('expiration_date')->nullable();
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'electrical_inventory',
            'chemical_inventory',
            'safety_inventory',
            'cleaning_inventory',
            'power_plant_inventory',
            'industrial_supplies_inventory',
            'production_supplies_inventory',
            'sanitation_inventory',
            'tools_inventory',
        ];

        foreach ($tables as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
