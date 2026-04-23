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
        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->string('old_wh_asset_number')->nullable()->after('parent_sn');
        });
        Schema::table('inventory', function (Blueprint $table) {
            $table->string('old_wh_asset_number')->nullable()->after('parent_serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->dropColumn('old_wh_asset_number');
        });
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('old_wh_asset_number');
        });
    }
};
