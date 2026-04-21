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
        if (!Schema::hasColumn('inbound', 'remarks')) {
            Schema::table('inbound', function (Blueprint $table) {
                $table->text('remarks')->nullable();
            });
        }

        if (!Schema::hasColumn('outbound', 'remarks')) {
            Schema::table('outbound', function (Blueprint $table) {
                $table->text('remarks')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });

        Schema::table('outbound', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
