<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $title = 'Inventory List';
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client'])
            ->where('qty', 1)
            ->when($request->sku, function ($query) use ($request) {
                return $query->where('unique_id', 'like', '%' . $request->sku . '%');
            })
            ->latest()
            ->paginate(20);

        return view('inventory.inventory-list.index', compact('title', 'inventory'));
    }

    public function stockMovement(): View
    {
        $title = 'Stock Movement';
        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user'])
            ->latest()
            ->paginate(20);

        return view('inventory.stock-movement.index', compact('title', 'movements'));
    }

    public function show($id): View
    {
        $title = 'Inventory Detail';
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'details.inboundDetail.inbound'])
            ->findOrFail($id);

        return view('inventory.inventory-list.show', compact('title', 'inventory'));
    }

    public function productMovementIndex(): View
    {
        $title = 'Product Movement';
        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user'])
            ->where('type', 'Movement')
            ->latest()
            ->paginate(20);

        return view('inventory.product-movement.index', compact('title', 'movements'));
    }

    public function productMovementProcess(): View
    {
        $title = 'Product Movement';
        $inventory = \App\Models\Inventory::where('qty', '>', 0)->latest()->get();
        $storageZone = \App\Models\StorageZone::all();

        return view('inventory.product-movement.process', compact('title', 'inventory', 'storageZone'));
    }

    public function productMovementUpdate(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'storage_level_id' => 'required'
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($request->products as $id) {
                $inventory = \App\Models\Inventory::findOrFail($id);
                $oldStorageLevelId = $inventory->storage_level_id;

                $inventory->update([
                    'storage_level_id' => $request->storage_level_id,
                    'last_movement_date' => now()
                ]);

                // Record History
                \App\Models\InventoryMovement::create([
                    'inventory_id' => $id,
                    'from_storage_level_id' => $oldStorageLevelId,
                    'to_storage_level_id' => $request->storage_level_id,
                    'user_id' => Auth::id(),
                    'type' => 'Movement',
                    'description' => 'Product moved by ' . Auth::user()->name
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }
}
