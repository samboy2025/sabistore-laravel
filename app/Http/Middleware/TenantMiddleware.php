<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TenantMiddleware
 *
 * Handles identifying and setting the current tenant (shop) based on the request's subdomain.
 *
 * @package App\Http\Middleware
 */
class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware inspects the host for a subdomain. If a valid, active shop corresponds
     * to the subdomain, it sets that shop as a global instance ('current_shop') and adds it
     * to the request attributes for easy access within the application.
     *
     * @param  \Illuminate\Http\Request  $request The incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next The next middleware in the stack.
     * @return \Symfony\Component\HttpFoundation\Response
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
     * Extract the subdomain from the given host.
     *
     * @param string $host The full host from the request (e.g., "my-shop.sabistore.com").
     * @return string|null The extracted subdomain (e.g., "my-shop") or null if not found.
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
