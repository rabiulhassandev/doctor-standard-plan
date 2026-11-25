<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('admin.auth', ['mode' => 'login']);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = 'owner-login:'.Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Too many attempts. Please try again in one minute.']);
        }
        RateLimiter::hit($key, 60);
        if (! Auth::attempt([...$data, 'is_admin' => true])) {
            throw ValidationException::withMessages(['email' => 'The sign-in details are incorrect.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgot(): View
    {
        return view('admin.auth', ['mode' => 'forgot']);
    }

    public function emailReset(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink([...$data, 'is_admin' => true]);

        return back()->with('status', 'If an owner account uses this email, a recovery link has been sent.');
    }

    public function reset(Request $request, string $token): View
    {
        return view('admin.auth', ['mode' => 'reset', 'token' => $token, 'email' => $request->string('email')->toString()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()],
        ]);
        $status = Password::reset([...$data, 'is_admin' => true], function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Password updated. Sign in with your new password.');
    }
}
