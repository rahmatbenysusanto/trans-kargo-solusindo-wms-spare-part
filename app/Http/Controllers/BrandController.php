<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $brand = Brand::when($search, function ($query) use ($search) {
            return $query->where('name', 'LIKE', "%{$search}%");
        })->paginate(10);

        $title = 'Brand';
        return view('brand.index', compact('title', 'brand', 'search'));
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
