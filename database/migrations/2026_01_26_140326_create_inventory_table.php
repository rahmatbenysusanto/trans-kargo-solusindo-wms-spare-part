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
            $table->string('unique_id')->unique();
            $table->integer('client_id');
            $table->integer('storage_level_id');
            $table->integer('qty')->default(0);
            $table->string('part_name');
            $table->string('part_number');
            $table->string('part_description')->nullable();
            $table->string('serial_number');
            $table->string('parent_serial_number')->nullable();
            $table->string('status');
            $table->timestamp('last_staging_date')->nullable();
            $table->timestamp('last_movement_date')->nullable();
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
