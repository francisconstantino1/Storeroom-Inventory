<?php

namespace App\Providers;

use App\Models\ChemicalInventory;
use App\Models\CleaningInventory;
use App\Models\ElectricalInventory;
use App\Models\IndustrialSuppliesInventory;
use App\Models\ItemRequest;
use App\Models\MechanicalInventory;
use App\Models\OfficeSuppliesInventory;
use App\Models\PowerPlantInventory;
use App\Models\ProductionSuppliesInventory;
use App\Models\SafetyInventory;
use App\Models\SanitationInventory;
use App\Models\ToolsInventory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(
            ['dashboard', 'inventory.index', 'requests.index', 'settings.index', 'login-logs.index'],
            function ($view): void {
                $pendingCount = 0;
                $expiredCount = 0;
                $myPendingCount = 0;
                $isStoreRoom = false;
                $isDepartmentSupervisor = false;
                if (Auth::check()) {
                    $user = Auth::user();
                    $role = $user->role ?? '';
                    $isStoreRoom = in_array($role, ['Store Room Supervisor', 'Store Room Assistant'], true);
                    $isDepartmentSupervisor = in_array($role, ['Engineering Supervisor', 'Production Supervisor', 'HR Supervisor', 'Finance Supervisor', 'Taxation Supervisor'], true);
                    if ($isStoreRoom) {
                        $pendingCount = ItemRequest::query()->where('status', 'Pending')->count();
                        $expiredCount = $this->countExpiredInventoryItems();
                    }
                    if (! $isStoreRoom) {
                        $myPendingCount = ItemRequest::query()
                            ->where('requested_by_user_id', $user->id)
                            ->where('status', 'Pending')
                            ->count();
                    }
                }
                $view->with([
                    'pendingItemRequestsCount' => $pendingCount,
                    'expiredInventoryItemsCount' => $expiredCount,
                    'myPendingItemRequestsCount' => $myPendingCount,
                    'isStoreRoom' => $isStoreRoom,
                    'isDepartmentSupervisor' => $isDepartmentSupervisor,
                ]);
            }
        );
    }

    /**
     * Count inventory items whose expiration_date is before today (already expired).
     */
    private function countExpiredInventoryItems(): int
    {
        $today = Carbon::today();

        $models = [
            MechanicalInventory::class,
            OfficeSuppliesInventory::class,
            ElectricalInventory::class,
            ChemicalInventory::class,
            SafetyInventory::class,
            CleaningInventory::class,
            PowerPlantInventory::class,
            IndustrialSuppliesInventory::class,
            ProductionSuppliesInventory::class,
            SanitationInventory::class,
            ToolsInventory::class,
        ];

        $total = 0;
        foreach ($models as $model) {
            $total += $model::query()
                ->whereNotNull('expiration_date')
                ->where('expiration_date', '<', $today)
                ->count();
        }

        return $total;
    }
}
