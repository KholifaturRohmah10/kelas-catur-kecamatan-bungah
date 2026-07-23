<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\GuardianSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuardianAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (GuardianSession::has($request)) {
            return redirect()->route('guardian.dashboard');
        }

        return view('auth.guardian-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_siswa' => ['required', 'string', 'max:255'],
        ], [
            'kode_siswa.required' => 'Kode siswa wajib diisi.',
        ]);

        $studentCode = Str::upper(trim($validated['kode_siswa']));

        $matchingStudents = Student::query()
            ->whereRaw('UPPER(kode_siswa) = ?', [$studentCode])
            ->orderByDesc('tanggal_daftar')
            ->get();

        if ($matchingStudents->isEmpty()) {
            return back()
                ->withErrors([
                    'kode_siswa' => 'Kode siswa tidak ditemukan. Pastikan Anda memasukkan kode yang benar.',
                ])
                ->onlyInput('kode_siswa');
        }

        if ($matchingStudents->count() > 1) {
            return back()
                ->withErrors([
                    'kode_siswa' => 'Kode siswa ganda ditemukan. Silakan hubungi admin.',
                ])
                ->onlyInput('kode_siswa');
        }

        Auth::logout();
        GuardianSession::logout($request);
        $request->session()->regenerate();
        GuardianSession::login($request, $matchingStudents->first());

        return redirect()->route('guardian.dashboard')
            ->with('success', 'Login wali murid berhasil.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        GuardianSession::logout($request);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('guardian.login')
            ->with('success', 'Anda telah keluar dari halaman wali murid.');
    }
}
