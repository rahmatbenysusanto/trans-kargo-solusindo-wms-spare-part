<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InboundDetail;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    echo "Fetching InventoryDetail records...\n";
    $details = InventoryDetail::with(['inventory', 'inboundDetail'])
        ->orderBy('created_at', 'asc')
        ->get();

    echo "Found " . $details->count() . " records to update.\n";

    $sequences = []; // Track sequence per date (YYmmdd => last_seq)

    foreach ($details as $detail) {
        $date = $detail->created_at->format('ymd');
        
        if (!isset($sequences[$date])) {
            $sequences[$date] = 0;
        }
        
        $sequences[$date]++;
        $newId = $date . str_pad($sequences[$date], 4, '0', STR_PAD_LEFT);

        // Update InboundDetail
        if ($detail->inbound_detail_id) {
            InboundDetail::where('id', $detail->inbound_detail_id)->update(['wh_asset_number' => $newId]);
        }

        // Update Inventory
        if ($detail->inventory_id) {
            Inventory::where('id', $detail->inventory_id)->update(['unique_id' => $newId]);
        }
        
        if ($sequences[$date] % 100 == 0) {
            echo "Processed 100 records for date $date...\n";
        }
    }

    DB::commit();
    echo "Successfully updated all records.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
