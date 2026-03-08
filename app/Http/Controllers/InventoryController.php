<?php

namespace App\Http\Controllers;

use App\Models\MechanicalInventory;
use App\Models\InventoryAdjustment;
use App\Models\OfficeSuppliesInventory;
use App\Models\ElectricalInventory;
use App\Models\ChemicalInventory;
use App\Models\SafetyInventory;
use App\Models\CleaningInventory;
use App\Models\PowerPlantInventory;
use App\Models\IndustrialSuppliesInventory;
use App\Models\ProductionSuppliesInventory;
use App\Models\SanitationInventory;
use App\Models\ToolsInventory;
use App\Http\Requests\StoreMechanicalRequest;
use App\Http\Requests\UpdateMechanicalRequest;
use App\Http\Requests\StoreOfficeSupplyRequest;
use App\Http\Requests\UpdateOfficeSupplyRequest;
use App\Http\Requests\StoreEquipmentByCategoryRequest;
use App\Http\Requests\UpdateTechnicalEquipmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorPNG;

class InventoryController extends Controller
{
    private function getNextInventoryId(string $modelClass, string $prefix): string
    {
        $last = $modelClass::query()
            ->where('id', 'like', $prefix.'-%')
            ->orderByRaw('CAST(SUBSTRING(id, '.(strlen($prefix) + 2).') AS UNSIGNED) DESC')
            ->first();

        if (! $last) {
            $next = 1;
        } else {
            $next = (int) substr($last->id, strlen($prefix) + 1) + 1;
        }

        return $prefix.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function assertStoreRoomRole(): void
    {
        $role = Auth::user()?->role ?? '';
        if (! in_array($role, ['Store Room Supervisor', 'Store Room Assistant'], true)) {
            abort(403);
        }
    }

    /**
     * @param  \App\Models\MechanicalInventory|\App\Models\OfficeSuppliesInventory|\App\Models\ElectricalInventory|\App\Models\ChemicalInventory|\App\Models\SafetyInventory|\App\Models\CleaningInventory|\App\Models\PowerPlantInventory|\App\Models\IndustrialSuppliesInventory|\App\Models\ProductionSuppliesInventory|\App\Models\SanitationInventory|\App\Models\ToolsInventory  $item
     * @return array<string, mixed>
     */
    private function itemToArray($item, string $categoryType, string $categoryLabel): array
    {
        $dateArrived = $item->date_arrived
            ? \Illuminate\Support\Carbon::parse($item->date_arrived)->format('M d, Y')
            : null;
        $expirationDate = $item->expiration_date
            ? \Illuminate\Support\Carbon::parse($item->expiration_date)->format('M d, Y')
            : null;
        $updatedAt = $item->updated_at
            ? $item->updated_at->format('M d, Y H:i')
            : 'N/A';
        $imageUrl = $item->image_path ? asset('storage/'.$item->image_path) : '';
        $qty = (int) $item->quantity;
        $minStock = $item->min_stock ?? null;
        $isLowStock = $minStock !== null && $qty <= (int) $minStock;

        return [
            'id' => $item->id,
            'item_name' => $item->item_name,
            'quantity' => $qty,
            'category_type' => $categoryType,
            'category_label' => $categoryLabel,
            'min_stock' => $item->min_stock,
            'max_stock' => $item->max_stock,
            'brand' => $item->brand ?? null,
            'location' => $item->location ?? null,
            'date_arrived' => $dateArrived,
            'expiration_date' => $expirationDate,
            'notes' => $item->notes ?? null,
            'image_path' => $imageUrl,
            'updated_at' => $updatedAt,
            'status' => $item->status ?? null,
            'stock_status' => $qty <= 0 ? 'OUT OF STOCK' : 'In Stock',
            'badge_class' => $qty <= 0 ? 'out' : 'in-stock',
            'is_low_stock' => $isLowStock,
            'out_of_stock' => $qty <= 0,
        ];
    }

    /**
     * Generate a Code 128 barcode image using item code and item description.
     */
    public function barcode(Request $request): Response
    {
        $validated = $request->validate([
            'item_code' => ['required', 'string', 'max:100'],
            'item_description' => ['nullable', 'string', 'max:200'],
        ]);
        $itemCode = $validated['item_code'];
        $itemDescription = $validated['item_description'] ?? '';
        $barcodeData = $itemDescription !== ''
            ? $itemCode.'|'.$itemDescription
            : $itemCode;
        $barcodeData = mb_substr($barcodeData, 0, 80);

        $generator = new BarcodeGeneratorPNG;
        $png = $generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 60);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="barcode-'.str_replace([' ', '/', '\\'], '-', $itemCode).'.png"',
        ]);
    }

    public function index(): View
    {
        $mechanicalRecords = MechanicalInventory::query()->orderBy('id')->get();
        $officeSuppliesRecords = OfficeSuppliesInventory::query()->orderBy('id')->get();
        $adjustmentRecords = InventoryAdjustment::query()->orderByDesc('adjusted_at')->limit(200)->get();
        $electricalRecords = ElectricalInventory::query()->orderBy('id')->get();
        $chemicalRecords = ChemicalInventory::query()->orderBy('id')->get();
        $safetyRecords = SafetyInventory::query()->orderBy('id')->get();
        $cleaningRecords = CleaningInventory::query()->orderBy('id')->get();
        $powerPlantRecords = PowerPlantInventory::query()->orderBy('id')->get();
        $industrialSuppliesRecords = IndustrialSuppliesInventory::query()->orderBy('id')->get();
        $productionSuppliesRecords = ProductionSuppliesInventory::query()->orderBy('id')->get();
        $sanitationRecords = SanitationInventory::query()->orderBy('id')->get();
        $toolsRecords = ToolsInventory::query()->orderBy('id')->get();

        $allEquipment = $electricalRecords->concat($chemicalRecords)->concat($safetyRecords)->concat($cleaningRecords)
            ->concat($powerPlantRecords)->concat($industrialSuppliesRecords)->concat($productionSuppliesRecords)
            ->concat($sanitationRecords)->concat($toolsRecords);
        $technicalWorkingCount = $allEquipment->where('status', 'Working')->count();
        $technicalNotWorkingCount = $allEquipment->where('status', '!=', 'Working')->count();

        $inventoryItems = collect();

        $pushItems = static function ($records, string $type, string $label) use (&$inventoryItems): void {
            foreach ($records as $record) {
                $inventoryItems->push([
                    'id' => $record->id,
                    'item_name' => $record->item_name,
                    'image_path' => $record->image_path ?? null,
                    'quantity' => (int) $record->quantity,
                    'min_stock' => $record->min_stock ?? null,
                    'max_stock' => $record->max_stock ?? null,
                    'status' => $record->status ?? null,
                    'notes' => $record->notes ?? null,
                    'brand' => $record->brand ?? null,
                    'location' => $record->location ?? null,
                    'date_arrived' => $record->date_arrived ?? null,
                    'expiration_date' => $record->expiration_date ?? null,
                    'updated_at' => $record->updated_at ?? null,
                    'category_type' => $type,
                    'category_label' => $label,
                ]);
            }
        };

        $pushItems($mechanicalRecords, 'mechanical', 'Mechanical');
        $pushItems($officeSuppliesRecords, 'office-supplies', 'Office Supplies');
        $pushItems($cleaningRecords, 'cleaning', 'Cleaning');
        $pushItems($industrialSuppliesRecords, 'industrial-supplies', 'Industrial Supplies');
        $pushItems($productionSuppliesRecords, 'production-supplies', 'Production Supplies');
        $pushItems($sanitationRecords, 'sanitation', 'Sanitation');
        $pushItems($electricalRecords, 'electrical', 'Electrical');
        $pushItems($chemicalRecords, 'chemical', 'Chemical');
        $pushItems($safetyRecords, 'safety', 'Safety');
        $pushItems($powerPlantRecords, 'power-plant', 'Power Plant');
        $pushItems($toolsRecords, 'tools', 'Tools');

        $inventoryLocations = $inventoryItems->pluck('location')
            ->map(static fn ($v) => $v ?: '—')
            ->unique()
            ->sort()
            ->values();

        $inventoryCategories = [
            ['slug' => 'mechanical', 'label' => 'Mechanical'],
            ['slug' => 'office-supplies', 'label' => 'Office Supplies'],
            ['slug' => 'cleaning', 'label' => 'Cleaning'],
            ['slug' => 'industrial-supplies', 'label' => 'Industrial Supplies'],
            ['slug' => 'production-supplies', 'label' => 'Production Supplies'],
            ['slug' => 'sanitation', 'label' => 'Sanitation'],
            ['slug' => 'electrical', 'label' => 'Electrical'],
            ['slug' => 'chemical', 'label' => 'Chemical'],
            ['slug' => 'safety', 'label' => 'Safety'],
            ['slug' => 'power-plant', 'label' => 'Power Plant'],
            ['slug' => 'tools', 'label' => 'Tools'],
        ];

        return view('inventory.index', [
            'mechanicalRecords' => $mechanicalRecords,
            'officeSuppliesRecords' => $officeSuppliesRecords,
            'adjustmentRecords' => $adjustmentRecords,
            'electricalRecords' => $electricalRecords,
            'chemicalRecords' => $chemicalRecords,
            'safetyRecords' => $safetyRecords,
            'cleaningRecords' => $cleaningRecords,
            'powerPlantRecords' => $powerPlantRecords,
            'industrialSuppliesRecords' => $industrialSuppliesRecords,
            'productionSuppliesRecords' => $productionSuppliesRecords,
            'sanitationRecords' => $sanitationRecords,
            'toolsRecords' => $toolsRecords,
            'technicalWorkingCount' => $technicalWorkingCount,
            'technicalNotWorkingCount' => $technicalNotWorkingCount,
            'inventoryItems' => $inventoryItems,
            'inventoryLocations' => $inventoryLocations,
            'inventoryCategories' => $inventoryCategories,
        ]);
    }

    public function adjustmentHistory(Request $request)
    {
        $records = InventoryAdjustment::query()->orderByDesc('adjusted_at')->limit(200)->get();

        return response()->json($records);
    }

    public function storeMechanical(StoreMechanicalRequest $request): RedirectResponse
    {
        $this->assertStoreRoomRole();
        $data = $request->validated();
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('inventory', 'public');
        }
        MechanicalInventory::query()->create([
            'id' => $data['id'],
            'item_name' => $data['item_name'],
            'image_path' => $imagePath,
            'quantity' => (int) $data['quantity'],
            'min_stock' => $data['min_stock'] ?? null,
            'max_stock' => $data['max_stock'] ?? null,
            'notes' => $data['notes'] ?? null,
            'brand' => $data['brand'] ?? null,
            'location' => $data['location'] ?? null,
            'date_arrived' => $data['date_arrived'] ?? null,
            'expiration_date' => $data['expiration_date'] ?? null,
        ]);
        if ($request->filled('updated_at')) {
            MechanicalInventory::query()->where('id', $data['id'])->update(['updated_at' => $request->input('updated_at')]);
        }

        // figure out where to send user after creating
        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }
        $redirect = redirect()->route('inventory.index', $params)
            ->with('message', 'Mechanical item added successfully!')
            ->with('messageType', 'success');
        // alert if initial quantity is already at or below min stock
        $qty = (int) $data['quantity'];
        $min = isset($data['min_stock']) ? (int) $data['min_stock'] : null;
        if ($min !== null && $qty > 0 && $qty <= $min) {
            $redirect->with('lowStockItem', ['name' => $data['item_name'], 'category' => 'Mechanical']);
        }
        return $redirect;
    }

    public function updateMechanical(UpdateMechanicalRequest $request): RedirectResponse|JsonResponse
    {
        $this->assertStoreRoomRole();
        $data = $request->validated();
        $currentQty = (int) $data['current_quantity'];
        $amount = (int) $data['quantity'];
        $stockAction = $data['stock_action'];

        if ($stockAction === 'withdraw_stock' && $amount > $currentQty) {
            $message = "Requested quantity exceeds available stock. Only {$currentQty} items are left.";

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->back()->with('message', $message)->with('messageType', 'danger')->withInput();
        }

        $quantity = $stockAction === 'withdraw_stock'
            ? max(0, $currentQty - $amount)
            : $currentQty + $amount;

        $item = MechanicalInventory::query()->findOrFail($data['id']);
        $item->item_name = $data['item_name'];
        $item->quantity = $quantity;
        $item->min_stock = isset($data['min_stock']) ? (int) $data['min_stock'] : null;
        $item->max_stock = isset($data['max_stock']) ? (int) $data['max_stock'] : null;
        $item->notes = $data['notes'] ?? null;
        $item->brand = $data['brand'] ?? null;
        $item->location = $data['location'] ?? null;
        $item->date_arrived = $request->filled('date_arrived') ? $request->input('date_arrived') : null;
        $item->expiration_date = $request->filled('expiration_date') ? $request->input('expiration_date') : null;
        if ($request->hasFile('image')) {
            $item->image_path = $request->file('image')->store('inventory', 'public');
        }
        $item->save();

        $departmentRequested = $stockAction === 'withdraw_stock'
            ? $request->input('department_requested')
            : null;

        if ($amount !== 0) {
            $this->logAdjustment(
                'mechanical',
                $item->id,
                $item->item_name,
                $stockAction === 'withdraw_stock' ? -$amount : $amount,
                $currentQty,
                $quantity,
                'Mechanical',
                $departmentRequested
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mechanical item updated successfully!',
                'item' => $this->itemToArray($item, 'mechanical', 'Mechanical'),
            ]);
        }

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'mechanical';
        }

        $redirect = redirect()->route('inventory.index', $params)
            ->with('message', 'Mechanical item updated successfully!')
            ->with('messageType', 'success');
        // notify if stock dropped to zero
        if ($quantity === 0) {
            $redirect->with('outOfStockItem', ['name' => $item->item_name, 'category' => 'Mechanical', 'message' => 'is now out of stock.']);
        }
        // also notify if we just crossed the minimum stock threshold
        $min = $item->min_stock !== null ? (int) $item->min_stock : null;
        if ($min !== null && $quantity > 0 && $quantity <= $min && $currentQty > $min) {
            $redirect->with('lowStockItem', ['name' => $item->item_name, 'category' => 'Mechanical']);
        }

        return $redirect;
    }

    public function destroyMechanical(Request $request): RedirectResponse
    {
        $this->assertStoreRoomRole();
        $id = $request->validate(['id' => ['required', 'string']])['id'];
        MechanicalInventory::query()->where('id', $id)->delete();

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }
        return redirect()->route('inventory.index', $params)
            ->with('message', 'Mechanical item deleted successfully!')
            ->with('messageType', 'success');
    }

    public function storeOfficeSupply(StoreOfficeSupplyRequest $request): RedirectResponse
    {
        $this->assertStoreRoomRole();
        $data = $request->validated();
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('inventory', 'public');
        }
        OfficeSuppliesInventory::query()->create([
            'id' => $data['id'],
            'item_name' => $data['item_name'],
            'image_path' => $imagePath,
            'quantity' => (int) $data['quantity'],
            'min_stock' => $data['min_stock'] ?? null,
            'max_stock' => $data['max_stock'] ?? null,
            'notes' => $data['notes'] ?? null,
            'brand' => $data['brand'] ?? null,
            'location' => $data['location'] ?? null,
            'date_arrived' => $data['date_arrived'] ?? null,
            'expiration_date' => $data['expiration_date'] ?? null,
        ]);
        if ($request->filled('updated_at')) {
            OfficeSuppliesInventory::query()->where('id', $data['id'])->update(['updated_at' => $request->input('updated_at')]);
        }

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }
        $redirect = redirect()->route('inventory.index', $params)
            ->with('message', 'Office supply added successfully!')
            ->with('messageType', 'success');
        $qty = (int) $data['quantity'];
        $min = isset($data['min_stock']) ? (int) $data['min_stock'] : null;
        if ($min !== null && $qty > 0 && $qty <= $min) {
            $redirect->with('lowStockItem', ['name' => $data['item_name'], 'category' => 'Office Supplies']);
        }
        return $redirect;
    }

    public function updateOfficeSupply(UpdateOfficeSupplyRequest $request): RedirectResponse|JsonResponse
    {
        $this->assertStoreRoomRole();
        $data = $request->validated();
        $currentQty = (int) $data['current_quantity'];
        $amount = (int) $data['quantity'];
        $stockAction = $data['stock_action'];

        if ($stockAction === 'withdraw_stock' && $amount > $currentQty) {
            $message = "Requested quantity exceeds available stock. Only {$currentQty} items are left.";

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->back()->with('message', $message)->with('messageType', 'danger')->withInput();
        }

        $quantity = $stockAction === 'withdraw_stock'
            ? max(0, $currentQty - $amount)
            : $currentQty + $amount;

        $item = OfficeSuppliesInventory::query()->findOrFail($data['id']);
        $item->item_name = $data['item_name'];
        $item->quantity = $quantity;
        $item->min_stock = isset($data['min_stock']) ? (int) $data['min_stock'] : null;
        $item->max_stock = isset($data['max_stock']) ? (int) $data['max_stock'] : null;
        $item->notes = $data['notes'] ?? null;
        $item->brand = $data['brand'] ?? null;
        $item->location = $data['location'] ?? null;
        $item->date_arrived = $request->filled('date_arrived') ? $request->input('date_arrived') : null;
        $item->expiration_date = $request->filled('expiration_date') ? $request->input('expiration_date') : null;
        if ($request->hasFile('image')) {
            $item->image_path = $request->file('image')->store('inventory', 'public');
        }
        $item->save();

        $departmentRequested = $stockAction === 'withdraw_stock'
            ? $request->input('department_requested')
            : null;

        if ($amount !== 0) {
            $this->logAdjustment(
                'office_supplies',
                $item->id,
                $item->item_name,
                $stockAction === 'withdraw_stock' ? -$amount : $amount,
                $currentQty,
                $quantity,
                'Office supply',
                $departmentRequested
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Office supply updated successfully!',
                'item' => $this->itemToArray($item, 'office-supplies', 'Office Supplies'),
            ]);
        }

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }

        $redirect = redirect()->route('inventory.index', $params)
            ->with('message', 'Office supply updated successfully!')
            ->with('messageType', 'success');
        if ($quantity === 0) {
            $redirect->with('outOfStockItem', ['name' => $item->item_name, 'category' => 'Office Supplies', 'message' => 'is now out of stock.']);
        }
        $min = $item->min_stock !== null ? (int) $item->min_stock : null;
        if ($min !== null && $quantity > 0 && $quantity <= $min && $currentQty > $min) {
            $redirect->with('lowStockItem', ['name' => $item->item_name, 'category' => 'Office Supplies']);
        }

        return $redirect;
    }

    public function destroyOfficeSupply(Request $request): RedirectResponse
    {
        $this->assertStoreRoomRole();
        $id = $request->validate(['id' => ['required', 'string']])['id'];
        OfficeSuppliesInventory::query()->where('id', $id)->delete();

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }
        return redirect()->route('inventory.index', $params)
            ->with('message', 'Office supply deleted successfully!')
            ->with('messageType', 'success');
    }

    private const EQUIPMENT_CATEGORIES = [
        'electrical' => [ElectricalInventory::class, 'Electrical', 'electrical'],
        'chemical' => [ChemicalInventory::class, 'Chemical', 'chemical'],
        'safety' => [SafetyInventory::class, 'Safety', 'safety'],
        'cleaning' => [CleaningInventory::class, 'Cleaning', 'cleaning'],
        'power-plant' => [PowerPlantInventory::class, 'Power Plant', 'power-plant'],
        'industrial-supplies' => [IndustrialSuppliesInventory::class, 'Industrial Supplies', 'industrial-supplies'],
        'production-supplies' => [ProductionSuppliesInventory::class, 'Production Supplies', 'production-supplies'],
        'sanitation' => [SanitationInventory::class, 'Sanitation', 'sanitation'],
        'tools' => [ToolsInventory::class, 'Tools', 'tools'],
    ];

    public function storeEquipmentByCategory(StoreEquipmentByCategoryRequest $request, string $category): RedirectResponse
    {
        $this->assertStoreRoomRole();
        $config = self::EQUIPMENT_CATEGORIES[$category] ?? null;
        if (! $config) {
            return redirect()->route('inventory.index')->with('message', 'Invalid category.')->with('messageType', 'danger');
        }
        [$modelClass, $label, $fragment] = $config;
        $data = $request->validated();
        if ($modelClass::query()->where('id', $data['id'])->exists()) {
            return redirect()->back()->with('message', 'Item code already exists in this category.')->with('messageType', 'danger')->withInput();
        }
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('inventory', 'public');
        }
        $modelClass::query()->create([
            'id' => $data['id'],
            'item_name' => $data['item_name'],
            'image_path' => $imagePath,
            'quantity' => (int) $data['quantity'],
            'min_stock' => $data['min_stock'] ?? null,
            'max_stock' => $data['max_stock'] ?? null,
            'status' => $data['status'] ?? 'Working',
            'notes' => $data['notes'] ?? null,
            'brand' => $data['brand'] ?? null,
            'location' => $data['location'] ?? null,
            'date_arrived' => $data['date_arrived'] ?? null,
            'expiration_date' => $data['expiration_date'] ?? null,
        ]);
        if ($request->filled('updated_at')) {
            $modelClass::query()->where('id', $data['id'])->update(['updated_at' => $request->input('updated_at')]);
        }

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }
        $redirect = redirect()->route('inventory.index', $params)
            ->with('message', "{$label} item added successfully!")
            ->with('messageType', 'success');
        $qty = (int) $data['quantity'];
        $min = isset($data['min_stock']) ? (int) $data['min_stock'] : null;
        if ($min !== null && $qty > 0 && $qty <= $min) {
            $redirect->with('lowStockItem', ['name' => $data['item_name'], 'category' => $label]);
        }
        return $redirect;
    }

    public function updateEquipmentByCategory(UpdateTechnicalEquipmentRequest $request, string $category): RedirectResponse|JsonResponse
    {
        $this->assertStoreRoomRole();
        $config = self::EQUIPMENT_CATEGORIES[$category] ?? null;
        if (! $config) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Invalid category.'], 400)
                : redirect()->route('inventory.index')->with('message', 'Invalid category.')->with('messageType', 'danger');
        }
        [$modelClass, $label, $fragment] = $config;
        $data = $request->validated();
        $currentQty = (int) $data['current_quantity'];
        $amount = (int) $data['quantity'];
        $stockAction = $data['stock_action'];

        if ($stockAction === 'withdraw_stock' && $amount > $currentQty) {
            $message = "Requested quantity exceeds available stock. Only {$currentQty} items are left.";

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->back()->with('message', $message)->with('messageType', 'danger')->withInput();
        }

        $quantity = $stockAction === 'withdraw_stock' ? max(0, $currentQty - $amount) : $currentQty + $amount;

        $item = $modelClass::query()->findOrFail($data['id']);
        $item->item_name = $data['item_name'];
        $item->quantity = $quantity;
        $item->min_stock = isset($data['min_stock']) ? (int) $data['min_stock'] : null;
        $item->max_stock = isset($data['max_stock']) ? (int) $data['max_stock'] : null;
        $item->status = $data['status'] ?? $item->status;
        $item->notes = $data['notes'] ?? null;
        $item->brand = $data['brand'] ?? null;
        $item->location = $data['location'] ?? null;
        $item->date_arrived = $request->filled('date_arrived') ? $request->input('date_arrived') : null;
        $item->expiration_date = $request->filled('expiration_date') ? $request->input('expiration_date') : null;
        if ($request->hasFile('image')) {
            $item->image_path = $request->file('image')->store('inventory', 'public');
        }
        $item->save();

        $departmentRequested = $stockAction === 'withdraw_stock'
            ? $request->input('department_requested')
            : null;

        if ($amount !== 0) {
            $this->logAdjustment(
                $category,
                $item->id,
                $item->item_name,
                $stockAction === 'withdraw_stock' ? -$amount : $amount,
                $currentQty,
                $quantity,
                $label,
                $departmentRequested
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$label} item updated successfully!",
                'item' => $this->itemToArray($item, $category, $label),
            ]);
        }

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }

        $redirect = redirect()->route('inventory.index', $params)
            ->with('message', "{$label} item updated successfully!")
            ->with('messageType', 'success');
        if ($quantity === 0) {
            $redirect->with('outOfStockItem', ['name' => $item->item_name, 'category' => $label, 'message' => 'is now out of stock.']);
        }
        $min = $item->min_stock !== null ? (int) $item->min_stock : null;
        if ($min !== null && $quantity > 0 && $quantity <= $min && $currentQty > $min) {
            $redirect->with('lowStockItem', ['name' => $item->item_name, 'category' => $label]);
        }

        return $redirect;
    }

    public function destroyEquipmentByCategory(Request $request, string $category): RedirectResponse
    {
        $this->assertStoreRoomRole();
        $config = self::EQUIPMENT_CATEGORIES[$category] ?? null;
        if (! $config) {
            return redirect()->route('inventory.index')->with('message', 'Invalid category.')->with('messageType', 'danger');
        }
        [$modelClass, $label, $fragment] = $config;
        $id = $request->validate(['id' => ['required', 'string']])['id'];
        $modelClass::query()->where('id', $id)->delete();

        $returnTab = $request->input('return_tab');
        $returnCategory = $request->input('return_category');
        $params = [];
        if ($returnTab) {
            $params['tab'] = $returnTab;
            if ($returnTab === 'inventory' && $returnCategory) {
                $params['category'] = $returnCategory;
            }
        } else {
            $params['tab'] = 'inventory';
        }
        return redirect()->route('inventory.index', $params)
            ->with('message', "{$label} item deleted successfully!")
            ->with('messageType', 'success');
    }

    private function logAdjustment(string $inventoryType, string $itemId, ?string $itemName, int $changeAmount, int $quantityBefore, int $quantityAfter, ?string $typeLabel = null, ?string $departmentRequested = null): void
    {
        InventoryAdjustment::query()->create([
            'inventory_type' => $inventoryType,
            'type_label' => $typeLabel,
            'item_id' => $itemId,
            'item_name' => $itemName,
            'change_amount' => $changeAmount,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'department_requested' => $departmentRequested,
            'adjusted_by' => Auth::user()?->name ?? Auth::user()?->email ?? 'Unknown',
        ]);
    }
}
