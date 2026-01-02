<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureClaimMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $serverKey = config('app.rsps_server_key') ?: env('RSPS_SERVER_KEY');
        
        if (!$serverKey) {
            return response()->json([
                'success' => false,
                'error' => 'Server key not configured'
            ], 500);
        }

        $providedKey = $request->header('ARAGON-AUTH');

        if ($providedKey !== $serverKey) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
