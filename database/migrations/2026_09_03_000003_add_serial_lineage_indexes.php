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
        // Indexes powering the Asset Group suggestions/timeline queries and the
        // existing RMA Monitoring / Relokasi chain lookups (all currently full scans).
        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->index('serial_number');
            $table->index('old_serial_number');
            $table->index('parent_sn');
        });
        Schema::table('outbound_detail', function (Blueprint $table) {
            $table->index('serial_number');
            $table->index('old_serial_number');
        });
        Schema::table('inventory_history', function (Blueprint $table) {
            $table->index('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->dropIndex(['serial_number']);
            $table->dropIndex(['old_serial_number']);
            $table->dropIndex(['parent_sn']);
        });
        Schema::table('outbound_detail', function (Blueprint $table) {
            $table->dropIndex(['serial_number']);
            $table->dropIndex(['old_serial_number']);
        });
        Schema::table('inventory_history', function (Blueprint $table) {
            $table->dropIndex(['serial_number']);
        });
    }
};
