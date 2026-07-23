<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuardianAuthController;
use App\Http\Controllers\GuardianDashboardController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Support\DatabaseConnectionState;
use App\Support\GuardianSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    try {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        if (GuardianSession::has($request)) {
            return redirect()->route('guardian.dashboard');
        }

        return redirect()->route('login');
    } catch (Throwable $exception) {
        if (DatabaseConnectionState::isUnavailable($exception)) {
            report($exception);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => DatabaseConnectionState::loginHelpMessage(),
            ]);
        }

        throw $exception;
    }
});

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');

Route::get('/login-wali-murid', [GuardianAuthController::class, 'create'])->name('guardian.login');
Route::post('/login-wali-murid', [GuardianAuthController::class, 'store'])->name('guardian.login.attempt');
Route::get('/materi-kelas/{classSession}/file', [ClassSessionController::class, 'materialFile'])->name('sessions.material-file');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('daftar-kelas-catur')->group(function (): void {
        Route::get('/', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::post('/', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::get('/{student}/edit', [RegistrationController::class, 'edit'])->name('registrations.edit');
        Route::put('/{student}', [RegistrationController::class, 'update'])->name('registrations.update');
        Route::delete('/{student}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
    });

    Route::prefix('data-siswa')->group(function (): void {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    });

    Route::get('/jadwal-kelas', [ClassSessionController::class, 'index'])->name('sessions.index');
    Route::post('/jadwal-kelas', [ClassSessionController::class, 'store'])->name('sessions.store');
    Route::prefix('riwayat-kelas')->group(function (): void {
        Route::get('/', [ClassSessionController::class, 'history'])->name('session-history.index');
        Route::get('/{classSession}/edit', [ClassSessionController::class, 'edit'])->name('session-history.edit');
        Route::put('/{classSession}', [ClassSessionController::class, 'update'])->name('session-history.update');
        Route::delete('/{classSession}', [ClassSessionController::class, 'destroy'])->name('session-history.destroy');
    });

    Route::get('/perkembangan-siswa', [ProgressController::class, 'index'])->name('progress.index');

    Route::get('/cetak-rapot', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/cetak-rapot/semester-config', [ReportController::class, 'saveSemesterConfig'])->name('reports.save-semester-config');
    Route::get('/cetak-rapot/seluruh-nilai', [ReportController::class, 'printAll'])->name('reports.print-all');
    Route::get('/cetak-rapot/{student}', [ReportController::class, 'show'])->name('reports.show');
});

Route::middleware('guardian')->group(function (): void {
    Route::post('/logout-wali-murid', [GuardianAuthController::class, 'destroy'])->name('guardian.logout');
    Route::get('/dashboard-wali-murid', [GuardianDashboardController::class, 'index'])->name('guardian.dashboard');
    Route::get('/jadwal-materi-wali-murid', [GuardianDashboardController::class, 'materials'])->name('guardian.materials');
    Route::get('/perkembangan-wali-murid', [GuardianDashboardController::class, 'progress'])->name('guardian.progress');
    Route::get('/rapot-wali-murid', [GuardianDashboardController::class, 'report'])->name('guardian.report');
    Route::get('/rapot-wali-murid/cetak', [GuardianDashboardController::class, 'print'])->name('guardian.report.print');
});

Route::get('/run-migrations-12345', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migrations completed: ' . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});
