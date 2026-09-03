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
        Schema::create('asset_group_items', function (Blueprint $table) {
            $table->id();
            // Group deleted -> join rows deleted (inventory rows untouched)
            $table->foreignId('asset_group_id')->constrained('asset_groups')->cascadeOnDelete();
            // Inventory row deleted -> only this join row dies, group survives
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asset_group_id', 'inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_group_items');
    }
};
