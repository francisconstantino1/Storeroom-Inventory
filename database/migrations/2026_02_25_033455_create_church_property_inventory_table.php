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
        Schema::create('church_property_inventory', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(0);
            $table->text('notes')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('location')->nullable();
            $table->string('status', 50)->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_property_inventory');
    }
};
