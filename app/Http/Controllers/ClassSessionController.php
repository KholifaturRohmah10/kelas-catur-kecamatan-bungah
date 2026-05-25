<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentScore;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassSessionController extends Controller
{
    public function index(): View
    {
        $students = $this->students();

        return view('sessions.index', compact('students'));
    }

    public function history(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $selectedMonth = $validated['month'] ?? now()->format('Y-m');
        $selectedMonthLabel = ucfirst(Carbon::createFromFormat('Y-m', $selectedMonth)->locale('id')->translatedFormat('F Y'));
        $sessions = $this->sessions($selectedMonth);

        return view('sessions.history', compact('sessions', 'selectedMonth', 'selectedMonthLabel'));
    }

    public function edit(ClassSession $classSession): View
    {
        $students = $this->students();
        $classSession->load(['scores.student']);

        $selectedStudents = $classSession->scores->pluck('student_id')->all();
        $scoreValues = $classSession->scores->pluck('score', 'student_id')->all();

        return view('sessions.edit', compact('classSession', 'students', 'selectedStudents', 'scoreValues'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedSession($request);
        $this->validateSelectedScores($validated);

        DB::transaction(function () use ($validated): void {
            $session = ClassSession::create([
                'title' => $validated['title'],
                'material' => $validated['material'],
                'session_date' => $validated['session_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncScores($session, $validated);
        });

        return redirect()
            ->route('sessions.index')
            ->with('success', 'Jadwal kelas dan nilai siswa berhasil disimpan.');
    }

    public function update(Request $request, ClassSession $classSession): RedirectResponse
    {
        $validated = $this->validatedSession($request);
        $this->validateSelectedScores($validated, $classSession);

        DB::transaction(function () use ($validated, $classSession): void {
            $classSession->update([
                'title' => $validated['title'],
                'material' => $validated['material'],
                'session_date' => $validated['session_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $classSession->scores()->delete();
            $this->syncScores($classSession, $validated);
        });

        return redirect()
            ->route('session-history.index')
            ->with('success', 'Riwayat kelas berhasil diperbarui.');
    }

    public function destroy(ClassSession $classSession): RedirectResponse
    {
        $classSession->delete();

        return redirect()
            ->route('session-history.index')
            ->with('success', 'Riwayat kelas berhasil dihapus.');
    }

    private function students()
    {
        return Student::query()
            ->orderByRaw("case when status = 'Aktif' then 0 else 1 end")
            ->orderBy('name')
            ->get();
    }

    private function sessions(?string $month = null)
    {
        return ClassSession::query()
            ->with(['scores.student'])
            ->withCount('scores')
            ->withAvg('scores as average_score', 'score')
            ->when($month !== null, function ($query) use ($month): void {
                $query->whereYear('session_date', (int) substr($month, 0, 4))
                    ->whereMonth('session_date', (int) substr($month, 5, 2));
            })
            ->orderByDesc('session_date')
            ->get();
    }

    private function validatedSession(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'material' => ['required', 'string'],
            'session_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:students,id'],
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'integer', 'between:0,100'],
        ]);
    }

    private function validateSelectedScores(array $validated, ?ClassSession $classSession = null): void
    {
        $selectedStudentIds = collect($validated['student_ids'])
            ->map(fn ($studentId) => (int) $studentId)
            ->values();
        $selectedStudents = Student::query()
            ->whereIn('id', $selectedStudentIds)
            ->get()
            ->keyBy('id');
        $existingScores = $classSession?->scores()
            ->get()
            ->keyBy('student_id')
            ?? collect();

        foreach ($validated['student_ids'] as $studentId) {
            $score = data_get($validated, "scores.{$studentId}");

            if ($score === null || $score === '') {
                throw ValidationException::withMessages([
                    "scores.{$studentId}" => 'Nilai wajib diisi untuk setiap siswa yang dipilih.',
                ]);
            }

            $student = $selectedStudents->get((int) $studentId);

            if (! $student || $student->status === 'Aktif') {
                continue;
            }

            $existingScore = $existingScores->get((int) $studentId);

            if (! $existingScore) {
                throw ValidationException::withMessages([
                    'student_ids' => 'Siswa nonaktif tidak bisa dipilih untuk penilaian.',
                ]);
            }

            if ((int) $existingScore->score !== (int) $score) {
                throw ValidationException::withMessages([
                    "scores.{$studentId}" => 'Nilai siswa nonaktif yang sudah tersimpan tidak bisa diubah.',
                ]);
            }
        }
    }

    private function syncScores(ClassSession $classSession, array $validated): void
    {
        foreach ($validated['student_ids'] as $studentId) {
            StudentScore::create([
                'class_session_id' => $classSession->id,
                'student_id' => $studentId,
                'score' => (int) data_get($validated, "scores.{$studentId}"),
            ]);
        }
    }
}
