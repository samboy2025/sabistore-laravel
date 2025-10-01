<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleMiddleware
 *
 * Middleware to verify that the authenticated user has a specific role.
 *
 * @package App\Http\Middleware
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user's role matches the required role specified in the route definition.
     * If the roles do not match, it aborts the request with a 403 Forbidden error.
     *
     * @param  \Illuminate\Http\Request  $request The incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next The next middleware in the stack.
     * @param  string $role The required role (e.g., 'admin', 'vendor', 'buyer').
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
