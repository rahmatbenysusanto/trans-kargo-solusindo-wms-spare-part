<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InboundDetail;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use Illuminate\Support\Facades\DB;

try {
    echo "Fetching all InboundDetail records with wh_asset_number...\n";
    $inboundDetails = InboundDetail::whereNotNull('wh_asset_number')
        ->orderBy('created_at', 'asc')
        ->get();

    echo "Found " . $inboundDetails->count() . " records to update.\n";

    DB::beginTransaction();
    echo "Clearing existing wh_asset_numbers and unique_ids to avoid unique constraint violations...\n";
    
    // Collect linked Inventory IDs to clear them too
    $inventoryIds = [];
    foreach ($inboundDetails as $ib) {
        $invDetail = InventoryDetail::where('inbound_detail_id', $ib->id)->first();
        if ($invDetail && $invDetail->inventory_id) {
            $inventoryIds[] = $invDetail->inventory_id;
        }
    }
    
    InboundDetail::whereNotNull('wh_asset_number')->update(['wh_asset_number' => null]);
    if (!empty($inventoryIds)) {
        Inventory::whereIn('id', $inventoryIds)->update(['unique_id' => null]);
    }
    echo "Cleared.\n";

    echo "Assigning new wh_asset_numbers...\n";
    $sequences = []; // Track sequence per date (YYmmdd => last_seq)

    foreach ($inboundDetails as $ib) {
        $date = $ib->created_at->format('ymd');
        
        if (!isset($sequences[$date])) {
            $sequences[$date] = 0;
        }
        
        $sequences[$date]++;
        $newId = $date . str_pad($sequences[$date], 4, '0', STR_PAD_LEFT);

        // Update InboundDetail
        $ib->update(['wh_asset_number' => $newId]);

        // Find linked Inventory via InventoryDetail and update it
        // We already have the logic to find the inventory id
        $invDetail = InventoryDetail::where('inbound_detail_id', $ib->id)->first();
        if ($invDetail && $invDetail->inventory_id) {
            Inventory::where('id', $invDetail->inventory_id)->update(['unique_id' => $newId]);
        }
        
        if ($sequences[$date] % 500 == 0) {
            echo "Processed 500 records for date $date...\n";
        }
    }

    DB::commit();
    echo "\nSuccessfully updated all records in both inbound_detail and inventory tables with new format YYmmddXXXX.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
