<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Only allow users whose role is in the given list, e.g. role:Admin,Cashier
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check() || ! in_array(Auth::user()->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
