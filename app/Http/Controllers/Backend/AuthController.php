<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Access\BusinessContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('backend.auth.login');
    }

    public function store(LoginRequest $request, BusinessContextService $contexts): RedirectResponse
    {
        $credentials = $request->validated();

        $throttleKey = Str::lower($credentials['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Too many login attempts. Try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (preg_match('/^[+]?[0-9][0-9 -]{6,}$/', $credentials['login']) ? 'mobile' : 'username');

        $user = User::where($field, $credentials['login'])->first();

        if (! $user || ! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            $this->logAttempt($request, 'login.failed', $user, ['identifier' => $credentials['login']]);
            $this->recordLoginHistory($request, 'login.failed', $user, $credentials['login']);
            throw ValidationException::withMessages(['login' => 'The supplied credentials are incorrect.']);
        }

        if (! $user->is_active || ! $user->role?->is_active) {
            Auth::logout();
            $request->session()->regenerate();
            RateLimiter::hit($throttleKey, 60);
            $this->logAttempt($request, 'login.blocked', $user);
            $this->recordLoginHistory($request, 'login.blocked', $user, $credentials['login']);
            throw ValidationException::withMessages(['login' => 'This account is inactive. Contact the Super Admin.']);
        }

        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);
        $user->loadMissing(['role.permissions', 'permissions', 'shops:id', 'godowns:id']);
        $user->update(['last_login_at' => now()]);
        $context = $contexts->synchronize($user);
        $permissionCodes = $user->isSuperAdmin() ? collect(['*']) : $user->effectivePermissionCodes();
        $history = $this->recordLoginHistory($request, 'login.success', $user, $credentials['login']);

        $request->session()->put([
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'permitted_shop_ids' => $user->isSuperAdmin() ? ['*'] : $user->shops->modelKeys(),
            'permitted_godown_ids' => $user->isSuperAdmin() ? ['*'] : $user->godowns->modelKeys(),
            'permitted_modules' => $user->isSuperAdmin()
                ? ['*']
                : $permissionCodes->map(fn (string $code) => str($code)->before('.')->toString())->unique()->values()->all(),
            'permitted_actions' => $permissionCodes->all(),
            'active_shop_id' => $context['shop_id'],
            'active_godown_id' => $context['godown_id'],
            'login_history_id' => $history->id,
            'login_timestamp' => now()->toIso8601String(),
        ]);

        $this->logAttempt($request, 'login.success', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->logAttempt($request, 'logout', $request->user());
        LoginHistory::query()
            ->whereKey($request->session()->get('login_history_id'))
            ->whereNull('logged_out_at')
            ->update(['event' => 'logout', 'logged_out_at' => now()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.index');
    }

    private function logAttempt(Request $request, string $event, ?User $user, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'module' => 'authentication',
            'action' => str($event)->after('.')->toString(),
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);
    }

    private function recordLoginHistory(Request $request, string $event, ?User $user, string $identifier): LoginHistory
    {
        return LoginHistory::create([
            'user_id' => $user?->id,
            'identifier' => $identifier,
            'event' => $event,
            'session_id' => $event === 'login.success' ? $request->session()->getId() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => $event === 'login.success' ? now() : null,
        ]);
    }
}
