<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isStoreRoom ? 'Item Requests — Store Room' : 'Pending Items — Store Room' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/franklin-baker-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
</head>
<body>
    <div class="dash-container">
        <main class="dash-content-area">
            <header class="dash-top-bar">
                <img src="{{ asset('images/franklin-baker-logo.png') }}" alt="Franklin Baker" class="dash-top-bar-logo">
                <div class="dash-top-bar-title">
                    <h1 class="dash-top-bar-heading">{{ $isStoreRoom ? 'Item Requests' : 'Pending Items' }}</h1>
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
                            <li class="inv-user-dropdown-border">
                                <form action="{{ route('logout') }}" method="post" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="inv-user-dropdown-signout" role="menuitem">
                                        <svg class="inv-user-dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/></svg>
                                        Sign out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="inv-content">
                @if(session('message'))
                    <div id="inv-flash-alert" class="inv-alert {{ session('messageType', 'success') === 'danger' ? 'danger' : 'success' }}">
                        <i class="fas fa-info-circle"></i>
                        {{ session('message') }}
                    </div>
                @endif

                @if($isStoreRoom)
                    <div class="inv-tabs req-tabs" role="tablist" style="margin-bottom:16px;">
                        <a href="#pending-requests-pane" class="active" data-tab="pending">Pending Requests</a>
                        <a href="#recent-requests-pane" data-tab="recent">Recent Requests</a>
                    </div>

                    <div class="inv-tab-pane req-tab-pane active" id="pending-requests-pane">
                        <div class="logs-table-wrap" style="margin-bottom: 24px;">
                            <h2 class="dash-top-bar-heading" style="margin-bottom: 8px;">Pending requests</h2>
                            <table class="inv-table" id="pending-requests-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Department</th>
                                        <th>Requested By</th>
                                        <th>Inventory Type</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Qty</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingRequests as $req)
                                        <tr>
                                            <td>{{ $req->id }}</td>
                                            <td>{{ $req->created_at?->format('M j, Y H:i') ?? '' }}</td>
                                            <td>{{ $req->requested_department ?? '—' }}</td>
                                            <td>{{ $req->requestedBy->name ?? '—' }}</td>
                                            <td>{{ $req->inventory_type }}</td>
                                            <td>{{ $req->item_id }}</td>
                                            <td>{{ $req->item_name }}</td>
                                            <td>{{ $req->requested_quantity }}</td>
                                            <td>
                                                <div class="inv-actions">
                                                    <button type="button" class="inv-action-btn view" title="View request" data-id="{{ $req->id }}"><i class="fas fa-eye"></i></button>
                                                    <form action="{{ route('requests.approve', $req) }}" method="post" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="inv-action-btn edit" title="Approve request"><i class="fas fa-check"></i></button>
                                                    </form>
                                                    <form action="{{ route('requests.reject', $req) }}" method="post" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="inv-action-btn delete" title="Reject request"><i class="fas fa-times"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" style="text-align:center;padding:16px;">No pending requests.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="inv-tab-pane req-tab-pane" id="recent-requests-pane">
                        <div class="logs-table-wrap">
                            <h2 class="dash-top-bar-heading" style="margin-bottom: 8px;">Recent requests</h2>
                            <table class="inv-table" id="recent-requests-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Department</th>
                                        <th>Requested By</th>
                                        <th>Type</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentRequests as $req)
                                        @php
                                            $recentStatus = $req->status ?? 'Pending';
                                            $recentStatusLower = strtolower($recentStatus);
                                            $recentStatusClass = $recentStatusLower === 'approved'
                                                ? 'approved'
                                                : ($recentStatusLower === 'rejected' ? 'rejected' : 'pending');
                                        @endphp
                                        <tr>
                                            <td>{{ $req->id }}</td>
                                            <td>{{ $req->created_at?->format('M j, Y H:i') ?? '' }}</td>
                                            <td>{{ $req->requested_department ?? '—' }}</td>
                                            <td>{{ $req->requestedBy->name ?? '—' }}</td>
                                            <td>{{ $req->inventory_type }}</td>
                                            <td>{{ $req->item_id }}</td>
                                            <td>{{ $req->item_name }}</td>
                                            <td>{{ $req->requested_quantity }}</td>
                                            <td><span class="inv-status-badge {{ $recentStatusClass }}">{{ $recentStatus }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" style="text-align:center;padding:16px;">No requests yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- View request modal (Store Room only) --}}
                    <div class="inv-modal" id="view-request-modal">
                        <div class="inv-modal-content">
                            <div class="inv-modal-header">
                                <img src="{{ asset('images/franklin-baker-form-logo.png') }}" alt="Franklin Baker" class="inv-modal-logo">
                                <h3>Store Room</h3>
                                <h4>Request details</h4>
                            </div>
                            <div class="inv-form-group"><label>ID</label><div class="inv-view-field" id="view-req-id"></div></div>
                            <div class="inv-form-group"><label>Date</label><div class="inv-view-field" id="view-req-date"></div></div>
                            <div class="inv-form-group"><label>Department</label><div class="inv-view-field" id="view-req-department"></div></div>
                            <div class="inv-form-group"><label>Requested by</label><div class="inv-view-field" id="view-req-by"></div></div>
                            <div class="inv-form-group"><label>Inventory type</label><div class="inv-view-field" id="view-req-type"></div></div>
                            <div class="inv-form-group"><label>Item code</label><div class="inv-view-field" id="view-req-item-id"></div></div>
                            <div class="inv-form-group"><label>Item name</label><div class="inv-view-field" id="view-req-item-name"></div></div>
                            <div class="inv-form-group"><label>Quantity</label><div class="inv-view-field" id="view-req-qty"></div></div>
                            <div class="inv-modal-buttons">
                                <button type="button" class="inv-btn inv-btn-exit" data-close="view-request-modal">Close</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="logs-table-wrap">
                        <h2 class="dash-top-bar-heading" style="margin-bottom: 8px;">My requests</h2>
                        <table class="inv-table" id="my-requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Qty</th>
                                    <th>Request Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRequests as $req)
                                    @php
                                        $status = $req->status ?? 'Pending';
                                        $statusLower = strtolower($status);
                                        $statusClass = $statusLower === 'approved'
                                            ? 'approved'
                                            : ($statusLower === 'rejected' ? 'rejected' : 'pending');
                                    @endphp
                                    <tr>
                                        <td>{{ $req->id }}</td>
                                        <td>{{ $req->created_at?->format('M j, Y H:i') ?? '' }}</td>
                                        <td>{{ $req->inventory_type }}</td>
                                        <td>{{ $req->item_id }}</td>
                                        <td>{{ $req->item_name }}</td>
                                        <td>{{ $req->requested_quantity }}</td>
                                        <td>
                                            <span class="inv-status-badge {{ $statusClass }}">
                                                {{ $status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" style="text-align:center;padding:16px;">No requests yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss flash alert after 5 seconds
            var flashAlert = document.getElementById('inv-flash-alert');
            if (flashAlert) {
                setTimeout(function() {
                    flashAlert.style.transition = 'opacity 0.3s';
                    flashAlert.style.opacity = '0';
                    setTimeout(function() {
                        flashAlert.remove();
                    }, 300);
                }, 5000);
            }

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
                invUserTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleInvUserDropdown();
                });
                document.addEventListener('click', function() {
                    closeInvUserDropdown();
                });
                invUserDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeInvUserDropdown();
            });

            // Initialize DataTables with layout so search, page length, and columns work (DataTables 2)
            var reqDtOptions = {
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total)',
                    emptyTable: 'No data',
                    zeroRecords: 'No matching records found',
                    paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
                }
            };
            ['pending-requests-table', 'recent-requests-table', 'my-requests-table'].forEach(function(id) {
                var table = document.getElementById(id);
                if (!table || typeof $ === 'undefined' || !$.fn.DataTable) return;
                var $t = $('#' + id);
                if ($.fn.DataTable.isDataTable('#' + id)) return;
                var emptyRow = table.querySelector('tbody tr:only-child td[colspan]');
                if (emptyRow) {
                    emptyRow.closest('tr').remove();
                }
                $t.DataTable(reqDtOptions);
            });

            // Simple tab handling for Store Room request lists
            var reqTabLinks = document.querySelectorAll('.req-tabs a');
            var reqTabPanes = document.querySelectorAll('.req-tab-pane');

            if (reqTabLinks.length > 0 && reqTabPanes.length > 0) {
                reqTabLinks.forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();

                        var targetId = this.getAttribute('href').replace('#', '');

                        reqTabLinks.forEach(function(l) {
                            l.classList.remove('active');
                        });

                        reqTabPanes.forEach(function(pane) {
                            pane.classList.remove('active');
                        });

                        this.classList.add('active');
                        var targetPane = document.getElementById(targetId);
                        if (targetPane) {
                            targetPane.classList.add('active');
                        }
                    });
                });
            }

            // View request button: open modal and fill from row (pending requests table)
            var viewRequestModal = document.getElementById('view-request-modal');
            if (viewRequestModal) {
                document.querySelector('.inv-content').addEventListener('click', function(e) {
                    var viewBtn = e.target.closest('.inv-action-btn.view');
                    if (!viewBtn) return;
                    var row = viewBtn.closest('tr');
                    if (!row || !row.cells || row.cells.length < 8) return;
                    var cells = row.cells;
                    document.getElementById('view-req-id').textContent = cells[0].textContent.trim();
                    document.getElementById('view-req-date').textContent = cells[1].textContent.trim();
                    document.getElementById('view-req-department').textContent = cells[2].textContent.trim();
                    document.getElementById('view-req-by').textContent = cells[3].textContent.trim();
                    document.getElementById('view-req-type').textContent = cells[4].textContent.trim();
                    document.getElementById('view-req-item-id').textContent = cells[5].textContent.trim();
                    document.getElementById('view-req-item-name').textContent = cells[6].textContent.trim();
                    document.getElementById('view-req-qty').textContent = cells[7].textContent.trim();
                    viewRequestModal.classList.add('active');
                });

                document.querySelectorAll('[data-close="view-request-modal"]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        viewRequestModal.classList.remove('active');
                    });
                });
                viewRequestModal.addEventListener('click', function(e) {
                    if (e.target === viewRequestModal) viewRequestModal.classList.remove('active');
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && viewRequestModal.classList.contains('active')) {
                        viewRequestModal.classList.remove('active');
                    }
                });
            }

            // Real-time polling for new pending requests (Store Room only)
            var pendingTableBody = document.getElementById('pending-requests-table') && document.querySelector('#pending-requests-table tbody');
            if (pendingTableBody) {
                var pendingPollUrl = '{{ route("requests.pending-data") }}';
                var csrfToken = '{{ csrf_token() }}';
                var requestsBaseUrl = '{{ url("requests") }}';
                var lastPendingCount = {{ $pendingRequests->count() }};
                var pendingDt = null;
                if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#pending-requests-table')) {
                    pendingDt = $('#pending-requests-table').DataTable();
                }

                function showNewRequestToast() {
                    var toast = document.getElementById('req-new-request-toast');
                    if (!toast) {
                        toast = document.createElement('div');
                        toast.id = 'req-new-request-toast';
                        toast.className = 'inv-alert success';
                        toast.style.cssText = 'position:fixed;top:90px;left:50%;transform:translateX(-50%);z-index:1100;min-width:280px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
                        toast.innerHTML = '<i class="fas fa-info-circle"></i> New item request received!';
                        document.body.appendChild(toast);
                    }
                    toast.style.display = 'block';
                    toast.style.opacity = '1';
                    clearTimeout(window._reqToastTimeout);
                    window._reqToastTimeout = setTimeout(function() {
                        toast.style.transition = 'opacity 0.3s';
                        toast.style.opacity = '0';
                        setTimeout(function() { toast.style.display = 'none'; }, 300);
                    }, 4000);
                }

                function escapeHtml(str) {
                    if (str === null || str === undefined) return '';
                    var div = document.createElement('div');
                    div.textContent = String(str);
                    return div.innerHTML;
                }

                function buildPendingRows(data) {
                    if (!data.pending || data.pending.length === 0) {
                        return '<tr><td colspan="9" style="text-align:center;padding:16px;">No pending requests.</td></tr>';
                    }
                    var html = '';
                    data.pending.forEach(function(r) {
                        html += '<tr><td>' + escapeHtml(r.id) + '</td><td>' + escapeHtml(r.created_at) + '</td><td>' + escapeHtml(r.requested_department) + '</td><td>' + escapeHtml(r.requested_by_name) + '</td><td>' + escapeHtml(r.inventory_type) + '</td><td>' + escapeHtml(r.item_id) + '</td><td>' + escapeHtml(r.item_name) + '</td><td>' + escapeHtml(r.requested_quantity) + '</td><td><div class="inv-actions"><button type="button" class="inv-action-btn view" title="View request" data-id="' + escapeHtml(r.id) + '"><i class="fas fa-eye"></i></button><form action="' + requestsBaseUrl + '/' + r.id + '/approve" method="post" style="display:inline;"><input type="hidden" name="_token" value="' + csrfToken + '"><button type="submit" class="inv-action-btn edit" title="Approve request"><i class="fas fa-check"></i></button></form><form action="' + requestsBaseUrl + '/' + r.id + '/reject" method="post" style="display:inline;"><input type="hidden" name="_token" value="' + csrfToken + '"><button type="submit" class="inv-action-btn delete" title="Reject request"><i class="fas fa-times"></i></button></form></div></td></tr>';
                    });
                    return html;
                }

                function refreshPendingTable() {
                    fetch(pendingPollUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.count > lastPendingCount) {
                                showNewRequestToast();
                            }
                            lastPendingCount = data.count;
                            if (pendingDt) {
                                pendingDt.clear();
                                if (data.pending && data.pending.length) {
                                    var rows = data.pending.map(function(r) {
                                        var actionsHtml = '<div class="inv-actions">'
                                            + '<button type="button" class="inv-action-btn view" title="View request" data-id="' + escapeHtml(r.id) + '"><i class="fas fa-eye"></i></button>'
                                            + '<form action="' + requestsBaseUrl + '/' + r.id + '/approve" method="post" style="display:inline;">'
                                            + '<input type="hidden" name="_token" value="' + csrfToken + '">'
                                            + '<button type="submit" class="inv-action-btn edit" title="Approve request"><i class="fas fa-check"></i></button>'
                                            + '</form>'
                                            + '<form action="' + requestsBaseUrl + '/' + r.id + '/reject" method="post" style="display:inline;">'
                                            + '<input type="hidden" name="_token" value="' + csrfToken + '">'
                                            + '<button type="submit" class="inv-action-btn delete" title="Reject request"><i class="fas fa-times"></i></button>'
                                            + '</form>'
                                            + '</div>';

                                        return [
                                            r.id,
                                            r.created_at,
                                            r.requested_department,
                                            r.requested_by_name,
                                            r.inventory_type,
                                            r.item_id,
                                            r.item_name,
                                            r.requested_quantity,
                                            actionsHtml
                                        ];
                                    });
                                    pendingDt.rows.add(rows);
                                }
                                pendingDt.draw(false);
                                return;
                            }

                            var tbody = document.querySelector('#pending-requests-table tbody');
                            if (!tbody) return;
                            tbody.innerHTML = buildPendingRows(data);
                        })
                        .catch(function() {});
                }

                refreshPendingTable();
                setInterval(refreshPendingTable, 8000);
            }
        });
    </script>
</body>
</html>

