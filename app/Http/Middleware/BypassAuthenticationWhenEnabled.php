<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BypassAuthenticationWhenEnabled
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (! (bool) config('auth.login_bypass.enabled')) {
            return $next($request);
        }

        if (
            $request->is('login') ||
            $request->is('login-wali-murid') ||
            $request->is('dashboard-wali-murid') ||
            $request->is('jadwal-materi-wali-murid') ||
            $request->is('perkembangan-wali-murid') ||
            $request->is('rapot-wali-murid') ||
            $request->is('logout-wali-murid') ||
            trim($request->path(), '/') === ''
        ) {
            return $next($request);
        }

        if (! Auth::check()) {
            $user = User::query()->firstOrCreate(
                ['email' => (string) config('auth.login_bypass.email')],
                [
                    'nama' => (string) config('auth.login_bypass.name'),
                    'kata_sandi' => bin2hex(random_bytes(16)),
                ],
            );

            Auth::onceUsingId($user->getAuthIdentifier());
        }

        return $next($request);
    }
}
