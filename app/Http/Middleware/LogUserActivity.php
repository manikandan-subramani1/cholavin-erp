<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && $request->route()?->getName() && ! str_starts_with($request->route()->getName(), 'admin.activity-logs')) {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'event' => $request->isMethodSafe() ? 'page.viewed' : 'record.changed',
                'method' => $request->method(),
                'route' => $request->route()->getName(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => ['status' => $response->getStatusCode()],
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
