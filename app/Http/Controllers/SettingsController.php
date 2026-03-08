<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'Store Room Supervisor';
        $users = $isSuperAdmin ? User::query()->orderBy('name')->get() : collect();

        return view('settings.index', [
            'users' => $users,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $creatorName = Auth::user()->name ?: Auth::user()->email;

        User::query()->create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'contact_number' => $data['contact_number'] ?? null,
            'address' => $data['address'] ?? null,
            'role' => $data['role'],
            'created_by' => $creatorName,
        ]);

        return redirect()->route('settings.index')
            ->with('message', 'User added successfully!')
            ->with('messageType', 'success')
            ->withFragment('user-management');
    }

    public function updateUser(UpdateUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::query()->findOrFail($data['user_id']);

        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->contact_number = $data['contact_number'] ?? null;
        $user->address = $data['address'] ?? null;
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('settings.index')
            ->with('message', 'User updated successfully!')
            ->with('messageType', 'success')
            ->withFragment('user-management');
    }

    public function destroyUser(Request $request): RedirectResponse
    {
        if (Auth::user()->role !== 'Store Room Supervisor') {
            abort(403);
        }

        $request->validate(['user_id' => ['required', 'exists:users,id']]);
        $userId = $request->input('user_id');

        if ((int) $userId === Auth::id()) {
            return redirect()->route('settings.index')
                ->with('message', 'You cannot delete your own account.')
                ->with('messageType', 'danger')
                ->withFragment('user-management');
        }

        User::query()->where('id', $userId)->delete();

        return redirect()->route('settings.index')
            ->with('message', 'User deleted successfully!')
            ->with('messageType', 'success')
            ->withFragment('user-management');
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->contact_number = $data['contact_number'] ?? null;
        $user->address = $data['address'] ?? null;

        if (! empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        if ($request->boolean('reset_profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = null;
        } elseif ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_picture')->store('profiles', 'public');
        }

        $user->save();

        return redirect()->route('settings.index')
            ->with('message', 'Profile updated successfully!')
            ->with('messageType', 'success')
            ->withFragment('profile-settings');
    }
}
