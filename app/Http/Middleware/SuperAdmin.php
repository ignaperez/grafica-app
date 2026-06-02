<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check() || !Auth::user()->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acceso denegado'], 403);
            }
            return redirect()->route('super-admin.login');
        }

        return $next($request);
    }
}
