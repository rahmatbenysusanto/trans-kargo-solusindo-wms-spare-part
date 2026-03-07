<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StagingController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['client', 'storageLevel.bin.rak.zone', 'brand', 'productGroup'])
            ->where('status', 'staging');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('serial_number', 'like', "%$s%")
                    ->orWhere('part_name', 'like', "%$s%")
                    ->orWhere('part_number', 'like', "%$s%");
            });
        }

        $stagingItems = $query->latest('last_staging_date')->paginate(15);
        $title = 'Staging Management';

        return view('staging.index', compact('stagingItems', 'title'));
    }

    /**
     * Search available inventory to pick for staging.
     */
    public function searchAvailable(Request $request)
    {
        $search = $request->search;
        $inventory = Inventory::with(['brand', 'storageLevel.bin.rak.zone'])
            ->where('status', 'available')
            ->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%$search%")
                    ->orWhere('part_name', 'like', "%$search%")
                    ->orWhere('part_number', 'like', "%$search%");
            })
            ->limit(20)
            ->get();

        return response()->json($inventory);
    }

    /**
     * Start staging for selected items.
     */
    public function startStaging(Request $request)
    {
        $request->validate([
            'inventory_ids' => 'required|array',
            'description' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $items = Inventory::whereIn('id', $request->inventory_ids)->get();

            foreach ($items as $item) {
                if ($item->status != 'available') {
                    throw new \Exception("Item {$item->serial_number} is not available for staging.");
                }

                $oldLocation = $item->storageLevel ? $item->storageLevel->zone->name . ' - ' . $item->storageLevel->name : 'N/A';

                $item->update([
                    'status' => 'staging',
                    'last_staging_date' => now()
                ]);

                InventoryHistory::create([
                    'inventory_id' => $item->id,
                    'serial_number' => $item->serial_number,
                    'type' => 'staging_in',
                    'category' => 'movement',
                    'description' => 'Moved to Staging Lab: ' . ($request->description ?? 'Checking/Testing'),
                    'user' => Auth::user()->name,
                    'from_location' => $oldLocation,
                    'to_location' => 'STAGING LAB'
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => count($items) . ' items moved to Staging.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Finish staging and return to inventory.
     */
    public function finishStaging(Request $request)
    {
        $request->validate([
            'inventory_ids' => 'required|array',
            'condition' => 'required|string', // Pass, Faulty, etc.
            'description' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $items = Inventory::whereIn('id', $request->inventory_ids)->get();

            foreach ($items as $item) {
                if ($item->status != 'staging') continue;

                $oldCondition = $item->condition;
                $item->update([
                    'status' => 'available',
                    'condition' => $request->condition
                ]);

                $locationName = $item->storageLevel ? $item->storageLevel->zone->name . ' - ' . $item->storageLevel->name : 'N/A';

                InventoryHistory::create([
                    'inventory_id' => $item->id,
                    'serial_number' => $item->serial_number,
                    'type' => 'staging_out',
                    'category' => 'movement',
                    'description' => "Staging Finished. Result: {$request->condition}. Description: " . ($request->description ?? '-'),
                    'user' => Auth::user()->name,
                    'from_location' => 'STAGING LAB',
                    'to_location' => $locationName
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Staging completed for selected items.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
