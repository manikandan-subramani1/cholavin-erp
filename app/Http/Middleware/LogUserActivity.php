<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $routeName = $request->route()?->getName();

        if ($request->user() && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('id', $request->session()->getId())
                ->update([
                    'active_shop_id' => $request->session()->get('active_shop_id'),
                    'active_godown_id' => $request->session()->get('active_godown_id'),
                    'active_financial_year_id' => $request->session()->get('active_financial_year_id'),
                ]);
        }

        if ($request->user()
            && $routeName
            && ! str_starts_with($routeName, 'admin.activity-logs')
            && ! str_starts_with($routeName, 'admin.location-context')) {
            [$module, $action] = $this->routeParts($routeName);
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'event' => $request->isMethodSafe() ? 'page.viewed' : 'record.changed',
                'module' => $module,
                'action' => $action,
                'shop_id' => $request->session()->get('active_shop_id'),
                'godown_id' => $request->session()->get('active_godown_id'),
                'financial_year_id' => $request->session()->get('active_financial_year_id'),
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

    private function routeParts(string $routeName): array
    {
        $parts = collect(explode('.', str($routeName)->after('admin.')->toString()));

        return [$parts->first(), $parts->last()];
    }
}
