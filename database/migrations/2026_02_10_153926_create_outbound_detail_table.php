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
        Schema::create('outbound_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_id')->constrained('outbound')->onDelete('cascade');
            $table->integer('product_id');
            $table->string('part_name');
            $table->string('part_number');
            $table->string('description')->nullable();
            $table->integer('qty')->default(0);
            $table->string('serial_number');
            $table->string('old_serial_number')->nullable();
            $table->string('condition');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_detail');
    }
};
