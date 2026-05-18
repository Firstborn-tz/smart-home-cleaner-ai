<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CityEnabledMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Basic city check - can be enhanced later
        return $next($request);
    }
}