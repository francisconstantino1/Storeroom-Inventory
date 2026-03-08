<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Logs — Store Room</title>
    <link rel="icon" type="image/png" href="{{ asset('images/franklin-baker-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login-logs.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.dataTables.min.css">
</head>
<body>
    <div class="dash-container">
        <main class="dash-content-area">
            <header class="dash-top-bar">
                <img src="{{ asset('images/franklin-baker-logo.png') }}" alt="Franklin Baker" class="dash-top-bar-logo">
                <div class="dash-top-bar-title">
                    <h1 class="dash-top-bar-heading">Login Logs</h1>
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

            <div class="logs-table-wrap">
                <table class="logs-table" id="login-logs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Status</th>
                            <th>Failure Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->username }}</td>
                                <td class="logs-table-datetime" data-order="{{ $log->created_at?->timestamp ?? '' }}">{{ $log->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td class="logs-table-datetime" data-order="{{ $log->logout_time?->timestamp ?? '' }}">{{ $log->logout_time?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>
                                    <span class="status-badge {{ strtolower($log->status) }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td>{{ $log->failure_reason ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 24px;">No login logs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeInvUserDropdown(); });

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

            var table = document.getElementById('login-logs-table');
            if (table && typeof $ !== 'undefined' && $.fn.DataTable && !table.querySelector('tbody tr td[colspan]')) {
                $('#login-logs-table').DataTable({ order: [[2, 'desc']], pageLength: 25 });
            }
        });
    </script>
</body>
</html>
