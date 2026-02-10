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
        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('type'); // Inbound, Outbound, Movement, Adjustment
            $table->string('category')->nullable(); // PO, Spare, Faulty, RMA, Transfer, etc.
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('user')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_history');
    }
};
