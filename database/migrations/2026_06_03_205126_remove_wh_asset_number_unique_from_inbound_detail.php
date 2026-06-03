<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexName = 'inbound_detail_wh_asset_number_unique';
        $exists = collect(DB::select("SHOW INDEX FROM inbound_detail"))
            ->contains('Key_name', $indexName);

        if ($exists) {
            DB::statement("ALTER TABLE inbound_detail DROP INDEX `{$indexName}`");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexName = 'inbound_detail_wh_asset_number_unique';
        $exists = collect(DB::select("SHOW INDEX FROM inbound_detail"))
            ->contains('Key_name', $indexName);

        if (!$exists) {
            Schema::table('inbound_detail', function (Blueprint $table) use ($indexName) {
                $table->unique('wh_asset_number', $indexName);
            });
        }
    }
};
