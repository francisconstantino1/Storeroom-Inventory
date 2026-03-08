<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\LoginLogsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/login-logs', LoginLogsController::class)->name('login-logs.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::post('/settings/users/update', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    Route::post('/settings/users/delete', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/barcode', [InventoryController::class, 'barcode'])->name('inventory.barcode');
    Route::get('/inventory/adjustment-history', [InventoryController::class, 'adjustmentHistory'])->name('inventory.adjustment-history');

    Route::get('/requests', [ItemRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/pending-data', [ItemRequestController::class, 'pendingData'])->name('requests.pending-data');
    Route::post('/requests', [ItemRequestController::class, 'store'])->name('requests.store');
    Route::post('/requests/{itemRequest}/approve', [ItemRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{itemRequest}/reject', [ItemRequestController::class, 'reject'])->name('requests.reject');
    Route::post('/inventory/mechanical', [InventoryController::class, 'storeMechanical'])->name('inventory.mechanical.store');
    Route::post('/inventory/mechanical/update', [InventoryController::class, 'updateMechanical'])->name('inventory.mechanical.update');
    Route::post('/inventory/mechanical/delete', [InventoryController::class, 'destroyMechanical'])->name('inventory.mechanical.destroy');
    Route::post('/inventory/office-supplies', [InventoryController::class, 'storeOfficeSupply'])->name('inventory.office-supplies.store');
    Route::post('/inventory/office-supplies/update', [InventoryController::class, 'updateOfficeSupply'])->name('inventory.office-supplies.update');
    Route::post('/inventory/office-supplies/delete', [InventoryController::class, 'destroyOfficeSupply'])->name('inventory.office-supplies.destroy');
    Route::post('/inventory/equipment/{category}', [InventoryController::class, 'storeEquipmentByCategory'])->name('inventory.equipment.store')->where('category', 'electrical|chemical|safety|cleaning|power-plant|industrial-supplies|production-supplies|sanitation|tools');
    Route::post('/inventory/equipment/{category}/update', [InventoryController::class, 'updateEquipmentByCategory'])->name('inventory.equipment.update')->where('category', 'electrical|chemical|safety|cleaning|power-plant|industrial-supplies|production-supplies|sanitation|tools');
    Route::post('/inventory/equipment/{category}/delete', [InventoryController::class, 'destroyEquipmentByCategory'])->name('inventory.equipment.destroy')->where('category', 'electrical|chemical|safety|cleaning|power-plant|industrial-supplies|production-supplies|sanitation|tools');
});
