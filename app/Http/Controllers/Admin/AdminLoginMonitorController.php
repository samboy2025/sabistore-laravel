<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AdminLoginMonitorController extends Controller
{
    /**
     * Display login monitoring dashboard
     */
    public function index(Request $request): View
    {
        $query = UserLogin::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Filter by suspicious activity
        if ($request->filled('suspicious')) {
            $query->where('is_suspicious', $request->suspicious === 'yes');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        // Search by IP or user agent
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ip_address', 'like', '%' . $request->search . '%')
                  ->orWhere('user_agent', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $logins = $query->latest('login_at')->paginate(20);

        // Get filter options
        $users = User::select('id', 'name', 'email')->get();
        $countries = UserLogin::distinct()->whereNotNull('country')->pluck('country')->sort();
        $deviceTypes = UserLogin::distinct()->whereNotNull('device_type')->pluck('device_type')->sort();

        // Statistics
        $stats = [
            'total_logins' => UserLogin::count(),
            'unique_users' => UserLogin::distinct('user_id')->count(),
            'suspicious_logins' => UserLogin::where('is_suspicious', true)->count(),
            'mobile_logins' => UserLogin::where('is_mobile', true)->count(),
            'today_logins' => UserLogin::whereDate('login_at', today())->count(),
            'unique_countries' => UserLogin::distinct('country')->whereNotNull('country')->count(),
        ];

        return view('admin.login-monitor.index', compact('logins', 'users', 'countries', 'deviceTypes', 'stats'));
    }

    /**
     * Show detailed login information for a user
     */
    public function show(User $user): View
    {
        $logins = UserLogin::where('user_id', $user->id)
            ->latest('login_at')
            ->paginate(20);

        // User login statistics
        $stats = [
            'total_logins' => $logins->total(),
            'unique_ips' => UserLogin::where('user_id', $user->id)->distinct('ip_address')->count(),
            'unique_countries' => UserLogin::where('user_id', $user->id)->distinct('country')->whereNotNull('country')->count(),
            'suspicious_logins' => UserLogin::where('user_id', $user->id)->where('is_suspicious', true)->count(),
            'mobile_logins' => UserLogin::where('user_id', $user->id)->where('is_mobile', true)->count(),
            'last_login' => UserLogin::where('user_id', $user->id)->latest('login_at')->first()?->login_at,
        ];

        // Recent locations
        $recentLocations = UserLogin::where('user_id', $user->id)
            ->whereNotNull('country')
            ->select('country', 'city', 'ip_address')
            ->distinct()
            ->latest('login_at')
            ->take(10)
            ->get();

        return view('admin.login-monitor.show', compact('user', 'logins', 'stats', 'recentLocations'));
    }

    /**
     * Mark login as suspicious
     */
    public function markSuspicious(UserLogin $login): JsonResponse
    {
        $login->markAsSuspicious();

        return response()->json([
            'success' => true,
            'message' => 'Login marked as suspicious successfully.'
        ]);
    }

    /**
     * Remove suspicious flag from login
     */
    public function removeSuspicious(UserLogin $login): JsonResponse
    {
        $login->update(['is_suspicious' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Suspicious flag removed successfully.'
        ]);
    }

    /**
     * Get login analytics data
     */
    public function analytics(): JsonResponse
    {
        // Login trends (last 30 days)
        $loginTrends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = UserLogin::whereDate('login_at', $date)->count();
            $loginTrends[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }

        // Top countries
        $topCountries = UserLogin::whereNotNull('country')
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        // Device type distribution
        $deviceTypes = UserLogin::whereNotNull('device_type')
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        // Browser distribution
        $browsers = UserLogin::whereNotNull('browser')
            ->selectRaw('browser, COUNT(*) as count')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        // Hourly login distribution
        $hourlyLogins = UserLogin::selectRaw('HOUR(login_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        // Fill missing hours with 0
        for ($i = 0; $i < 24; $i++) {
            if (!isset($hourlyLogins[$i])) {
                $hourlyLogins[$i] = 0;
            }
        }
        ksort($hourlyLogins);

        return response()->json([
            'login_trends' => $loginTrends,
            'top_countries' => $topCountries,
            'device_types' => $deviceTypes,
            'browsers' => $browsers,
            'hourly_logins' => array_values($hourlyLogins),
        ]);
    }

    /**
     * Export login data
     */
    public function export(Request $request)
    {
        $query = UserLogin::with('user');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }
        if ($request->filled('suspicious')) {
            $query->where('is_suspicious', $request->suspicious === 'yes');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        $logins = $query->latest('login_at')->get();

        $filename = 'login_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logins) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User Name', 'User Email', 'IP Address', 'Country', 'City',
                'Device Type', 'Browser', 'Platform', 'Is Mobile', 'Is Suspicious',
                'Login At', 'Logout At', 'Session Duration (minutes)'
            ]);

            // CSV data
            foreach ($logins as $login) {
                fputcsv($file, [
                    $login->id,
                    $login->user->name,
                    $login->user->email,
                    $login->ip_address,
                    $login->country,
                    $login->city,
                    $login->device_type,
                    $login->browser,
                    $login->platform,
                    $login->is_mobile ? 'Yes' : 'No',
                    $login->is_suspicious ? 'Yes' : 'No',
                    $login->login_at->format('Y-m-d H:i:s'),
                    $login->logout_at?->format('Y-m-d H:i:s'),
                    $login->session_duration,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
