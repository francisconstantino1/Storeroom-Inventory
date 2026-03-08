<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings — Store Room</title>
    <link rel="icon" type="image/png" href="{{ asset('images/franklin-baker-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.dataTables.min.css">
</head>
<body>
    <div class="dash-container">
        <main class="dash-content-area">
            <header class="dash-top-bar">
                <img src="{{ asset('images/franklin-baker-logo.png') }}" alt="Franklin Baker" class="dash-top-bar-logo">
                <div class="dash-top-bar-title">
                    <h1 class="dash-top-bar-heading">Settings</h1>
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

            @if(session('message'))
                <div class="alert alert-{{ session('messageType', 'success') === 'danger' ? 'danger' : 'success' }}" id="settings-alert">
                    <i class="fas fa-{{ session('messageType') === 'danger' ? 'exclamation-circle' : 'check-circle' }}"></i>
                    {{ session('message') }}
                </div>
            @endif

            <div class="settings-tabs">
                @if($isSuperAdmin)
                    <a href="#user-management" class="active" data-tab="user-management">User Management</a>
                @endif
                <a href="#profile-settings" data-tab="profile-settings" @if(!$isSuperAdmin) class="active" @endif>Profile Settings</a>
            </div>

            <div class="settings-content">
                @if($isSuperAdmin)
                    <div class="settings-pane active" id="user-management">
                        <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                            <button type="button" class="btn btn-primary" id="add-user-btn">
                                <i class="fas fa-user-plus"></i> Add New User
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="settings-table" id="users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Role</th>
                                        <th>Created On</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $u)
                                        <tr>
                                            <td>{{ $u->id }}</td>
                                            <td>{{ $u->username ?? '—' }}</td>
                                            <td>{{ $u->name }}</td>
                                            <td>
                                                @php $roleClass = 'role-store-room-assistant'; if ($u->role === 'Store Room Supervisor') $roleClass = 'role-store-room-supervisor'; @endphp
                                                <span class="role-badge {{ $roleClass }}">{{ $u->role ?? 'Store Room Assistant' }}</span>
                                            </td>
                                            <td>{{ $u->created_at?->format('M d, Y') ?? '—' }}</td>
                                            <td>{{ $u->created_by ?? '—' }}</td>
                                            <td>
                                                <div class="action-btns">
                                                    <button type="button" class="action-btn edit" title="Edit" onclick="editUser({{ $u->id }}, '{{ addslashes($u->username ?? '') }}', '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->contact_number ?? '') }}', '{{ addslashes($u->address ?? '') }}', '{{ addslashes($u->role ?? 'Store Room Assistant') }}')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($u->id !== auth()->id())
                                                        <button type="button" class="action-btn del" title="Delete" onclick="deleteUser({{ $u->id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="settings-pane {{ !$isSuperAdmin ? 'active' : '' }}" id="profile-settings">
                    <h3 style="margin: 0 0 8px;">Profile Settings</h3>
                    <p style="color: #6b7280; margin-bottom: 20px;">Update your information and password.</p>

                    <form action="{{ route('settings.profile.update') }}" method="post" id="profile-form" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label>Profile Picture</label>
                                    <div class="profile-picture-preview" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background: var(--inv-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 600; margin-bottom: 10px;">
                                        @if(auth()->user()->profile_picture)
                                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        @endif
                                    </div>
                                    <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small style="color: #6b7280; font-size: 12px;">Max 2MB. JPG, PNG, GIF, WebP.</small>
                                    @error('profile_picture') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                                    @error('name') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                                    @error('email') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="contact_number">Contact Number</label>
                                    <input type="text" id="contact_number" name="contact_number" class="form-control" value="{{ old('contact_number', auth()->user()->contact_number) }}">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" id="address" name="address" class="form-control" value="{{ old('address', auth()->user()->address) }}">
                                </div>
                            </div>
                        </div>

                        <h4 style="margin: 24px 0 12px;">Change Password</h4>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 16px;">Leave blank to keep your current password.</p>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password">
                                    @error('current_password') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password">
                                    @error('new_password') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="max-width: 50%;">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" autocomplete="new-password">
                            <div class="password-feedback" id="profile-password-feedback"></div>
                        </div>

                        <div style="margin-top: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-outline" id="reset-profile-picture-btn">
                                <i class="fas fa-undo"></i> Reset Profile Picture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    @if($isSuperAdmin)
    <!-- Add User Modal -->
    <div class="modal" id="add-user-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button type="button" class="modal-close" data-close="add-user-modal">&times;</button>
            </div>
            <form action="{{ route('settings.users.store') }}" method="post">
                @csrf
                <input type="hidden" name="form_type" value="add">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_username">Username</label>
                            <input type="text" id="new_username" name="username" class="form-control" value="{{ old('username') }}" required>
                            @error('username') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_name">Full Name</label>
                            <input type="text" id="new_name" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_email">Email</label>
                            <input type="email" id="new_email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_contact_number">Contact Number</label>
                            <input type="text" id="new_contact_number" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_address">Address</label>
                    <textarea id="new_address" name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_password">Password</label>
                            <input type="password" id="new_password" name="password" class="form-control" required>
                            @error('password') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm Password</label>
                            <input type="password" id="new_password_confirmation" name="password_confirmation" class="form-control" required>
                            <div class="password-feedback" id="add-user-password-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_role">Role</label>
                    <select id="new_role" name="role" class="form-control" required>
                        <option value="Store Room Assistant" {{ old('role') === 'Store Room Assistant' ? 'selected' : '' }}>Store Room Assistant</option>
                        <option value="Store Room Supervisor" {{ old('role') === 'Store Room Supervisor' ? 'selected' : '' }}>Store Room Supervisor</option>
                        <option value="Engineering Supervisor" {{ old('role') === 'Engineering Supervisor' ? 'selected' : '' }}>Engineering Supervisor</option>
                        <option value="Production Supervisor" {{ old('role') === 'Production Supervisor' ? 'selected' : '' }}>Production Supervisor</option>
                        <option value="HR Supervisor" {{ old('role') === 'HR Supervisor' ? 'selected' : '' }}>HR Supervisor</option>
                        <option value="Finance Supervisor" {{ old('role') === 'Finance Supervisor' ? 'selected' : '' }}>Finance Supervisor</option>
                        <option value="Taxation Supervisor" {{ old('role') === 'Taxation Supervisor' ? 'selected' : '' }}>Taxation Supervisor</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-close-btn" data-close="add-user-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="edit-user-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button type="button" class="modal-close" data-close="edit-user-modal">&times;</button>
            </div>
            <form action="{{ route('settings.users.update') }}" method="post">
                @csrf
                <input type="hidden" name="user_id" id="edit_user_id" value="{{ old('user_id') }}">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_username">Username</label>
                            <input type="text" id="edit_username" name="username" class="form-control" value="{{ old('username') }}" required>
                            @error('username') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_name">Full Name</label>
                            <input type="text" id="edit_name" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email') <span class="password-feedback mismatch">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_contact_number">Contact Number</label>
                            <input type="text" id="edit_contact_number" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_address">Address</label>
                    <textarea id="edit_address" name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" class="form-control" required>
                        <option value="Store Room Assistant" {{ old('role') === 'Store Room Assistant' ? 'selected' : '' }}>Store Room Assistant</option>
                        <option value="Store Room Supervisor" {{ old('role') === 'Store Room Supervisor' ? 'selected' : '' }}>Store Room Supervisor</option>
                        <option value="Engineering Supervisor" {{ old('role') === 'Engineering Supervisor' ? 'selected' : '' }}>Engineering Supervisor</option>
                        <option value="Production Supervisor" {{ old('role') === 'Production Supervisor' ? 'selected' : '' }}>Production Supervisor</option>
                        <option value="HR Supervisor" {{ old('role') === 'HR Supervisor' ? 'selected' : '' }}>HR Supervisor</option>
                        <option value="Finance Supervisor" {{ old('role') === 'Finance Supervisor' ? 'selected' : '' }}>Finance Supervisor</option>
                        <option value="Taxation Supervisor" {{ old('role') === 'Taxation Supervisor' ? 'selected' : '' }}>Taxation Supervisor</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-close-btn" data-close="edit-user-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal" id="delete-user-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delete User</h3>
                <button type="button" class="modal-close" data-close="delete-user-modal">&times;</button>
            </div>
            <form action="{{ route('settings.users.destroy') }}" method="post">
                @csrf
                <input type="hidden" name="user_id" id="delete_user_id">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-close-btn" data-close="delete-user-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
    @endif

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

            var hash = window.location.hash.replace('#', '');
            var tabLinks = document.querySelectorAll('.settings-tabs a');
            var panes = document.querySelectorAll('.settings-pane');
            if (hash && document.getElementById(hash)) {
                tabLinks.forEach(function(l) {
                    l.classList.toggle('active', l.getAttribute('data-tab') === hash);
                });
                panes.forEach(function(p) {
                    p.classList.toggle('active', p.id === hash);
                });
            }

            tabLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('data-tab');
                    tabLinks.forEach(function(l) { l.classList.remove('active'); });
                    panes.forEach(function(p) { p.classList.remove('active'); });
                    this.classList.add('active');
                    var pane = document.getElementById(tabId);
                    if (pane) pane.classList.add('active');
                });
            });

            document.querySelectorAll('.modal-close, .modal-close-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-close');
                    if (id) {
                        var el = document.getElementById(id);
                        if (el) el.classList.remove('show');
                    }
                });
            });
            document.querySelectorAll('.modal').forEach(function(modal) {
                modal.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
            });

            if (document.getElementById('add-user-btn')) {
                document.getElementById('add-user-btn').addEventListener('click', function() {
                    document.getElementById('add-user-modal').classList.add('show');
                });
            }

            var resetProfilePicBtn = document.getElementById('reset-profile-picture-btn');
            if (resetProfilePicBtn) {
                resetProfilePicBtn.addEventListener('click', function() {
                    var form = document.getElementById('profile-form');
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'reset_profile_picture';
                    input.value = '1';
                    form.appendChild(input);
                    form.submit();
                });
            }

            var addPw = document.getElementById('new_password');
            var addPwConf = document.getElementById('new_password_confirmation');
            var addFeedback = document.getElementById('add-user-password-feedback');
            if (addPw && addPwConf && addFeedback) {
                function checkAdd() {
                    if (addPw.value && addPwConf.value) {
                        if (addPw.value === addPwConf.value) {
                            addFeedback.textContent = 'Passwords match';
                            addFeedback.className = 'password-feedback match';
                        } else {
                            addFeedback.textContent = 'Passwords do not match';
                            addFeedback.className = 'password-feedback mismatch';
                        }
                    } else { addFeedback.textContent = ''; addFeedback.className = 'password-feedback'; }
                }
                addPw.addEventListener('input', checkAdd);
                addPwConf.addEventListener('input', checkAdd);
            }

            var profileNew = document.getElementById('new_password');
            var profileConf = document.getElementById('new_password_confirmation');
            var profileFeedback = document.getElementById('profile-password-feedback');
            if (profileNew && profileConf && profileFeedback) {
                function checkProfile() {
                    if (profileNew.value && profileConf.value) {
                        if (profileNew.value === profileConf.value) {
                            profileFeedback.textContent = 'Passwords match';
                            profileFeedback.className = 'password-feedback match';
                        } else {
                            profileFeedback.textContent = 'Passwords do not match';
                            profileFeedback.className = 'password-feedback mismatch';
                        }
                    } else { profileFeedback.textContent = ''; profileFeedback.className = 'password-feedback'; }
                }
                profileNew.addEventListener('input', checkProfile);
                profileConf.addEventListener('input', checkProfile);
            }

            if (document.getElementById('users-table') && typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#users-table').DataTable({ order: [[2, 'asc']], pageLength: 10 });
            }

            var alertEl = document.getElementById('settings-alert');
            if (alertEl) setTimeout(function() { alertEl.style.opacity = '0'; setTimeout(function() { alertEl.style.display = 'none'; }, 300); }, 4000);

            @if($errors->any())
                @if(old('user_id'))
                    document.getElementById('edit-user-modal').classList.add('show');
                @elseif(old('form_type') === 'add')
                    document.getElementById('add-user-modal').classList.add('show');
                @endif
            @endif
        });

        function editUser(id, username, name, email, contact_number, address, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_contact_number').value = contact_number || '';
            document.getElementById('edit_address').value = address || '';
            document.getElementById('edit_role').value = role || 'Store Room Assistant';
            document.getElementById('edit-user-modal').classList.add('show');
        }
        function deleteUser(id) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('delete-user-modal').classList.add('show');
        }
    </script>
</body>
</html>
