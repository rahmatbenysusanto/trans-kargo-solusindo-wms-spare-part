<?php

namespace App\Http\Controllers;

use App\Models\AssetGroup;
use App\Models\AssetGroupItem;
use App\Models\InboundDetail;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\OutboundDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssetGroupController extends Controller
{
    /**
     * List asset groups (search by group number / name / member SN or asset).
     */
    public function index(Request $request): View
    {
        $title = 'Asset Group';
        $user = Auth::user();
        $search = trim($request->get('search', ''));

        $groups = AssetGroup::with(['items.inventory', 'creator'])
            ->withCount('items');

        if ($search !== '') {
            $groups->where(function ($q) use ($search) {
                $q->where('group_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('items.inventory', function ($sub) use ($search) {
                        $sub->where('serial_number', 'like', "%{$search}%")
                            ->orWhere('unique_id', 'like', "%{$search}%");
                    });
            });
        }

        if (!$user->isAdminWMS()) {
            $ids = $user->getAccessibleClientIds();
            $groups->whereHas('items.inventory', function ($q) use ($ids) {
                $q->whereIn('client_id', $ids)->orWhereNull('client_id');
            });
        }

        $groups = $groups->latest()->paginate(15)->withQueryString();

        // inventory_id param (from inventory detail page) preselects the seed member in the create modal
        $inventoryId = $request->get('inventory_id');

        $seedInventory = null;
        if ($inventoryId) {
            $query = Inventory::with('product.brand', 'product.productGroup');
            if (!$this->isAdmin()) {
                $ids = Auth::user()->getAccessibleClientIds();
                $query->where(function ($q) use ($ids) {
                    $q->whereIn('client_id', $ids)->orWhereNull('client_id');
                });
            }
            $seedInventory = $query->find($inventoryId);
        }

        return view('inventory.asset-group.index', compact('title', 'groups', 'search', 'inventoryId', 'seedInventory'));
    }

    /**
     * Create a new asset group (optionally seeded with initial members).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'inventory_ids' => 'nullable|array',
            'inventory_ids.*' => 'integer|distinct',
        ]);

        DB::statement("SELECT GET_LOCK('asset_group_number', 15)");
        try {
            DB::beginTransaction();

            $group = AssetGroup::create([
                'group_number' => self::generateGroupNumber(),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'created_by' => Auth::id(),
            ]);

            $safeIds = $this->inScopeInventoryIds($request->input('inventory_ids', []));
            $this->insertItems($group->id, $safeIds);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Asset group created.',
                'group_id' => $group->id,
                'redirect' => route('inventory.asset-group.show', $group->id),
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        } finally {
            DB::statement("SELECT RELEASE_LOCK('asset_group_number')");
        }
    }

    /**
     * Show one group: members, related suggestions and the combined timeline.
     */
    public function show($id): View
    {
        $group = AssetGroup::findOrFail($id);
        if (!$this->groupAccessible($group)) {
            abort(403);
        }

        $title = 'Asset Group';

        $items = AssetGroupItem::with([
            'inventory.client',
            'inventory.product.brand',
            'inventory.product.productGroup',
            'inventory.storageLevel.bin.rak.zone',
            'inventory.details.inboundDetail.inbound',
        ])
            ->where('asset_group_id', $group->id)
            ->orderBy('id')
            ->get();

        $memberRows = $items->pluck('inventory')->filter()->values();

        $suggestions = $this->buildSuggestions($memberRows);
        $history = $this->buildTimeline($memberRows);

        return view('inventory.asset-group.show', compact('title', 'group', 'items', 'suggestions', 'history'));
    }

    /**
     * AJAX: fresh suggestion rows (rendered as HTML so the panel matches the page).
     */
    public function suggest($id): JsonResponse
    {
        $group = AssetGroup::findOrFail($id);
        if (!$this->groupAccessible($group)) {
            abort(403);
        }

        $memberSns = AssetGroupItem::where('asset_group_id', $group->id)
            ->with('inventory:id,serial_number')
            ->get()
            ->pluck('inventory.serial_number')
            ->filter()
            ->values();

        $inventory = Inventory::whereIn('serial_number', $memberSns->all())->get();
        $suggestions = $this->buildSuggestions($inventory);

        $html = view('inventory.asset-group._suggestion_rows', compact('suggestions'))->render();

        return response()->json(['status' => true, 'html' => $html]);
    }

    /**
     * AJAX (select2): search inventory rows to add as members.
     */
    public function searchInventory(Request $request): JsonResponse
    {
        $search = trim($request->get('search', ''));
        $excludeIds = $request->get('exclude_ids')
            ? array_filter(explode(',', $request->get('exclude_ids')))
            : [];

        $query = Inventory::with('product.brand', 'product.productGroup', 'client');

        if (!$this->isAdmin()) {
            $ids = Auth::user()->getAccessibleClientIds();
            $query->where(function ($q) use ($ids) {
                $q->whereIn('client_id', $ids)->orWhereNull('client_id');
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $rows = $query->orderBy('unique_id')->limit(50)->get();

        return response()->json([
            'results' => $rows->map(fn ($inv) => [
                'id' => $inv->id,
                'text' => "{$inv->serial_number} — {$inv->part_name} ({$inv->unique_id})",
            ])->all(),
        ]);
    }

    /**
     * AJAX (select2): search existing groups (for "Add to Existing Group").
     */
    public function searchGroups(Request $request): JsonResponse
    {
        $search = trim($request->get('search', ''));

        $query = AssetGroup::query();

        if (!$this->isAdmin()) {
            $ids = Auth::user()->getAccessibleClientIds();
            $query->whereHas('items.inventory', function ($q) use ($ids) {
                $q->whereIn('client_id', $ids)->orWhereNull('client_id');
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('group_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderByDesc('id')->limit(50)->get();

        return response()->json([
            'results' => $rows->map(fn ($group) => [
                'id' => $group->id,
                'text' => "{$group->group_number}" . ($group->name ? " · {$group->name}" : ''),
            ])->all(),
        ]);
    }

    /**
     * Add inventory rows to a group (client-scope sanitized, duplicates skipped).
     */
    public function addItems(Request $request, $id): JsonResponse
    {
        $request->validate([
            'inventory_ids' => 'required|array|min:1',
            'inventory_ids.*' => 'integer|distinct',
        ]);

        $group = AssetGroup::findOrFail($id);
        if (!$this->groupAccessible($group)) {
            return response()->json(['status' => false, 'message' => 'You do not have access to this asset group.'], 403);
        }

        $submitted = $request->input('inventory_ids');
        $safeIds = $this->inScopeInventoryIds($submitted);

        DB::beginTransaction();
        try {
            $added = $this->insertItems($group->id, $safeIds);
            DB::commit();
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }

        return response()->json([
            'status' => true,
            'message' => "{$added} member(s) added.",
            'added' => $added,
            'skipped' => max(count($submitted) - $added, 0),
        ]);
    }

    /**
     * Remove one member from a group.
     */
    public function removeItem(Request $request, $id): JsonResponse
    {
        $request->validate(['item_id' => 'required|integer']);

        $group = AssetGroup::findOrFail($id);
        if (!$this->groupAccessible($group)) {
            return response()->json(['status' => false, 'message' => 'You do not have access to this asset group.'], 403);
        }

        $item = AssetGroupItem::where('asset_group_id', $group->id)->findOrFail($request->input('item_id'));

        // Non-admin may only remove members within their client scope
        if (!$this->isAdmin()) {
            $inventory = $item->inventory()->first();
            if ($inventory) {
                $ids = Auth::user()->getAccessibleClientIds();
                $allowed = $inventory->client_id === null || in_array($inventory->client_id, $ids);
                if (!$allowed) {
                    return response()->json(['status' => false, 'message' => 'You do not have permission to remove this member.'], 403);
                }
            }
        }

        $item->delete();

        return response()->json(['status' => true, 'message' => 'Member removed.']);
    }

    /**
     * Delete a group (admin only). Items cascade; inventory rows are untouched.
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->isAdmin()) {
            return back()->with('error', 'You do not have permission to delete asset groups.');
        }

        $group = AssetGroup::findOrFail($id);
        $group->delete();

        return redirect()->route('inventory.asset-group.index')->with('success', 'Asset group deleted.');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function isAdmin(): bool
    {
        return Auth::user()->isAdminWMS();
    }

    /**
     * Whether the current user may access the group: admins see all, client
     * users only groups that contain at least one inventory row of theirs.
     */
    private function groupAccessible(AssetGroup $group): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $ids = Auth::user()->getAccessibleClientIds();

        return $group->items()->whereHas('inventory', function ($q) use ($ids) {
            $q->whereIn('client_id', $ids)->orWhereNull('client_id');
        })->exists();
    }

    /**
     * Filter an array of inventory ids down to rows the current user may attach.
     */
    private function inScopeInventoryIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, fn ($v) => is_numeric($v))));

        if (empty($ids)) {
            return [];
        }

        $query = Inventory::whereIn('id', $ids);

        if (!$this->isAdmin()) {
            $accessible = Auth::user()->getAccessibleClientIds();
            $query->where(function ($q) use ($accessible) {
                $q->whereIn('client_id', $accessible)->orWhereNull('client_id');
            });
        }

        return $query->pluck('id')->all();
    }

    /**
     * Bulk-insert members, skipping ids already attached to the group.
     */
    private function insertItems(int $groupId, array $inventoryIds): int
    {
        if (empty($inventoryIds)) {
            return 0;
        }

        $existing = AssetGroupItem::where('asset_group_id', $groupId)
            ->whereIn('inventory_id', $inventoryIds)
            ->pluck('inventory_id')
            ->all();

        $toAdd = array_values(array_diff($inventoryIds, $existing));

        if (!empty($toAdd)) {
            AssetGroupItem::insert(array_map(fn ($id) => [
                'asset_group_id' => $groupId,
                'inventory_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ], $toAdd));
        }

        return count($toAdd);
    }

    /**
     * Group number: AG + YYMMDD + 4-digit daily sequence (unique, like generateUniqueId).
     */
    private static function generateGroupNumber(): string
    {
        $prefix = 'AG' . date('ymd');
        $length = strlen($prefix) + 4;

        $last = AssetGroup::where('group_number', 'like', $prefix . '%')
            ->whereRaw('LENGTH(group_number) = ' . $length)
            ->orderBy('group_number', 'desc')
            ->value('group_number');

        $seq = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Semi-automatic candidates: inventory rows related to the members through
     * existing lineage (swap pairs, parent links, inherited asset numbers,
     * activity references). Returns rows with a "reason" attribute attached.
     */
    private function buildSuggestions(Collection $members): Collection
    {
        $memberIds = $members->pluck('id')->all();
        $memberSns = $members->pluck('serial_number')->filter(fn ($v) => $v !== null && $v !== '')->unique()->values()->all();
        $memberUniqueIds = $members->pluck('unique_id')->filter(fn ($v) => $v !== null && $v !== '')->unique()->values()->all();

        if (empty($memberIds) || (empty($memberSns) && empty($memberUniqueIds))) {
            return collect();
        }

        /** @var array<string, string[]> $candidates SN => reasons */
        $candidates = [];
        $addCandidate = function (string $sn, string $reason) use (&$candidates) {
            if ($sn === '') {
                return;
            }
            $candidates[$sn] = $candidates[$sn] ?? [];
            $candidates[$sn][] = $reason;
        };

        // 1. Outbound swap pairs (spare went out to replace an old faulty unit)
        OutboundDetail::with('outbound')
            ->whereNotNull('old_serial_number')
            ->where(fn ($q) => $q->whereIn('serial_number', $memberSns)->orWhereIn('old_serial_number', $memberSns))
            ->latest('id')
            ->get()
            ->each(function ($detail) use ($memberSns, $addCandidate) {
                $ref = $detail->outbound->number ?? $detail->outbound->tks_dn_number ?? '-';
                if (in_array($detail->serial_number, $memberSns, true)) {
                    $addCandidate($detail->old_serial_number, "Faulty unit replaced by {$detail->serial_number} (DN {$ref})");
                }
                if (in_array($detail->old_serial_number, $memberSns, true)) {
                    $addCandidate($detail->serial_number, "Replacement unit sent for {$detail->old_serial_number} (DN {$ref})");
                }
            });

        // 2. Inbound return/RMA pairs (soft-deleted rows are excluded by the model)
        InboundDetail::with('inbound')
            ->whereNotNull('old_serial_number')
            ->where(fn ($q) => $q->whereIn('serial_number', $memberSns)->orWhereIn('old_serial_number', $memberSns))
            ->latest('id')
            ->get()
            ->each(function ($detail) use ($memberSns, $addCandidate) {
                $ref = $detail->inbound->number ?? '-';
                if (in_array($detail->serial_number, $memberSns, true)) {
                    $addCandidate($detail->old_serial_number, "RMA/return: {$detail->serial_number} came back from old SN {$detail->old_serial_number} (Inbound {$ref})");
                }
                if (in_array($detail->old_serial_number, $memberSns, true)) {
                    $addCandidate($detail->serial_number, "RMA/return received for {$detail->old_serial_number} (Inbound {$ref})");
                }
            });

        // 3. parent_sn links on inbound lines
        InboundDetail::with('inbound')
            ->whereNotNull('parent_sn')
            ->where(fn ($q) => $q->whereIn('serial_number', $memberSns)->orWhereIn('parent_sn', $memberSns))
            ->latest('id')
            ->get()
            ->each(function ($detail) use ($memberSns, $addCandidate) {
                $ref = $detail->inbound->number ?? '-';
                if (in_array($detail->serial_number, $memberSns, true)) {
                    $addCandidate($detail->parent_sn, "Linked via parent SN {$detail->parent_sn} (Inbound {$ref})");
                }
                if (in_array($detail->parent_sn, $memberSns, true)) {
                    $addCandidate($detail->serial_number, "Linked via parent SN {$detail->parent_sn} (Inbound {$ref})");
                }
            });

        // 4. inventory-level lineage: children of a member, parents of a member,
        //    rows that inherited a member's WH asset number
        Inventory::whereNotIn('id', $memberIds)
            ->where(fn ($q) => $q
                ->whereIn('parent_serial_number', $memberSns)
                ->orWhereIn('old_wh_asset_number', $memberUniqueIds))
            ->get()
            ->each(function ($inv) use ($memberSns, $memberUniqueIds, $addCandidate) {
                if (in_array($inv->parent_serial_number, $memberSns, true)) {
                    $addCandidate($inv->serial_number, "Child unit of {$inv->parent_serial_number} (parent SN)");
                }
                if (in_array($inv->old_wh_asset_number, $memberUniqueIds, true)) {
                    $addCandidate($inv->serial_number, "Inherited WH asset # {$inv->old_wh_asset_number}");
                }
            });

        foreach ($members as $member) {
            if ($member->parent_serial_number && !in_array($member->parent_serial_number, $memberSns, true)) {
                $addCandidate($member->parent_serial_number, "Parent unit of {$member->serial_number}");
            }
        }

        // 5. Bounded fallback: history entries whose description mentions a member SN
        if (count($memberSns) <= 20) {
            InventoryHistory::whereNotNull('serial_number')
                ->whereNotIn('serial_number', $memberSns)
                ->where(function ($q) use ($memberSns) {
                    foreach ($memberSns as $sn) {
                        $q->orWhere('description', 'like', '%' . $sn . '%');
                    }
                })
                ->latest()
                ->limit(500)
                ->get()
                ->each(function ($history) use ($memberSns, $addCandidate) {
                    foreach ($memberSns as $sn) {
                        if (mb_strpos($history->description ?? '', $sn) !== false) {
                            $addCandidate($history->serial_number, "Referenced in activity of {$sn} ({$history->type}/{$history->category})");
                            break;
                        }
                    }
                });
        }

        if (empty($candidates)) {
            return collect();
        }

        $query = Inventory::with(['product.brand', 'product.productGroup', 'client', 'storageLevel.bin.rak.zone'])
            ->whereIn('serial_number', array_keys($candidates))
            ->whereNotIn('id', $memberIds);

        if (!$this->isAdmin()) {
            $accessible = Auth::user()->getAccessibleClientIds();
            $query->where(function ($q) use ($accessible) {
                $q->whereIn('client_id', $accessible)->orWhereNull('client_id');
            });
        }

        return $query->orderBy('serial_number')
            ->get()
            ->map(function ($inv) use ($candidates) {
                $inv->reason = implode(' | ', array_slice($candidates[$inv->serial_number] ?? [], 0, 2));
                return $inv;
            })
            ->values();
    }

    /**
     * Combined timeline across all member serials, sorted newest first.
     */
    private function buildTimeline(Collection $members): Collection
    {
        $memberSns = $members->pluck('serial_number')->filter(fn ($v) => $v !== null && $v !== '')->unique()->values()->all();

        $history = collect();

        if (!empty($memberSns)) {
            $query = InventoryHistory::whereIn('serial_number', $memberSns);

            // Cross-reference entries that mention a member SN in their description
            // (bounded: LIKE over the description is a scan)
            if (count($memberSns) <= 20) {
                $query->orWhere(function ($q) use ($memberSns) {
                    foreach ($memberSns as $sn) {
                        $q->orWhere('description', 'like', '%' . $sn . '%');
                    }
                });
            }

            $query->latest()->limit(2000)->get()->each(function ($item) use ($history) {
                $history->push([
                    'date' => $item->created_at,
                    'type' => $item->type,
                    'category' => $item->category,
                    'reference' => $item->reference_number,
                    'description' => $item->description,
                    'user' => $item->user,
                    'from_location' => $item->from_location,
                    'to_location' => $item->to_location,
                    'sn' => $item->serial_number,
                    'parent_sn' => null,
                ]);
            });
        }

        // Receiving pseudo-events per member (mirrors InventoryController::show)
        foreach ($members as $inventory) {
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
                        'sn' => $inventory->serial_number,
                        'parent_sn' => $detail->inboundDetail->parent_sn ?? $detail->inboundDetail->old_serial_number,
                    ]);
                }
            }
        }

        return $history->sortByDesc('date')->values();
    }
}
