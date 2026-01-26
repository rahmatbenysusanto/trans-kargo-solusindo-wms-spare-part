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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->integer('inbound_detail_id');
            $table->integer('bin_id');
            $table->string('unique_number')->unique();
            $table->string('part_name');
            $table->string('part_number');
            $table->string('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('status', ['available', 'reserved', 'loan', 'defective', 'rma'])->default('available');
            $table->date('last_staging_date')->nullable();
            $table->date('last_movement_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
