<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProductGroup;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $productGroup = ProductGroup::paginate(10);
        $title = 'Product Group';
        return view('product-group.index', compact('title', 'productGroup'));
    }

    public function store(Request $request)
    {
        ProductGroup::create([
            'name' => $request->post('name'),
        ]);

        return back()->with('success', 'Product Group created successfully');
    }

    public function update(Request $request)
    {
        $productGroup = ProductGroup::find($request->post('id'));
        $productGroup->update([
            'name' => $request->post('name'),
        ]);

        return back()->with('success', 'Product Group updated successfully');
    }
}
