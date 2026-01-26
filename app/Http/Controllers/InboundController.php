<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InboundController extends Controller
{
    public function receiving(): View
    {
        $inbound = Inbound::latest()->paginate(10);
        $title = 'Receiving';
        return view('inbound.receiving.index', compact('title', 'inbound'));
    }

    public function putAway(): View
    {
        $title = 'Put Away';
        return view('inbound.put-away.index', compact('title'));
    }

    public function createSpare(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.spare.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createFaulty(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.faulty.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createRma(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.rma.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createNewPO(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.new-po.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    /**
     * @throws \Throwable
     */
    public function storeNewPO(Request $request)
    {
        $request->validate([
            'category'      => 'required',
            'poNumber'      => 'required',
            'vendor'        => 'required',
            'receivedDate'  => 'required',
            'receivedBy'    => 'required',
            'products'      => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'       => $request->post('category'),
                'number'         => $request->post('poNumber'),
                'receiving_note' => $request->post('receivingNote'),
                'vendor'         => $request->post('vendor'),
                'qty'            => count($request->post('products')),
                'received_date'  => $request->post('receivedDate'),
                'received_by'    => $request->post('receivedBy'),
            ]);

            foreach ($request->post('products') as $product) {
                // Find or create Brand
                $brand = Brand::firstOrCreate(['name' => $product['brand']]);

                // Find or create Product Group
                $productGroup = ProductGroup::firstOrCreate(['name' => $product['productGroup']]);

                // Find or create Product
                $findProduct = Product::where('part_name', $product['partName'])
                    ->where('brand_id', $brand->id)
                    ->where('product_group_id', $productGroup->id)
                    ->first();

                if ($findProduct) {
                    $productId = $findProduct->id;
                } else {
                    $createProduct = Product::create([
                        'part_name'         => $product['partName'],
                        'brand_id'          => $brand->id,
                        'product_group_id'  => $productGroup->id,
                    ]);
                    $productId = $createProduct->id;
                }

                InboundDetail::create([
                    'inbound_id'    => $inbound->id,
                    'product_id'    => $productId,
                    'part_name'     => $product['partName'],
                    'part_number'   => $product['partNumber'],
                    'description'   => $product['partDescription'] ?? '',
                    'qty'           => 1,
                    'serial_number' => $product['serialNumber'],
                    'condition'     => $product['condition'],
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => $err->getMessage(),
            ]);
        }
    }
}
