<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Client;
use App\Models\StorageZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InboundController extends Controller
{
    public function receiving(Request $request): View
    {
        $query = Inbound::latest();
        $user = Auth::user();

        if ($user->isAdminWMS()) {
            if ($request->client_id) {
                $query->where('client_id', $request->client_id);
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($request->client_id && in_array($request->client_id, $accessibleIds)) {
                $query->where('client_id', $request->client_id);
            } else {
                $query->where(function ($q) use ($accessibleIds) {
                    $q->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            }
        }

        $inbound = $query
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->request_type, function ($query) use ($request) {
                return $query->where('request_type', $request->request_type);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('rma_number', 'like', '%' . $request->search . '%')
                        ->orWhere('itsm_number', 'like', '%' . $request->search . '%')
                        ->orWhere('vendor', 'like', '%' . $request->search . '%')
                        ->orWhere('sap_po_number', 'like', '%' . $request->search . '%')
                        ->orWhereHas('details', function ($dq) use ($request) {
                            $dq->where('wh_asset_number', 'like', '%' . $request->search . '%')
                                ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                                ->orWhere('part_name', 'like', '%' . $request->search . '%')
                                ->orWhere('part_number', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->when($request->part_numbers, function ($query) use ($request) {
                // Support multiple delimiters: comma, newline, semicolon
                $normalized = str_replace(["\r\n", "\r", "\n", ';'], ',', $request->part_numbers);
                $partNumbers = array_filter(array_map('trim', explode(',', $normalized)), function ($v) {
                    return $v !== '';
                });
                if (empty($partNumbers)) {
                    return $query;
                }
                return $query->whereHas('details', function ($dq) use ($partNumbers) {
                    $dq->where(function ($subQ) use ($partNumbers) {
                        foreach ($partNumbers as $i => $pn) {
                            if ($i === 0) {
                                $subQ->where('part_number', 'like', '%' . $pn . '%');
                            } else {
                                $subQ->orWhere('part_number', 'like', '%' . $pn . '%');
                            }
                        }
                    });
                });
            })
            ->paginate(10);

        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration', 'Spare Return'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration', 'Return'];
        $clients = $user->getAvailableClients();
        $title = 'Receiving';

        return view('inbound.receiving.index', compact('title', 'inbound', 'categories', 'requestTypes', 'clients'));
    }

    public function staging(): View
    {
        $title = 'Staging';
        return view('inbound.staging.index', compact('title'));
    }

    public function show($id): View
    {
        $inbound = Inbound::with([
            'details.brand',
            'details.storageLevel.zone',
            'details.storageLevel.rak',
            'details.storageLevel.bin',
            'client',
            'invoices'
        ])->findOrFail($id);
        $title = 'Receiving';
        return view('inbound.receiving.show', compact('title', 'inbound'));
    }

    public function showPutAway($id): View
    {
        $inbound = Inbound::with([
            'details.brand',
            'details.storageLevel.zone',
            'details.storageLevel.rak',
            'details.storageLevel.bin',
            'client',
            'invoices'
        ])->findOrFail($id);
        $title = 'Put Away';
        return view('inbound.put-away.show', compact('title', 'inbound'));
    }

    public function printPdf($id): View
    {
        $inbound = Inbound::with(['client', 'details'])->findOrFail($id);
        $title = 'Receiving Report';
        return view('inbound.receiving.pdf', compact('title', 'inbound'));
    }

    public function edit($id): View
    {
        $inbound = Inbound::with(['details.brand', 'details.productGroup', 'client'])->findOrFail($id);

        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();
        $title = 'Receiving';

        return view('inbound.receiving.edit', compact('title', 'inbound', 'brand', 'productGroup', 'client'));
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $inbound = Inbound::findOrFail($id);

            $inbound->update([
                'client_id'             => $request->post('client_id') ?: null,
                'client_contact'        => $request->post('client_contact'),
                'pickup_address'        => $request->post('pickup_address'),
                'vendor'                => $request->post('vendor') ?? 'Internal',
                'request_type'          => $request->post('request_type'),
                'ntt_requestor'         => $request->post('ntt_requestor'),
                'request_date'          => $request->post('request_date'),
                'number'                => $request->post('po_number'),
                'receiving_note'        => $request->post('number'),
                'sttb'                  => $request->post('sttb'),
                'courier_delivery_note' => $request->post('delivery_note'),
                'courier_invoice'       => $request->post('courier_invoice'),
                'rma_number'            => $request->post('rma_number'),
                'itsm_number'           => $request->post('itsm_number'),
                'sap_po_number'         => $request->post('sap_po_number') ? preg_replace('/\D/', '', $request->post('sap_po_number')) : null,
                'ecapex_number'         => $request->post('ecapex_number'),
                'vendor_dn_number'      => $request->post('vendor_dn_number'),
                'tks_dn_number'         => $request->post('tks_dn_number'),
                'tks_invoice_number'    => $request->post('tks_invoice_number'),
                'ntt_dn_number'         => $request->post('ntt_dn_number'),
                'delivery_date'         => $request->post('delivery_date'),
                'received_date'         => $request->post('receivedDate'),
                'received_by'           => $request->post('receivedBy'),
                'remarks'               => $request->post('remarks'),
            ]);

            return response()->json(['status' => true, 'message' => 'Receiving updated successfully.']);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function approve(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $inbound = Inbound::findOrFail($request->post('id'));
            $inbound->update(['status' => 'process qc']);

            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function cancel(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $inbound = Inbound::findOrFail($request->post('id'));
            $inbound->update(['status' => 'cancel']);

            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function putAway(Request $request): View
    {
        $query = Inbound::where('status', 'process qc');
        $user = Auth::user();

        if ($user->isAdminWMS()) {
            if ($request->client_id) {
                $query->where('client_id', $request->client_id);
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($request->client_id && in_array($request->client_id, $accessibleIds)) {
                $query->where('client_id', $request->client_id);
            } else {
                $query->where(function ($q) use ($accessibleIds) {
                    $q->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            }
        }

        $inbound = $query
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->request_type, function ($query) use ($request) {
                return $query->where('request_type', $request->request_type);
            })
            ->when($request->serial_number, function ($query) use ($request) {
                return $query->whereHas('details', function ($q) use ($request) {
                    $q->where('serial_number', 'like', '%' . $request->serial_number . '%');
                });
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('receiving_note', 'like', '%' . $request->search . '%')
                        ->orWhere('vendor', 'like', '%' . $request->search . '%')
                        ->orWhereHas('details', function ($dq) use ($request) {
                            $dq->where('serial_number', 'like', '%' . $request->search . '%')
                                ->orWhere('wh_asset_number', 'like', '%' . $request->search . '%')
                                ->orWhere('part_number', 'like', '%' . $request->search . '%')
                                ->orWhere('part_name', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()->paginate(10);

        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration', 'Spare Return'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration', 'Return'];
        $clients = $user->getAvailableClients();
        $title = 'Put Away';
        return view('inbound.put-away.index', compact('title', 'inbound', 'categories', 'requestTypes', 'clients'));
    }

    public function processPutAway($id): View
    {
        $inbound = Inbound::with(['details' => function ($query) {
            $query->whereNull('storage_level_id');
        }])->findOrFail($id);

        $storageZone = StorageZone::all();
        $title = 'Put Away';
        return view('inbound.put-away.process', compact('title', 'inbound', 'storageZone'));
    }

    private static function makeCode(string $text, int $length = 3): string
    {
        return strtoupper(substr(Str::slug($text, ''), 0, $length));
    }

    public static function generateUniqueId($date = null, array $exclude = []): string
    {
        $prefix = $date ? date('ymd', strtotime($date)) : date('ymd');

        // Find last assigned sequence for this prefix in Inventory
        $lastInventory = Inventory::where('unique_id', 'like', $prefix . '%')
            ->whereRaw('LENGTH(unique_id) = 10')
            ->orderBy('unique_id', 'desc')
            ->first();

        // Find last assigned sequence for this prefix in InboundDetail
        $lastInbound = InboundDetail::where('wh_asset_number', 'like', $prefix . '%')
            ->whereRaw('LENGTH(wh_asset_number) = 10')
            ->orderBy('wh_asset_number', 'desc')
            ->first();

        // Get the latest ID between both tables
        $lastIdInDb = null;
        if ($lastInventory && $lastInbound) {
            $lastIdInDb = max($lastInventory->unique_id, $lastInbound->wh_asset_number);
        } else {
            $lastIdInDb = $lastInventory ? $lastInventory->unique_id : ($lastInbound ? $lastInbound->wh_asset_number : null);
        }

        // Also check if there's a higher ID in the exclude list (the current processing batch)
        $lastIdFinal = $lastIdInDb;
        if (!empty($exclude)) {
            $lastExcluded = collect($exclude)
                ->filter(fn($id) => str_starts_with($id, $prefix) && strlen($id) === 10)
                ->sortDesc()
                ->first();
            if ($lastExcluded) {
                $lastIdFinal = $lastIdFinal ? max($lastIdFinal, $lastExcluded) : $lastExcluded;
            }
        }

        $nextSequence = 1;
        if ($lastIdFinal && str_starts_with($lastIdFinal, $prefix)) {
            // Extract the sequence part and increment (the last 4 digits)
            $lastSequenceStr = substr($lastIdFinal, 6);
            if (is_numeric($lastSequenceStr)) {
                $nextSequence = (int) $lastSequenceStr + 1;
            }
        }

        return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    private static function getRomanMonth($month)
    {
        $roman = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $roman[(int)$month] ?? 'I';
    }

    public static function generateInboundNumber(): string
    {
        $year = date('Y');
        $monthRoman = self::getRomanMonth(date('n'));
        $format = "/RN/WH/$monthRoman/$year";

        $lastInbound = Inbound::where('number', 'like', "%$format")->orderBy('number', 'desc')->first();
        $lastSerial = 0;
        if ($lastInbound) {
            $parts = explode('/', $lastInbound->number);
            $lastSerial = (int) $parts[0];
        }
        $newSerial = str_pad($lastSerial + 1, 6, '0', STR_PAD_LEFT);
        return "$newSerial$format";
    }

    public static function generateTksDnNumber(): string
    {
        $year = date('Y');
        $monthRoman = self::getRomanMonth(date('n'));
        $format = "/RN/WH/$monthRoman/$year";

        $lastInbound = Inbound::where('tks_dn_number', 'like', "%$format")->orderBy('tks_dn_number', 'desc')->first();
        $lastSerial = 0;
        if ($lastInbound) {
            $parts = explode('/', $lastInbound->tks_dn_number);
            $lastSerial = (int) $parts[0];
        }
        $newSerial = str_pad($lastSerial + 1, 6, '0', STR_PAD_LEFT);
        return "$newSerial$format";
    }

    /**
     * @throws \Throwable
     */
    public function updatePutAway(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::statement("SELECT GET_LOCK('put_away_unique_id', 15)");
        try {
            DB::beginTransaction();
            $products = $request->post('products');
            $storageLevelId = $request->post('storage_level_id');
            $notes = $request->post('notes', []); // array keyed by detail id

            $usedIds = [];
            foreach ($products as $id) {
                $inboundDetail = InboundDetail::findOrFail($id);
                $inbound = Inbound::find($inboundDetail->inbound_id);

                // Save notes if provided
                if (isset($notes[$id]) && !empty($notes[$id])) {
                    $inboundDetail->notes = $notes[$id];
                }

                // Initial Inventory check (by SN or by existing WH Asset Number)
                $checkInventory = Inventory::where('serial_number', $inboundDetail->serial_number)
                    ->when($inboundDetail->wh_asset_number, function ($q) use ($inboundDetail) {
                        return $q->orWhere('unique_id', $inboundDetail->wh_asset_number);
                    })
                    ->first();

                // Generate or Fetch WH Asset Number
                if (!$inboundDetail->wh_asset_number) {
                    if ($checkInventory && $checkInventory->unique_id) {
                        $inboundDetail->wh_asset_number = $checkInventory->unique_id;
                    } else {
                        $inboundDetail->wh_asset_number = self::generateUniqueId(null, $usedIds);
                    }
                }

                $usedIds[] = $inboundDetail->wh_asset_number;

                $inboundDetail->storage_level_id = $storageLevelId;
                $inboundDetail->save();

                // Update or Create Inventory Data
                if ($checkInventory) {
                    $inventoryId = $checkInventory->id;
                    $checkInventory->update([
                        'unique_id'         => $inboundDetail->wh_asset_number,
                        'client_id'         => $inbound->client_id,
                        'storage_level_id'  => $storageLevelId,
                        'product_id'        => $inboundDetail->product_id,
                        'brand_id'          => $inboundDetail->brand_id,
                        'product_group_id'  => $inboundDetail->product_group_id,
                        'qty'               => 1,
                        'part_name'         => $inboundDetail->part_name,
                        'part_number'       => $inboundDetail->part_number,
                        'part_description'  => $inboundDetail->description,
                        'serial_number'     => $inboundDetail->serial_number,
                        'parent_serial_number' => $inboundDetail->parent_sn ?? ($inboundDetail->old_serial_number ?? $checkInventory->parent_serial_number),
                        'status'            => 'available',
                        'condition'         => $inboundDetail->condition,
                    ]);
                } else {
                    $createInventory = Inventory::create([
                        'unique_id'         => $inboundDetail->wh_asset_number,
                        'client_id'         => $inbound->client_id,
                        'storage_level_id'  => $storageLevelId,
                        'product_id'        => $inboundDetail->product_id,
                        'brand_id'          => $inboundDetail->brand_id,
                        'product_group_id'  => $inboundDetail->product_group_id,
                        'qty'               => 1,
                        'part_name'         => $inboundDetail->part_name,
                        'part_number'       => $inboundDetail->part_number,
                        'part_description'  => $inboundDetail->description,
                        'serial_number'     => $inboundDetail->serial_number,
                        'parent_serial_number' => $inboundDetail->parent_sn ?? $inboundDetail->old_serial_number,
                        'status'            => 'available',
                        'condition'         => $inboundDetail->condition,
                    ]);
                    $inventoryId = $createInventory->id;
                }

                InventoryDetail::create([
                    'inventory_id'      => $inventoryId,
                    'inbound_detail_id' => $id,
                ]);

                // Record History for Put Away
                \App\Models\InventoryMovement::create([
                    'inventory_id' => $inventoryId,
                    'from_storage_level_id' => null, // Initial placement from staging
                    'to_storage_level_id' => $storageLevelId,
                    'user_id' => Auth::id(),
                    'type' => 'Put Away',
                    'description' => 'Initial Put Away from Staging by ' . Auth::user()->name
                ]);

                // Record to Unified History
                $storage = \App\Models\StorageLevel::with('bin.rak.zone')->find($storageLevelId);
                $locationName = $storage ? "{$storage->bin->rak->zone->name}-{$storage->bin->rak->name}-{$storage->bin->name}-{$storage->name}" : 'N/A';

                \App\Models\InventoryHistory::create([
                    'inventory_id' => $inventoryId,
                    'serial_number' => $inboundDetail->serial_number,
                    'type' => 'Inbound',
                    'category' => 'Put Away',
                    'reference_number' => $inbound->number,
                    'description' => 'Item moved from Receiving Staging to ' . $locationName,
                    'user' => Auth::user()->name,
                    'to_location' => $locationName
                ]);

                // Automatically clear Old SN in Outbound if this SN was a replacement target
                // This satisfies the request to have Old SN "disappear" once it's back in WH
                \App\Models\OutboundDetail::where('old_serial_number', $inboundDetail->serial_number)
                    ->update(['old_serial_number' => null]);
            }

            // Check if all products in this inbound are already put away
            $detail = InboundDetail::findOrFail($products[0]);
            $inboundId = $detail->inbound_id;

            $remaining = InboundDetail::where('inbound_id', $inboundId)
                ->whereNull('storage_level_id')
                ->count();

            if ($remaining === 0) {
                Inbound::where('id', $inboundId)->update(['status' => 'close']);
            }

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            Log::info($err->getLine());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        } finally {
            DB::statement("SELECT RELEASE_LOCK('put_away_unique_id')");
        }
    }

    public function cancelPutAway(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $inboundId = $request->post('id');
            $inbound = Inbound::with('details')->findOrFail($inboundId);

            // Fetch items that have NOT been put away
            $pendingDetails = InboundDetail::where('inbound_id', $inboundId)
                ->whereNull('storage_level_id')
                ->get();

            $finishedDetailsCount = InboundDetail::where('inbound_id', $inboundId)
                ->whereNotNull('storage_level_id')
                ->count();

            if ($pendingDetails->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No pending items found to cancel.']);
            }

            if ($finishedDetailsCount === 0) {
                // If no items were processed yet, cancel the entire Inbound record
                $inbound->update(['status' => 'cancel']);
            } else {
                // Partial cancel: Splitting Inbound (PO/PA)
                $cancelledInbound = $inbound->replicate();
                $cancelledInbound->number = $inbound->number . '-CANCEL';
                $cancelledInbound->qty = $pendingDetails->count();
                $cancelledInbound->status = 'cancel';
                $cancelledInbound->save();

                // Move pending details to the new cancelled inbound
                foreach ($pendingDetails as $detail) {
                    $detail->update(['inbound_id' => $cancelledInbound->id]);

                    // Add historical record to Unified History
                    \App\Models\InventoryHistory::create([
                        'inventory_id' => null,
                        'serial_number' => $detail->serial_number,
                        'type' => 'Inbound',
                        'category' => $cancelledInbound->category,
                        'reference_number' => $cancelledInbound->number,
                        'description' => "Put Away cancelled partially. Moved from {$inbound->number} to {$cancelledInbound->number}",
                        'user' => Auth::user()->name,
                    ]);
                }

                // Update original inbound to reflect only the finished items count
                $inbound->update([
                    'qty' => $finishedDetailsCount,
                    'status' => 'close'
                ]);
            }

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::error("Cancel Put Away Error: " . $err->getMessage());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function bulkImport(): View
    {
        $client = Client::all();
        $title = "Bulk Import Receiving";
        return view('inbound.receiving.bulk-import', compact('title', 'client'));
    }

    public function bulkImportStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'client_id'  => 'nullable',
            'receivings' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $clientId = $request->post('client_id') ?: null;
            $receivings = $request->post('receivings');
            $receivedBy = Auth::check() ? Auth::user()->name : 'System';

            $year = date('Y');
            $monthRoman = self::getRomanMonth(date('n'));
            $format = "/RN/WH/$monthRoman/$year";

            $lastInbound = Inbound::where('number', 'like', "%$format")->orderBy('number', 'desc')->first();
            $lastSerial = 0;
            if ($lastInbound) {
                $parts = explode('/', $lastInbound->number);
                $lastSerial = (int) $parts[0];
            }

            $lastInboundDn = Inbound::where('tks_dn_number', 'like', "%$format")->orderBy('tks_dn_number', 'desc')->first();
            $lastSerialDn = 0;
            if ($lastInboundDn) {
                $partsDn = explode('/', $lastInboundDn->tks_dn_number);
                $lastSerialDn = (int) $partsDn[0];
            }

            foreach ($receivings as $rec) {
                $category = $rec['category'] ?? 'New PO';
                $sapPo = $rec['sap_po_number'] ?? null;
                if ($sapPo) {
                    $sapPo = preg_replace('/\D/', '', $sapPo);
                }
                $itsm = $rec['itsm_number'] ?? null;
                $receiveDate = $rec['receivedDate'] ?? date('Y-m-d');
                $products = $rec['products'] ?? [];

                // Simple check if products exist
                if (empty($products)) continue;

                $lastSerial++;
                $newNumber = str_pad($lastSerial, 6, '0', STR_PAD_LEFT) . $format;

                $lastSerialDn++;
                $newDnNumber = str_pad($lastSerialDn, 6, '0', STR_PAD_LEFT) . $format;

                // Determine request type based on category roughly
                $requestType = 'New PO';
                if (stripos($category, 'RMA') !== false || stripos($category, 'Faulty') !== false || stripos($category, 'Replacement') !== false) {
                    $requestType = 'RMA';
                } elseif (stripos($category, 'Loan') !== false) {
                    $requestType = 'Loan';
                } elseif (stripos($category, 'Write-off') !== false) {
                    $requestType = 'Spare Write Off';
                } elseif (stripos($category, 'Migration') !== false) {
                    $requestType = 'Spare Migration';
                }

                $inbound = Inbound::create([
                    'category'              => $category,
                    'request_type'          => $requestType,
                    'client_id'             => $clientId,
                    'number'                => $newNumber,
                    'receiving_note'        => '-', // Not provided in Bulk Upload
                    'sttb'                  => '-', // Not provided
                    'sap_po_number'         => $sapPo,
                    'itsm_number'           => $itsm,
                    'tks_dn_number'         => $newDnNumber,
                    'vendor'                => 'Internal',
                    'qty'                   => count($products),
                    'received_date'         => $receiveDate,
                    'received_by'           => $receivedBy,
                    'remarks'               => $rec['remarks'] ?? 'Bulk Imported',
                    'status'                => 'new'
                ]);

                $this->storeDetails($inbound, $products);
            }

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::error("Bulk Import Store Error: " . $err->getMessage());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function create(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createSpare(): View
    {
        return $this->create();
    }
    public function createFaulty(): View
    {
        return $this->create();
    }
    public function createRma(): View
    {
        return $this->create();
    }
    public function createRelokasi(): View
    {
        return $this->create();
    }
    public function createNewPO(): View
    {
        return $this->create();
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'category'        => 'required',
            'request_type'    => 'nullable',
            'client_id'       => 'nullable',
            'number'          => 'nullable', // NTT RN#
            'po_number'       => 'nullable', // Transkargo SN / PO
            'sap_po_number'   => 'nullable',
            'ecapex_number'   => 'nullable',
            'sttb'            => 'nullable',
            'receivedDate'    => 'required',
            'receivedBy'      => 'required',
            'products'        => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'              => $request->post('category'),
                'request_type'          => $request->post('request_type'),
                'ntt_requestor'         => $request->post('ntt_requestor'),
                'request_date'          => $request->post('request_date'),
                'client_id'             => $request->post('client_id') ?: null,
                'client_contact'        => $request->post('client_contact'),
                'pickup_address'        => $request->post('pickup_address'),
                'number'                => $request->post('po_number') ?? self::generateInboundNumber(),
                'receiving_note'        => $request->post('number'), // NTT RN#
                'sttb'                  => $request->post('sttb'),
                'courier_delivery_note' => $request->post('delivery_note'),
                'courier_invoice'       => $request->post('courier_invoice'),
                'rma_number'            => $request->post('rma_number'),
                'itsm_number'           => $request->post('itsm_number'),
                'sap_po_number'         => $request->post('sap_po_number') ? preg_replace('/\D/', '', $request->post('sap_po_number')) : null,
                'ecapex_number'         => $request->post('ecapex_number'),
                'vendor_dn_number'      => $request->post('vendor_dn_number'),
                'tks_dn_number'         => $request->post('tks_dn_number') ?? self::generateTksDnNumber(),
                'tks_invoice_number'    => $request->post('tks_invoice_number'),
                'vendor'                => $request->post('vendor') ?? 'Internal',
                'qty'                   => count($request->post('products')),
                'received_date'         => $request->post('receivedDate'),
                'received_by'           => $request->post('receivedBy'),
                'remarks'               => $request->post('remarks'),
                'status'                => 'new'
            ]);

            $this->storeDetails($inbound, $request->post('products'));

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function storeSpare(Request $request)
    {
        return $this->store($request);
    }
    public function storeNewPO(Request $request)
    {
        return $this->store($request);
    }
    public function storeFaulty(Request $request)
    {
        return $this->store($request);
    }
    public function storeRma(Request $request)
    {
        return $this->store($request);
    }
    public function storeRelokasi(Request $request)
    {
        return $this->store($request);
    }

    private function storeDetails($inbound, $products)
    {
        foreach ($products as $product) {
            $brand = Brand::firstOrCreate(['name' => $product['brand']]);
            $productGroup = ProductGroup::firstOrCreate(['name' => $product['productGroup']]);

            $partName = $product['partName'] ?: ($product['partNumber'] ?: ($product['partDescription'] ?: 'Unknown Product'));

            $findProduct = Product::where('part_name', $partName)
                ->where('brand_id', $brand->id)
                ->where('product_group_id', $productGroup->id)
                ->first();

            if ($findProduct) {
                $productId = $findProduct->id;
            } else {
                $createProduct = Product::create([
                    'part_name'         => $partName,
                    'brand_id'          => $brand->id,
                    'product_group_id'  => $productGroup->id,
                ]);
                $productId = $createProduct->id;
            }

            $serialNumber = $product['serialNumber'] ?? null;
            if (empty($serialNumber)) {
                $serialNumber = 'TKS_' . strtoupper(Str::random(10));
            }

            InboundDetail::create([
                'inbound_id'        => $inbound->id,
                'product_id'        => $productId,
                'part_name'         => $product['partName'],
                'part_number'       => $product['partNumber'],
                'description'       => $product['partDescription'] ?? '',
                'qty'               => 1,
                'wh_asset_number'   => $product['whAssetNumber'] ?? null,
                'serial_number'     => $serialNumber,
                'old_serial_number' => $product['oldSerialNumber'] ?? null,
                'old_wh_asset_number' => $product['oldWhAsset'] ?? null,
                'parent_sn'         => $product['parentSn'] ?? null,
                'condition'         => $product['condition'],
                'stock_status'      => $product['stockStatus'] ?? 'Available',
                'staging_date'      => $product['stagingDate'] ?? null,
                'receiving_remarks' => $product['remarks'] ?? null,
                'storage_level_id'  => null,
                'brand_id'          => $brand->id,
                'product_group_id' => $productGroup->id,
            ]);

            \App\Models\InventoryHistory::create([
                'inventory_id' => null, // Linked later during Put Away
                'serial_number' => $serialNumber,
                'type' => 'Receiving',
                'category' => $inbound->category,
                'reference_number' => $inbound->number,
                'description' => "Received item via {$inbound->category} (Ref: {$inbound->number})" . (isset($product['parentSn']) && $product['parentSn'] ? " - Linked to SN: {$product['parentSn']}" : ""),
                'user' => $inbound->received_by,
            ]);
        }
    }

    /**
     * Show Quick Return (Back to WH) page
     */
    public function quickReturn(): View
    {
        $title = 'Back to WH';
        return view('inbound.receiving.back-to-wh.index', compact('title'));
    }

    /**
     * Process Quick Return (Back to WH) - Single & Batch
     */
    public function quickReturnStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $isBatch = $request->post('batch') === true || $request->post('batch') === 'true';

        if ($isBatch) {
            $request->validate([
                'serial_numbers' => 'required|array|min:1|max:100',
                'serial_numbers.*' => 'required|string',
                'return_type' => 'required|in:in,available',
                'condition' => 'nullable|string',
            ]);
            return $this->processBatchReturn($request);
        }

        $request->validate([
            'serial_number' => 'required|string',
            'return_type' => 'required|in:in,available',
            'condition' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $result = $this->processSingleReturn(
                $request->post('serial_number'),
                $request->post('return_type'),
                $request->post('condition')
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'data'   => $result,
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::error("Quick Return Error: " . $err->getMessage());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    /**
     * Process batch return
     */
    private function processBatchReturn(Request $request): \Illuminate\Http\JsonResponse
    {
        $serialNumbers = $request->post('serial_numbers', []);
        $returnType = $request->post('return_type');
        $newCondition = $request->post('condition');

        $results = [];

        try {
            DB::beginTransaction();

            foreach ($serialNumbers as $sn) {
                try {
                    $result = $this->processSingleReturn($sn, $returnType, $newCondition);
                    $results[] = [
                        'success'         => true,
                        'serial_number'   => $sn,
                        'inbound_number'  => $result['inbound_number'],
                        'part_name'       => $result['part_name'],
                        'condition'       => $result['condition'],
                        'old_location'    => $result['old_location'],
                        'error'           => null,
                    ];
                } catch (\Throwable $itemErr) {
                    $results[] = [
                        'success'        => false,
                        'serial_number'  => $sn,
                        'inbound_number' => null,
                        'part_name'      => null,
                        'condition'      => null,
                        'old_location'   => null,
                        'error'          => $itemErr->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'results' => $results,
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::error("Batch Return Error: " . $err->getMessage());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    /**
     * Process a single item return
     *
     * @throws \Throwable
     */
    private function processSingleReturn(string $serialNumber, string $returnType, ?string $newCondition = null): array
    {
        $user = Auth::user();

        // Find the inventory record
        $inventory = Inventory::where('serial_number', $serialNumber)->first();

        if (!$inventory) {
            throw new \RuntimeException('SN ' . $serialNumber . ' tidak ditemukan di database.');
        }

        // Check if it's already in-stock (qty > 0)
        if ($inventory->qty > 0) {
            throw new \RuntimeException('SN ' . $serialNumber . ' masih IN STOCK (qty=' . $inventory->qty . '). Tidak perlu di-return.');
        }

        // Auto-create Inbound record (one per item for individual tracking)
        $inbound = Inbound::create([
            'category'      => 'Spare Return',
            'request_type'  => 'Return',
            'client_id'     => $inventory->client_id,
            'number'        => self::generateInboundNumber(),
            'tks_dn_number' => self::generateTksDnNumber(),
            'vendor'        => 'Internal',
            'qty'           => 1,
            'received_date' => now()->toDateString(),
            'received_by'   => $user->name,
            'remarks'       => 'Quick Return - Back to WH (SN: ' . $serialNumber . ')',
            'status'        => ($returnType === 'available') ? 'close' : 'process qc',
        ]);

        // Determine storage and condition
        $existingStorageId = $inventory->storage_level_id;
        $condition = $newCondition ?: ($inventory->condition ?? 'Good');

        $existingLocation = 'N/A';
        if ($inventory->storageLevel && $inventory->storageLevel->bin && $inventory->storageLevel->bin->rak && $inventory->storageLevel->bin->rak->zone) {
            $existingLocation = $inventory->storageLevel->bin->rak->zone->name . '-' .
                $inventory->storageLevel->bin->rak->name . '-' .
                $inventory->storageLevel->bin->name . '-' .
                $inventory->storageLevel->name;
        }

        // Auto-create InboundDetail
        $inboundDetail = InboundDetail::create([
            'inbound_id'        => $inbound->id,
            'product_id'        => $inventory->product_id,
            'part_name'         => $inventory->part_name,
            'part_number'       => $inventory->part_number,
            'description'       => $inventory->part_description ?: $inventory->part_number,
            'qty'               => 1,
            'wh_asset_number'   => $inventory->unique_id,
            'serial_number'     => $serialNumber,
            'parent_sn'         => $inventory->parent_serial_number,
            'condition'         => $condition,
            'stock_status'      => 'Available',
            'storage_level_id'  => ($returnType === 'available') ? $existingStorageId : null,
            'brand_id'          => $inventory->brand_id,
            'product_group_id'  => $inventory->product_group_id,
            'staging_date'      => now()->toDateString(),
            'receiving_remarks' => 'Quick Return - ' . ($returnType === 'available' ? 'Bypass PA' : 'Need Put Away'),
        ]);

        // Record receiving history
        \App\Models\InventoryHistory::create([
            'inventory_id'     => $inventory->id,
            'serial_number'    => $serialNumber,
            'type'             => 'Receiving',
            'category'         => 'Spare Return',
            'reference_number' => $inbound->number,
            'description'      => 'Quick Return (Back to WH) - SN: ' . $serialNumber . ' (' . ($returnType === 'available' ? 'Bypass PA' : 'Need Put Away') . ')',
            'user'             => $user->name,
            'to_location'      => $returnType === 'available' ? ($existingStorageId ? $existingLocation : 'N/A') : 'Staging (Pending PA)',
        ]);

        // Update Inventory
        $inventoryUpdate = [
            'qty'                => 1,
            'status'             => 'available',
            'last_movement_date' => now(),
        ];

        if ($newCondition) {
            $inventoryUpdate['condition'] = $newCondition;
        }

        $inventory->update($inventoryUpdate);

        // If bypass PA, create full links and history
        if ($returnType === 'available') {
            InventoryDetail::create([
                'inventory_id'      => $inventory->id,
                'inbound_detail_id' => $inboundDetail->id,
            ]);

            \App\Models\InventoryMovement::create([
                'inventory_id'          => $inventory->id,
                'from_storage_level_id' => null,
                'to_storage_level_id'   => $existingStorageId,
                'user_id'               => Auth::id(),
                'type'                  => 'Put Away',
                'description'           => 'Auto Put Away (Return Bypass) by ' . $user->name
            ]);

            \App\Models\InventoryHistory::create([
                'inventory_id'     => $inventory->id,
                'serial_number'    => $serialNumber,
                'type'             => 'Inbound',
                'category'         => 'Put Away',
                'reference_number' => $inbound->number,
                'description'      => 'Item returned to ' . $existingLocation . ' (Bypass Put Away)',
                'user'             => $user->name,
                'to_location'      => $existingLocation,
            ]);

            // Auto-clear old SN from Outbound
            \App\Models\OutboundDetail::where('old_serial_number', $serialNumber)
                ->update(['old_serial_number' => null]);
        }

        return [
            'inbound_id'       => $inbound->id,
            'inbound_number'   => $inbound->number,
            'serial_number'    => $serialNumber,
            'part_name'        => $inventory->part_name,
            'return_type'      => $returnType,
            'condition'        => $condition,
            'old_location'     => $returnType === 'available' ? ($existingStorageId ? $existingLocation : 'N/A') : 'Pending Put Away',
        ];
    }

    public function checkOutbounded(Request $request)
    {
        $search = $request->get('search');
        if (!$search) {
            return response()->json(['status' => false, 'message' => 'Search term is required']);
        }

        $inventory = \App\Models\Inventory::with(['brand', 'productGroup', 'client', 'storageLevel.bin.rak.zone'])->where('serial_number', $search)
            ->orWhere('unique_id', $search)
            ->first();

        if (!$inventory) {
            return response()->json(['status' => false, 'message' => 'Item tidak ditemukan di database.']);
        }

        $isOutbounded = ($inventory->qty == 0) || \App\Models\OutboundDetail::where('serial_number', $inventory->serial_number)->exists();

        if (!$isOutbounded) {
            return response()->json([
                'status' => false,
                'message' => 'Item dengan SN ' . $inventory->serial_number . ' masih IN STOCK (qty=' . $inventory->qty . '). Tidak perlu di-return.',
            ]);
        }

        $location = 'N/A';
        if ($inventory->storageLevel && $inventory->storageLevel->bin && $inventory->storageLevel->bin->rak && $inventory->storageLevel->bin->rak->zone) {
            $location = $inventory->storageLevel->bin->rak->zone->name . '-' .
                $inventory->storageLevel->bin->rak->name . '-' .
                $inventory->storageLevel->bin->name . '-' .
                $inventory->storageLevel->name;
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'serial_number'     => $inventory->serial_number,
                'unique_id'         => $inventory->unique_id,
                'part_name'         => $inventory->part_name,
                'part_number'       => $inventory->part_number,
                'part_description'  => $inventory->part_description,
                'brand'             => $inventory->brand->name ?? '-',
                'product_group'     => $inventory->productGroup->name ?? '-',
                'client'            => $inventory->client->name ?? '-',
                'condition'         => $inventory->condition,
                'old_location'      => $location,
            ]
        ]);
    }

    public function searchOutbounded(Request $request)
    {
        $search = $request->get('search');
        $clientId = $request->get('client_id');

        \Log::info("Search Outbounded called", ['search' => $search, 'client_id' => $clientId]);

        $query = \App\Models\Inventory::query();
        
        // Ensure it has been outbounded (qty 0)
        $query->where('qty', 0);

        if ($clientId) {
            $query->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereNull('client_id');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%$search%")
                    ->orWhere('unique_id', 'like', "%$search%")
                    ->orWhere('part_name', 'like', "%$search%")
                    ->orWhere('part_number', 'like', "%$search%");
            });
        }

        $results = $query->with(['brand', 'productGroup'])->limit(30)->get();

        \Log::info("Search Results count: " . $results->count());

        return response()->json([
            'status' => true,
            'results' => $results->map(function ($item) {
                return [
                    'id' => $item->serial_number,
                    'text' => $item->serial_number . ' | ' . $item->unique_id . ' (' . ($item->part_name ?: $item->part_number) . ')',
                    'serial_number' => $item->serial_number,
                    'unique_id' => $item->unique_id,
                    'part_name' => $item->part_name,
                    'part_number' => $item->part_number,
                    'part_description' => $item->part_description,
                    'brand' => $item->brand->name ?? '-',
                    'product_group' => $item->productGroup->name ?? '-',
                ];
            })
        ]);
    }

    public function lookupPartNumber(Request $request)
    {
        $partNumber = $request->get('part_number');
        if (!$partNumber) {
            return response()->json([
                'found' => false,
                'message' => 'Part Number is required'
            ]);
        }

        // Try to find the latest Inventory item with this part number and a valid description
        $item = \App\Models\Inventory::with(['brand', 'productGroup'])
            ->where('part_number', $partNumber)
            ->whereNotNull('part_description')
            ->where('part_description', '!=', '')
            ->latest('id')
            ->first();

        if ($item) {
            return response()->json([
                'found'            => true,
                'part_description' => $item->part_description,
                'part_name'        => $item->part_name,
                'brand'            => $item->brand->name ?? '',
                'product_group'    => $item->productGroup->name ?? '',
            ]);
        }

        // Fallback: search in InboundDetail for historical receiving data
        $inboundDetail = \App\Models\InboundDetail::with(['brand', 'productGroup'])
            ->where('part_number', $partNumber)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->latest('id')
            ->first();

        if ($inboundDetail) {
            return response()->json([
                'found'            => true,
                'part_description' => $inboundDetail->description,
                'part_name'        => $inboundDetail->part_name,
                'brand'            => $inboundDetail->brand->name ?? '',
                'product_group'    => $inboundDetail->productGroup->name ?? '',
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Part Number not found in master data'
        ]);
    }
}
