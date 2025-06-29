<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserLogin;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class TrackUserLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track for authenticated users
        if ($request->user()) {
            $this->trackLogin($request);
        }

        return $response;
    }

    /**
     * Track user login information
     */
    private function trackLogin(Request $request)
    {
        $user = $request->user();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Check if we already have a recent login record for this session
        $existingLogin = UserLogin::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('user_agent', $userAgent)
            ->where('login_at', '>=', now()->subHours(1)) // Within last hour
            ->first();

        if ($existingLogin) {
            return; // Don't create duplicate records
        }

        // Parse user agent
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        // Get location data
        $locationData = $this->getLocationData($ipAddress);

        // Detect suspicious activity
        $isSuspicious = $this->detectSuspiciousActivity($user, $ipAddress, $locationData);

        // Create login record
        UserLogin::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $this->getDeviceType($agent),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'country' => $locationData['country'] ?? null,
            'city' => $locationData['city'] ?? null,
            'region' => $locationData['region'] ?? null,
            'latitude' => $locationData['latitude'] ?? null,
            'longitude' => $locationData['longitude'] ?? null,
            'timezone' => $locationData['timezone'] ?? null,
            'is_mobile' => $agent->isMobile(),
            'is_suspicious' => $isSuspicious,
            'login_at' => now(),
        ]);
    }

    /**
     * Get device type from agent
     */
    private function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isDesktop()) {
            return 'desktop';
        } else {
            return 'unknown';
        }
    }

    /**
     * Get location data from IP address
     */
    private function getLocationData(string $ipAddress): array
    {
        // Skip for local/private IPs
        if ($this->isPrivateIP($ipAddress)) {
            return [];
        }

        try {
            // Using a free IP geolocation service (you can replace with your preferred service)
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::warning('Failed to get location data for IP: ' . $ipAddress, ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIP(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Detect suspicious login activity
     */
    private function detectSuspiciousActivity($user, string $ipAddress, array $locationData): bool
    {
        $suspicious = false;

        // Check for new country
        if (isset($locationData['country'])) {
            $hasLoggedFromCountry = UserLogin::where('user_id', $user->id)
                ->where('country', $locationData['country'])
                ->exists();

            if (!$hasLoggedFromCountry) {
                $suspicious = true;
            }
        }

        // Check for multiple rapid logins from different IPs
        $recentLogins = UserLogin::where('user_id', $user->id)
            ->where('login_at', '>=', now()->subHours(1))
            ->distinct('ip_address')
            ->count();

        if ($recentLogins > 3) {
            $suspicious = true;
        }

        // Check for login from known suspicious IP ranges (you can implement your own logic)
        // This is a placeholder - you might want to maintain a blacklist of IP ranges
        
        return $suspicious;
    }
}
