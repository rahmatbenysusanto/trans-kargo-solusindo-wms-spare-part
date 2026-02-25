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
        Schema::table('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('part_name');
            $table->unsignedBigInteger('brand_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('product_group_id')->nullable()->after('brand_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'brand_id', 'product_group_id']);
        });
    }
};
