<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('church_property_inventory', 'mechanical_inventory');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('mechanical_inventory', 'church_property_inventory');
    }
};
