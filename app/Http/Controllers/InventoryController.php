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
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('part_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15);

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
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client'])
            ->findOrFail($id);

        $sn = $inventory->serial_number;

        // Fetch unified history from the dedicated table
        $history = \App\Models\InventoryHistory::where('serial_number', $sn)
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at,
                    'type' => $item->type,
                    'category' => $item->category,
                    'reference' => $item->reference_number,
                    'description' => $item->description,
                    'user' => $item->user,
                    'from_location' => $item->from_location,
                    'to_location' => $item->to_location
                ];
            });

        return view('inventory.inventory-list.show', compact('title', 'inventory', 'history'));
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

                // Record Unified History
                $toStorage = \App\Models\StorageLevel::with('bin.rak.zone')->find($request->storage_level_id);
                $toName = $toStorage ? $toStorage->bin->rak->zone->name . ' - ' . $toStorage->name : 'N/A';

                $fromStorage = \App\Models\StorageLevel::with('bin.rak.zone')->find($oldStorageLevelId);
                $fromName = $fromStorage ? $fromStorage->bin->rak->zone->name . ' - ' . $fromStorage->name : 'N/A';

                \App\Models\InventoryHistory::create([
                    'inventory_id' => $id,
                    'serial_number' => $inventory->serial_number,
                    'type' => 'Movement',
                    'category' => 'Location Change',
                    'reference_number' => '-',
                    'description' => "Unit moved from [$fromName] to [$toName]",
                    'user' => Auth::user()->name,
                    'from_location' => $fromName,
                    'to_location' => $toName
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
