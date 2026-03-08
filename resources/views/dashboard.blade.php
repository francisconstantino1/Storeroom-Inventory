<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Store Room</title>
    <link rel="icon" type="image/png" href="{{ asset('images/franklin-baker-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dash-container">
        <main class="dash-content-area">
            <header class="dash-top-bar">
                <img src="{{ asset('images/franklin-baker-logo.png') }}" alt="Franklin Baker" class="dash-top-bar-logo">
                <div class="dash-top-bar-title">
                    <h1 class="dash-top-bar-heading">Store Room Department</h1>
                    <p class="dash-top-bar-welcome">Welcome, {{ auth()->user()->name }}</p>
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
                            @else
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

            @php
                $role = auth()->user()->role ?? '';
                $isStoreRoom = in_array($role, ['Store Room Supervisor', 'Store Room Assistant'], true);
            @endphp

            <div class="dash-grid">
                @if($isStoreRoom && count($lowStockByCategory) > 0)
                <div class="dash-card dash-card-full dash-restock-card">
                    <div class="dash-restock-header">
                        <div class="dash-restock-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="dash-restock-title">Items needing restock</h3>
                            <p class="dash-restock-sub">Categories with stock at or below minimum. Restock soon to avoid running out.</p>
                        </div>
                    </div>
                    <div class="dash-restock-list">
                        @foreach($lowStockByCategory as $group)
                            <div class="dash-restock-item">
                                <div class="dash-restock-item-main">
                                    <span class="dash-restock-category">{{ $group['label'] }}</span>
                                    <span class="dash-restock-count">{{ $group['count'] }} {{ $group['count'] === 1 ? 'item' : 'items' }} low</span>
                                    <a href="{{ route('inventory.index') }}#{{ $group['fragment'] }}" class="dash-restock-link">View in Inventory <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <ul class="dash-restock-item-names dash-item-list">
                                    @foreach($group['items'] as $item)
                                        <li class="dash-item-row">
                                            <span class="dash-item-name">{{ $item['name'] }}</span>
                                            <span class="dash-item-stock-meta">
                                                <span class="dash-item-stock-left">
                                                    <span class="dash-item-meta-label">Stock left</span>
                                                    <span class="dash-item-meta-value dash-stock-current">{{ $item['quantity'] }}</span>
                                                </span>
                                                <span class="dash-item-min-stock">
                                                    <span class="dash-item-meta-label">Min stock</span>
                                                    <span class="dash-item-meta-value">{{ $item['min_stock'] ?? '—' }}</span>
                                                </span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($isStoreRoom)
                <div class="dash-card dash-card-full dash-restock-card dash-restock-ok">
                    <div class="dash-restock-header">
                        <div class="dash-restock-icon dash-restock-ok-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="dash-restock-title">Stock levels OK</h3>
                            <p class="dash-restock-sub">No categories are currently below minimum stock. Check inventory to adjust min levels or add items.</p>
                        </div>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="dash-card-link" style="margin-top: 12px; display: inline-flex;">
                        <i class="fas fa-boxes"></i> Open Inventory
                    </a>
                </div>
            @endif

            @if(!$isStoreRoom)
                @if(isset($pendingRequests) && $pendingRequests->count() > 0)
                <div class="dash-card dash-card-full dash-restock-card">
                    <div class="dash-restock-header">
                        <div class="dash-restock-icon dash-restock-warning-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h3 class="dash-restock-title">Your pending item requests</h3>
                            <p class="dash-restock-sub">These requests are waiting for Store Room approval.</p>
                        </div>
                    </div>
                    <div class="dash-restock-list">
                        <ul class="dash-restock-item-names">
                            @foreach($pendingRequests as $req)
                                <li>
                                    <strong>{{ $req->item_name }}</strong>
                                    ({{ $req->item_id }}) —
                                    {{ $req->requested_quantity }} pcs,
                                    requested on {{ $req->created_at?->format('M d, Y H:i') ?? 'N/A' }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('requests.index') }}" class="dash-card-link" style="margin-top: 12px; display: inline-flex;">
                            <i class="fas fa-eye"></i> Pending Items
                        </a>
                    </div>
                </div>
                @else
                <div class="dash-card dash-card-full dash-restock-card dash-restock-ok">
                    <div class="dash-restock-header">
                        <div class="dash-restock-icon dash-restock-ok-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="dash-restock-title">No pending item requests</h3>
                            <p class="dash-restock-sub">You do not have any pending requests. Go to Pending Items to see your history or create a new request from Inventory.</p>
                        </div>
                    </div>
                    <a href="{{ route('requests.index') }}" class="dash-card-link" style="margin-top: 12px; display: inline-flex;">
                        <i class="fas fa-clipboard-list"></i> Pending Items
                    </a>
                </div>
                @endif
            @endif

            @if($isStoreRoom && isset($expiringSoonByCategory) && count($expiringSoonByCategory) > 0)
                <div class="dash-card dash-card-full dash-restock-card dash-expiry-card">
                    <div class="dash-restock-header">
                        <div class="dash-restock-icon dash-restock-warning-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="dash-restock-title">Items nearing expiration</h3>
                            <p class="dash-restock-sub">Items whose expiration date is within the next 30 days. Use or replace these soon.</p>
                        </div>
                    </div>
                    <div class="dash-restock-list">
                        @foreach($expiringSoonByCategory as $group)
                            <div class="dash-restock-item">
                                <div class="dash-restock-item-main">
                                    <span class="dash-restock-category">{{ $group['label'] }}</span>
                                    <span class="dash-restock-count">{{ $group['count'] }} {{ $group['count'] === 1 ? 'item' : 'items' }} expiring soon</span>
                                    <a href="{{ route('inventory.index') }}#{{ $group['fragment'] }}" class="dash-restock-link">View in Inventory <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <ul class="dash-restock-item-names dash-item-list dash-expiry-list">
                                    @foreach($group['items'] as $item)
                                        <li class="dash-item-row dash-expiry-row">
                                            <span class="dash-item-name">{{ $item['name'] }} <span class="dash-item-id">({{ $item['id'] }})</span></span>
                                            <span class="dash-item-expiry">
                                                <span class="dash-item-meta-label">Expires</span>
                                                <span class="dash-item-meta-value">{{ $item['expiration_date'] }}</span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($isStoreRoom)
                <div class="dash-card dash-card-full dash-restock-card dash-restock-ok dash-expiry-ok">
                    <div class="dash-restock-header">
                        <div class="dash-restock-icon dash-restock-ok-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="dash-restock-title">No items nearing expiration</h3>
                            <p class="dash-restock-sub">There are currently no items expiring within the next 30 days.</p>
                        </div>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="dash-card-link" style="margin-top: 12px; display: inline-flex;">
                        <i class="fas fa-calendar-alt"></i> Review expiration dates
                    </a>
                </div>
            @endif
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-dismiss-target]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var targetSelector = this.getAttribute('data-dismiss-target');
                    var el = targetSelector ? document.querySelector(targetSelector) : null;
                    if (el) el.style.display = 'none';
                });
            });

            document.addEventListener('keydown', function (e) {
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
                invUserTrigger.addEventListener('click', function (e) { e.stopPropagation(); toggleInvUserDropdown(); });
                document.addEventListener('click', function () { closeInvUserDropdown(); });
                invUserDropdown.addEventListener('click', function (e) { e.stopPropagation(); });
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
