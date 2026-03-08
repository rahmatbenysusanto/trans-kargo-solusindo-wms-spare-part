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
        Schema::table('outbound', function (Blueprint $table) {
            $table->string('request_type')->nullable()->after('category');
            $table->string('ntt_requestor')->nullable()->after('request_type');
            $table->date('request_date')->nullable()->after('ntt_requestor');
            $table->string('sap_po_number')->nullable()->after('request_date');
            $table->string('client_contact')->nullable()->after('client_id');
            $table->text('pickup_address')->nullable()->after('client_contact');
            $table->string('shipment_status')->default('Pending')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbound', function (Blueprint $table) {
            $table->dropColumn([
                'request_type',
                'ntt_requestor',
                'request_date',
                'sap_po_number',
                'client_contact',
                'pickup_address',
                'shipment_status'
            ]);
        });
    }
};
