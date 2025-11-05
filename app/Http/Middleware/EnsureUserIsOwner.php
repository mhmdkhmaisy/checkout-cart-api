<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureUserIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user instanceof User || !$user->isOwner()) {
            abort(403, 'Access denied. Owner privileges required.');
        }

        return $next($request);
    }
}
