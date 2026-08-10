<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Brand;
use App\Models\ProductGroup;
use App\Models\StorageLevel;

class InventoryController extends Controller
{
    private function applyClientFilter($query, $clientId, $column = 'client_id')
    {
        $user = Auth::user();
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $query->where($column, $clientId);
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $query->where($column, $clientId);
            } else {
                $query->where(function ($q) use ($column, $accessibleIds) {
                    $q->whereIn($column, $accessibleIds)
                        ->orWhereNull($column);
                });
            }
        }
        return $query;
    }

    /**
     * Build the common filter chain used by index, exportPdf, exportExcel.
     */
    private function applyFilters($query, array $filter, ?string $quickSearch = null)
    {
        // Helper to extract single or multi filter value
        $fv = function ($key) use ($filter) {
            return $filter[$key] ?? [];
        };

        // Text columns — support both string and array
        foreach (['unique_id', 'serial_number', 'parent_serial_number', 'part_number', 'part_description', 'location', 'check_date', 'activity'] as $col) {
            $val = $fv($col);
            if (empty($val)) continue;
            $values = is_array($val) ? $val : [$val];
            $values = array_filter($values, fn($v) => $v !== '' && $v !== null);

            if (empty($values)) continue;

            if ($col === 'location') {
                $query->where(function ($q) use ($values) {
                    foreach ($values as $s) {
                        $q->orWhereHas('storageLevel.bin.rak.zone', function ($sq) use ($s) {
                            $sq->where('storage_zone.name', 'like', "%$s%")
                                ->orWhere('storage_rak.name', 'like', "%$s%")
                                ->orWhere('storage_bin.name', 'like', "%$s%")
                                ->orWhere('storage_level.name', 'like', "%$s%");
                        });
                    }
                });
            } elseif ($col === 'check_date') {
                $query->where(function ($q) use ($values) {
                    foreach ($values as $v) {
                        $q->orWhere('last_staging_date', 'like', "%$v%");
                    }
                });
            } elseif ($col === 'activity') {
                $query->where(function ($q) use ($values) {
                    foreach ($values as $v) {
                        $q->orWhere('last_movement_date', 'like', "%$v%");
                    }
                });
            } else {
                // Direct DB column — use WHERE IN for exact match (checkbox values)
                $query->whereIn($col, $values);
            }
        }

        // Brand (relationship)
        $brandVal = $fv('brand');
        if (!empty($brandVal)) {
            $brandIds = is_array($brandVal) ? $brandVal : [$brandVal];
            $brandIds = array_filter($brandIds, fn($v) => $v !== '' && $v !== null);
            if (!empty($brandIds)) {
                $query->whereHas('product.brand', function ($q) use ($brandIds) {
                    $q->whereIn('brands.id', $brandIds);
                });
            }
        }

        // Group (relationship)
        $groupVal = $fv('group');
        if (!empty($groupVal)) {
            $groupIds = is_array($groupVal) ? $groupVal : [$groupVal];
            $groupIds = array_filter($groupIds, fn($v) => $v !== '' && $v !== null);
            if (!empty($groupIds)) {
                $query->whereHas('product.productGroup', function ($q) use ($groupIds) {
                    $q->whereIn('product_groups.id', $groupIds);
                });
            }
        }

        // Exact-match columns (condition, staging_condition, status)
        foreach (['condition', 'staging_condition', 'status'] as $col) {
            $val = $fv($col);
            if (empty($val)) continue;
            $values = is_array($val) ? $val : [$val];
            $values = array_filter($values, fn($v) => $v !== '' && $v !== null);
            if (!empty($values)) {
                $query->whereIn($col, $values);
            }
        }

        // Quick Search: multi SN / Asset ID (paste multiple values)
        if ($quickSearch && trim($quickSearch) !== '') {
            $terms = preg_split('/[\r\n,; ]+/', $quickSearch);
            $terms = array_map('trim', $terms);
            $terms = array_filter($terms, fn($v) => $v !== '');

            if (!empty($terms)) {
                $query->where(function ($q) use ($terms) {
                    $q->whereIn('serial_number', $terms)
                      ->orWhereIn('unique_id', $terms);
                });
            }
        }

        return $query;
    }

    /** Allowed sort columns mapped to DB columns or subqueries */
    private function applySorting($query, ?string $sortField, string $sortDirection = 'asc')
    {
        $dir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $directMap = [
            'unique_id'          => 'unique_id',
            'serial_number'      => 'serial_number',
            'part_name'          => 'part_name',
            'part_number'        => 'part_number',
            'part_description'   => 'part_description',
            'condition'          => 'condition',
            'staging_condition'  => 'staging_condition',
            'status'             => 'status',
            'last_staging_date'  => 'last_staging_date',
            'last_movement_date' => 'last_movement_date',
            'created_at'         => 'created_at',
        ];

        if ($sortField && isset($directMap[$sortField])) {
            $query->orderBy($directMap[$sortField], $dir);
        } elseif ($sortField === 'brand') {
            $query->orderBy(
                Brand::select('name')
                    ->whereColumn('brands.id', 'products.brand_id')
                    ->whereColumn('products.id', 'inventory.product_id')
                    ->limit(1),
                $dir
            );
        } elseif ($sortField === 'group') {
            $query->orderBy(
                ProductGroup::select('name')
                    ->whereColumn('product_groups.id', 'products.product_group_id')
                    ->whereColumn('products.id', 'inventory.product_id')
                    ->limit(1),
                $dir
            );
        } elseif ($sortField === 'location') {
            $query->orderBy(
                StorageLevel::selectRaw("CONCAT_WS('-', sz.name, sr.name, sb.name, storage_level.name)")
                    ->leftJoin('storage_bin as sb', 'sb.id', 'storage_level.storage_bin_id')
                    ->leftJoin('storage_rak as sr', 'sr.id', 'sb.storage_rak_id')
                    ->leftJoin('storage_zone as sz', 'sz.id', 'sr.storage_zone_id')
                    ->whereColumn('storage_level.id', 'inventory.storage_level_id')
                    ->limit(1),
                $dir
            );
        } else {
            // Default sort: group by parent-child relationship
            // Items without parent (parent_serial_number = NULL) use their own serial_number
            // Items with parent use the parent's serial_number, so children appear near their parent
            $query->orderByRaw('COALESCE(parent_serial_number, serial_number) ASC')
                  ->orderBy('serial_number', 'ASC');
        }

        return $query;
    }

    /** Apply date range filter (created_at) */
    private function applyDateFilter($query, Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        if ($dateFrom && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $query->whereDate('inventory.created_at', '>=', $dateFrom);
        }
        if ($dateTo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $query->whereDate('inventory.created_at', '<=', $dateTo);
        }

        return $query;
    }

    public function scan($unique_id)
    {
        $inventory = Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->where('unique_id', $unique_id)
            ->firstOrFail();

        $sn = $inventory->serial_number;

        $history = \App\Models\InventoryHistory::where('serial_number', $sn)
            ->latest()
            ->get();

        return view('inventory.scan', compact('inventory', 'history'));
    }

    public function index(Request $request): View
    {
        $title = 'Inventory List';
        $clientId = $request->get('client_id');
        $user = Auth::user();
        $filter = $request->get('filter', []);
        $sortField = $request->get('sort_field');
        $sortDirection = $request->get('sort_direction', 'asc');
        $quickSearch = $request->get('quick_search');

        $query = Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup', 'details.inboundDetail']);

        $this->applyClientFilter($query, $clientId);
        $this->applyFilters($query, $filter, $quickSearch);
        $this->applyDateFilter($query, $request);
        $this->applySorting($query, $sortField, $sortDirection);

        $inventory = $query->paginate(15)->appends(request()->query());

        // Collect all serial numbers that are referenced as parent (for parent badge display)
        $parentSns = \App\Models\Inventory::whereNotNull('parent_serial_number')
            ->where('parent_serial_number', '!=', '')
            ->distinct()
            ->pluck('parent_serial_number')
            ->toArray();

        $clients = $user->getAvailableClients();

        return view('inventory.inventory-list.index', compact(
            'title', 'inventory', 'clients', 'sortField', 'sortDirection', 'parentSns'
        ));
    }

    /**
     * API: Return distinct values for a column (used by Excel-style filter dropdown)
     */
    public function filterValues(Request $request)
    {
        $column = $request->get('column');
        $search = $request->get('search', '');
        $clientId = $request->get('client_id');

        $allowed = ['unique_id', 'serial_number', 'parent_serial_number', 'part_number', 'part_description',
                     'brand', 'group', 'location',
                     'condition', 'staging_condition', 'status',
                     'last_staging_date', 'last_movement_date'];

        if (!in_array($column, $allowed)) {
            return response()->json(['column' => $column, 'values' => []]);
        }

        $query = Inventory::query();
        $this->applyClientFilter($query, $clientId);

        if ($column === 'brand') {
            $items = $query->clone()
                ->whereHas('product.brand', fn($q) => $q->when($search, fn($sq) => $sq->where('name', 'like', "%$search%")))
                ->with('product.brand')
                ->get()
                ->pluck('product.brand')
                ->filter()
                ->unique('id')
            ->values()
            ->map(fn($b) => ['value' => (string)$b->id, 'label' => $b->name, 'count' => 0]);

            return response()->json(['column' => $column, 'values' => $items]);
        }

        if ($column === 'group') {
            $items = $query->clone()
                ->whereHas('product.productGroup', fn($q) => $q->when($search, fn($sq) => $sq->where('name', 'like', "%$search%")))
                ->with('product.productGroup')
                ->get()
                ->pluck('product.productGroup')
                ->filter()
                ->unique('id')
            ->values()
            ->map(fn($g) => ['value' => (string)$g->id, 'label' => $g->name, 'count' => 0]);

            return response()->json(['column' => $column, 'values' => $items]);
        }

        if ($column === 'location') {
            $items = $query->clone()
                ->whereHas('storageLevel.bin.rak.zone')
                ->with('storageLevel.bin.rak.zone')
                ->get()
                ->map(function ($item) {
                    if ($item->storageLevel) {
                        return [
                            'value' => "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}",
                            'label' => "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}",
                        ];
                    }
                    return null;
                })
                ->filter()
                ->unique('value')
                ->values();

            if ($search) {
                $items = $items->filter(fn($v) => stripos($v['value'], $search) !== false)->values();
            }

            return response()->json(['column' => $column, 'values' => $items]);
        }

        // Direct DB column
        $rawQuery = $query->clone()->select($column)->distinct();

        if ($search) {
            $rawQuery->where($column, 'like', "%$search%");
        }

        $values = $rawQuery->limit(200)->pluck($column)->filter()->sort()->values()->map(function ($v) {
            return ['value' => $v, 'label' => $v];
        });

        return response()->json(['column' => $column, 'values' => $values]);
    }

    public function exportPdf(Request $request): View
    {
        $clientId = $request->get('client_id');
        $filter = $request->get('filter', []);
        $sortField = $request->get('sort_field');
        $sortDirection = $request->get('sort_direction', 'asc');
        $quickSearch = $request->get('quick_search');

        $query = Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup']);

        $this->applyClientFilter($query, $clientId);
        $this->applyFilters($query, $filter, $quickSearch);
        $this->applyDateFilter($query, $request);
        $this->applySorting($query, $sortField, $sortDirection);

        $inventory = $query->get();

        $title = 'Inventory List Report';
        return view('inventory.inventory-list.pdf', compact('title', 'inventory'));
    }

    public function exportExcel(Request $request)
    {
        $clientId = $request->get('client_id');
        $filter = $request->get('filter', []);
        $sortField = $request->get('sort_field');
        $sortDirection = $request->get('sort_direction', 'asc');
        $quickSearch = $request->get('quick_search');

        $query = Inventory::with([
            'storageLevel.bin.rak.zone',
            'client',
            'product.brand',
            'product.productGroup',
            'details.inboundDetail.inbound',
        ]);

        $this->applyClientFilter($query, $clientId);
        $this->applyFilters($query, $filter, $quickSearch);
        $this->applyDateFilter($query, $request);
        $this->applySorting($query, $sortField, $sortDirection);

        $inventory = $query->get();

        // Collect SNs to batch-fetch outbound data
        $sns = $inventory->pluck('serial_number')->toArray();
        $outboundDetails = \App\Models\OutboundDetail::with('outbound')
            ->whereIn('serial_number', $sns)
            ->get()
            ->keyBy('serial_number');

        $filename = "inventory-list-" . date('Y-m-d') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $yellow = 'background-color: #FFFF00;';
        $bold = 'font-weight: bold;';

        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th style='{$yellow}{$bold}'>Movement Category</th>";
        echo "<th style='{$yellow}{$bold}'>Stock Category</th>";
        echo "<th style='{$yellow}{$bold}'>Request Type</th>";
        echo "<th style='{$bold}'>NTT Requestor</th>";
        echo "<th style='{$bold}'>Request Date</th>";
        echo "<th style='{$bold}'>Product Group</th>";
        echo "<th style='{$bold}'>Brand</th>";
        echo "<th style='{$bold}'>Product Number (SKU)</th>";
        echo "<th style='{$bold}'>Product Description</th>";
        echo "<th style='{$bold}'>Serial Number (SN)</th>";
        echo "<th style='{$bold}'>Parent SN</th>";
        echo "<th style='{$bold}'>Qty</th>";
        echo "<th style='{$yellow}{$bold}'>WH Asset Number</th>";
        echo "<th style='{$bold}'>Stock Status</th>";
        echo "<th style='{$yellow}{$bold}'>Stock Condition</th>";
        echo "<th style='{$yellow}{$bold}'>Stock Location (Rack ID)</th>";
        echo "<th style='{$bold}'>eCapex#</th>";
        echo "<th style='{$bold}'>SAP PO#</th>";
        echo "<th style='{$bold}'>Vendor/Supplier DN#</th>";
        echo "<th style='{$bold}'>NTT RN#</th>";
        echo "<th style='{$bold}'>Received Date</th>";
        echo "<th style='{$bold}'>NTT DN#</th>";
        echo "<th style='{$bold}'>Delivery Date</th>";
        echo "<th style='{$bold}'>Transkargo DN#</th>";
        echo "<th style='{$bold}'>Transkargo Invoice#</th>";
        echo "<th style='{$bold}'>Staging Date</th>";
        echo "<th style='{$bold}'>ITSM#</th>";
        echo "<th style='{$bold}'>RMA#</th>";
        echo "<th style='{$bold}'>Processed by</th>";
        echo "<th style='{$bold}'>Client Name</th>";
        echo "<th style='{$bold}'>Client Contact</th>";
        echo "<th style='{$bold}'>Pickup/Shipment Address</th>";
        echo "<th style='{$bold}'>Shipment Status</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($inventory as $index => $item) {
            $inboundDetail = $item->details->first()?->inboundDetail;
            $inbound = $inboundDetail?->inbound;

            $outboundDetail = $outboundDetails->get($item->serial_number);
            $outbound = $outboundDetail?->outbound;

            $brand = $item->product?->brand?->name ?? '-';
            $group = $item->product?->productGroup?->name ?? '-';
            $storage = $item->storageLevel
                ? "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}"
                : '-';

            $movementCategory = $inbound?->category ?? ($outbound?->category ?? '-');

            echo "<tr>";
            echo "<td>{$movementCategory}</td>";
            echo "<td>" . ($inbound?->category ?? $outbound?->category ?? '-') . "</td>";
            echo "<td>" . ($inbound?->request_type ?? $outbound?->request_type ?? '-') . "</td>";
            echo "<td>" . ($inbound?->ntt_requestor ?? $outbound?->ntt_requestor ?? '-') . "</td>";
            echo "<td>" . ($inbound?->request_date ?? $outbound?->request_date ?? '-') . "</td>";
            echo "<td>{$group}</td>";
            echo "<td>{$brand}</td>";
            echo "<td>{$item->part_number}</td>";
            echo "<td>" . ($item->part_description ?? '-') . "</td>";
            echo "<td style=\"mso-number-format:\\@;\">{$item->serial_number}</td>";
            echo "<td>" . ($item->parent_serial_number ?? '-') . "</td>";
            echo "<td>{$item->qty}</td>";
            echo "<td>{$item->unique_id}</td>";
            echo "<td>{$item->status}</td>";
            echo "<td>{$item->condition}</td>";
            echo "<td>{$storage}</td>";
            echo "<td>" . ($inbound?->ecapex_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->sap_po_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->vendor_dn_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->receiving_note ?? '-') . "</td>";
            echo "<td>" . ($inbound?->received_date ?? '-') . "</td>";
            echo "<td>" . ($inbound?->ntt_dn_number ?? $outbound?->ntt_dn_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->delivery_date ?? '-') . "</td>";
            echo "<td>" . ($inbound?->tks_dn_number ?? $outbound?->tks_dn_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->tks_invoice_number ?? $outbound?->tks_invoice_number ?? '-') . "</td>";
            echo "<td>" . ($inboundDetail?->staging_date ?? $item->last_staging_date ?? '-') . "</td>";
            echo "<td>" . ($inbound?->itsm_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->rma_number ?? $outbound?->rma_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->received_by ?? $outbound?->outbound_by ?? '-') . "</td>";
            echo "<td>" . ($item->client?->name ?? '-') . "</td>";
            echo "<td>" . ($inbound?->client_contact ?? $outbound?->client_contact ?? '-') . "</td>";
            echo "<td>" . ($inbound?->pickup_address ?? $outbound?->pickup_address ?? '-') . "</td>";
            echo "<td>" . ($inbound?->shipment_status ?? $outbound?->shipment_status ?? '-') . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        exit;
    }

    public function stockMovement(): View
    {
        $title = 'Stock Movement';
        $user = Auth::user();

        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user']);

        if ($user->isAdminWMS()) {
            // No automatic filter
        } else {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $movements->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }

        $movements = $movements->latest()->paginate(20);

        return view('inventory.stock-movement.index', compact('title', 'movements'));
    }

    public function stockMovementPdf()
    {
        $title = 'Stock Movement Report';
        $user = Auth::user();

        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user']);

        if (!$user->isAdminWMS()) {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $movements->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }

        $movements = $movements->latest()->get();

        return view('inventory.stock-movement.pdf', compact('title', 'movements'));
    }

    public function stockMovementExcel()
    {
        $user = Auth::user();
        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user']);

        if (!$user->isAdminWMS()) {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $movements->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }

        $movements = $movements->latest()->get();

        $filename = "stock-movement-" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<table border='1'>";
        echo "<thead><tr><th>No</th><th>Date</th><th>Item</th><th>SN</th><th>From</th><th>To</th><th>User</th><th>Type</th><th>Description</th></tr></thead>";
        echo "<tbody>";
        foreach ($movements as $index => $item) {
            $from = $item->fromStorageLevel ? $item->fromStorageLevel->bin->rak->zone->name . "-" . $item->fromStorageLevel->bin->rak->name . "-" . $item->fromStorageLevel->bin->name . "-" . $item->fromStorageLevel->name : "-";
            $to = $item->toStorageLevel ? $item->toStorageLevel->bin->rak->zone->name . "-" . $item->toStorageLevel->bin->rak->name . "-" . $item->toStorageLevel->bin->name . "-" . $item->toStorageLevel->name : "-";
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . $item->created_at . "</td>";
            echo "<td>" . ($item->inventory->part_name ?? '-') . "</td>";
            echo "<td style=\"mso-number-format:\\@;\">" . ($item->inventory->serial_number ?? '-') . "</td>";
            echo "<td>" . $from . "</td>";
            echo "<td>" . $to . "</td>";
            echo "<td>" . ($item->user->name ?? '-') . "</td>";
            echo "<td>" . $item->type . "</td>";
            echo "<td>" . $item->description . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    public function show($id): View
    {
        $title = 'Inventory Detail';
        $inventory = \App\Models\Inventory::with([
            'storageLevel.bin.rak.zone',
            'client',
            'product.brand',
            'product.productGroup',
            'details.inboundDetail.inbound'
        ])
            ->findOrFail($id);

        $parentInventory = null;
        if ($inventory->parent_serial_number) {
            $parentInventory = \App\Models\Inventory::where('serial_number', $inventory->parent_serial_number)->first();
        }

        $sn = $inventory->serial_number;

        // Fetch unified history
        $history = \App\Models\InventoryHistory::where('serial_number', $sn)
            ->orWhere('serial_number', $inventory->parent_serial_number)
            ->orWhere('description', 'like', '%' . $sn . '%')
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
                    'to_location' => $item->to_location,
                    'sn' => $item->serial_number,
                    'parent_sn' => null
                ];
            });

        foreach ($inventory->details as $detail) {
            if ($detail->inboundDetail) {
                $history->push([
                    'date' => $detail->inboundDetail->created_at,
                    'type' => 'Inbound',
                    'category' => 'Receiving',
                    'reference' => $detail->inboundDetail->inbound->number ?? '-',
                    'description' => 'Received unit into warehouse.',
                    'user' => $detail->inboundDetail->inbound->received_by,
                    'from_location' => $detail->inboundDetail->vendor ?: 'Supplier',
                    'to_location' => 'Inbound Staging',
                    'sn' => $sn,
                    'parent_sn' => $detail->inboundDetail->parent_sn ?? $detail->inboundDetail->old_serial_number
                ]);
            }
        }

        $history = $history->sortByDesc('date')->values();

        return view('inventory.inventory-list.show', compact('title', 'inventory', 'history', 'parentInventory'));
    }

    public function productMovementIndex(): View
    {
        $title = 'Product Movement';
        $user = Auth::user();

        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user'])
            ->where('type', 'Movement');

        if ($user->isAdminWMS()) {
            // No automatic filter
        } else {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $movements->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }

        $movements = $movements->latest()->paginate(20);

        return view('inventory.product-movement.index', compact('title', 'movements'));
    }

    public function productMovementProcess(): View
    {
        $title = 'Product Movement';
        $storageZone = \App\Models\StorageZone::all();

        return view('inventory.product-movement.process', compact('title', 'storageZone'));
    }

    public function productMovementSearch(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search', '');

        $inventory = \App\Models\Inventory::with('client', 'storageLevel.bin.rak.zone')
            ->where('qty', '>', 0);

        if (!$user->isAdminWMS()) {
            $inventory->where(function ($q) use ($user) {
                $q->whereIn('client_id', $user->getAccessibleClientIds())
                    ->orWhereNull('client_id');
            });
        }

        if ($search) {
            $inventory->where(function ($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('unique_id', 'like', "%{$search}%")
                  ->orWhere('old_wh_asset_number', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50);
        $inventory = $inventory->latest()->paginate($perPage);

        $inventory->getCollection()->transform(function ($item) {
            return [
                'id'            => $item->id,
                'part_name'     => $item->part_name,
                'part_number'   => $item->part_number ?? '-',
                'unique_id'     => $item->unique_id ?? '-',
                'serial_number' => $item->serial_number,
                'condition'     => $item->condition,
                'client_name'   => $item->client->name ?? '-',
                'wh_asset_number' => $item->old_wh_asset_number ?? '-',
                'location'      => ($item->storageLevel->bin->rak->zone->name ?? '-')
                    . ' / ' . ($item->storageLevel->bin->rak->name ?? '-')
                    . ' / ' . ($item->storageLevel->bin->name ?? '-')
                    . ' / ' . ($item->storageLevel->name ?? '-'),
            ];
        });

        return response()->json($inventory);
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
                $oldCondition = $inventory->condition;
                $newCondition = $request->conditions[$id] ?? $oldCondition;

                $inventory->update([
                    'storage_level_id' => $request->storage_level_id,
                    'condition' => $newCondition,
                    'last_movement_date' => now()
                ]);

                \App\Models\InventoryMovement::create([
                    'inventory_id' => $id,
                    'from_storage_level_id' => $oldStorageLevelId,
                    'to_storage_level_id' => $request->storage_level_id,
                    'user_id' => Auth::id(),
                    'type' => 'Movement',
                    'description' => 'Product moved by ' . Auth::user()->name . ($oldCondition != $newCondition ? " (Condition changed from $oldCondition to $newCondition)" : "")
                ]);

                $toStorage = \App\Models\StorageLevel::with('bin.rak.zone')->find($request->storage_level_id);
                $toName = $toStorage ? "{$toStorage->bin->rak->zone->name}-{$toStorage->bin->rak->name}-{$toStorage->bin->name}-{$toStorage->name}" : 'N/A';

                $fromStorage = \App\Models\StorageLevel::with('bin.rak.zone')->find($oldStorageLevelId);
                $fromName = $fromStorage ? "{$fromStorage->bin->rak->zone->name}-{$fromStorage->bin->rak->name}-{$fromStorage->bin->name}-{$fromStorage->name}" : 'N/A';

                $historyDesc = "Unit moved from [$fromName] to [$toName]";
                if ($oldCondition != $newCondition) {
                    $historyDesc .= ". Condition updated: $oldCondition -> $newCondition";
                }

                \App\Models\InventoryHistory::create([
                    'inventory_id' => $id,
                    'serial_number' => $inventory->serial_number,
                    'type' => 'Movement',
                    'category' => 'Location Change',
                    'reference_number' => '-',
                    'description' => $historyDesc,
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

    public function productSummary(Request $request): View
    {
        $title = 'Inventory Product';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $query = \App\Models\Inventory::select(
            'part_name',
            'part_number',
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_in'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as in_inventory'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty = 0 THEN 1 ELSE 0 END) as total_out')
        );

        $this->applyClientFilter($query, $clientId);

        // Quick Search: multi SN / Asset ID (paste multiple values)
        $quickSearch = $request->get('quick_search');
        if ($quickSearch && trim($quickSearch) !== '') {
            $terms = preg_split('/[\r\n,; ]+/', $quickSearch);
            $terms = array_map('trim', $terms);
            $terms = array_filter($terms, fn($v) => $v !== '');

            if (!empty($terms)) {
                $query->where(function ($q) use ($terms) {
                    $q->whereIn('serial_number', $terms)
                      ->orWhereIn('unique_id', $terms);
                });
            }
        }

        $query = $query->when($request->search, function ($query) use ($request) {
            return $query->where(function ($q) use ($request) {
                $q->where('part_name', 'like', '%' . $request->search . '%')
                    ->orWhere('part_number', 'like', '%' . $request->search . '%');
            });
        })
            ->groupBy('part_name', 'part_number')
            ->orderBy('part_name');

        $data = $query->paginate(15);
        $clients = $user->getAvailableClients();

        return view('inventory.product-summary', compact('title', 'data', 'clients'));
    }

    public function productSummaryPdf(Request $request)
    {
        $title = 'Product Summary Report';
        $clientId = $request->get('client_id');

        $query = \App\Models\Inventory::select(
            'part_name',
            'part_number',
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_in'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as in_inventory'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty = 0 THEN 1 ELSE 0 END) as total_out')
        );

        $this->applyClientFilter($query, $clientId);

        // Quick Search: multi SN / Asset ID (paste multiple values)
        $quickSearch = $request->get('quick_search');
        if ($quickSearch && trim($quickSearch) !== '') {
            $terms = preg_split('/[\r\n,; ]+/', $quickSearch);
            $terms = array_map('trim', $terms);
            $terms = array_filter($terms, fn($v) => $v !== '');

            if (!empty($terms)) {
                $query->where(function ($q) use ($terms) {
                    $q->whereIn('serial_number', $terms)
                      ->orWhereIn('unique_id', $terms);
                });
            }
        }

        $data = $query->groupBy('part_name', 'part_number')
            ->orderBy('part_name')
            ->get();

        return view('inventory.product-summary-pdf', compact('title', 'data'));
    }

    public function productSummaryExcel(Request $request)
    {
        $clientId = $request->get('client_id');

        $query = \App\Models\Inventory::select(
            'part_name',
            'part_number',
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_in'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as in_inventory'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty = 0 THEN 1 ELSE 0 END) as total_out')
        );

        $this->applyClientFilter($query, $clientId);

        // Quick Search: multi SN / Asset ID (paste multiple values)
        $quickSearch = $request->get('quick_search');
        if ($quickSearch && trim($quickSearch) !== '') {
            $terms = preg_split('/[\r\n,; ]+/', $quickSearch);
            $terms = array_map('trim', $terms);
            $terms = array_filter($terms, fn($v) => $v !== '');

            if (!empty($terms)) {
                $query->where(function ($q) use ($terms) {
                    $q->whereIn('serial_number', $terms)
                      ->orWhereIn('unique_id', $terms);
                });
            }
        }

        $data = $query->groupBy('part_name', 'part_number')
            ->orderBy('part_name')
            ->get();

        $filename = "product-summary-" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<table border='1'>";
        echo "<thead><tr><th>No</th><th>Part Number</th><th>Total Received</th><th>In Inventory</th><th>Total Outbound</th></tr></thead>";
        echo "<tbody>";
        foreach ($data as $index => $item) {
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . $item->part_number . "</td>";
            echo "<td>" . $item->total_in . "</td>";
            echo "<td>" . $item->in_inventory . "</td>";
            echo "<td>" . $item->total_out . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    public function productSummaryDetail(Request $request)
    {
        $partName = $request->part_name;
        $partNumber = $request->part_number;

        $details = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client'])
            ->where('part_name', $partName)
            ->where('part_number', $partNumber);

        if (!Auth::user()->isAdminWMS()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $details->where(function ($q) use ($user) {
                $q->whereIn('client_id', $user->getAccessibleClientIds())
                    ->orWhereNull('client_id');
            });
        }

        $details = $details->get()
            ->map(function ($item) {
                $storage = $item->storageLevel ? "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}" : "-";
                return [
                    'unique_id' => $item->unique_id,
                    'serial_number' => $item->serial_number,
                    'status' => $item->status,
                    'condition' => $item->condition,
                    'client' => $item->client->name ?? '-',
                    'storage' => $storage
                ];
            });

        return response()->json($details);
    }

    public function stockStatement(Request $request): View
    {
        $title = 'Inventory Stock Statement';
        $user = Auth::user();
        $clients = $user->getAvailableClients();
        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration', 'Spare Return'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration', 'Return'];

        $clientId = $request->get('client_id');

        $inboundData = \App\Models\InboundDetail::with(['inbound.client', 'brand', 'storageLevel.bin.rak.zone', 'productGroup'])
            ->select('inbound_detail.*')
            ->join('inbound', 'inbound_detail.inbound_id', '=', 'inbound.id')
            ->where(function ($q) {
                $q->whereNotNull('inbound_detail.storage_level_id')
                    ->orWhereExists(function ($query) {
                        $query->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('outbound_detail')
                            ->whereColumn('outbound_detail.serial_number', 'inbound_detail.serial_number');
                    });
            });

        // Custom filter for Inbound relation
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $inboundData->where('inbound.client_id', $clientId);
            }
        } else {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $inboundData->where('inbound.client_id', $clientId);
            } else {
                $inboundData->where(function ($q) use ($accessibleIds) {
                    $q->whereIn('inbound.client_id', $accessibleIds)
                        ->orWhereNull('inbound.client_id');
                });
            }
        }

        $inboundData = $inboundData->when($request->category, function ($query) use ($request) {
            return $query->where('inbound.category', $request->category);
        })
            ->when($request->request_type, function ($query) use ($request) {
                return $query->where('inbound.request_type', $request->request_type);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('inbound_detail.serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('inbound_detail.part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('inbound.number', 'like', '%' . $request->search . '%')
                        ->orWhere('inbound.receiving_note', 'like', '%' . $request->search . '%')
                        ->orWhereExists(function ($query) use ($request) {
                            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('inventory')
                                ->whereColumn('inventory.serial_number', 'inbound_detail.serial_number')
                                ->where('inventory.unique_id', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(50);

        $sns = $inboundData->pluck('serial_number')->toArray();
        $inventories = \App\Models\Inventory::with('storageLevel.bin.rak.zone')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');
        $outbounds = \App\Models\OutboundDetail::with('outbound')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');

        foreach ($inboundData as $item) {
            $inventory = $inventories->get($item->serial_number);
            $outbound = $outbounds->get($item->serial_number);

            $item->is_outbound = (bool)$outbound;
            $item->is_in_stock = $inventory && $inventory->qty > 0;
            $item->current_inventory = $inventory;
            $item->outbound_detail = $outbound;
        }

        return view('inventory.stock-statement.index', compact('title', 'inboundData', 'clients', 'categories', 'requestTypes'));
    }

    public function stockStatementPdf(Request $request)
    {
        $title = 'Stock Statement Report';
        $user = Auth::user();
        $clientId = $request->get('client_id');

        $query = \App\Models\InboundDetail::with(['inbound.client', 'brand', 'storageLevel.bin.rak.zone', 'productGroup'])
            ->select('inbound_detail.*')
            ->join('inbound', 'inbound_detail.inbound_id', '=', 'inbound.id');

        if (!$user->isAdminWMS()) {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $query->where(function ($q) use ($accessibleIds) {
                $q->whereIn('inbound.client_id', $accessibleIds)
                    ->orWhereNull('inbound.client_id');
            });
        }

        if ($clientId) {
            $query->where('inbound.client_id', $clientId);
        }

        $data = $query->latest()->get();
        $sns = $data->pluck('serial_number')->toArray();
        $inventories = \App\Models\Inventory::whereIn('serial_number', $sns)->get()->keyBy('serial_number');
        $outbounds = \App\Models\OutboundDetail::with('outbound')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');

        foreach ($data as $item) {
            $inventory = $inventories->get($item->serial_number);
            $outbound = $outbounds->get($item->serial_number);
            $item->is_outbound = (bool)$outbound;
            $item->is_in_stock = $inventory && $inventory->qty > 0;
            $item->outbound_detail = $outbound;
        }

        return view('inventory.stock-statement.pdf', compact('title', 'data'));
    }

    public function stockStatementExcel(Request $request)
    {
        $user = Auth::user();
        $clientId = $request->get('client_id');
        $category = $request->get('category');
        $requestType = $request->get('request_type');
        $search = $request->get('search');

        $query = \App\Models\InboundDetail::with(['inbound.client', 'brand', 'storageLevel.bin.rak.zone', 'productGroup'])
            ->select('inbound_detail.*')
            ->join('inbound', 'inbound_detail.inbound_id', '=', 'inbound.id');

        if (!$user->isAdminWMS()) {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $query->where(function ($q) use ($accessibleIds) {
                $q->whereIn('inbound.client_id', $accessibleIds)
                    ->orWhereNull('inbound.client_id');
            });
        }

        if ($clientId) {
            $query->where('inbound.client_id', $clientId);
        }

        $query->when($category, function ($q) use ($category) {
            return $q->where('inbound.category', $category);
        })
            ->when($requestType, function ($q) use ($requestType) {
                return $q->where('inbound.request_type', $requestType);
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($sq) use ($search) {
                    $sq->where('inbound_detail.serial_number', 'like', '%' . $search . '%')
                        ->orWhere('inbound_detail.part_name', 'like', '%' . $search . '%')
                        ->orWhere('inbound_detail.part_number', 'like', '%' . $search . '%')
                        ->orWhere('inbound.number', 'like', '%' . $search . '%');
                });
            });

        $data = $query->latest()->get();
        $sns = $data->pluck('serial_number')->toArray();
        $inventories = \App\Models\Inventory::with('storageLevel.bin.rak.zone')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');
        $outbounds = \App\Models\OutboundDetail::with('outbound')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');

        $filename = "stock-statement-" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo '<table border="1">';
        echo '<thead><tr>';
        echo '<th>No</th>';
        echo '<th>Movement Category</th>';
        echo '<th>Stock Category</th>';
        echo '<th>Request Type</th>';
        echo '<th>NTT Requestor</th>';
        echo '<th>Request Date</th>';
        echo '<th>Product Group</th>';
        echo '<th>Brand</th>';
        echo '<th>Product Number (SKU)</th>';
        echo '<th>Product Description</th>';
        echo '<th>Serial Number (SN)</th>';
        echo '<th>Parent SN</th>';
        echo '<th>Qty</th>';
        echo '<th>WH Asset Number</th>';
        echo '<th>Stock Status</th>';
        echo '<th>Stock Condition</th>';
        echo '<th>Stock Location (Rack/Bin/Level)</th>';
        echo '<th>eCapex #</th>';
        echo '<th>SAP PO #</th>';
        echo '<th>Vendor DN #</th>';
        echo '<th>NTT RN #</th>';
        echo '<th>Received Date</th>';
        echo '<th>NTT DN #</th>';
        echo '<th>Delivery Date</th>';
        echo '<th>Trans Kargo DN #</th>';
        echo '<th>Trans Kargo Invoice #</th>';
        echo '<th>Staging Date</th>';
        echo '<th>ITSM #</th>';
        echo '<th>RMA #</th>';
        echo '<th>Processed By</th>';
        echo '<th>Client Name</th>';
        echo '<th>Client Contact</th>';
        echo '<th>Pickup Address</th>';
        echo '<th>Shipment Status</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        foreach ($data as $index => $item) {
            $inventory = $inventories->get($item->serial_number);
            $outbound = $outbounds->get($item->serial_number);
            $isOutbound = (bool) $outbound;
            $movementCategory = $isOutbound ? 'Outbound' : 'Inbound';

            $location = '-';
            if ($item->storageLevel) {
                $location = $item->storageLevel->bin->rak->zone->name . '-'
                    . $item->storageLevel->bin->rak->name . '-'
                    . $item->storageLevel->bin->name . '-'
                    . $item->storageLevel->name;
            }

            echo '<tr>';
            echo '<td style="text-align:center">' . ($index + 1) . '</td>';
            echo '<td>' . $movementCategory . '</td>';
            echo '<td>' . ($item->inbound->category ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->request_type ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->ntt_requestor ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->request_date ? \Carbon\Carbon::parse($item->inbound->request_date)->format('d/m/Y') : '-') . '</td>';
            echo '<td>' . ($item->productGroup->name ?? '-') . '</td>';
            echo '<td>' . ($item->brand->name ?? '-') . '</td>';
            echo '<td>' . $item->part_number . '</td>';
            echo '<td>' . $item->part_name . '</td>';
            echo '<td style="mso-number-format:\@;">' . $item->serial_number . '</td>';
            echo '<td>' . ($item->parent_sn ?? ($item->old_serial_number ?? '-')) . '</td>';
            echo '<td style="text-align:center">' . $item->qty . '</td>';
            echo '<td>' . ($inventory->unique_id ?? ($item->wh_asset_number ?? '-')) . '</td>';
            echo '<td>' . $item->stock_status . '</td>';
            echo '<td>' . $item->condition . '</td>';
            echo '<td>' . $location . '</td>';
            echo '<td>' . ($item->inbound->ecapex_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->sap_po_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->vendor_dn_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->ntt_rn_number ?? ($item->inbound->number ?? '-')) . '</td>';
            echo '<td>' . ($item->inbound->received_date ? \Carbon\Carbon::parse($item->inbound->received_date)->format('d/m/Y') : '-') . '</td>';
            echo '<td>' . ($item->inbound->ntt_dn_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->delivery_date ? \Carbon\Carbon::parse($item->inbound->delivery_date)->format('d/m/Y') : '-') . '</td>';
            echo '<td>' . ($item->inbound->tks_dn_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->tks_invoice_number ?? '-') . '</td>';
            echo '<td>' . ($item->staging_date ? \Carbon\Carbon::parse($item->staging_date)->format('d/m/Y') : '-') . '</td>';
            echo '<td>' . ($item->inbound->itsm_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->rma_number ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->received_by ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->client->name ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->client_contact ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->pickup_address ?? '-') . '</td>';
            echo '<td>' . ($item->inbound->shipment_status ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        exit;
    }

    public function storageInventory(Request $request): View
    {
        $title = 'Storage Inventory';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $query = \App\Models\Inventory::query()
            ->join('storage_level', 'inventory.storage_level_id', '=', 'storage_level.id')
            ->join('storage_bin', 'storage_level.storage_bin_id', '=', 'storage_bin.id')
            ->join('storage_rak', 'storage_bin.storage_rak_id', '=', 'storage_rak.id')
            ->join('storage_zone', 'storage_rak.storage_zone_id', '=', 'storage_zone.id')
            ->select(
                'inventory.storage_level_id',
                'storage_zone.name as zone_name',
                'storage_rak.name as rak_name',
                'storage_bin.name as bin_name',
                'storage_level.name as level_name',
                \Illuminate\Support\Facades\DB::raw('COUNT(inventory.id) as total_items')
            )
            ->where('inventory.qty', '>', 0);

        $this->applyClientFilter($query, $clientId, 'inventory.client_id');

        $query->when($request->search, function ($q) use ($request) {
            $q->where(function ($sq) use ($request) {
                $sq->where('storage_zone.name', 'like', '%' . $request->search . '%')
                    ->orWhere('storage_rak.name', 'like', '%' . $request->search . '%')
                    ->orWhere('storage_bin.name', 'like', '%' . $request->search . '%')
                    ->orWhere('storage_level.name', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.unique_id', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.serial_number', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.part_name', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.part_number', 'like', '%' . $request->search . '%');
            });
        });

        $data = $query->groupBy(
            'inventory.storage_level_id',
            'storage_zone.name',
            'storage_rak.name',
            'storage_bin.name',
            'storage_level.name'
        )
            ->orderBy('storage_zone.name')
            ->orderBy('storage_rak.name')
            ->orderBy('storage_bin.name')
            ->orderBy('storage_level.name')
            ->paginate(20);

        $clients = $user->getAvailableClients();

        return view('inventory.storage-inventory', compact('title', 'data', 'clients'));
    }

    public function storageInventoryDetail(Request $request)
    {
        $storageLevelId = $request->storage_level_id;
        $clientId = $request->client_id;

        $items = \App\Models\Inventory::with(['client', 'product.brand', 'product.productGroup'])
            ->where('storage_level_id', $storageLevelId)
            ->where('qty', '>', 0);

        $user = Auth::user();
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $items->where('client_id', $clientId);
            }
        } else {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $items->where('client_id', $clientId);
            } else {
                $items->where(function ($q) use ($accessibleIds) {
                    $q->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            }
        }

        $items = $items->get()->map(function ($item) {
            return [
                'unique_id' => $item->unique_id,
                'part_name' => $item->part_name,
                'part_number' => $item->part_number,
                'serial_number' => $item->serial_number,
                'parent_serial_number' => $item->parent_serial_number ?? '-',
                'client' => $item->client->name ?? '-',
                'status' => $item->status,
                'condition' => $item->condition,
            ];
        });

        return response()->json($items);
    }

    public function storageInventoryExportExcel(Request $request)
    {
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $query = \App\Models\Inventory::query()
            ->join('storage_level', 'inventory.storage_level_id', '=', 'storage_level.id')
            ->join('storage_bin', 'storage_level.storage_bin_id', '=', 'storage_bin.id')
            ->join('storage_rak', 'storage_bin.storage_rak_id', '=', 'storage_rak.id')
            ->join('storage_zone', 'storage_rak.storage_zone_id', '=', 'storage_zone.id')
            ->leftJoin('client', 'inventory.client_id', '=', 'client.id')
            ->select(
                'inventory.*',
                'storage_zone.name as zone_name',
                'storage_rak.name as rak_name',
                'storage_bin.name as bin_name',
                'storage_level.name as level_name',
                'client.name as client_name'
            )
            ->where('inventory.qty', '>', 0);

        if ($user->isAdminWMS()) {
            if ($clientId) {
                $query->where('inventory.client_id', $clientId);
            }
        } else {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $query->where('inventory.client_id', $clientId);
            } else {
                $query->where(function ($q) use ($accessibleIds) {
                    $q->whereIn('inventory.client_id', $accessibleIds)
                        ->orWhereNull('inventory.client_id');
                });
            }
        }

        $query->when($request->search, function ($q) use ($request) {
            $q->where(function ($sq) use ($request) {
                $sq->where('storage_zone.name', 'like', '%' . $request->search . '%')
                    ->orWhere('storage_rak.name', 'like', '%' . $request->search . '%')
                    ->orWhere('storage_bin.name', 'like', '%' . $request->search . '%')
                    ->orWhere('storage_level.name', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.unique_id', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.part_name', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.part_number', 'like', '%' . $request->search . '%')
                    ->orWhere('inventory.serial_number', 'like', '%' . $request->search . '%');
            });
        });

        if ($request->has('storage_level_id')) {
            $query->where('inventory.storage_level_id', $request->get('storage_level_id'));
        }

        $data = $query->orderBy('storage_zone.name')
            ->orderBy('storage_rak.name')
            ->orderBy('storage_bin.name')
            ->orderBy('storage_level.name')
            ->get();

        $filename = "storage-inventory-detail-" . date('Y-m-d') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Warehouse Asset ID</th>";
        echo "<th>Part Number</th>";
        echo "<th>Serial Number</th>";
        echo "<th>Parent Serial Number</th>";
        echo "<th>Client</th>";
        echo "<th>Zone</th>";
        echo "<th>Rak</th>";
        echo "<th>Bin</th>";
        echo "<th>Level</th>";
        echo "<th>Status</th>";
        echo "<th>Condition</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($data as $index => $item) {
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>{$item->unique_id}</td>";
            echo "<td>{$item->part_number}</td>";
            echo "<td style=\"mso-number-format:\\@;\">{$item->serial_number}</td>";
            echo "<td>" . ($item->parent_serial_number ?? '-') . "</td>";
            echo "<td>" . ($item->client_name ?? '-') . "</td>";
            echo "<td>" . ($item->zone_name ?? '-') . "</td>";
            echo "<td>" . ($item->rak_name ?? '-') . "</td>";
            echo "<td>" . ($item->bin_name ?? '-') . "</td>";
            echo "<td>" . ($item->level_name ?? '-') . "</td>";
            echo "<td>{$item->status}</td>";
            echo "<td>{$item->condition}</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        exit;
    }

    public function storageInventoryExportPdf(Request $request)
    {
        $title = 'Storage Inventory Report';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $query = \App\Models\Inventory::query()
            ->join('storage_level', 'inventory.storage_level_id', '=', 'storage_level.id')
            ->join('storage_bin', 'storage_level.storage_bin_id', '=', 'storage_bin.id')
            ->join('storage_rak', 'storage_bin.storage_rak_id', '=', 'storage_rak.id')
            ->join('storage_zone', 'storage_rak.storage_zone_id', '=', 'storage_zone.id')
            ->leftJoin('client', 'inventory.client_id', '=', 'client.id')
            ->select(
                'inventory.*',
                'storage_zone.name as zone_name',
                'storage_rak.name as rak_name',
                'storage_bin.name as bin_name',
                'storage_level.name as level_name',
                'client.name as client_name'
            )
            ->where('inventory.qty', '>', 0);

        if (!$user->isAdminWMS()) {
            /** @var \App\Models\User $user */
            $accessibleIds = $user->getAccessibleClientIds();
            $query->where(function ($q) use ($accessibleIds) {
                $q->whereIn('inventory.client_id', $accessibleIds)
                    ->orWhereNull('inventory.client_id');
            });
        }

        if ($clientId) {
            $query->where('inventory.client_id', $clientId);
        }

        $data = $query->orderBy('storage_zone.name')
            ->orderBy('storage_rak.name')
            ->orderBy('storage_bin.name')
            ->orderBy('storage_level.name')
            ->get();

        return view('inventory.storage-inventory-pdf', compact('title', 'data'));
    }

    public function history(Request $request): View
    {
        $title = 'Inventory History';
        $user = Auth::user();
        $clientId = $request->get('client_id');
        $serialNumber = $request->get('serial_number');

        $query = \App\Models\OutboundDetail::with(['outbound.client', 'inventory.storageLevel.bin.rak.zone'])
            ->whereHas('outbound', function ($q) use ($user, $clientId) {
                if (!$user->isAdminWMS()) {
                    /** @var \App\Models\User $user */
                    $q->where(function ($sub) use ($user) {
                        $sub->whereIn('client_id', $user->getAccessibleClientIds())
                            ->orWhereNull('client_id');
                    });
                }
                if ($clientId) {
                    $q->where('client_id', $clientId);
                }
            });

        if ($serialNumber) {
            $query->where('serial_number', 'like', '%' . $serialNumber . '%');
        }

        $history = $query->latest()->paginate(20);
        $clients = $user->getAvailableClients();

        return view('inventory.history', compact('title', 'history', 'clients'));
    }

    public function historyPdf(Request $request)
    {
        $title = 'Inventory History Report (Outbound)';
        $user = Auth::user();
        $clientId = $request->get('client_id');

        $query = \App\Models\OutboundDetail::with(['outbound.client', 'inventory.storageLevel.bin.rak.zone']);

        $query->whereHas('outbound', function ($q) use ($user, $clientId) {
            if (!$user->isAdminWMS()) {
                /** @var \App\Models\User $user */
                $q->where(function ($sub) use ($user) {
                    $sub->whereIn('client_id', $user->getAccessibleClientIds())
                        ->orWhereNull('client_id');
                });
            }
            if ($clientId) {
                $q->where('client_id', $clientId);
            }
        });

        $data = $query->latest()->get();

        return view('inventory.history-pdf', compact('title', 'data'));
    }

    public function historyExcel(Request $request)
    {
        $user = Auth::user();
        $clientId = $request->get('client_id');

        $query = \App\Models\OutboundDetail::with(['outbound.client', 'inventory.storageLevel.bin.rak.zone']);

        $query->whereHas('outbound', function ($q) use ($user, $clientId) {
            if (!$user->isAdminWMS()) {
                /** @var \App\Models\User $user */
                $q->where(function ($sub) use ($user) {
                    $sub->whereIn('client_id', $user->getAccessibleClientIds())
                        ->orWhereNull('client_id');
                });
            }
            if ($clientId) {
                $q->where('client_id', $clientId);
            }
        });

        $data = $query->latest()->get();

        $filename = "inventory-history-" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<table border='1'>";
        echo "<thead><tr><th>No</th><th>Date</th><th>Outbound No</th><th>Client</th><th>SN</th><th>Parent Serial Number</th><th>Condition</th></tr></thead>";
        echo "<tbody>";
        foreach ($data as $index => $item) {
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . ($item->outbound->outbound_date ?? '-') . "</td>";
            echo "<td>" . ($item->outbound->number ?? '-') . "</td>";
            echo "<td>" . ($item->outbound->client->name ?? '-') . "</td>";
            echo "<td style=\"mso-number-format:\\@;\">" . $item->serial_number . "</td>";
            echo "<td>" . ($item->inventory->parent_serial_number ?? '-') . "</td>";
            echo "<td>" . $item->condition . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    public function editSn()
    {
        $title = 'Edit Serial Number';
        return view('inventory.edit-sn', compact('title'));
    }

    public function updateSn(Request $request)
    {
        $request->validate([
            'search_sn' => 'required|string',
            'new_sn'    => 'required|string',
        ]);

        $search = $request->search_sn;
        $newSn = $request->new_sn;

        $inventory = \App\Models\Inventory::where('serial_number', $search)
            ->orWhere('unique_id', $search)
            ->first();

        $inboundDetail = \App\Models\InboundDetail::where('serial_number', $search)
            ->orWhere('wh_asset_number', $search)
            ->first();

        if (!$inventory && !$inboundDetail) {
            return back()->with('error', 'Item not found with given SN or Asset ID');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($inventory) {
                \App\Models\InventoryHistory::create([
                    'inventory_id' => $inventory->id,
                    'serial_number' => $newSn,
                    'type' => 'edit_sn',
                    'category' => 'movement',
                    'description' => "SN Changed from " . ($inventory->serial_number ?: '[EMPTY]') . " to {$newSn}. Asset ID: {$inventory->unique_id}",
                    'user' => Auth::user()->name,
                    'from_location' => '-',
                    'to_location' => '-'
                ]);

                $inventory->update([
                    'serial_number' => $newSn
                ]);
            }

            if ($inboundDetail) {
                $inboundDetail->update([
                    'serial_number' => $newSn
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Serial Number updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Failed to update Serial Number: ' . $e->getMessage());
        }
    }

    public function editPartNumber(Request $request)
    {
        $title = 'Edit Part Number';
        $sn = $request->get('sn', '');
        $currentPartNumber = $request->get('part_number', '');

        return view('inventory.edit-part-number', compact('title', 'sn', 'currentPartNumber'));
    }

    public function updatePartNumber(Request $request)
    {
        $request->validate([
            'search_sn'       => 'required|string',
            'new_part_number' => 'required|string',
        ]);

        $search = $request->search_sn;
        $newPartNumber = $request->new_part_number;

        $inventory = \App\Models\Inventory::where('serial_number', $search)
            ->orWhere('unique_id', $search)
            ->first();

        $inboundDetail = \App\Models\InboundDetail::where('serial_number', $search)
            ->orWhere('wh_asset_number', $search)
            ->first();

        if (!$inventory && !$inboundDetail) {
            return back()->with('error', 'Item not found with given SN or Asset ID');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($inventory) {
                $oldPartNumber = $inventory->part_number ?: '[EMPTY]';

                \App\Models\InventoryHistory::create([
                    'inventory_id'    => $inventory->id,
                    'serial_number'   => $inventory->serial_number,
                    'type'            => 'edit_part_number',
                    'category'        => 'movement',
                    'description'     => "Part Number Changed from {$oldPartNumber} to {$newPartNumber}. Asset ID: {$inventory->unique_id}",
                    'user'            => Auth::user()->name,
                    'from_location'   => '-',
                    'to_location'     => '-'
                ]);

                $inventory->update([
                    'part_number' => $newPartNumber
                ]);
            }

            if ($inboundDetail) {
                $inboundDetail->update([
                    'part_number' => $newPartNumber
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Part Number updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Failed to update Part Number: ' . $e->getMessage());
        }
    }
}
