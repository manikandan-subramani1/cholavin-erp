<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('backend.auth.forgot-password');
    }

    public function email(ForgotPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = Password::sendResetLink($request->validated());

        if (in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            if ($request->expectsJson()) {
                return ResponseHelper::success('If that email is registered, a password reset link has been sent.');
            }

            return back()->with('status', 'If that email is registered, a password reset link has been sent.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function reset(string $token): View
    {
        return view('backend.auth.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function update(ResetPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $redirect = route('admin.auth.index');

            return $request->expectsJson()
                ? ResponseHelper::success('Your password has been reset.', ['redirect' => $redirect])
                : redirect()->route('admin.auth.index')->with('status', __($status));
        }

        return $request->expectsJson()
            ? ResponseHelper::error(__($status), ['email' => [__($status)]])
            : back()->withErrors(['email' => __($status)]);
    }
}
