<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $serverKey = config('app.rsps_server_key');
        
        if (!$serverKey) {
            return response()->json(['error' => 'Server authentication not configured'], 500);
        }

        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Missing or invalid authorization header'], 401);
        }

        $token = substr($authHeader, 7);
        
        if (!hash_equals($serverKey, $token)) {
            return response()->json(['error' => 'Invalid server key'], 401);
        }

        return $next($request);
    }
}