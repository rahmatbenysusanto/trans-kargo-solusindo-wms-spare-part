<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InboundDetail;
use App\Models\Inventory;
use App\Models\InventoryDetail;

echo "Checking InboundDetail wh_asset_number:\n";
$inboundDetails = InboundDetail::whereNotNull('wh_asset_number')->limit(5)->get();
foreach ($inboundDetails as $detail) {
    echo "ID: {$detail->id}, WH Asset: {$detail->wh_asset_number}, Created At: {$detail->created_at}\n";
}

echo "\nChecking Inventory unique_id:\n";
$inventories = Inventory::whereNotNull('unique_id')->limit(5)->get();
foreach ($inventories as $inv) {
    echo "ID: {$inv->id}, Unique ID: {$inv->unique_id}, Created At: {$inv->created_at}\n";
}

echo "\nChecking InventoryDetail counts:\n";
echo "Total InventoryDetail: " . InventoryDetail::count() . "\n";
