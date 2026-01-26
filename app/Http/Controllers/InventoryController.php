<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $title = 'Inventory List';
        return view('inventory.inventory-list.index', compact('title'));
    }

    public function stockMovement(): View
    {
        $title = 'Stock Movement';
        return view('inventory.stock-movement.index', compact('title'));
    }
}
