<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\DatabaseConnectionState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        try {
            if (Auth::check()) {
                return redirect()->route('dashboard');
            }
        } catch (Throwable $exception) {
            if (DatabaseConnectionState::isUnavailable($exception)) {
                report($exception);
                $this->resetLoginSession($request);

                return view('auth.login')->withErrors([
                    'email' => DatabaseConnectionState::loginHelpMessage(),
                ]);
            }

            throw $exception;
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->isLoginBypassEnabled()) {
            $bypassRole = UserRole::tryFrom((string) config('auth.login_bypass.role', UserRole::Admin->value)) ?? UserRole::Admin;

            try {
                $user = User::query()->firstOrNew([
                    'email' => (string) config('auth.login_bypass.email'),
                ]);
                $isNewUser = ! $user->exists;

                if ($isNewUser) {
                    $user->forceFill([
                        'name' => (string) config('auth.login_bypass.name'),
                        'password' => bin2hex(random_bytes(16)),
                    ]);
                }

                $user->role = $bypassRole;
                $user->save();
            } catch (Throwable $exception) {
                if (DatabaseConnectionState::isUnavailable($exception)) {
                    report($exception);
                    $this->resetLoginSession($request);

                    return back()
                        ->withErrors([
                            'email' => DatabaseConnectionState::loginHelpMessage(),
                        ])
                        ->onlyInput('email');
                }

                throw $exception;
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Login sementara aktif. Anda langsung masuk ke dashboard.');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        try {
            $authenticated = Auth::attempt($credentials, $request->boolean('remember'));
        } catch (Throwable $exception) {
            if (DatabaseConnectionState::isUnavailable($exception)) {
                report($exception);
                $this->resetLoginSession($request);

                return back()
                    ->withErrors([
                        'email' => DatabaseConnectionState::loginHelpMessage(),
                    ])
                    ->onlyInput('email');
            }

            throw $exception;
        }

        if (! $authenticated) {
            return back()
                ->withErrors([
                    'email' => 'Email atau kata sandi tidak sesuai.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Login berhasil. Selamat datang kembali.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah keluar dari aplikasi.');
    }

    private function resetLoginSession(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function isLoginBypassEnabled(): bool
    {
        return (bool) config('auth.login_bypass.enabled');
    }
}
