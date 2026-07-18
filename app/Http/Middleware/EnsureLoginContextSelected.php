<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoginContextSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('admin.logout')) {
            return $next($request);
        }

        if (! (bool) $request->session()->get('auth_location_selection_required', false)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return ResponseHelper::error(
                'Choose your working location before continuing.',
                [],
                409,
                'LOGIN_CONTEXT_REQUIRED',
            );
        }

        return redirect()->route('admin.auth.context.index');
    }
}
