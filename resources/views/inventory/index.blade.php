<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Room Department — Franklin Baker</title>
    <link rel="icon" type="image/png" href="{{ asset('images/franklin-baker-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="inv-page-wrap">
        <header class="inv-top-bar">
            <img src="{{ asset('images/franklin-baker-logo.png') }}" alt="Franklin Baker" class="inv-logo">
            <div class="inv-top-bar-title">
                <h1 class="inv-top-bar-heading">Store Room Department</h1>
                <p class="inv-top-bar-welcome">Welcome, {{ auth()->user()->name }}</p>
            </div>
            <div class="inv-user-dropdown-wrap">
                <button type="button" id="inv-user-dropdown-trigger" class="inv-user-dropdown-trigger" aria-expanded="false" aria-haspopup="true">
                    <span class="inv-user-avatar">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="">
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        @endif
                    </span>
                    <span>{{ auth()->user()->name }}</span>
                    <svg class="inv-user-chevron" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                </button>
                @if($isStoreRoom && (($pendingItemRequestsCount ?? 0) > 0 || ($expiredInventoryItemsCount ?? 0) > 0))
                    <span class="inv-trigger-badges">
                        @if(($pendingItemRequestsCount ?? 0) > 0)
                            <span class="inv-trigger-notification-badge" aria-label="{{ $pendingItemRequestsCount }} pending item request(s)">{{ $pendingItemRequestsCount > 99 ? '99+' : $pendingItemRequestsCount }}</span>
                        @endif
                        @if(($expiredInventoryItemsCount ?? 0) > 0)
                            <span class="inv-trigger-notification-badge inv-trigger-badge-expired" aria-label="{{ $expiredInventoryItemsCount }} expired item(s)">{{ $expiredInventoryItemsCount > 99 ? '99+' : $expiredInventoryItemsCount }}</span>
                        @endif
                    </span>
                @elseif(!$isStoreRoom && ($myPendingItemRequestsCount ?? 0) > 0)
                    <span class="inv-trigger-badges">
                        <span class="inv-trigger-notification-badge" aria-label="{{ $myPendingItemRequestsCount }} of your request(s) pending">{{ $myPendingItemRequestsCount > 99 ? '99+' : $myPendingItemRequestsCount }}</span>
                    </span>
                @endif
                <div id="inv-user-dropdown" class="inv-user-dropdown" role="menu">
                    <div class="inv-user-dropdown-header">
                        <span class="inv-user-avatar">
                            @if(auth()->user()->profile_picture)
                                <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="">
                            @else
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            @endif
                        </span>
                        <div class="inv-user-info">
                            <div class="inv-user-name">{{ auth()->user()->name }}</div>
                            <div class="inv-user-email">{{ auth()->user()->email }}</div>
                        </div>
                        <span class="inv-user-dropdown-badge">{{ auth()->user()->role ?? 'User' }}</span>
                    </div>
                    <ul class="inv-user-dropdown-menu">
                        <li><a href="{{ route('dashboard') }}" role="menuitem">
                            <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4 12h16M4 8h16M4 16h8"/></svg>
                            Dashboard
                        </a></li>
                        @php
                            $role = auth()->user()->role ?? '';
                            $isStoreRoom = in_array($role, ['Store Room Supervisor', 'Store Room Assistant'], true);
                            $isDepartmentSupervisor = in_array($role, ['Engineering Supervisor', 'Production Supervisor', 'HR Supervisor', 'Finance Supervisor', 'Taxation Supervisor'], true);
                        @endphp
                        <li><a href="{{ route('inventory.index') }}" {{ $isStoreRoom && ($expiredInventoryItemsCount ?? 0) > 0 ? 'class="inv-dropdown-item-with-badge"' : '' }} role="menuitem">
                            <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19V5a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1Zm0 0V9a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10M5 8V5"/></svg>
                            {{ $isStoreRoom ? 'Inventory' : 'Request Item' }}
                            @if($isStoreRoom && ($expiredInventoryItemsCount ?? 0) > 0)
                                <span class="inv-dropdown-notification-badge inv-dropdown-notification-badge-expired" aria-label="{{ $expiredInventoryItemsCount }} expired item(s)">{{ $expiredInventoryItemsCount > 99 ? '99+' : $expiredInventoryItemsCount }}</span>
                            @endif
                        </a></li>
                        @if($isStoreRoom)
                            <li><a href="{{ route('requests.index') }}" {{ ($pendingItemRequestsCount ?? 0) > 0 ? 'class="inv-dropdown-item-with-badge"' : '' }} role="menuitem">
                                <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h7"/></svg>
                                Item Requests
                                @if(($pendingItemRequestsCount ?? 0) > 0)
                                    <span class="inv-dropdown-notification-badge" aria-label="{{ $pendingItemRequestsCount }} pending item request(s)">{{ $pendingItemRequestsCount > 99 ? '99+' : $pendingItemRequestsCount }}</span>
                                @endif
                            </a></li>
                        @elseif($isDepartmentSupervisor)
                            <li><a href="{{ route('requests.index') }}" {{ ($myPendingItemRequestsCount ?? 0) > 0 ? 'class="inv-dropdown-item-with-badge"' : '' }} role="menuitem">
                                <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h7"/></svg>
                                Pending Items
                                @if(($myPendingItemRequestsCount ?? 0) > 0)
                                    <span class="inv-dropdown-notification-badge" aria-label="{{ $myPendingItemRequestsCount }} request(s) pending approval">{{ $myPendingItemRequestsCount > 99 ? '99+' : $myPendingItemRequestsCount }}</span>
                                @endif
                            </a></li>
                        @endif
                        <li><a href="{{ route('settings.index') }}" role="menuitem">
                            <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M20 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6h-2m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4m16 6H10m0 0a2 2 0 1 0-4 0m4 0a2 2 0 1 1-4 0m0 0H4"/></svg>
                            Settings
                        </a></li>
                        @if(auth()->user()->role === 'Store Room Supervisor')
                        <li><a href="{{ route('login-logs.index') }}" role="menuitem">
                            <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/></svg>
                            Login Logs
                        </a></li>
                        @endif
                        <li class="inv-user-dropdown-border"><form action="{{ route('logout') }}" method="post" style="margin:0;">
                            @csrf
                            <button type="submit" class="inv-user-dropdown-signout" role="menuitem">
                                <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/></svg>
                                Sign out
                            </button>
                        </form></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="inv-content">
        @if(session('message'))
            <div id="inv-flash-alert" class="inv-alert {{ session('messageType', 'success') === 'danger' ? 'danger' : 'success' }}">
                <i class="fas fa-info-circle"></i>
                {{ session('message') }}
            </div>
        @endif

        @php
            // Location lists for each legacy category-specific table (mechanical, office supplies, etc.)
            // These are still used further down in the view.
            $mechanicalLocations = $mechanicalRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $officeSuppliesLocations = $officeSuppliesRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $electricalLocations = $electricalRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $chemicalLocations = $chemicalRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $safetyLocations = $safetyRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $cleaningLocations = $cleaningRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $powerPlantLocations = $powerPlantRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $industrialSuppliesLocations = $industrialSuppliesRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $productionSuppliesLocations = $productionSuppliesRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $sanitationLocations = $sanitationRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();
            $toolsLocations = $toolsRecords->pluck('location')->map(fn ($v) => $v ?: '—')->unique()->sort()->values();

            $activeTab = request()->query('tab', 'inventory');
            if (! in_array($activeTab, ['inventory', 'adjustment-history'], true)) {
                $activeTab = 'inventory';
            }
        @endphp

        <div class="inv-main-tabs" role="tablist">
            <a href="{{ route('inventory.index', ['tab' => 'inventory']) }}" class="{{ $activeTab === 'inventory' ? 'active' : '' }}">Inventory</a>
            @if($isStoreRoom)
                <a href="{{ route('inventory.index', ['tab' => 'adjustment-history']) }}" class="{{ $activeTab === 'adjustment-history' ? 'active' : '' }}">History</a>
            @endif
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'inventory' ? 'active' : '' }}" id="inventory">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-unified-item-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="inventory-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-inventory-table" aria-label="Filter by stock">
                    <option value="all" {{ request('stock') === 'all' || !request('stock') ? 'selected' : '' }}>All</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low stock only</option>
                    <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>In stock</option>
                    <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-inventory-table" aria-label="Filter by location">
                    <option value="" {{ request('location') === '' || !request('location') ? 'selected' : '' }}>All locations</option>
                    @foreach($inventoryLocations ?? [] as $loc)
                        <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
                <label class="inv-filter-label">Category:</label>
                <select class="inv-filter-select" id="filter-category-inventory-table" aria-label="Filter by category">
                    <option value="" {{ request('category') === '' ? 'selected' : '' }}>All categories</option>
                    @foreach($inventoryCategories ?? [] as $cat)
                        <option value="{{ $cat['slug'] }}" {{ request('category') === $cat['slug'] ? 'selected' : '' }}>{{ $cat['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="inv-table" id="inventory-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($inventoryItems ?? collect()) as $row)
                            @php
                                $qty = (int) ($row['quantity'] ?? 0);
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $minStock = $row['min_stock'] ?? null;
                                $isLowStock = $minStock !== null && $qty <= (int) $minStock;
                                $rowLocation = $row['location'] ?? '—';
                                $imageUrl = ! empty($row['image_path']) ? asset('storage/'.$row['image_path']) : '';
                                $updatedAt = $row['updated_at']?->format('M d, Y H:i') ?? 'N/A';
                            @endphp
                            <tr
                                data-id="{{ $row['id'] }}"
                                data-type="{{ $row['category_type'] }}"
                                data-category="{{ $row['category_type'] }}"
                                data-min-stock="{{ $row['min_stock'] ?? '' }}"
                                data-max-stock="{{ $row['max_stock'] ?? '' }}"
                                data-updated="{{ $updatedAt }}"
                                data-low-stock="{{ $isLowStock ? '1' : '0' }}"
                                data-out-of-stock="{{ $qty <= 0 ? '1' : '0' }}"
                                data-location="{{ $rowLocation }}"
                                data-image="{{ $imageUrl }}"
                            >
                                <td>{{ $row['id'] }}</td>
                                <td>{{ $row['item_name'] }}</td>
                                <td>{{ $row['category_label'] }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $qty }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if(!empty($row['brand']))
                                        <span class="inv-badge brand">{{ $row['brand'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $row['location'] ?? '—' }}</td>
                                <td>{{ $row['date_arrived'] ? \Illuminate\Support\Carbon::parse($row['date_arrived'])->format('M d, Y') : '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $row['expiration_date'] ? \Illuminate\Support\Carbon::parse($row['expiration_date'])->format('M d, Y') : '—' }}</span></td>
                                <td>{{ $row['notes'] ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $row['id'], 'item_description' => $row['item_name'] ?? '']) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="{{ ($row['category_type'] ?? '') === 'office-supplies' ? 'office_supplies' : ($row['category_type'] ?? '') }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" style="text-align: center; padding: 24px;">No inventory records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'mechanical' ? 'active' : '' }}" id="mechanical">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-mechanical-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="mechanical-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-mechanical-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-mechanical-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($mechanicalLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="mechanical-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mechanicalRecords as $record)
                            @php
                                $qty = (int) $record->quantity;
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock;
                                $rowLocation = $record->location ?? '—';
                            @endphp
                            <tr data-id="{{ $record->id }}" data-type="mechanical" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if($record->brand)
                                        <span class="inv-badge brand">{{ $record->brand }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}" data-type="mechanical"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}" data-type="mechanical"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}" data-type="mechanical"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="mechanical"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No mechanical records. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'office-supplies' ? 'active' : '' }}" id="office-supplies">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-office-supplies-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="office-supplies-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-office-supplies-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-office-supplies-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($officeSuppliesLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="office-supplies-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($officeSuppliesRecords as $record)
                            @php
                                $qty = (int) $record->quantity;
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock;
                                $rowLocation = $record->location ?? '—';
                            @endphp
                            <tr data-id="{{ $record->id }}" data-type="office-supplies" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if($record->brand)
                                        <span class="inv-badge brand">{{ $record->brand }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}" data-type="office-supplies"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}" data-type="office-supplies"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}" data-type="office-supplies"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="office_supplies"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No office supplies. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'electrical' ? 'active' : '' }}" id="electrical">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-electrical-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="electrical-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-electrical-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-electrical-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($electricalLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="electrical-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($electricalRecords as $record)
                            @php
                                $qty = (int) $record->quantity;
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock;
                                $rowLocation = $record->location ?? '—';
                            @endphp
                            <tr data-id="{{ $record->id }}" data-type="electrical" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if($record->brand)
                                        <span class="inv-badge brand">{{ $record->brand }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="electrical"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No electrical items. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'chemical' ? 'active' : '' }}" id="chemical">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-chemical-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="chemical-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-chemical-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-chemical-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($chemicalLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="chemical-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chemicalRecords as $record)
                            @php
                                $qty = (int) $record->quantity;
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock;
                                $rowLocation = $record->location ?? '—';
                            @endphp
                            <tr data-id="{{ $record->id }}" data-type="chemical" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if($record->brand)
                                        <span class="inv-badge brand">{{ $record->brand }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="chemical"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No chemical items. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'safety' ? 'active' : '' }}" id="safety">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-safety-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="safety-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-safety-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-safety-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($safetyLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="safety-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($safetyRecords as $record)
                            @php
                                $qty = (int) $record->quantity;
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock;
                                $rowLocation = $record->location ?? '—';
                            @endphp
                            <tr data-id="{{ $record->id }}" data-type="safety" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if($record->brand)
                                        <span class="inv-badge brand">{{ $record->brand }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="safety"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No safety items. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'cleaning' ? 'active' : '' }}" id="cleaning">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-cleaning-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="cleaning-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-cleaning-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-cleaning-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($cleaningLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="cleaning-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cleaningRecords as $record)
                            @php
                                $qty = (int) $record->quantity;
                                $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock';
                                $badgeClass = $qty <= 0 ? 'out' : 'in-stock';
                                $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock;
                                $rowLocation = $record->location ?? '—';
                            @endphp
                            <tr data-id="{{ $record->id }}" data-type="cleaning" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>
                                    @if($record->brand)
                                        <span class="inv-badge brand">{{ $record->brand }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="cleaning"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No cleaning items. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'power-plant' ? 'active' : '' }}" id="power-plant">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-power-plant-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="power-plant-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-power-plant-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-power-plant-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($powerPlantLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="power-plant-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($powerPlantRecords as $record)
                            @php $qty = (int) $record->quantity; $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock'; $badgeClass = $qty <= 0 ? 'out' : 'in-stock'; $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock; $rowLocation = $record->location ?? '—'; @endphp
                            <tr data-id="{{ $record->id }}" data-type="power-plant" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>@if($record->brand)<span class="inv-badge brand">{{ $record->brand }}</span>@else—@endif</td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="power-plant"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No power plant items. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'industrial-supplies' ? 'active' : '' }}" id="industrial-supplies">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-industrial-supplies-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="industrial-supplies-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-industrial-supplies-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-industrial-supplies-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($industrialSuppliesLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="industrial-supplies-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($industrialSuppliesRecords as $record)
                            @php $qty = (int) $record->quantity; $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock'; $badgeClass = $qty <= 0 ? 'out' : 'in-stock'; $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock; $rowLocation = $record->location ?? '—'; @endphp
                            <tr data-id="{{ $record->id }}" data-type="industrial-supplies" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>@if($record->brand)<span class="inv-badge brand">{{ $record->brand }}</span>@else—@endif</td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="industrial-supplies"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No industrial supplies. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'production-supplies' ? 'active' : '' }}" id="production-supplies">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-production-supplies-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="production-supplies-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-production-supplies-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-production-supplies-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($productionSuppliesLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="production-supplies-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productionSuppliesRecords as $record)
                            @php $qty = (int) $record->quantity; $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock'; $badgeClass = $qty <= 0 ? 'out' : 'in-stock'; $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock; $rowLocation = $record->location ?? '—'; @endphp
                            <tr data-id="{{ $record->id }}" data-type="production-supplies" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>@if($record->brand)<span class="inv-badge brand">{{ $record->brand }}</span>@else—@endif</td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="production-supplies"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No production supplies. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'sanitation' ? 'active' : '' }}" id="sanitation">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-sanitation-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="sanitation-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-sanitation-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-sanitation-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($sanitationLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="sanitation-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sanitationRecords as $record)
                            @php $qty = (int) $record->quantity; $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock'; $badgeClass = $qty <= 0 ? 'out' : 'in-stock'; $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock; $rowLocation = $record->location ?? '—'; @endphp
                            <tr data-id="{{ $record->id }}" data-type="sanitation" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>@if($record->brand)<span class="inv-badge brand">{{ $record->brand }}</span>@else—@endif</td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="sanitation"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No sanitation items. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'tools' ? 'active' : '' }}" id="tools">
            @if($isStoreRoom)
            <div class="inv-action-bar">
                <button type="button" class="inv-btn" id="add-tools-btn"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
            @endif
            <div class="inv-filters" data-table-id="tools-table">
                <label class="inv-filter-label">Stock:</label>
                <select class="inv-filter-select" id="filter-stock-tools-table" aria-label="Filter by stock">
                    <option value="all">All</option>
                    <option value="low">Low stock only</option>
                    <option value="in_stock">In stock</option>
                    <option value="out">Out of stock</option>
                </select>
                @unless($isDepartmentSupervisor)
                <label class="inv-filter-label">Location:</label>
                <select class="inv-filter-select" id="filter-location-tools-table" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($toolsLocations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="inv-table" id="tools-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Brand</th>
                            <th>Location</th>
                            <th>Date Arrived</th>
                            <th>Expiration Date</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($toolsRecords as $record)
                            @php $qty = (int) $record->quantity; $stockStatus = $qty <= 0 ? 'OUT OF STOCK' : 'In Stock'; $badgeClass = $qty <= 0 ? 'out' : 'in-stock'; $isLowStock = isset($record->min_stock) && $record->quantity <= $record->min_stock; $rowLocation = $record->location ?? '—'; @endphp
                            <tr data-id="{{ $record->id }}" data-type="tools" data-min-stock="{{ $record->min_stock ?? '' }}" data-max-stock="{{ $record->max_stock ?? '' }}" data-updated="{{ $record->updated_at?->format('M d, Y H:i') ?? 'N/A' }}" data-low-stock="{{ $isLowStock ? '1' : '0' }}" data-out-of-stock="{{ $record->quantity <= 0 ? '1' : '0' }}" data-brand="{{ $record->brand ?? '' }}" data-location="{{ $rowLocation }}" data-equipment-status="{{ $record->status ?? '' }}" data-image="{{ $record->image_path ? asset('storage/'.$record->image_path) : '' }}">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->item_name }}</td>
                                <td class="qty-cell"><span class="qty-value">{{ $record->quantity }}</span></td>
                                <td><span class="inv-badge {{ $badgeClass }}">{{ $stockStatus }}</span></td>
                                <td>@if($record->brand)<span class="inv-badge brand">{{ $record->brand }}</span>@else—@endif</td>
                                <td>{{ $record->location ?? '—' }}</td>
                                <td>{{ $record->date_arrived?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="inv-expiration-date">{{ $record->expiration_date?->format('M d, Y') ?? '—' }}</span></td>
                                <td>{{ $record->notes ?? '' }}</td>
                                <td>
                                    <div class="inv-actions">
                                        <a href="{{ route('inventory.barcode', ['item_code' => $record->id, 'item_description' => $record->item_name]) }}" target="_blank" rel="noopener noreferrer" class="inv-action-btn barcode" title="Generate barcode"><i class="fas fa-barcode"></i></a>
                                        <button type="button" class="inv-action-btn view" title="View" data-id="{{ $record->id }}"><i class="fas fa-eye"></i></button>
                                        @if($isStoreRoom)
                                            <button type="button" class="inv-action-btn edit" title="Edit" data-id="{{ $record->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="inv-action-btn delete" title="Delete" data-id="{{ $record->id }}"><i class="fas fa-trash"></i></button>
                                        @elseif($isDepartmentSupervisor)
                                            <button type="button" class="inv-action-btn request" title="Request item"
                                                data-request-type="tools"
                                                data-request-id="{{ $record->id }}"
                                                data-request-name="{{ $record->item_name }}"
                                                data-request-qty="{{ $record->quantity }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No tools. Click "Add New Item" to add one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="inv-tab-pane {{ $activeTab === 'adjustment-history' ? 'active' : '' }}" id="adjustment-history">
            <p style="margin-bottom: 16px; color: #6b7280;">Records of quantity increases and decreases from Add stock / Withdraw stock.</p>
            <div class="table-responsive">
                <table class="inv-table" id="adjustment-history-table">
                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Type</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Qty Change</th>
                            <th>Action</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Department Requested</th>
                            <th>Check by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustmentRecords as $adj)
                            @php
                                $ch = (int) $adj->change_amount;
                                $isAdd = $ch > 0;
                                $label = $isAdd ? 'Add to Stock' : 'Withdraw';
                                $styleClass = $isAdd ? 'in-stock' : 'out';
                            @endphp
                            <tr>
                                <td>{{ $adj->adjusted_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                <td>{{ $adj->type_label ?? ucfirst(str_replace('_', ' ', $adj->inventory_type ?? '')) }}</td>
                                <td>{{ $adj->item_id }}</td>
                                <td>{{ $adj->item_name ?? '' }}</td>
                                <td>{{ abs($ch) }}</td>
                                <td><span class="inv-badge {{ $styleClass }}">{{ $label }}</span></td>
                                <td>{{ $adj->quantity_before }}</td>
                                <td>{{ $adj->quantity_after }}</td>
                                <td>{{ $adj->department_requested ?? '—' }}</td>
                                <td>{{ $adj->adjusted_by ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; padding: 24px;">No adjustment records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    </div>

    {{-- Out of stock notification (shown when an item just reached 0 quantity) --}}
    @if(session('outOfStockItem'))
        @php $outOfStock = session('outOfStockItem'); @endphp
        <div class="inv-modal inv-modal-notification active" id="out-of-stock-modal" tabindex="-1" role="dialog" aria-labelledby="out-of-stock-title" aria-modal="true">
            <div class="inv-modal-content inv-modal-notification-content">
                <button type="button" class="inv-modal-notification-close" data-close="out-of-stock-modal" aria-label="Close">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                </button>
                <div class="inv-modal-notification-body">
                    <svg class="inv-modal-notification-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <h3 class="inv-modal-notification-title" id="out-of-stock-title">{{ str_contains($outOfStock['message'] ?? '', 'cannot be requested') ? 'Request Denied' : 'Out of stock' }}</h3>
                    <p class="inv-modal-notification-message">{{ $outOfStock['name'] }} (in {{ $outOfStock['category'] }}) {{ $outOfStock['message'] ?? 'is now out of stock.' }}</p>
                    <div class="inv-modal-notification-actions">
                        <button type="button" class="inv-btn inv-btn-primary" data-close="out-of-stock-modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Low stock notification (shown when an item falls to or below its minimum stock) --}}
    @if(session('lowStockItem'))
        @php $lowStock = session('lowStockItem'); @endphp
        <div class="inv-modal inv-modal-notification active" id="low-stock-modal" tabindex="-1" role="dialog" aria-labelledby="low-stock-title" aria-modal="true">
            <div class="inv-modal-content inv-modal-notification-content">
                <button type="button" class="inv-modal-notification-close" data-close="low-stock-modal" aria-label="Close">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                </button>
                <div class="inv-modal-notification-body">
                    <svg class="inv-modal-notification-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <h3 class="inv-modal-notification-title" id="low-stock-title">Low stock</h3>
                    <p class="inv-modal-notification-message">{{ $lowStock['name'] }} (in {{ $lowStock['category'] }}) has reached its minimum stock level.</p>
                    <div class="inv-modal-notification-actions">
                        <button type="button" class="inv-btn inv-btn-primary" data-close="low-stock-modal" onclick="closeModal('low-stock-modal')">OK</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- View modals --}}
    <div class="inv-modal" id="view-mechanical-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header">
                <img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo">
                <h3>Store Room</h3>
                <h4>Mechanical Item Details</h4>
            </div>
            <div class="inv-form-group" id="view-mec-image-wrap" style="display:none; text-align:center; margin-bottom: 12px;">
                <img id="view-mec-image" src="" alt="Item image" class="inv-item-image">
            </div>
            <div class="inv-form-group"><label>Item Code</label><div class="inv-view-field" id="view-mec-id"></div></div>
            <div class="inv-form-group"><label>Description</label><div class="inv-view-field" id="view-mec-name"></div></div>
            <div class="inv-form-group"><label>Quantity</label><div class="inv-view-field" id="view-mec-qty"></div></div>
            <div class="inv-form-group"><label>Minimum Stock</label><div class="inv-view-field" id="view-mec-min-stock"></div></div>
            <div class="inv-form-group"><label>Maximum Stock</label><div class="inv-view-field" id="view-mec-max-stock"></div></div>
            <div class="inv-form-group"><label>Brand</label><div class="inv-view-field" id="view-mec-brand"></div></div>
            <div class="inv-form-group"><label>Location</label><div class="inv-view-field" id="view-mec-location"></div></div>
            <div class="inv-form-group"><label>Date Arrived</label><div class="inv-view-field" id="view-mec-date-arrived"></div></div>
            <div class="inv-form-group"><label>Expiration Date</label><div class="inv-view-field inv-expiration-date" id="view-mec-expiration"></div></div>
            <div class="inv-form-group"><label>Notes</label><div class="inv-view-field" id="view-mec-notes"></div></div>
            <div class="inv-form-group"><label>Updated</label><div class="inv-view-field" id="view-mec-updated"></div></div>
            <div class="inv-modal-buttons">
                <button type="button" class="inv-btn inv-btn-exit" data-close="view-mechanical-modal">Close</button>
            </div>
        </div>
    </div>
    <div class="inv-modal" id="view-office-supplies-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header">
                <img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo">
                <h3>Store Room</h3>
                <h4>Office Supply Details</h4>
            </div>
            <div class="inv-form-group" id="view-os-image-wrap" style="display:none; text-align:center; margin-bottom: 12px;">
                <img id="view-os-image" src="" alt="Item image" class="inv-item-image">
            </div>
            <div class="inv-form-group"><label>Item Code</label><div class="inv-view-field" id="view-os-id"></div></div>
            <div class="inv-form-group"><label>Description</label><div class="inv-view-field" id="view-os-name"></div></div>
            <div class="inv-form-group"><label>Quantity</label><div class="inv-view-field" id="view-os-qty"></div></div>
            <div class="inv-form-group"><label>Minimum Stock</label><div class="inv-view-field" id="view-os-min-stock"></div></div>
            <div class="inv-form-group"><label>Maximum Stock</label><div class="inv-view-field" id="view-os-max-stock"></div></div>
            <div class="inv-form-group"><label>Brand</label><div class="inv-view-field" id="view-os-brand"></div></div>
            <div class="inv-form-group"><label>Location</label><div class="inv-view-field" id="view-os-location"></div></div>
            <div class="inv-form-group"><label>Date Arrived</label><div class="inv-view-field" id="view-os-date-arrived"></div></div>
            <div class="inv-form-group"><label>Expiration Date</label><div class="inv-view-field inv-expiration-date" id="view-os-expiration"></div></div>
            <div class="inv-form-group"><label>Notes</label><div class="inv-view-field" id="view-os-notes"></div></div>
            <div class="inv-form-group"><label>Updated</label><div class="inv-view-field" id="view-os-updated"></div></div>
            <div class="inv-modal-buttons">
                <button type="button" class="inv-btn inv-btn-exit" data-close="view-office-supplies-modal">Close</button>
            </div>
        </div>
    </div>
    <div class="inv-modal" id="view-technical-equipments-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header">
                <img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo">
                <h3>Store Room</h3>
                <h4 id="view-te-modal-title">Chemical Item Details</h4>
            </div>
            <div class="inv-form-group" id="view-te-image-wrap" style="display:none; text-align:center; margin-bottom: 12px;">
                <img id="view-te-image" src="" alt="Item image" class="inv-item-image">
            </div>
            <div class="inv-form-group"><label>Item Code</label><div class="inv-view-field" id="view-te-id"></div></div>
            <div class="inv-form-group"><label>Description</label><div class="inv-view-field" id="view-te-name"></div></div>
            <div class="inv-form-group"><label>Quantity</label><div class="inv-view-field" id="view-te-qty"></div></div>
            <div class="inv-form-group"><label>Minimum Stock</label><div class="inv-view-field" id="view-te-min-stock"></div></div>
            <div class="inv-form-group"><label>Maximum Stock</label><div class="inv-view-field" id="view-te-max-stock"></div></div>
            <div class="inv-form-group"><label>Brand</label><div class="inv-view-field" id="view-te-brand"></div></div>
            <div class="inv-form-group"><label>Location</label><div class="inv-view-field" id="view-te-location"></div></div>
            <div class="inv-form-group"><label>Date Arrived</label><div class="inv-view-field" id="view-te-date-arrived"></div></div>
            <div class="inv-form-group"><label>Expiration Date</label><div class="inv-view-field inv-expiration-date" id="view-te-expiration"></div></div>
            <div class="inv-form-group"><label>Notes</label><div class="inv-view-field" id="view-te-notes"></div></div>
            <div class="inv-form-group"><label>Updated</label><div class="inv-view-field" id="view-te-updated"></div></div>
            <div class="inv-modal-buttons">
                <button type="button" class="inv-btn inv-btn-exit" data-close="view-technical-equipments-modal">Close</button>
            </div>
        </div>
    </div>

    {{-- Request Item (for department supervisors) --}}
    <div class="inv-modal" id="request-item-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header">
                <img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo">
                <h3>Store Room</h3>
                <h4>Request Item</h4>
            </div>
            <form action="{{ route('requests.store') }}" method="post">
                @csrf
                <input type="hidden" name="inventory_type" id="req-inventory-type">
                <input type="hidden" name="item_id" id="req-item-id">
                <input type="hidden" name="item_name" id="req-item-name">
                <input type="hidden" name="return_tab" id="req-return-tab">
                <input type="hidden" name="return_category" id="req-return-category">
                <input type="hidden" name="return_stock" id="req-return-stock">
                <input type="hidden" name="return_location" id="req-return-location">
                <div class="inv-form-group">
                    <label>Item Code</label>
                    <div class="inv-view-field" id="req-item-id-display"></div>
                </div>
                <div class="inv-form-group">
                    <label>Description</label>
                    <div class="inv-view-field" id="req-item-name-display"></div>
                </div>
                <div class="inv-form-group">
                    <label>Available quantity</label>
                    <div class="inv-view-field" id="req-available-qty"></div>
                </div>
                <div class="inv-form-group">
                    <label for="req-quantity">Quantity to request</label>
                    <input type="number" id="req-quantity" name="requested_quantity" class="inv-form-control" min="1" required>
                </div>
                <div class="inv-form-group">
                    <label for="req-reason">Reason (optional)</label>
                    <textarea id="req-reason" name="reason" class="inv-form-control" rows="3"></textarea>
                </div>
                <div class="inv-modal-buttons">
                    <button type="submit" class="inv-btn">Submit request</button>
                    <button type="button" class="inv-btn inv-btn-exit" data-close="request-item-modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Mechanical --}}
    <div class="inv-modal" id="add-mechanical-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Store Room</h3><h4 id="add-modal-title">Add Item</h4></div>
            <form action="{{ route('inventory.mechanical.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                {{-- used when adding so that redirect can restore filters/tab --}}
                <input type="hidden" name="return_tab" id="add-return-tab-mechanical">
                <input type="hidden" name="return_category" id="add-return-category-mechanical">

                <div class="inv-form-group"><label for="add-mec-id">Item Code</label><input type="text" id="add-mec-id" name="id" class="inv-form-control" required></div>
                <div class="inv-form-group"><label for="add-mec-name">Description</label><input type="text" id="add-mec-name" name="item_name" class="inv-form-control" required></div>
                <div class="inv-form-group"><label for="add-category">Category</label><select id="add-category" name="category" class="inv-form-control" required>
                        <option value="">— Select category —</option>
                        <option value="mechanical">Mechanical</option>
                        <option value="office-supplies">Office Supplies</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="industrial-supplies">Industrial Supplies</option>
                        <option value="production-supplies">Production Supplies</option>
                        <option value="sanitation">Sanitation</option>
                        <option value="electrical">Electrical</option>
                        <option value="chemical">Chemical</option>
                        <option value="safety">Safety</option>
                        <option value="power-plant">Power Plant</option>
                        <option value="tools">Tools</option>
                    </select></div>
                <div class="inv-form-group"><label for="add-mec-qty">Quantity</label><input type="number" id="add-mec-qty" name="quantity" class="inv-form-control" min="0" value="0" required></div>
                <div class="inv-form-group"><label for="add-mec-min">Minimum Stock</label><input type="number" id="add-mec-min" name="min_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="add-mec-max">Maximum Stock</label><input type="number" id="add-mec-max" name="max_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="add-mec-brand">Brand</label><input type="text" id="add-mec-brand" name="brand" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-mec-image">Item image (optional)</label><input type="file" id="add-mec-image" name="image" class="inv-form-control" accept="image/*"></div>
                <div class="inv-form-group"><label for="add-mec-location">Location</label><input type="text" id="add-mec-location" name="location" class="inv-form-control" placeholder="Store room location"></div>
                <div class="inv-form-group"><label for="add-mec-date-arrived">Date Arrived</label><input type="date" id="add-mec-date-arrived" name="date_arrived" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-mec-expiration">Expiration Date</label><input type="date" id="add-mec-expiration" name="expiration_date" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-mec-notes">Notes</label><textarea id="add-mec-notes" name="notes" class="inv-form-control" rows="3"></textarea></div>
                <div class="inv-form-group"><label for="add-mec-updated">Updated</label><input type="datetime-local" id="add-mec-updated" name="updated_at" class="inv-form-control"><span class="inv-form-hint">Leave empty for current time</span></div>
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn">Submit</button><button type="button" class="inv-btn inv-btn-exit" data-close="add-mechanical-modal">Cancel</button></div>
            </form>
        </div>
    </div>
    {{-- Add Office Supply --}}
    <div class="inv-modal" id="add-office-supplies-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Store Room</h3><h4>Add Office Supply</h4></div>
            <form action="{{ route('inventory.office-supplies.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                {{-- preserve return location for redirect after add --}}
                <input type="hidden" name="return_tab" id="add-return-tab-os">
                <input type="hidden" name="return_category" id="add-return-category-os">
                <div class="inv-form-group"><label for="add-os-id">Item Code</label><input type="text" id="add-os-id" name="id" class="inv-form-control" required></div>
                <div class="inv-form-group"><label for="add-os-name">Description</label><input type="text" id="add-os-name" name="item_name" class="inv-form-control" required></div>
                <div class="inv-form-group"><label for="add-os-qty">Quantity</label><input type="number" id="add-os-qty" name="quantity" class="inv-form-control" min="0" value="0" required></div>
                <div class="inv-form-group"><label for="add-os-min">Minimum Stock</label><input type="number" id="add-os-min" name="min_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="add-os-max">Maximum Stock</label><input type="number" id="add-os-max" name="max_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="add-os-brand">Brand</label><input type="text" id="add-os-brand" name="brand" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-os-image">Item image (optional)</label><input type="file" id="add-os-image" name="image" class="inv-form-control" accept="image/*"></div>
                <div class="inv-form-group"><label for="add-os-location">Location</label><input type="text" id="add-os-location" name="location" class="inv-form-control" placeholder="Store room location"></div>
                <div class="inv-form-group"><label for="add-os-date-arrived">Date Arrived</label><input type="date" id="add-os-date-arrived" name="date_arrived" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-os-expiration">Expiration Date</label><input type="date" id="add-os-expiration" name="expiration_date" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-os-notes">Notes</label><textarea id="add-os-notes" name="notes" class="inv-form-control" rows="3"></textarea></div>
                <div class="inv-form-group"><label for="add-os-updated">Updated</label><input type="datetime-local" id="add-os-updated" name="updated_at" class="inv-form-control"><span class="inv-form-hint">Leave empty for current time</span></div>
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn">Submit</button><button type="button" class="inv-btn inv-btn-exit" data-close="add-office-supplies-modal">Cancel</button></div>
            </form>
        </div>
    </div>
    {{-- Add Technical / Category-based Equipment --}}
    <div class="inv-modal" id="add-technical-equipments-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Store Room</h3><h4 id="add-te-modal-title">Add Chemical Item</h4></div>
            <form id="add-equipment-form" action="" method="post" enctype="multipart/form-data">
                @csrf
                {{-- redirect info for equipment add --}}
                <input type="hidden" name="return_tab" id="add-return-tab-te">
                <input type="hidden" name="return_category" id="add-return-category-te">

                <div class="inv-form-group"><label for="add-te-id">Item Code</label><input type="text" id="add-te-id" name="id" class="inv-form-control" required></div>
                <div class="inv-form-group"><label for="add-te-name">Description</label><input type="text" id="add-te-name" name="item_name" class="inv-form-control" required></div>
                <div class="inv-form-group"><label for="add-te-qty">Quantity</label><input type="number" id="add-te-qty" name="quantity" class="inv-form-control" min="0" value="0" required></div>
                <div class="inv-form-group"><label for="add-te-min">Minimum Stock</label><input type="number" id="add-te-min" name="min_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="add-te-max">Maximum Stock</label><input type="number" id="add-te-max" name="max_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="add-te-brand">Brand</label><input type="text" id="add-te-brand" name="brand" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-te-image">Item image (optional)</label><input type="file" id="add-te-image" name="image" class="inv-form-control" accept="image/*"></div>
                <div class="inv-form-group"><label for="add-te-location">Location</label><input type="text" id="add-te-location" name="location" class="inv-form-control" placeholder="Store room location"></div>
                <div class="inv-form-group"><label for="add-te-date-arrived">Date Arrived</label><input type="date" id="add-te-date-arrived" name="date_arrived" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-te-expiration">Expiration Date</label><input type="date" id="add-te-expiration" name="expiration_date" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="add-te-notes">Notes</label><textarea id="add-te-notes" name="notes" class="inv-form-control" rows="3"></textarea></div>
                <div class="inv-form-group"><label for="add-te-updated">Updated</label><input type="datetime-local" id="add-te-updated" name="updated_at" class="inv-form-control"><span class="inv-form-hint">Leave empty for current time</span></div>
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn">Submit</button><button type="button" class="inv-btn inv-btn-exit" data-close="add-technical-equipments-modal">Cancel</button></div>
            </form>
        </div>
    </div>

    {{-- Edit Mechanical --}}
    <div class="inv-modal" id="edit-mechanical-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Store Room</h3><h4>Edit Mechanical Item</h4></div>
            <form action="{{ route('inventory.mechanical.update') }}" method="post" enctype="multipart/form-data">
                @csrf
                {{-- used by controller to know which tab/category to return to after POST --}}
                <input type="hidden" name="return_tab" id="edit-return-tab-mechanical">
                <input type="hidden" name="return_category" id="edit-return-category-mechanical">
                <input type="hidden" name="id" id="edit-mec-id">
                <div class="inv-form-group"><label for="edit-mec-name">Description</label><input type="text" id="edit-mec-name" name="item_name" class="inv-form-control" required></div>
                <div class="inv-form-group"><label>Current quantity</label><div class="inv-form-control" id="edit-mec-current-display" readonly style="background:#f3f4f6;"></div><input type="hidden" name="current_quantity" id="edit-mec-current"></div>
                <div class="inv-form-group"><label for="edit-mec-qty">Amount to add or withdraw</label><input type="number" id="edit-mec-qty" name="quantity" class="inv-form-control" min="0" value="0" required></div>
                <div class="inv-form-group"><label for="edit-mec-action">Stock action</label><select id="edit-mec-action" name="stock_action" class="inv-form-control"><option value="add_stock">Add stock</option><option value="withdraw_stock">Withdraw stock</option></select></div>
                <div class="inv-form-group"><label for="edit-mec-department">Department Requested</label><select id="edit-mec-department" name="department_requested" class="inv-form-control"><option value="">— Select —</option><option value="Engineering">Engineering</option><option value="Production">Production</option><option value="Finance">Finance</option><option value="Taxation">Taxation</option><option value="Store Room">Store Room</option></select></div>
                <div class="inv-form-group"><label for="edit-mec-min-stock">Minimum Stock</label><input type="number" id="edit-mec-min-stock" name="min_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="edit-mec-max-stock">Maximum Stock</label><input type="number" id="edit-mec-max-stock" name="max_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="edit-mec-brand">Brand</label><input type="text" id="edit-mec-brand" name="brand" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-mec-image">Item image (optional)</label><input type="file" id="edit-mec-image" name="image" class="inv-form-control" accept="image/*"></div>
                <div class="inv-form-group"><label for="edit-mec-location">Location</label><input type="text" id="edit-mec-location" name="location" class="inv-form-control" placeholder="Store room location"></div>
                <div class="inv-form-group"><label for="edit-mec-date-arrived">Date Arrived</label><input type="date" id="edit-mec-date-arrived" name="date_arrived" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-mec-expiration">Expiration Date</label><input type="date" id="edit-mec-expiration" name="expiration_date" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-mec-notes">Notes</label><textarea id="edit-mec-notes" name="notes" class="inv-form-control" rows="3"></textarea></div>
                <div class="inv-form-group"><label for="edit-mec-updated">Updated</label><input type="datetime-local" id="edit-mec-updated" name="updated_at" class="inv-form-control"><span class="inv-form-hint">Leave empty for current time</span></div>
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn">Update</button><button type="button" class="inv-btn inv-btn-exit" data-close="edit-mechanical-modal">Cancel</button></div>
            </form>
        </div>
    </div>
    {{-- Edit Office Supply --}}
    <div class="inv-modal" id="edit-office-supplies-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Store Room</h3><h4>Edit Office Supply</h4></div>
            <form action="{{ route('inventory.office-supplies.update') }}" method="post" enctype="multipart/form-data">
                @csrf
                {{-- preserve return location for redirects --}}
                <input type="hidden" name="return_tab" id="edit-return-tab-os">
                <input type="hidden" name="return_category" id="edit-return-category-os">
                <input type="hidden" name="id" id="edit-os-id">
                <div class="inv-form-group"><label for="edit-os-name">Description</label><input type="text" id="edit-os-name" name="item_name" class="inv-form-control" required></div>
                <div class="inv-form-group"><label>Current quantity</label><div class="inv-form-control" id="edit-os-current-display" readonly style="background:#f3f4f6;"></div><input type="hidden" name="current_quantity" id="edit-os-current"></div>
                <div class="inv-form-group"><label for="edit-os-qty">Amount to add or withdraw</label><input type="number" id="edit-os-qty" name="quantity" class="inv-form-control" min="0" value="0" required></div>
                <div class="inv-form-group"><label for="edit-os-action">Stock action</label><select id="edit-os-action" name="stock_action" class="inv-form-control"><option value="add_stock">Add stock</option><option value="withdraw_stock">Withdraw stock</option></select></div>
                <div class="inv-form-group"><label for="edit-os-department">Department Requested</label><select id="edit-os-department" name="department_requested" class="inv-form-control"><option value="">— Select —</option><option value="Engineering">Engineering</option><option value="Production">Production</option><option value="Finance">Finance</option><option value="Taxation">Taxation</option><option value="Store Room">Store Room</option></select></div>
                <div class="inv-form-group"><label for="edit-os-min-stock">Minimum Stock</label><input type="number" id="edit-os-min-stock" name="min_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="edit-os-max-stock">Maximum Stock</label><input type="number" id="edit-os-max-stock" name="max_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="edit-os-brand">Brand</label><input type="text" id="edit-os-brand" name="brand" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-os-image">Item image (optional)</label><input type="file" id="edit-os-image" name="image" class="inv-form-control" accept="image/*"></div>
                <div class="inv-form-group"><label for="edit-os-location">Location</label><input type="text" id="edit-os-location" name="location" class="inv-form-control" placeholder="Store room location"></div>
                <div class="inv-form-group"><label for="edit-os-date-arrived">Date Arrived</label><input type="date" id="edit-os-date-arrived" name="date_arrived" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-os-expiration">Expiration Date</label><input type="date" id="edit-os-expiration" name="expiration_date" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-os-notes">Notes</label><textarea id="edit-os-notes" name="notes" class="inv-form-control" rows="3"></textarea></div>
                <div class="inv-form-group"><label for="edit-os-updated">Updated</label><input type="datetime-local" id="edit-os-updated" name="updated_at" class="inv-form-control"><span class="inv-form-hint">Leave empty for current time</span></div>
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn">Update</button><button type="button" class="inv-btn inv-btn-exit" data-close="edit-office-supplies-modal">Cancel</button></div>
            </form>
        </div>
    </div>
    {{-- Edit Technical / Category-based Equipment --}}
    <div class="inv-modal" id="edit-technical-equipments-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Store Room</h3><h4 id="edit-te-modal-title">Edit Chemical Item</h4></div>
            <form id="edit-equipment-form" action="" method="post" enctype="multipart/form-data">
                @csrf
                {{-- preserve return location for redirects --}}
                <input type="hidden" name="return_tab" id="edit-return-tab-te">
                <input type="hidden" name="return_category" id="edit-return-category-te">
                <input type="hidden" name="id" id="edit-te-id">
                <div class="inv-form-group"><label for="edit-te-name">Description</label><input type="text" id="edit-te-name" name="item_name" class="inv-form-control" required></div>
                <div class="inv-form-group"><label>Current quantity</label><div class="inv-form-control" id="edit-te-current-display" readonly style="background:#f3f4f6;"></div><input type="hidden" name="current_quantity" id="edit-te-current"></div>
                <div class="inv-form-group"><label for="edit-te-qty">Amount to add or withdraw</label><input type="number" id="edit-te-qty" name="quantity" class="inv-form-control" min="0" value="0" required></div>
                <div class="inv-form-group"><label for="edit-te-action">Stock action</label><select id="edit-te-action" name="stock_action" class="inv-form-control"><option value="add_stock">Add stock</option><option value="withdraw_stock">Withdraw stock</option></select></div>
                <div class="inv-form-group"><label for="edit-te-department">Department Requested</label><select id="edit-te-department" name="department_requested" class="inv-form-control"><option value="">— Select —</option><option value="Engineering">Engineering</option><option value="Production">Production</option><option value="Finance">Finance</option><option value="Taxation">Taxation</option><option value="Store Room">Store Room</option></select></div>
                <div class="inv-form-group"><label for="edit-te-min-stock">Minimum Stock</label><input type="number" id="edit-te-min-stock" name="min_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="edit-te-max-stock">Maximum Stock</label><input type="number" id="edit-te-max-stock" name="max_stock" class="inv-form-control" min="0"></div>
                <div class="inv-form-group"><label for="edit-te-brand">Brand</label><input type="text" id="edit-te-brand" name="brand" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-te-image">Item image (optional)</label><input type="file" id="edit-te-image" name="image" class="inv-form-control" accept="image/*"></div>
                <div class="inv-form-group"><label for="edit-te-location">Location</label><input type="text" id="edit-te-location" name="location" class="inv-form-control" placeholder="Store room location"></div>
                <div class="inv-form-group"><label for="edit-te-date-arrived">Date Arrived</label><input type="date" id="edit-te-date-arrived" name="date_arrived" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-te-expiration">Expiration Date</label><input type="date" id="edit-te-expiration" name="expiration_date" class="inv-form-control"></div>
                <div class="inv-form-group"><label for="edit-te-notes">Notes</label><textarea id="edit-te-notes" name="notes" class="inv-form-control" rows="3"></textarea></div>
                <div class="inv-form-group"><label for="edit-te-updated">Updated</label><input type="datetime-local" id="edit-te-updated" name="updated_at" class="inv-form-control"><span class="inv-form-hint">Leave empty for current time</span></div>
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn">Update</button><button type="button" class="inv-btn inv-btn-exit" data-close="edit-technical-equipments-modal">Cancel</button></div>
            </form>
        </div>
    </div>

    {{-- Delete confirmation --}}
    <div class="inv-modal" id="delete-confirmation-modal">
        <div class="inv-modal-content">
            <div class="inv-modal-header"><img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo"><h3>Confirm Deletion</h3><p id="delete-message" style="color:#374151; margin-top:8px;"></p><p style="color: var(--inv-danger); font-weight: 600; margin-top: 8px;">This action cannot be undone.</p></div>
            <form action="" method="post" id="delete-form">
                @csrf
                {{-- redirect info so filters/tabs are preserved after delete --}}
                <input type="hidden" name="return_tab" id="delete-return-tab">
                <input type="hidden" name="return_category" id="delete-return-category">
                <input type="hidden" name="id" id="delete-id">
                <div class="inv-modal-buttons"><button type="submit" class="inv-btn" style="background: var(--inv-danger);">Delete</button><button type="button" class="inv-btn inv-btn-exit" data-close="delete-confirmation-modal">Cancel</button></div>
            </form>
        </div>
    </div>

    @php
        $equipmentRoutes = [
            'electrical' => [
                'store' => route('inventory.equipment.store', ['category' => 'electrical']),
                'update' => route('inventory.equipment.update', ['category' => 'electrical']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'electrical']),
            ],
            'chemical' => [
                'store' => route('inventory.equipment.store', ['category' => 'chemical']),
                'update' => route('inventory.equipment.update', ['category' => 'chemical']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'chemical']),
            ],
            'safety' => [
                'store' => route('inventory.equipment.store', ['category' => 'safety']),
                'update' => route('inventory.equipment.update', ['category' => 'safety']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'safety']),
            ],
            'cleaning' => [
                'store' => route('inventory.equipment.store', ['category' => 'cleaning']),
                'update' => route('inventory.equipment.update', ['category' => 'cleaning']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'cleaning']),
            ],
            'power-plant' => [
                'store' => route('inventory.equipment.store', ['category' => 'power-plant']),
                'update' => route('inventory.equipment.update', ['category' => 'power-plant']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'power-plant']),
            ],
            'industrial-supplies' => [
                'store' => route('inventory.equipment.store', ['category' => 'industrial-supplies']),
                'update' => route('inventory.equipment.update', ['category' => 'industrial-supplies']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'industrial-supplies']),
            ],
            'production-supplies' => [
                'store' => route('inventory.equipment.store', ['category' => 'production-supplies']),
                'update' => route('inventory.equipment.update', ['category' => 'production-supplies']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'production-supplies']),
            ],
            'sanitation' => [
                'store' => route('inventory.equipment.store', ['category' => 'sanitation']),
                'update' => route('inventory.equipment.update', ['category' => 'sanitation']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'sanitation']),
            ],
            'tools' => [
                'store' => route('inventory.equipment.store', ['category' => 'tools']),
                'update' => route('inventory.equipment.update', ['category' => 'tools']),
                'destroy' => route('inventory.equipment.destroy', ['category' => 'tools']),
            ],
        ];
    @endphp

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openModal = (id) => document.getElementById(id)?.classList.add('active');
            const closeModal = (id) => document.getElementById(id)?.classList.remove('active');

            var flashAlert = document.getElementById('inv-flash-alert');
            if (flashAlert) {
                setTimeout(function() {
                    flashAlert.style.transition = 'opacity 0.4s';
                    flashAlert.style.opacity = '0';
                    setTimeout(function() { flashAlert.remove(); }, 400);
                }, 5000);
            }

            // delegate close-button clicks (works for existing and future modals)
            document.body.addEventListener('click', function(e) {
                var btn = e.target.closest('[data-close]');
                if (!btn) return;
                var target = btn.getAttribute('data-close');
                if (target) closeModal(target);
            });
            // legacy per-element binding (optional, harmless)
            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(btn.getAttribute('data-close')));
            });
            document.querySelectorAll('.inv-modal').forEach(modal => {
                modal.addEventListener('click', e => { if (e.target === modal) closeModal(modal.id); });
            });

            document.querySelectorAll('[data-dismiss-target]').forEach(btn => {
                btn.addEventListener('click', function () {
                    var targetSelector = this.getAttribute('data-dismiss-target');
                    if (!targetSelector) return;
                    var target = document.querySelector(targetSelector);
                    if (target) {
                        target.parentNode?.removeChild(target);
                    }
                });
            });


            document.getElementById('add-office-supplies-btn')?.addEventListener('click', function() {
                var pane = this.closest('.inv-tab-pane');
                var returnTabVal = pane ? pane.id : '';
                var returnCatVal = '';
                if (returnTabVal === 'inventory') {
                    var catEl = document.getElementById('filter-category-inventory-table');
                    if (catEl) returnCatVal = catEl.value || '';
                }
                var modal = document.getElementById('add-office-supplies-modal');
                if (modal) {
                    var addTabInput = modal.querySelector('input[name="return_tab"]');
                    var addCatInput = modal.querySelector('input[name="return_category"]');
                    if (addTabInput) addTabInput.value = returnTabVal;
                    if (addCatInput) addCatInput.value = returnCatVal;
                }
                openModal('add-office-supplies-modal');
            });
            document.getElementById('add-unified-item-btn')?.addEventListener('click', function() {
                var pane = this.closest('.inv-tab-pane');
                var returnTabVal = pane ? pane.id : '';
                var returnCatVal = '';
                if (returnTabVal === 'inventory') {
                    var catEl = document.getElementById('filter-category-inventory-table');
                    if (catEl) returnCatVal = catEl.value || '';
                }
                var modal = document.getElementById('add-mechanical-modal');
                if (modal) {
                    var addTabInput = modal.querySelector('input[name="return_tab"]');
                    var addCatInput = modal.querySelector('input[name="return_category"]');
                    if (addTabInput) addTabInput.value = returnTabVal;
                    if (addCatInput) addCatInput.value = returnCatVal;
                }
                var form = document.querySelector('#add-mechanical-modal form');
                if (form) form.reset();
                openModal('add-mechanical-modal');
                var cat = document.getElementById('add-category');
                if (cat) {
                    cat.value = '';
                    cat.dispatchEvent(new Event('change'));
                }
            });

            var EQUIPMENT_ROUTES = @json($equipmentRoutes);
            var EQUIPMENT_LABELS = { electrical: 'Electrical', chemical: 'Chemical', safety: 'Safety', cleaning: 'Cleaning', 'power-plant': 'Power Plant', 'industrial-supplies': 'Industrial Supplies', 'production-supplies': 'Production Supplies', sanitation: 'Sanitation', tools: 'Tools' };
            // helper to update add form action/title when category selected
            var addForm = document.querySelector('#add-mechanical-modal form');
            var addTitle = document.getElementById('add-modal-title');
            document.getElementById('add-category')?.addEventListener('change', function() {
                var val = this.value;
                // update title
                if (addTitle) {
                    if (val) {
                        var label = (EQUIPMENT_LABELS[val] || val.replace(/-/g, ' '));
                        label = label.charAt(0).toUpperCase() + label.slice(1);
                        addTitle.textContent = 'Add ' + label + ' Item';
                    } else {
                        addTitle.textContent = 'Add Item';
                    }
                }
                // choose route
                if (!addForm) return;
                if (!val) {
                    addForm.action = '#';
                } else if (val === 'mechanical') {
                    addForm.action = '{{ route("inventory.mechanical.store") }}';
                } else if (val === 'office-supplies') {
                    addForm.action = '{{ route("inventory.office-supplies.store") }}';
                } else if (EQUIPMENT_ROUTES[val]) {
                    addForm.action = EQUIPMENT_ROUTES[val].store;
                }
            });            const addEquipmentForm = document.getElementById('add-equipment-form');
            const editEquipmentForm = document.getElementById('edit-equipment-form');
            const addTeModalTitle = document.getElementById('add-te-modal-title');
            const editTeModalTitle = document.getElementById('edit-te-modal-title');
            const viewTeModalTitle = document.getElementById('view-te-modal-title');

            const openAddTeModal = (slug) => {
                if (EQUIPMENT_ROUTES[slug] && addEquipmentForm) addEquipmentForm.action = EQUIPMENT_ROUTES[slug].store;
                if (addTeModalTitle) addTeModalTitle.textContent = 'Add ' + (EQUIPMENT_LABELS[slug] || slug) + ' Item';
                // fill return inputs for equipment add modal
                var pane = document.activeElement ? document.activeElement.closest('.inv-tab-pane') : null;
                var returnTabVal = pane ? pane.id : '';
                var returnCatVal = '';
                if (returnTabVal === 'inventory') {
                    var catEl = document.getElementById('filter-category-inventory-table');
                    if (catEl) returnCatVal = catEl.value || '';
                }
                var modal = document.getElementById('add-technical-equipments-modal');
                if (modal) {
                    var addTabInput = modal.querySelector('input[name="return_tab"]');
                    var addCatInput = modal.querySelector('input[name="return_category"]');
                    if (addTabInput) addTabInput.value = returnTabVal;
                    if (addCatInput) addCatInput.value = returnCatVal;
                }
                openModal('add-technical-equipments-modal');
            };

            document.getElementById('add-chemical-btn')?.addEventListener('click', () => openAddTeModal('chemical'));
            document.getElementById('add-electrical-btn')?.addEventListener('click', () => openAddTeModal('electrical'));
            document.getElementById('add-safety-btn')?.addEventListener('click', () => openAddTeModal('safety'));
            document.getElementById('add-cleaning-btn')?.addEventListener('click', () => openAddTeModal('cleaning'));
            document.getElementById('add-power-plant-btn')?.addEventListener('click', () => openAddTeModal('power-plant'));
            document.getElementById('add-industrial-supplies-btn')?.addEventListener('click', () => openAddTeModal('industrial-supplies'));
            document.getElementById('add-production-supplies-btn')?.addEventListener('click', () => openAddTeModal('production-supplies'));
            document.getElementById('add-sanitation-btn')?.addEventListener('click', () => openAddTeModal('sanitation'));
            document.getElementById('add-tools-btn')?.addEventListener('click', () => openAddTeModal('tools'));

            function qtyFromRow(row) {
                const cell = row.querySelector('.qty-cell');
                const val = cell?.querySelector('.qty-value') || cell;
                return parseInt(val?.textContent?.trim() || '0', 10) || 0;
            }
            function toDatetimeLocal(str) {
                if (!str || str === 'N/A') return '';
                const d = new Date(str);
                if (isNaN(d.getTime())) return '';
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0') + 'T' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
            }
            function toDateOnly(str) {
                if (!str || str === '—' || str.trim() === '') return '';
                try {
                    var d = new Date(str.trim());
                    if (isNaN(d.getTime())) return '';
                    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                } catch (err) { return ''; }
            }

            var dtInventory = null, dtMechanical = null, dtOffice = null, dtChemical = null, dtElectrical = null, dtSafety = null, dtCleaning = null, dtPowerPlant = null, dtIndustrialSupplies = null, dtProductionSupplies = null, dtSanitation = null, dtTools = null, dtAdjust = null;
            var dtOptions = {
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[0, 'asc']],
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total)',
                    emptyTable: 'No records found.',
                    zeroRecords: 'No matching records found',
                    paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
                }
            };

            var invFilterState = {};
            var invFilterTableIds = ['inventory-table', 'mechanical-table', 'office-supplies-table', 'electrical-table', 'chemical-table', 'safety-table', 'cleaning-table', 'power-plant-table', 'industrial-supplies-table', 'production-supplies-table', 'sanitation-table', 'tools-table'];
            invFilterTableIds.forEach(function(tableId) {
                invFilterState[tableId] = { stock: 'all', location: '', category: '' };
            });

            // Extract filters from URL query parameters
            function getQueryParam(name) {
                var url = window.location.href;
                name = name.replace(/[\[\]]/g, "\\$&");
                var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, " "));
            }
            var categoryFromUrl = getQueryParam('category');
            var stockFromUrl = getQueryParam('stock') || 'all';
            var locationFromUrl = getQueryParam('location') || '';
            if (categoryFromUrl || stockFromUrl !== 'all' || locationFromUrl) {
                invFilterTableIds.forEach(function(tableId) {
                    if (categoryFromUrl) invFilterState[tableId].category = categoryFromUrl;
                    invFilterState[tableId].stock = stockFromUrl;
                    invFilterState[tableId].location = locationFromUrl;
                });
            }

            function updateUrlFromFilters(tableId) {
                var state = invFilterState[tableId];
                var params = new URLSearchParams(window.location.search);
                params.set('tab', 'inventory');
                if (state.category) {
                    params.set('category', state.category);
                } else {
                    params.delete('category');
                }
                if (state.stock && state.stock !== 'all') {
                    params.set('stock', state.stock);
                } else {
                    params.delete('stock');
                }
                if (state.location) {
                    params.set('location', state.location);
                } else {
                    params.delete('location');
                }
                var newUrl = window.location.pathname + '?' + params.toString();
                window.history.replaceState(null, '', newUrl);
            }

            // Also sync dropdown values into filter state (drop-down may already have a server-selected value)
            invFilterTableIds.forEach(function(tableId) {
                var stockEl = document.getElementById('filter-stock-' + tableId);
                var locationEl = document.getElementById('filter-location-' + tableId);
                var categoryEl = document.getElementById('filter-category-' + tableId);
                if (stockEl) invFilterState[tableId].stock = stockEl.value;
                if (locationEl) invFilterState[tableId].location = locationEl.value;
                if (categoryEl) {
                    // populate state from the select itself unless we plan to override from URL
                    invFilterState[tableId].category = categoryEl.value;
                    if (categoryFromUrl) {
                        categoryEl.value = categoryFromUrl;
                        invFilterState[tableId].category = categoryFromUrl;
                    }
                }
                if (stockEl) stockEl.value = invFilterState[tableId].stock;
                if (locationEl) locationEl.value = invFilterState[tableId].location;
            });
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var tableId = settings.nTable?.id;
                if (!tableId || invFilterTableIds.indexOf(tableId) === -1) return true;
                var api = new $.fn.dataTable.Api(settings);
                var row = $(api.row(dataIndex).node());
                if (!row.length) return true;
                var state = invFilterState[tableId] || {};
                var lowStock = row.attr('data-low-stock');
                var outOfStock = row.attr('data-out-of-stock');
                var location = row.attr('data-location') || '';
                var category = row.attr('data-category') || '';
                if (state.stock === 'low' && lowStock !== '1') return false;
                if (state.stock === 'in_stock' && (lowStock === '1' || outOfStock === '1')) return false;
                if (state.stock === 'out' && outOfStock !== '1') return false;
                if (state.location && location !== state.location) return false;
                if (state.category && category !== state.category) return false;
                return true;
            });

            // Apply any filters that were pre-selected via query parameters (e.g. ?category=mechanical)
            // (Category already set above via URL parameter extraction)

            function initDataTable(tableId) {
                var $t = $('#' + tableId);
                if (!$t.length) return null;
                
                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    $t.DataTable().destroy();
                }
                
                $t.find('tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('td').length === 1 && $row.find('td').attr('colspan')) {
                        $row.remove();
                    }
                });
                return $t.DataTable(dtOptions);
            }

            // Initialize all tables AFTER search callback is registered
            dtInventory = initDataTable('inventory-table');
            dtMechanical = initDataTable('mechanical-table');
            dtOffice = initDataTable('office-supplies-table');
            dtChemical = initDataTable('chemical-table');
            dtElectrical = initDataTable('electrical-table');
            dtSafety = initDataTable('safety-table');
            dtCleaning = initDataTable('cleaning-table');
            dtPowerPlant = initDataTable('power-plant-table');
            dtIndustrialSupplies = initDataTable('industrial-supplies-table');
            dtProductionSupplies = initDataTable('production-supplies-table');
            dtSanitation = initDataTable('sanitation-table');
            dtTools = initDataTable('tools-table');
            dtAdjust = initDataTable('adjustment-history-table');

            // Now apply initial filter state and draw tables
            function applyInvFilters(tableId) {
                var dt = $('#' + tableId).DataTable();
                if (dt) dt.draw();
            }
            invFilterTableIds.forEach(function(tableId) {
                applyInvFilters(tableId);
            });
            // Show the inventory table after initial filtering
            var inventoryTableWrapper = document.querySelector('#inventory .table-responsive');
            if (inventoryTableWrapper) inventoryTableWrapper.style.display = 'block';
            document.querySelectorAll('.inv-filters').forEach(function(wrap) {
                var tableId = wrap.getAttribute('data-table-id');
                if (!tableId) return;
                var stockEl = document.getElementById('filter-stock-' + tableId);
                var locationEl = document.getElementById('filter-location-' + tableId);
                var categoryEl = document.getElementById('filter-category-' + tableId);
                function updateState() {
                    invFilterState[tableId] = {
                        stock: stockEl ? stockEl.value : 'all',
                        location: locationEl ? locationEl.value : '',
                        category: categoryEl ? categoryEl.value : ''
                    };
                    updateUrlFromFilters(tableId);
                    applyInvFilters(tableId);
                }
                if (stockEl) stockEl.addEventListener('change', updateState);
                if (locationEl) locationEl.addEventListener('change', updateState);
                if (categoryEl) categoryEl.addEventListener('change', updateState);
            });



            function adjustTable(dt) {
                if (dt) try { dt.columns().adjust(); } catch (err) {}
            }

            document.querySelector('.inv-content').addEventListener('click', function(e) {
                var viewBtn = e.target.closest('.inv-action-btn.view');
                var editBtn = e.target.closest('.inv-action-btn.edit');
                var deleteBtn = e.target.closest('.inv-action-btn.delete');
                var requestBtn = e.target.closest('.inv-action-btn.request');
                var row = (viewBtn || editBtn || deleteBtn || requestBtn) ? (viewBtn || editBtn || deleteBtn || requestBtn).closest('tr') : null;
                if (!row) return;
                var type = row.getAttribute('data-type');
                var cells = row.querySelectorAll('td');
                if (requestBtn) {
                    var id = row.getAttribute('data-id');
                    var name = cells[1].textContent;
                    var availableQty = qtyFromRow(row);
                    var invType = type === 'office-supplies' ? 'office_supplies' : type;
                    document.getElementById('req-inventory-type').value = invType;
                    document.getElementById('req-item-id').value = id;
                    document.getElementById('req-item-name').value = name;
                    document.getElementById('req-item-id-display').textContent = id;
                    document.getElementById('req-item-name-display').textContent = name;
                    document.getElementById('req-available-qty').textContent = availableQty;
                    document.getElementById('req-quantity').value = '';
                    document.getElementById('req-reason').value = '';
                    // set return params
                    var pane = requestBtn.closest('.inv-tab-pane');
                    var returnTabVal = pane ? pane.id : '';
                    var returnCatVal = '';
                    var returnStockVal = 'all';
                    var returnLocVal = '';
                    if (returnTabVal === 'inventory') {
                        var catEl = document.getElementById('filter-category-inventory-table');
                        var stockEl = document.getElementById('filter-stock-inventory-table');
                        var locEl = document.getElementById('filter-location-inventory-table');
                        if (catEl) returnCatVal = catEl.value || '';
                        if (stockEl) returnStockVal = stockEl.value || 'all';
                        if (locEl) returnLocVal = locEl.value || '';
                    }
                    document.getElementById('req-return-tab').value = returnTabVal;
                    document.getElementById('req-return-category').value = returnCatVal;
                    document.getElementById('req-return-stock').value = returnStockVal;
                    document.getElementById('req-return-location').value = returnLocVal;
                    openModal('request-item-modal');
                } else if (viewBtn) {
                    var minStock = row.getAttribute('data-min-stock') || '';
                    var maxStock = row.getAttribute('data-max-stock') || '';
                    var updatedText = row.getAttribute('data-updated') || 'N/A';
                    var imageUrl = row.getAttribute('data-image') || '';
                    var minMaxDisplay = function(v) { return (v === '' || v === null || v === undefined) ? '—' : v; };
                    if (type === 'mechanical') {
                        document.getElementById('view-mec-id').textContent = cells[0].textContent;
                        document.getElementById('view-mec-name').textContent = cells[1].textContent;
                        document.getElementById('view-mec-qty').textContent = cells[2].textContent.trim();
                        document.getElementById('view-mec-min-stock').textContent = minMaxDisplay(minStock);
                        document.getElementById('view-mec-max-stock').textContent = minMaxDisplay(maxStock);
                        document.getElementById('view-mec-brand').textContent = row.getAttribute('data-brand') || '—';
                        document.getElementById('view-mec-location').textContent = cells[5].textContent.trim() || '—';
                        document.getElementById('view-mec-date-arrived').textContent = cells[6].textContent.trim() || '—';
                        document.getElementById('view-mec-expiration').textContent = cells[7].textContent.trim() || '—';
                        document.getElementById('view-mec-notes').textContent = cells[8].textContent || '—';
                        document.getElementById('view-mec-updated').textContent = updatedText;
                        var mecImg = document.getElementById('view-mec-image');
                        var mecWrap = document.getElementById('view-mec-image-wrap');
                        if (mecImg && mecWrap) {
                            if (imageUrl) {
                                mecImg.src = imageUrl;
                                mecWrap.style.display = 'block';
                            } else {
                                mecImg.src = '';
                                mecWrap.style.display = 'none';
                            }
                        }
                        openModal('view-mechanical-modal');
                    } else if (type === 'office-supplies') {
                        document.getElementById('view-os-id').textContent = cells[0].textContent;
                        document.getElementById('view-os-name').textContent = cells[1].textContent;
                        document.getElementById('view-os-qty').textContent = cells[2].textContent.trim();
                        document.getElementById('view-os-min-stock').textContent = minMaxDisplay(minStock);
                        document.getElementById('view-os-max-stock').textContent = minMaxDisplay(maxStock);
                        document.getElementById('view-os-brand').textContent = row.getAttribute('data-brand') || '—';
                        document.getElementById('view-os-location').textContent = cells[5].textContent.trim() || '—';
                        document.getElementById('view-os-date-arrived').textContent = cells[6].textContent.trim() || '—';
                        document.getElementById('view-os-expiration').textContent = cells[7].textContent.trim() || '—';
                        document.getElementById('view-os-notes').textContent = cells[8].textContent || '—';
                        document.getElementById('view-os-updated').textContent = updatedText;
                        var osImg = document.getElementById('view-os-image');
                        var osWrap = document.getElementById('view-os-image-wrap');
                        if (osImg && osWrap) {
                            if (imageUrl) {
                                osImg.src = imageUrl;
                                osWrap.style.display = 'block';
                            } else {
                                osImg.src = '';
                                osWrap.style.display = 'none';
                            }
                        }
                        openModal('view-office-supplies-modal');
                    } else if (EQUIPMENT_ROUTES[type]) {
                        if (viewTeModalTitle) viewTeModalTitle.textContent = (EQUIPMENT_LABELS[type] || type) + ' Item Details';
                        document.getElementById('view-te-id').textContent = cells[0].textContent;
                        document.getElementById('view-te-name').textContent = cells[1].textContent;
                        document.getElementById('view-te-qty').textContent = cells[2].textContent.trim();
                        document.getElementById('view-te-min-stock').textContent = minMaxDisplay(minStock);
                        document.getElementById('view-te-max-stock').textContent = minMaxDisplay(maxStock);
                        document.getElementById('view-te-brand').textContent = row.getAttribute('data-brand') || '—';
                        document.getElementById('view-te-location').textContent = cells[5].textContent.trim() || '—';
                        document.getElementById('view-te-date-arrived').textContent = cells[6].textContent.trim() || '—';
                        document.getElementById('view-te-expiration').textContent = cells[7].textContent.trim() || '—';
                        document.getElementById('view-te-notes').textContent = cells[8].textContent || '—';
                        document.getElementById('view-te-updated').textContent = updatedText;
                        var teImg = document.getElementById('view-te-image');
                        var teWrap = document.getElementById('view-te-image-wrap');
                        if (teImg && teWrap) {
                            if (imageUrl) {
                                teImg.src = imageUrl;
                                teWrap.style.display = 'block';
                            } else {
                                teImg.src = '';
                                teWrap.style.display = 'none';
                            }
                        }
                        openModal('view-technical-equipments-modal');
                    }
                } else if (editBtn) {
                    var id = row.getAttribute('data-id');
                    var currentQty = qtyFromRow(row);
                    var editMinStock = row.getAttribute('data-min-stock') || '';
                    var editMaxStock = row.getAttribute('data-max-stock') || '';
                    var updatedRaw = row.getAttribute('data-updated') || '';
                    // figure out which tab the row belongs to; use its parent pane id
                    var pane = row.closest('.inv-tab-pane');
                    var returnTabVal = pane ? pane.id : '';
                    // if we're on the inventory tab, also record the currently-selected category filter
                    var returnCatVal = '';
                    if (returnTabVal === 'inventory') {
                        var catEl = document.getElementById('filter-category-inventory-table');
                        if (catEl) {
                            returnCatVal = catEl.value || '';
                        }
                    }
                    // choose the modal that will open for this type so we can update its hidden fields
                    var targetModal = null;
                    if (type === 'mechanical') {
                        targetModal = document.getElementById('edit-mechanical-modal');
                    } else if (type === 'office-supplies') {
                        targetModal = document.getElementById('edit-office-supplies-modal');
                    } else if (EQUIPMENT_ROUTES[type]) {
                        targetModal = document.getElementById('edit-technical-equipments-modal');
                    }
                    if (targetModal) {
                        var tabInput = targetModal.querySelector('input[name="return_tab"]');
                        var catInput = targetModal.querySelector('input[name="return_category"]');
                        if (tabInput) tabInput.value = returnTabVal || '';
                        if (catInput) catInput.value = returnCatVal || '';
                    }
                    if (type === 'mechanical') {
                        document.getElementById('edit-mec-id').value = id;
                        document.getElementById('edit-mec-name').value = cells[1].textContent;
                        document.getElementById('edit-mec-current').value = currentQty;
                        document.getElementById('edit-mec-current-display').textContent = currentQty;
                        document.getElementById('edit-mec-qty').value = '0';
                        document.getElementById('edit-mec-min-stock').value = editMinStock;
                        document.getElementById('edit-mec-max-stock').value = editMaxStock;
                        document.getElementById('edit-mec-brand').value = row.getAttribute('data-brand') || ''; // use data-attribute to avoid index shifting when responsive
                        document.getElementById('edit-mec-location').value = cells[5].textContent.trim() || '';
                        document.getElementById('edit-mec-date-arrived').value = toDateOnly(cells[6].textContent);
                        document.getElementById('edit-mec-expiration').value = toDateOnly(cells[7].textContent);
                        document.getElementById('edit-mec-notes').value = cells[8].textContent || '';
                        document.getElementById('edit-mec-updated').value = toDatetimeLocal(updatedRaw);
                        if (document.getElementById('edit-mec-department')) {
                            document.getElementById('edit-mec-department').value = '';
                        }
                        window._invEditRow = row;
                        openModal('edit-mechanical-modal');
                    } else if (type === 'office-supplies') {
                        document.getElementById('edit-os-id').value = id;
                        document.getElementById('edit-os-name').value = cells[1].textContent;
                        document.getElementById('edit-os-current').value = currentQty;
                        document.getElementById('edit-os-current-display').textContent = currentQty;
                        document.getElementById('edit-os-qty').value = '0';
                        document.getElementById('edit-os-min-stock').value = editMinStock;
                        document.getElementById('edit-os-max-stock').value = editMaxStock;
                        document.getElementById('edit-os-brand').value = row.getAttribute('data-brand') || ''; // use attribute instead of cell index
                        document.getElementById('edit-os-location').value = cells[5].textContent.trim() || '';
                        document.getElementById('edit-os-date-arrived').value = toDateOnly(cells[6].textContent);
                        document.getElementById('edit-os-expiration').value = toDateOnly(cells[7].textContent);
                        document.getElementById('edit-os-notes').value = cells[8].textContent || '';
                        document.getElementById('edit-os-updated').value = toDatetimeLocal(updatedRaw);
                        if (document.getElementById('edit-os-department')) {
                            document.getElementById('edit-os-department').value = '';
                        }
                        window._invEditRow = row;
                        openModal('edit-office-supplies-modal');
                    } else if (EQUIPMENT_ROUTES[type]) {
                        if (editEquipmentForm) editEquipmentForm.action = EQUIPMENT_ROUTES[type].update;
                        if (editTeModalTitle) editTeModalTitle.textContent = 'Edit ' + (EQUIPMENT_LABELS[type] || type) + ' Item';
                        document.getElementById('edit-te-id').value = id;
                        document.getElementById('edit-te-name').value = cells[1].textContent;
                        document.getElementById('edit-te-current').value = currentQty;
                        document.getElementById('edit-te-current-display').textContent = currentQty;
                        document.getElementById('edit-te-qty').value = '0';
                        document.getElementById('edit-te-min-stock').value = editMinStock;
                        document.getElementById('edit-te-max-stock').value = editMaxStock;
                        document.getElementById('edit-te-brand').value = row.getAttribute('data-brand') || ''; // avoid grabbing status text when columns shift
                        document.getElementById('edit-te-location').value = cells[5].textContent.trim() || '';
                        document.getElementById('edit-te-date-arrived').value = toDateOnly(cells[6].textContent);
                        document.getElementById('edit-te-expiration').value = toDateOnly(cells[7].textContent);
                        document.getElementById('edit-te-notes').value = cells[8].textContent || '';
                        document.getElementById('edit-te-updated').value = toDatetimeLocal(updatedRaw);
                        if (document.getElementById('edit-te-department')) {
                            document.getElementById('edit-te-department').value = '';
                        }
                        window._invEditRow = row;
                        openModal('edit-technical-equipments-modal');
                    }
                } else if (deleteBtn) {
                    var deleteForm = document.getElementById('delete-form');
                    var id = row.getAttribute('data-id');
                    var name = row.querySelectorAll('td')[1].textContent;
                    document.getElementById('delete-id').value = id;
                    document.getElementById('delete-message').innerHTML = 'Are you sure you want to delete <strong>' + name.replace(/</g, '&lt;') + '</strong> (Item Code: ' + id + ')?';
                    // preserve return information
                    var pane = row.closest('.inv-tab-pane');
                    var returnTabVal = pane ? pane.id : '';
                    var returnCatVal = '';
                    if (returnTabVal === 'inventory') {
                        var catEl = document.getElementById('filter-category-inventory-table');
                        if (catEl) returnCatVal = catEl.value || '';
                    }
                    var deleteTabInput = document.getElementById('delete-return-tab');
                    var deleteCatInput = document.getElementById('delete-return-category');
                    if (deleteTabInput) deleteTabInput.value = returnTabVal || '';
                    if (deleteCatInput) deleteCatInput.value = returnCatVal || '';
                    if (type === 'mechanical') deleteForm.action = '{{ route("inventory.mechanical.destroy") }}';
                    else if (type === 'office-supplies') deleteForm.action = '{{ route("inventory.office-supplies.destroy") }}';
                    else if (EQUIPMENT_ROUTES[type]) deleteForm.action = EQUIPMENT_ROUTES[type].destroy;
                    openModal('delete-confirmation-modal');
                }
            });

            function bindDepartmentToggle(actionId, departmentId) {
                var actionEl = document.getElementById(actionId);
                var deptEl = document.getElementById(departmentId);
                if (!actionEl || !deptEl) return;
                function syncDept() {
                    var isWithdraw = actionEl.value === 'withdraw_stock';
                    deptEl.disabled = !isWithdraw;
                    if (!isWithdraw) {
                        deptEl.value = '';
                    }
                }
                actionEl.addEventListener('change', syncDept);
                syncDept();
            }

            bindDepartmentToggle('edit-mec-action', 'edit-mec-department');
            bindDepartmentToggle('edit-os-action', 'edit-os-department');
            bindDepartmentToggle('edit-te-action', 'edit-te-department');

            function escapeHtml(s) {
                if (s == null || s === '') return '';
                var div = document.createElement('div');
                div.textContent = s;
                return div.innerHTML;
            }
            function showInvAjaxMessage(message, type) {
                type = type || 'success';
                var content = document.querySelector('.inv-content');
                if (!content) return;
                var existing = document.getElementById('inv-ajax-alert');
                if (existing) existing.remove();
                var el = document.createElement('div');
                el.id = 'inv-ajax-alert';
                el.className = 'inv-alert ' + (type === 'danger' ? 'danger' : 'success');
                el.setAttribute('role', 'alert');
                el.innerHTML = '<i class="fas fa-info-circle"></i> ' + escapeHtml(message);
                content.insertBefore(el, content.firstChild);
                setTimeout(function() { if (el.parentNode) el.remove(); }, 4000);
            }

            // show low-stock modal dynamically (used by AJAX updates)
            function showLowStockModal(name, category) {
                // remove old if exists
                var old = document.getElementById('low-stock-modal');
                if (old) old.remove();
                var html =
                    '<div class="inv-modal inv-modal-notification active" id="low-stock-modal" tabindex="-1" role="dialog" aria-labelledby="low-stock-title" aria-modal="true">' +
                      '<div class="inv-modal-content inv-modal-notification-content">' +
                        '<button type="button" class="inv-modal-notification-close" data-close="low-stock-modal" aria-label="Close">' +
                          '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>' +
                        '</button>' +
                        '<div class="inv-modal-notification-body">' +
                          '<svg class="inv-modal-notification-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>' +
                          '<h3 class="inv-modal-notification-title" id="low-stock-title">Low stock</h3>' +
                          '<p class="inv-modal-notification-message">' + escapeHtml(name) + ' (in ' + escapeHtml(category) + ') has reached its minimum stock level.</p>' +
                          '<div class="inv-modal-notification-actions">' +
                            '<button type="button" class="inv-btn inv-btn-primary" data-close="low-stock-modal" onclick="closeModal(\'low-stock-modal\')">OK</button>' +
                          '</div>' +
                        '</div>' +
                      '</div>' +
                    '</div>';
                var div = document.createElement('div');
                div.innerHTML = html;
                document.body.appendChild(div.firstChild);
                // wire up listeners
                var btn = document.querySelector('#low-stock-modal [data-close]');
                if (btn) btn.addEventListener('click', function() { closeModal('low-stock-modal'); });
                var modal = document.getElementById('low-stock-modal');
                if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeModal('low-stock-modal'); });
            }

            // show out-of-stock modal via JS (used by AJAX updates)
            function showOutOfStockModal(name, category) {
                var old = document.getElementById('out-of-stock-modal');
                if (old) old.remove();
                var html =
                    '<div class="inv-modal inv-modal-notification active" id="out-of-stock-modal" tabindex="-1" role="dialog" aria-labelledby="out-of-stock-title" aria-modal="true">' +
                      '<div class="inv-modal-content inv-modal-notification-content">' +
                        '<button type="button" class="inv-modal-notification-close" data-close="out-of-stock-modal" aria-label="Close">' +
                          '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>' +
                        '</button>' +
                        '<div class="inv-modal-notification-body">' +
                          '<svg class="inv-modal-notification-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>' +
                          '<h3 class="inv-modal-notification-title" id="out-of-stock-title">Out of stock</h3>' +
                          '<p class="inv-modal-notification-message">' + escapeHtml(name) + ' (in ' + escapeHtml(category) + ') is now out of stock.</p>' +
                          '<div class="inv-modal-notification-actions">' +
                            '<button type="button" class="inv-btn inv-btn-primary" data-close="out-of-stock-modal" onclick="closeModal(\'out-of-stock-modal\')">OK</button>' +
                          '</div>' +
                        '</div>' +
                      '</div>' +
                    '</div>';
                var div = document.createElement('div');
                div.innerHTML = html;
                document.body.appendChild(div.firstChild);
                var btn = document.querySelector('#out-of-stock-modal [data-close]');
                if (btn) btn.addEventListener('click', function() { closeModal('out-of-stock-modal'); });
                var modal = document.getElementById('out-of-stock-modal');
                if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeModal('out-of-stock-modal'); });
            }
            function updateRowFromItem(row, item) {
                var cells = row.querySelectorAll('td');
                var isUnified = cells.length >= 11;
                var dash = '—';
                row.setAttribute('data-id', item.id);
                row.setAttribute('data-type', item.category_type);
                row.setAttribute('data-category', item.category_type);
                row.setAttribute('data-min-stock', item.min_stock != null ? String(item.min_stock) : '');
                row.setAttribute('data-max-stock', item.max_stock != null ? String(item.max_stock) : '');
                row.setAttribute('data-updated', item.updated_at || 'N/A');
                row.setAttribute('data-low-stock', item.is_low_stock ? '1' : '0');
                row.setAttribute('data-out-of-stock', item.out_of_stock ? '1' : '0');
                row.setAttribute('data-location', item.location || dash);
                row.setAttribute('data-image', item.image_path || '');
                if (item.brand != null) row.setAttribute('data-brand', item.brand || '');
                if (item.status != null) row.setAttribute('data-equipment-status', item.status || '');
                var statusHtml = '<span class="inv-badge ' + (item.badge_class || 'in-stock') + '">' + escapeHtml(item.stock_status || 'In Stock') + '</span>';
                var brandHtml = (item.brand && item.brand !== '') ? '<span class="inv-badge brand">' + escapeHtml(item.brand) + '</span>' : dash;
                var expHtml = '<span class="inv-expiration-date">' + escapeHtml(item.expiration_date || dash) + '</span>';
                var loc = item.location || dash;
                var dateArr = item.date_arrived || dash;
                var notes = item.notes || '';
                if (isUnified) {
                    cells[0].textContent = item.id;
                    cells[1].textContent = item.item_name;
                    cells[2].textContent = item.category_label || '';
                    var qtyCell = cells[3].querySelector('.qty-value');
                    if (qtyCell) qtyCell.textContent = item.quantity; else cells[3].textContent = item.quantity;
                    cells[4].innerHTML = statusHtml;
                    cells[5].innerHTML = brandHtml;
                    cells[6].textContent = loc;
                    cells[7].textContent = dateArr;
                    cells[8].innerHTML = expHtml;
                    cells[9].textContent = notes;
                } else {
                    cells[0].textContent = item.id;
                    cells[1].textContent = item.item_name;
                    var qtyCell = cells[2].querySelector('.qty-value');
                    if (qtyCell) qtyCell.textContent = item.quantity; else cells[2].textContent = item.quantity;
                    cells[3].innerHTML = statusHtml;
                    cells[4].innerHTML = brandHtml;
                    cells[5].textContent = loc;
                    cells[6].textContent = dateArr;
                    cells[7].innerHTML = expHtml;
                    cells[8].textContent = notes;
                }
                var table = row.closest('table');
                if (table && typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
                    var dt = $(table).DataTable();
                    if (dt && dt.row) dt.row(row).invalidate().draw(false);
                }
            }
            function handleEditFormSubmit(e) {
                var form = e.target;
                if (!form || !form.closest('.inv-modal')) return;
                var row = window._invEditRow;
                if (!row) return;
                e.preventDefault();
                var formData = new FormData(form);
                var submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Updating…'; }
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(res) {
                    return res.json().then(function(data) {
                        if (!res.ok) throw { status: res.status, data: data };
                        return data;
                    });
                }).then(function(data) {
                    if (data.success && data.item) {
                        updateRowFromItem(row, data.item);
                        var modal = form.closest('.inv-modal');
                        if (modal && modal.id) closeModal(modal.id);
                        showInvAjaxMessage(data.message || 'Item updated successfully.', 'success');
                        // if server indicates item went out of stock, show notification
                        if (data.item.out_of_stock) {
                            showOutOfStockModal(data.item.item_name || data.item.id, data.item.category_label || data.item.category_type || '');
                        }
                        // else if it's low stock (but not fully out) show low-stock message
                        else if (data.item.is_low_stock) {
                            showLowStockModal(data.item.item_name || data.item.id, data.item.category_label || data.item.category_type || '');
                        }
                    } else {
                        showInvAjaxMessage(data.message || 'Update failed.', 'danger');
                    }
                }).catch(function(err) {
                    var msg = (err && err.data && err.data.message) ? err.data.message : (err && err.message) ? err.message : 'Update failed. Please try again.';
                    showInvAjaxMessage(msg, 'danger');
                }).finally(function() {
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Update'; }
                    window._invEditRow = null;
                });
            }
            document.querySelector('#edit-mechanical-modal form')?.addEventListener('submit', handleEditFormSubmit);
            document.querySelector('#edit-office-supplies-modal form')?.addEventListener('submit', handleEditFormSubmit);
            document.getElementById('edit-equipment-form')?.addEventListener('submit', handleEditFormSubmit);

            // No JS tab switching needed; tabs are controlled via ?tab=... query parameter.

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeInvUserDropdown();
            });

            var invUserTrigger = document.getElementById('inv-user-dropdown-trigger');
            var invUserDropdown = document.getElementById('inv-user-dropdown');
            function closeInvUserDropdown() {
                if (invUserDropdown) invUserDropdown.classList.remove('open');
                if (invUserTrigger) invUserTrigger.setAttribute('aria-expanded', 'false');
            }
            function toggleInvUserDropdown() {
                if (!invUserDropdown) return;
                var isOpen = invUserDropdown.classList.toggle('open');
                if (invUserTrigger) invUserTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
            if (invUserTrigger && invUserDropdown) {
                invUserTrigger.addEventListener('click', function(e) { e.stopPropagation(); toggleInvUserDropdown(); });
                document.addEventListener('click', function() { closeInvUserDropdown(); });
                invUserDropdown.addEventListener('click', function(e) { e.stopPropagation(); });
            }

            // Store Room: update pending request badge without refresh
            if (@json($isStoreRoom ?? false)) {
                var pollUrl = '{{ route('requests.pending-data') }}';
                var requestsUrl = '{{ route('requests.index') }}';

                function ensureTriggerBadgesWrap() {
                    var wrap = document.querySelector('.inv-user-dropdown-wrap');
                    if (!wrap) return null;
                    var el = wrap.querySelector('.inv-trigger-badges');
                    if (el) return el;
                    el = document.createElement('span');
                    el.className = 'inv-trigger-badges';
                    wrap.appendChild(el);
                    return el;
                }

                function setPendingBadgeCount(count) {
                    var wrap = ensureTriggerBadgesWrap();
                    if (!wrap) return;

                    var pendingBadge = wrap.querySelector('.inv-trigger-notification-badge:not(.inv-trigger-badge-expired)');
                    if (count > 0) {
                        if (!pendingBadge) {
                            pendingBadge = document.createElement('span');
                            pendingBadge.className = 'inv-trigger-notification-badge';
                            wrap.prepend(pendingBadge);
                        }
                        pendingBadge.textContent = count > 99 ? '99+' : String(count);
                        pendingBadge.setAttribute('aria-label', count + ' pending item request(s)');
                    } else if (pendingBadge) {
                        pendingBadge.parentNode.removeChild(pendingBadge);
                    }

                    if (wrap.children.length === 0) {
                        wrap.parentNode.removeChild(wrap);
                    }

                    var dropdownLink = document.querySelector('.inv-user-dropdown a[href="' + requestsUrl + '"]');
                    if (dropdownLink) {
                        var linkBadge = dropdownLink.querySelector('.inv-dropdown-notification-badge:not(.inv-dropdown-notification-badge-expired)');
                        if (count > 0) {
                            dropdownLink.classList.add('inv-dropdown-item-with-badge');
                            if (!linkBadge) {
                                linkBadge = document.createElement('span');
                                linkBadge.className = 'inv-dropdown-notification-badge';
                                dropdownLink.appendChild(linkBadge);
                            }
                            linkBadge.textContent = count > 99 ? '99+' : String(count);
                            linkBadge.setAttribute('aria-label', count + ' pending item request(s)');
                        } else {
                            if (linkBadge) linkBadge.parentNode.removeChild(linkBadge);
                            dropdownLink.classList.remove('inv-dropdown-item-with-badge');
                        }
                    }
                }

                function poll() {
                    fetch(pollUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (res) { return res.json(); })
                        .then(function (data) { setPendingBadgeCount(parseInt(data.count || 0, 10) || 0); })
                        .catch(function () {});
                }

                poll();
                setInterval(poll, 8000);
            }
        });
    </script>
</body>
</html>
