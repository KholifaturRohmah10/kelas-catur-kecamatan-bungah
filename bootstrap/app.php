<?php

use App\Http\Middleware\BypassAuthenticationWhenEnabled;
use App\Http\Middleware\EnsureGuardianAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            BypassAuthenticationWhenEnabled::class,
        ]);
        $middleware->alias([
            'guardian' => EnsureGuardianAuthenticated::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            $message = 'Sesi halaman sudah kedaluwarsa atau cookie login tidak terbaca. Muat ulang halaman login lalu coba lagi. Jika membuka aplikasi dari Mobile View/preview VS Code, coba browser biasa.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 419);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $message,
                ])
                ->onlyInput('email');
        });
    })->create();
