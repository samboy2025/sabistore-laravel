<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = $this->getSubdomain($host);

        if ($subdomain && $subdomain !== 'www') {
            $shop = Shop::where('slug', $subdomain)
                ->where('is_active', true)
                ->first();

            if (!$shop) {
                abort(404, 'Shop not found');
            }

            // Set the shop in the request for easy access
            $request->attributes->set('tenant_shop', $shop);
            
            // Set the shop globally for all queries
            app()->instance('current_shop', $shop);
        }

        return $next($request);
    }

    /**
     * Extract subdomain from host
     */
    private function getSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);
        
        // If we have more than 2 parts (e.g., shop.domain.com)
        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}
