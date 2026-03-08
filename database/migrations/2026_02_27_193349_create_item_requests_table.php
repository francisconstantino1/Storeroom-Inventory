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
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_type', 50);
            $table->string('item_id', 50);
            $table->string('item_name');
            $table->unsignedInteger('requested_quantity');
            $table->string('status', 20)->default('Pending');
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('requested_department', 100)->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_notes')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_requests');
    }
};
