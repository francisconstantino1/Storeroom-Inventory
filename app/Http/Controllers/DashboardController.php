<?php

namespace App\Http\Controllers;

use App\Models\ChemicalInventory;
use App\Models\CleaningInventory;
use App\Models\ElectricalInventory;
use App\Models\IndustrialSuppliesInventory;
use App\Models\MechanicalInventory;
use App\Models\OfficeSuppliesInventory;
use App\Models\PowerPlantInventory;
use App\Models\ProductionSuppliesInventory;
use App\Models\SafetyInventory;
use App\Models\SanitationInventory;
use App\Models\ToolsInventory;
use App\Models\ItemRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Category label and inventory tab fragment for technical equipment categories.
     *
     * @var array<string, string>
     */
    private const TE_CATEGORY_FRAGMENTS = [
        'Electrical' => 'electrical',
        'Chemical' => 'technical-equipments',
        'Safety' => 'safety',
        'Cleaning' => 'cleaning',
        'Power Plant' => 'power-plant',
        'Industrial Supplies' => 'industrial-supplies',
        'Production Supplies' => 'production-supplies',
        'Sanitation' => 'sanitation',
        'Tools' => 'tools',
    ];

    public function index(): View
    {
        $churchPropertyCount = MechanicalInventory::query()->count();
        $officeSuppliesCount = OfficeSuppliesInventory::query()->count();
        $totalItems = $churchPropertyCount + $officeSuppliesCount;

        $lowStockByCategory = $this->buildLowStockByCategory();
        $expiringSoonByCategory = $this->buildExpiringSoonByCategory();

        $user = Auth::user();
        $pendingRequests = collect();
        if ($user) {
            $pendingRequests = ItemRequest::query()
                ->where('requested_by_user_id', $user->id)
                ->where('status', 'Pending')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        return view('dashboard', [
            'churchPropertyCount' => $churchPropertyCount,
            'officeSuppliesCount' => $officeSuppliesCount,
            'totalItems' => $totalItems,
            'lowStockByCategory' => $lowStockByCategory,
            'expiringSoonByCategory' => $expiringSoonByCategory,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * Build list of categories with items at or below min_stock (need restock).
     *
     * @return array<int, array{label: string, fragment: string, count: int, items: array<int, array{name: string, quantity: int, min_stock: int|null}>}>
     */
    private function buildLowStockByCategory(): array
    {
        $result = [];

        $churchLow = MechanicalInventory::query()
            ->whereNotNull('min_stock')
            ->whereRaw('quantity <= min_stock')
            ->get();
        if ($churchLow->isNotEmpty()) {
            $result[] = [
                'label' => 'Mechanical',
                'fragment' => 'mechanical',
                'count' => $churchLow->count(),
                'items' => $churchLow->map(fn ($r) => [
                    'name' => $r->item_name,
                    'quantity' => $r->quantity,
                    'min_stock' => $r->min_stock,
                ])->values()->all(),
            ];
        }

        $officeLow = OfficeSuppliesInventory::query()
            ->whereNotNull('min_stock')
            ->whereRaw('quantity <= min_stock')
            ->get();
        if ($officeLow->isNotEmpty()) {
            $result[] = [
                'label' => 'Office Supplies',
                'fragment' => 'office-supplies',
                'count' => $officeLow->count(),
                'items' => $officeLow->map(fn ($r) => [
                    'name' => $r->item_name,
                    'quantity' => $r->quantity,
                    'min_stock' => $r->min_stock,
                ])->values()->all(),
            ];
        }

        return $result;
    }

    /**
     * Build list of categories with items whose expiration date is within the next month.
     *
     * @return array<int, array{label: string, fragment: string, count: int, items: array<int, array{name: string, id: string, expiration_date: string}>>>
     */
    private function buildExpiringSoonByCategory(): array
    {
        $today = Carbon::today();
        $threshold = $today->copy()->addMonth();

        $result = [];

        $mechanical = MechanicalInventory::query()
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$today, $threshold])
            ->get();

        if ($mechanical->isNotEmpty()) {
            $result[] = [
                'label' => 'Mechanical',
                'fragment' => 'mechanical',
                'count' => $mechanical->count(),
                'items' => $mechanical->map(fn (MechanicalInventory $r) => [
                    'id' => $r->id,
                    'name' => $r->item_name,
                    'expiration_date' => optional($r->expiration_date)->format('M d, Y') ?? '—',
                ])->values()->all(),
            ];
        }

        $office = OfficeSuppliesInventory::query()
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$today, $threshold])
            ->get();

        if ($office->isNotEmpty()) {
            $result[] = [
                'label' => 'Office Supplies',
                'fragment' => 'office-supplies',
                'count' => $office->count(),
                'items' => $office->map(fn (OfficeSuppliesInventory $r) => [
                    'id' => $r->id,
                    'name' => $r->item_name,
                    'expiration_date' => optional($r->expiration_date)->format('M d, Y') ?? '—',
                ])->values()->all(),
            ];
        }

        $equipmentGroups = [
            'Electrical' => [ElectricalInventory::class, 'electrical'],
            'Chemical' => [ChemicalInventory::class, 'chemical'],
            'Safety' => [SafetyInventory::class, 'safety'],
            'Cleaning' => [CleaningInventory::class, 'cleaning'],
            'Power Plant' => [PowerPlantInventory::class, 'power-plant'],
            'Industrial Supplies' => [IndustrialSuppliesInventory::class, 'industrial-supplies'],
            'Production Supplies' => [ProductionSuppliesInventory::class, 'production-supplies'],
            'Sanitation' => [SanitationInventory::class, 'sanitation'],
            'Tools' => [ToolsInventory::class, 'tools'],
        ];

        foreach ($equipmentGroups as $label => [$modelClass, $fragment]) {
            /** @var \Illuminate\Database\Eloquent\Collection $items */
            $items = $modelClass::query()
                ->whereNotNull('expiration_date')
                ->whereBetween('expiration_date', [$today, $threshold])
                ->get();

            if ($items->isNotEmpty()) {
                $result[] = [
                    'label' => $label,
                    'fragment' => $fragment,
                    'count' => $items->count(),
                    'items' => $items->map(static function ($r) {
                        return [
                            'id' => $r->id,
                            'name' => $r->item_name,
                            'expiration_date' => optional($r->expiration_date)->format('M d, Y') ?? '—',
                        ];
                    })->values()->all(),
                ];
            }
        }

        return $result;
    }
}
