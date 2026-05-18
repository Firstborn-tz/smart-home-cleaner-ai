<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CleanerVerifiedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $cleaner = $request->user()?->cleaner;

        if (!$cleaner || !$cleaner->is_verified) {
            return redirect()->route('cleaner.dashboard')
                ->with('error', 'Your account is pending verification.');
        }

        return $next($request);
    }
}