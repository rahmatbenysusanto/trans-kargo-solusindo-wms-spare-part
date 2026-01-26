<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brand = Brand::paginate(10);

        $title = 'Brand';
        return view('brand.index', compact('title', 'brand'));
    }

    public function store(Request $request)
    {
        Brand::create([
            'name' => $request->post('name'),
        ]);

        return back()->with('success', 'Brand created successfully');
    }

    public function update(Request $request)
    {
        $brand = Brand::find($request->post('id'));
        $brand->update([
            'name' => $request->post('name'),
        ]);

        return back()->with('success', 'Brand updated successfully');
    }
}
