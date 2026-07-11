<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
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

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

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
            throw ValidationException::withMessages(['login' => 'The supplied credentials are incorrect.']);
        }

        if (! $user->is_active || ! $user->role?->is_active) {
            Auth::logout();
            $request->session()->regenerate();
            RateLimiter::hit($throttleKey, 60);
            $this->logAttempt($request, 'login.blocked', $user);
            throw ValidationException::withMessages(['login' => 'This account is inactive. Contact the Super Admin.']);
        }

        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);
        $user->loadMissing(['role.permissions', 'shops:id', 'godowns:id']);
        $user->update(['last_login_at' => now()]);

        $request->session()->put([
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'permitted_shop_ids' => $user->isSuperAdmin() ? ['*'] : $user->shops->modelKeys(),
            'permitted_godown_ids' => $user->isSuperAdmin() ? ['*'] : $user->godowns->modelKeys(),
            'permitted_modules' => $user->isSuperAdmin() ? ['*'] : $user->role->permissions->pluck('module')->unique()->values()->all(),
            'permitted_actions' => $user->isSuperAdmin() ? ['*'] : $user->role->permissions->pluck('code')->values()->all(),
            'login_timestamp' => now()->toIso8601String(),
        ]);

        $this->logAttempt($request, 'login.success', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->logAttempt($request, 'logout', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function logAttempt(Request $request, string $event, ?User $user, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);
    }
}
