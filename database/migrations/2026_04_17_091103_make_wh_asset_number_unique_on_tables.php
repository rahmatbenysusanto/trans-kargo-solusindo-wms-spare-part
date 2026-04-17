<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inbound_detail', function (Blueprint $table) {
            // Check if index exists before adding
            $logicalName = 'inbound_detail_wh_asset_number_unique';
            $indexExists = collect(DB::select("SHOW INDEX FROM inbound_detail"))->contains('Key_name', $logicalName);
            
            if (!$indexExists) {
                $table->unique('wh_asset_number');
            }
        });

        Schema::table('inventory', function (Blueprint $table) {
            $logicalName = 'inventory_unique_id_unique';
            $indexExists = collect(DB::select("SHOW INDEX FROM inventory"))->contains('Key_name', $logicalName);
            
            if (!$indexExists) {
                $table->unique('unique_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->dropUnique(['wh_asset_number']);
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropUnique(['unique_id']);
        });
    }
};
