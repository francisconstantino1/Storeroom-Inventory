<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginLogsController extends Controller
{
    public function __invoke(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->role !== 'Store Room Supervisor') {
            abort(403, 'Only Store Room Supervisors can view login logs.');
        }

        $logs = LoginLog::query()->orderByDesc('created_at')->limit(500)->get();

        $totalAttempts = LoginLog::query()->count();
        $successfulLogins = LoginLog::query()->where('status', 'Success')->count();
        $failedLogins = LoginLog::query()->where('status', 'Failed')->count();
        $uniqueUsers = LoginLog::query()->pluck('username')->unique()->count();
        $successRate = $totalAttempts > 0
            ? round(($successfulLogins / $totalAttempts) * 100, 2)
            : 0;

        return view('login-logs.index', [
            'logs' => $logs,
            'totalAttempts' => $totalAttempts,
            'successfulLogins' => $successfulLogins,
            'failedLogins' => $failedLogins,
            'uniqueUsers' => $uniqueUsers,
            'successRate' => $successRate,
        ]);
    }
}
