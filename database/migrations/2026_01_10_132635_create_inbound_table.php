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
        Schema::create('inbound', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('number');
            $table->string('reff_number')->nullable();
            $table->string('receiving_note')->nullable();
            $table->string('sttb')->nullable();
            $table->string('courier_delivery_note')->nullable();
            $table->string('courier_invoice')->nullable();
            $table->string('rma_number')->nullable();
            $table->string('itsm_number')->nullable();
            $table->string('vendor');
            $table->integer('qty')->default(0);
            $table->date('received_date')->nullable();
            $table->string('received_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound');
    }
};
