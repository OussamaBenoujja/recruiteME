<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $roles = is_array($role) ? $role : explode('|', $role);

        if (!in_array(Auth::user()->role, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}