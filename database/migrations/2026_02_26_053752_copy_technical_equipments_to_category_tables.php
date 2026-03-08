<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORY_TO_TABLE = [
        'Electrical' => 'electrical_inventory',
        'Chemical' => 'chemical_inventory',
        'Safety' => 'safety_inventory',
        'Cleaning' => 'cleaning_inventory',
        'Power Plant' => 'power_plant_inventory',
        'Industrial Supplies' => 'industrial_supplies_inventory',
        'Production Supplies' => 'production_supplies_inventory',
        'Sanitation' => 'sanitation_inventory',
        'Tools' => 'tools_inventory',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('technical_equipments_inventory')) {
            return;
        }

        $records = DB::table('technical_equipments_inventory')->get();

        foreach ($records as $row) {
            $category = $row->category === null || $row->category === '' ? 'Chemical' : $row->category;
            $table = self::CATEGORY_TO_TABLE[$category] ?? 'chemical_inventory';

            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->insert([
                'id' => $row->id,
                'item_name' => $row->item_name,
                'quantity' => $row->quantity,
                'min_stock' => $row->min_stock,
                'max_stock' => $row->max_stock,
                'status' => $row->status ?? 'Working',
                'notes' => $row->notes,
                'brand' => $row->brand,
                'date_arrived' => $row->date_arrived,
                'expiration_date' => $row->expiration_date,
                'location' => $row->location,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = array_values(self::CATEGORY_TO_TABLE);
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }
};
