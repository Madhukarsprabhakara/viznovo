<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class EnsureEmailIsVerifiedForInstall extends EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (config('app.local_install')) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}