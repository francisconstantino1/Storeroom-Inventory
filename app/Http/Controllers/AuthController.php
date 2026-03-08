<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\LoginLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $log = LoginLog::query()->create([
            'user_id' => $user->id,
            'username' => $user->username ?? $user->email,
            'status' => 'Success',
        ]);
        $request->session()->put('login_log_id', $log->id);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(\Illuminate\Http\Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $logId = $request->session()->get('login_log_id');
            if ($logId) {
                LoginLog::query()->where('id', $logId)->where('user_id', $user->id)->update(['logout_time' => now()]);
            } else {
                LoginLog::query()
                    ->where('user_id', $user->id)
                    ->whereNull('logout_time')
                    ->orderByDesc('id')
                    ->limit(1)
                    ->update(['logout_time' => now()]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
