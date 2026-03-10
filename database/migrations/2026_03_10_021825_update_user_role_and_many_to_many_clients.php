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
        Schema::table('users', function (Blueprint $table) {
            // Drop client_id from users since it's now many-to-many
            if (Schema::hasColumn('users', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
            // Add role to users
            $table->enum('role', ['Admin WMS', 'Client User'])->default('Admin WMS')->after('status');
        });

        Schema::create('user_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('client')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_clients');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->foreignId('client_id')->nullable()->after('id')->constrained('client')->onDelete('cascade');
        });
    }
};
