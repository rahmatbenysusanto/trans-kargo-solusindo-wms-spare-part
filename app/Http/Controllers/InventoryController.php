<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        return view('inventory.stock-movement.index', compact('title'));
    }

    public function show($id): View
    {
        $title = 'Inventory Detail';
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'details.inboundDetail.inbound'])
            ->findOrFail($id);

        return view('inventory.inventory-list.show', compact('title', 'inventory'));
    }
}
