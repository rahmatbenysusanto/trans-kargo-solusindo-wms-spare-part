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
        Schema::table('inbound', function (Blueprint $table) {
            $table->string('request_type')->nullable()->after('category');
            $table->string('ntt_requestor')->nullable()->after('request_type');
            $table->date('request_date')->nullable()->after('ntt_requestor');
            $table->string('ecapex_number')->nullable()->after('itsm_number');
            $table->string('sap_po_number')->nullable()->after('ecapex_number');
            $table->string('vendor_dn_number')->nullable()->after('sap_po_number');
            $table->string('tks_dn_number')->nullable()->after('vendor_dn_number');
            $table->string('tks_invoice_number')->nullable()->after('tks_dn_number');
            $table->string('client_contact')->nullable()->after('client_id');
            $table->text('pickup_address')->nullable()->after('client_contact');
            $table->string('ntt_dn_number')->nullable()->after('tks_invoice_number');
            $table->date('delivery_date')->nullable()->after('ntt_dn_number');
            $table->string('shipment_status')->nullable()->after('status');
        });

        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->string('wh_asset_number')->nullable()->after('qty');
            $table->string('stock_status')->nullable()->after('wh_asset_number');
            $table->date('staging_date')->nullable()->after('stock_status');
            $table->string('parent_sn')->nullable()->after('old_serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound', function (Blueprint $table) {
            $table->dropColumn([
                'request_type',
                'ntt_requestor',
                'request_date',
                'ecapex_number',
                'sap_po_number',
                'vendor_dn_number',
                'tks_dn_number',
                'tks_invoice_number',
                'client_contact',
                'pickup_address',
                'ntt_dn_number',
                'delivery_date',
                'shipment_status'
            ]);
        });

        Schema::table('inbound_detail', function (Blueprint $table) {
            $table->dropColumn(['wh_asset_number', 'stock_status', 'staging_date', 'parent_sn']);
        });
    }
};
