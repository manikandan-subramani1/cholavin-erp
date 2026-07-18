<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Access\BusinessContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View
    {
        if (config('erp_auth.captcha.enabled')) {
            $left = random_int(1, 9);
            $right = random_int(1, 9);
            $request->session()->put('auth_captcha_answer', $left + $right);
            $request->session()->put('auth_captcha_question', $left.' + '.$right);
        } else {
            $request->session()->forget(['auth_captcha_answer', 'auth_captcha_question']);
        }

        return view('backend.auth.login');
    }

    public function store(LoginRequest $request, BusinessContextService $contexts): JsonResponse|RedirectResponse
    {
        $credentials = $request->validated();

        $identifier = trim($credentials['login']);
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (preg_match('/^[+]?[0-9][0-9 -]{6,}$/', $identifier) ? 'mobile' : 'username');
        $user = User::with('role')->where($field, $identifier)->first();
        $throttleKey = Str::lower($identifier).'|'.$request->ip();
        $maxAttempts = max(1, (int) config('erp_auth.throttle.max_attempts', 5));
        $decaySeconds = max(30, (int) config('erp_auth.throttle.decay_seconds', 60));

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($throttleKey);
            $this->logAttempt($request, 'login.throttled', $user, ['retry_after' => $retryAfter]);
            $this->recordLoginHistory($request, 'login.throttled', $user, $identifier);

            if ($request->expectsJson()) {
                return ResponseHelper::error(
                    'Too many login attempts. Try again in '.$retryAfter.' seconds.',
                    ['login' => ['Login is temporarily locked.']],
                    429,
                    'LOGIN_THROTTLED',
                );
            }

            return redirect()->route('admin.auth.locked')->with('retry_after', $retryAfter);
        }

        if (! $user || ! Auth::attempt([$field => $identifier, 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $this->logAttempt($request, 'login.failed', $user, ['identifier' => $identifier]);
            $this->recordLoginHistory($request, 'login.failed', $user, $identifier);
            throw ValidationException::withMessages(['login' => 'The supplied credentials are incorrect.']);
        }

        if (! $user->is_active || ! $user->role?->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($throttleKey, $decaySeconds);
            $this->logAttempt($request, 'login.blocked', $user);
            $this->recordLoginHistory($request, 'login.blocked', $user, $identifier);
            throw ValidationException::withMessages(['login' => 'This account is inactive. Contact the Super Admin.']);
        }

        $request->session()->regenerate();
        $request->session()->forget(['auth_captcha_answer', 'auth_captcha_question']);
        RateLimiter::clear($throttleKey);
        $user->loadMissing(['role.permissions', 'permissions', 'shops:id', 'godowns:id']);
        $user->update(['last_login_at' => now()]);
        $context = $contexts->synchronize($user);
        $permissionCodes = $user->isSuperAdmin() ? collect(['*']) : $user->effectivePermissionCodes();
        $history = $this->recordLoginHistory($request, 'login.success', $user, $identifier);
        $allShops = $contexts->permittedShops($user);
        $allGodowns = $contexts->permittedGodowns($user);
        $allFinancialYears = $contexts->permittedFinancialYears($user);
        $requiresLocationSelection = ! $user->isSuperAdmin() && (
            $allShops->count() !== 1
            || $allGodowns->count() !== 1
            || $allFinancialYears->count() !== 1
        );

        $request->session()->put([
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'permitted_shop_ids' => $user->isSuperAdmin() ? ['*'] : $user->shops->modelKeys(),
            'permitted_godown_ids' => $user->isSuperAdmin() ? ['*'] : $user->godowns->modelKeys(),
            'permitted_financial_year_ids' => $user->isSuperAdmin() ? ['*'] : $allFinancialYears->modelKeys(),
            'permitted_modules' => $user->isSuperAdmin()
                ? ['*']
                : $permissionCodes->map(fn (string $code) => str($code)->before('.')->toString())->unique()->values()->all(),
            'permitted_actions' => $permissionCodes->all(),
            'active_shop_id' => $context['shop_id'],
            'active_godown_id' => $context['godown_id'],
            'active_financial_year_id' => $context['financial_year_id'],
            'login_history_id' => $history->id,
            'login_timestamp' => now()->toIso8601String(),
            'auth_location_selection_required' => $requiresLocationSelection,
        ]);

        $this->logAttempt($request, 'login.success', $user);

        $redirect = $requiresLocationSelection
            ? route('admin.auth.context.index')
            : route('admin.dashboard');

        if ($request->expectsJson()) {
            return ResponseHelper::success('Login successful.', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role?->name,
                    'is_super_admin' => $user->isSuperAdmin(),
                ],
                'permissions' => $permissionCodes->values(),
                'shops' => $context['shops']->map->only(['id', 'name', 'code'])->values(),
                'godowns' => $context['godowns']->map->only(['id', 'name', 'code'])->values(),
                'financial_years' => $context['financial_years']->map->only(['id', 'name', 'code'])->values(),
                'default_context' => [
                    'shop_id' => $context['shop_id'],
                    'godown_id' => $context['godown_id'],
                    'financial_year_id' => $context['financial_year_id'],
                ],
                'requires_location_selection' => $requiresLocationSelection,
                'redirect' => $redirect,
            ]);
        }

        return $requiresLocationSelection
            ? redirect()->route('admin.auth.context.index')
            : redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $this->logAttempt($request, 'logout', $request->user());
        LoginHistory::query()
            ->whereKey($request->session()->get('login_history_id'))
            ->whereNull('logged_out_at')
            ->update(['event' => 'logout', 'logged_out_at' => now()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return ResponseHelper::success('Logged out successfully.', [
                'redirect' => route('admin.auth.index'),
            ]);
        }

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
