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
        Schema::create('outbound', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->integer('client_id');
            $table->string('number')->nullable(); // PO#
            $table->string('ntt_dn_number')->nullable();
            $table->string('tks_dn_number')->nullable();
            $table->string('tks_invoice_number')->nullable();
            $table->string('rma_number')->nullable();
            $table->string('itsm_number')->nullable();
            $table->integer('qty')->default(0);
            $table->string('status');
            $table->date('outbound_date')->nullable();
            $table->string('outbound_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound');
    }
};
