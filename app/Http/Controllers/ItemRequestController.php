<?php

namespace App\Http\Controllers;

use App\Models\ChemicalInventory;
use App\Models\CleaningInventory;
use App\Models\ElectricalInventory;
use App\Models\IndustrialSuppliesInventory;
use App\Models\InventoryAdjustment;
use App\Models\ItemRequest;
use App\Models\MechanicalInventory;
use App\Models\OfficeSuppliesInventory;
use App\Models\PowerPlantInventory;
use App\Models\ProductionSuppliesInventory;
use App\Models\SanitationInventory;
use App\Models\SafetyInventory;
use App\Models\ToolsInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ItemRequestController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $role = $user->role ?? '';

        $storeRoomRoles = ['Store Room Supervisor', 'Store Room Assistant'];

        if (in_array($role, $storeRoomRoles, true)) {
            $pendingRequests = ItemRequest::query()
                ->with(['requestedBy', 'approvedBy'])
                ->where('status', 'Pending')
                ->orderByDesc('created_at')
                ->get();

            $recentRequests = ItemRequest::query()
                ->with(['requestedBy', 'approvedBy'])
                ->orderByDesc('created_at')
                ->limit(200)
                ->get();

            $myRequests = ItemRequest::query()
                ->with('approvedBy')
                ->where('requested_by_user_id', $user->id)
                ->orderByDesc('created_at')
                ->get();

            return view('requests.index', [
                'isStoreRoom' => true,
                'pendingRequests' => $pendingRequests,
                'recentRequests' => $recentRequests,
                'myRequests' => $myRequests,
            ]);
        }

        $myRequests = ItemRequest::query()
            ->with('approvedBy')
            ->where('requested_by_user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('requests.index', [
            'isStoreRoom' => false,
            'pendingRequests' => collect(),
            'recentRequests' => collect(),
            'myRequests' => $myRequests,
        ]);
    }

    /**
     * JSON endpoint for Store Room: current pending requests (for real-time polling).
     */
    public function pendingData(Request $request): JsonResponse
    {
        $role = Auth::user()->role ?? '';
        if (! in_array($role, ['Store Room Supervisor', 'Store Room Assistant'], true)) {
            return response()->json(['count' => 0, 'pending' => []]);
        }

        $pending = ItemRequest::query()
            ->with('requestedBy')
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'count' => $pending->count(),
            'pending' => $pending->map(fn ($req) => [
                'id' => $req->id,
                'created_at' => $req->created_at?->format('M j, Y H:i') ?? '',
                'requested_department' => $req->requested_department ?? '—',
                'requested_by_name' => $req->requestedBy->name ?? '—',
                'inventory_type' => $req->inventory_type,
                'item_id' => $req->item_id,
                'item_name' => $req->item_name,
                'requested_quantity' => $req->requested_quantity,
            ])->values()->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inventory_type' => ['required', 'string', 'max:50'],
            'item_id' => ['required', 'string', 'max:50'],
            'item_name' => ['required', 'string', 'max:255'],
            'requested_quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        [$modelClass, $label] = $this->resolveInventoryModel($data['inventory_type']);
        $item = $modelClass::query()->findOrFail($data['item_id']);

        if ((int) $item->quantity <= 0) {
            return redirect()->back()->with('outOfStockItem', [
                'name' => $item->item_name,
                'category' => $label,
                'message' => 'cannot be requested, it is currently out of stock.',
            ]);
        }

        $user = Auth::user();
        $role = $user->role ?? '';

        ItemRequest::query()->create([
            'inventory_type' => $data['inventory_type'],
            'item_id' => $data['item_id'],
            'item_name' => $data['item_name'],
            'requested_quantity' => $data['requested_quantity'],
            'status' => 'Pending',
            'requested_by_user_id' => $user->id,
            'requested_department' => $user->role ?? null,
            'decision_notes' => $data['reason'] ?? null,
        ]);

        // Store Room users submit from the Item Requests page; others submit from Inventory.
        if (in_array($role, ['Store Room Supervisor', 'Store Room Assistant'], true)) {
            return redirect()
                ->route('requests.index')
                ->with('message', 'Request submitted successfully.')
                ->with('messageType', 'success');
        }

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $returnStock = $request->input('return_stock', 'all');
        $returnLocation = $request->input('return_location', '');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
            if ($returnTab === 'inventory' && $returnStock && $returnStock !== 'all') {
                $params['stock'] = $returnStock;
            }
            if ($returnTab === 'inventory' && $returnLocation) {
                $params['location'] = $returnLocation;
            }
        }

        return redirect()
            ->route('inventory.index', $params)
            ->with('message', 'Request submitted successfully.')
            ->with('messageType', 'success');
    }

    public function approve(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $this->assertStoreRoomRole();

        if ($itemRequest->status !== 'Pending') {
            return redirect()->route('requests.index')->with('message', 'Only pending requests can be approved.')->with('messageType', 'danger');
        }

        [$modelClass, $label] = $this->resolveInventoryModel($itemRequest->inventory_type);

        $item = $modelClass::query()->findOrFail($itemRequest->item_id);
        $currentQty = (int) $item->quantity;
        $amount = (int) $itemRequest->requested_quantity;

        if ($amount > $currentQty) {
            $itemRequest->status = 'Rejected';
            $itemRequest->decision_notes = trim(($itemRequest->decision_notes ?? '') . "\n" . 'Rejected: insufficient stock.');
            $itemRequest->approved_by_user_id = Auth::id();
            $itemRequest->decision_at = now();
            $itemRequest->save();

            return redirect()->route('requests.index')->with('message', 'Request rejected due to insufficient stock.')->with('messageType', 'danger');
        }

        $newQty = max(0, $currentQty - $amount);
        $item->quantity = $newQty;
        $item->save();

        InventoryAdjustment::query()->create([
            'inventory_type' => $itemRequest->inventory_type,
            'type_label' => $label,
            'item_id' => $itemRequest->item_id,
            'item_name' => $itemRequest->item_name,
            'change_amount' => -$amount,
            'quantity_before' => $currentQty,
            'quantity_after' => $newQty,
            'department_requested' => $itemRequest->requested_department,
            'adjusted_by' => Auth::user()?->name ?? Auth::user()?->email ?? 'Unknown',
        ]);

        $note = $request->input('decision_notes') ?? 'Approved';

        $itemRequest->status = 'Approved';
        $itemRequest->decision_notes = trim(($itemRequest->decision_notes ?? '') . "\n" . $note);
        $itemRequest->approved_by_user_id = Auth::id();
        $itemRequest->decision_at = now();
        $itemRequest->save();

        if ($newQty === 0) {
            $tabMap = [
                'mechanical' => 'mechanical',
                'office_supplies' => 'office-supplies',
                'electrical' => 'electrical',
                'chemical' => 'chemical',
                'safety' => 'safety',
                'cleaning' => 'cleaning',
                'power-plant' => 'power-plant',
                'industrial-supplies' => 'industrial-supplies',
                'production-supplies' => 'production-supplies',
                'sanitation' => 'sanitation',
                'tools' => 'tools',
            ];

            $tab = $tabMap[$itemRequest->inventory_type] ?? 'mechanical';

            return redirect()
                ->route('inventory.index', ['tab' => $tab])
                ->with('message', 'Request approved and stock updated.')
                ->with('messageType', 'success')
                ->with('outOfStockItem', [
                    'name' => $item->item_name,
                    'category' => $label,
                    'message' => 'is now out of stock.',
                ]);
        }

        return redirect()->route('requests.index')->with('message', 'Request approved and stock updated.')->with('messageType', 'success');
    }

    public function reject(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $this->assertStoreRoomRole();

        if ($itemRequest->status !== 'Pending') {
            return redirect()->route('requests.index')->with('message', 'Only pending requests can be rejected.')->with('messageType', 'danger');
        }

        $note = $request->input('decision_notes') ?: 'Rejected';

        $itemRequest->status = 'Rejected';
        $itemRequest->decision_notes = trim(($itemRequest->decision_notes ?? '') . "\n" . $note);
        $itemRequest->approved_by_user_id = Auth::id();
        $itemRequest->decision_at = now();
        $itemRequest->save();

        return redirect()->route('requests.index')->with('message', 'Request rejected.')->with('messageType', 'success');
    }

    private function assertStoreRoomRole(): void
    {
        $role = Auth::user()->role ?? '';
        $storeRoomRoles = ['Store Room Supervisor', 'Store Room Assistant'];
        if (! in_array($role, $storeRoomRoles, true)) {
            abort(403);
        }
    }

    /**
     * @return array{0: class-string, 1: string}
     */
    private function resolveInventoryModel(string $inventoryType): array
    {
        $map = [
            'mechanical' => [MechanicalInventory::class, 'Mechanical'],
            'office_supplies' => [OfficeSuppliesInventory::class, 'Office supply'],
            'electrical' => [ElectricalInventory::class, 'Electrical'],
            'chemical' => [ChemicalInventory::class, 'Chemical'],
            'safety' => [SafetyInventory::class, 'Safety'],
            'cleaning' => [CleaningInventory::class, 'Cleaning'],
            'power-plant' => [PowerPlantInventory::class, 'Power Plant'],
            'industrial-supplies' => [IndustrialSuppliesInventory::class, 'Industrial Supplies'],
            'production-supplies' => [ProductionSuppliesInventory::class, 'Production Supplies'],
            'sanitation' => [SanitationInventory::class, 'Sanitation'],
            'tools' => [ToolsInventory::class, 'Tools'],
        ];

        if (! isset($map[$inventoryType])) {
            abort(400, 'Unknown inventory type: ' . $inventoryType);
        }

        return $map[$inventoryType];
    }
}
