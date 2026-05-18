<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserTypeMiddleware
{
    public function handle(Request $request, Closure $next, string ...$types)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!in_array($request->user()->user_type, $types)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}