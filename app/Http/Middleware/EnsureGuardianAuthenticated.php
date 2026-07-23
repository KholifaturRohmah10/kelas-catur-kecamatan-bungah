<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Support\GuardianSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuardianAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = GuardianSession::studentId($request);

        if ($studentId === null) {
            return redirect()->route('guardian.login')
                ->withErrors([
                    'username' => 'Silakan masuk sebagai wali murid terlebih dahulu.',
                ]);
        }

        $student = Student::query()
            ->with(['scores.classSession' => fn ($query) => $query->orderBy('tanggal')])
            ->find($studentId);

        if (! $student) {
            GuardianSession::logout($request);

            return redirect()->route('guardian.login')
                ->withErrors([
                    'username' => 'Data siswa untuk akun wali murid ini tidak ditemukan.',
                ]);
        }

        $request->attributes->set('guardianStudent', $student);
        View::share('guardianStudent', $student);

        return $next($request);
    }
}
