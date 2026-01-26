<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inbound_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained('inbound');
            $table->integer('product_id');
            $table->string('part_name');
            $table->string('part_number');
            $table->string('description');
            $table->string('qty')->default(0);
            $table->string('serial_number');
            $table->string('old_serial_number')->nullable();
            $table->string('condition');
            $table->integer('storage_level_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_detail');
    }
};
