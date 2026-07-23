<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentScore;
use App\Support\GuardianSession;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $selectedMonth = $validated['month'] ?? null;
        $selectedMonthLabel = $selectedMonth !== null
            ? ucfirst(Carbon::createFromFormat('Y-m', $selectedMonth)->locale('id')->translatedFormat('F Y'))
            : null;
        $sessions = $this->sessions($selectedMonth);
        $sessionCount = $sessions->count();

        return view('sessions.history', compact('sessions', 'selectedMonth', 'selectedMonthLabel', 'sessionCount'));
    }

    public function edit(ClassSession $classSession): View
    {
        $students = $this->students();
        $classSession->load(['scores.student']);

        $selectedStudents = $classSession->scores->pluck('siswa_id')->all();
        $scoreValues = $classSession->scores->pluck('nilai', 'siswa_id')->all();
        $attendanceValues = $classSession->scores->pluck('status_kehadiran', 'siswa_id')->all();

        return view('sessions.edit', compact('classSession', 'students', 'selectedStudents', 'scoreValues', 'attendanceValues'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedSession($request);
        $validated['kehadiran'] = $this->normalizedAttendanceValues($validated['siswa_ids'], $validated['kehadiran'] ?? []);
        $this->validateSelectedScores($validated);
        $materialAttributes = $this->storeUploadedMaterial($validated['file_materi']);

        try {
            DB::transaction(function () use ($validated, $materialAttributes): void {
                $session = ClassSession::create([
                    'judul' => $validated['judul'],
                    'tanggal' => $validated['tanggal'],
                    'catatan' => $validated['catatan'] ?? null,
                    ...$materialAttributes,
                ]);

                $this->syncScores($session, $validated);
            });
        } catch (\Throwable $exception) {
            $this->deleteMaterialFile($materialAttributes['path_file_materi'] ?? null);

            throw $exception;
        }

        return redirect()
            ->route('sessions.index')
            ->with('success', 'Jadwal kelas dan nilai siswa berhasil disimpan.');
    }

    public function update(Request $request, ClassSession $classSession): RedirectResponse
    {
        $validated = $this->validatedSession($request, true);
        $validated['kehadiran'] = $this->normalizedAttendanceValues($validated['siswa_ids'], $validated['kehadiran'] ?? []);
        $this->validateSelectedScores($validated, $classSession);
        $materialAttributes = null;
        $oldMaterialFilePath = null;

        if (! empty($validated['file_materi'])) {
            $materialAttributes = $this->storeUploadedMaterial($validated['file_materi']);
            $oldMaterialFilePath = $classSession->path_file_materi;
        }

        try {
            DB::transaction(function () use ($validated, $classSession, $materialAttributes): void {
                $updateAttributes = [
                    'judul' => $validated['judul'],
                    'tanggal' => $validated['tanggal'],
                    'catatan' => $validated['catatan'] ?? null,
                ];

                if ($materialAttributes !== null) {
                    $updateAttributes = [
                        ...$updateAttributes,
                        ...$materialAttributes,
                    ];
                }

                $classSession->update($updateAttributes);

                $classSession->scores()->delete();
                $this->syncScores($classSession, $validated);
            });
        } catch (\Throwable $exception) {
            $this->deleteMaterialFile($materialAttributes['path_file_materi'] ?? null);

            throw $exception;
        }

        if ($materialAttributes !== null && $oldMaterialFilePath !== null) {
            $this->deleteMaterialFile($oldMaterialFilePath);
        }

        return redirect()
            ->route('session-history.index')
            ->with('success', 'Riwayat kelas berhasil diperbarui.');
    }

    public function materialFile(Request $request, ClassSession $classSession): StreamedResponse
    {
        abort_unless($this->canViewMaterialFile($request, $classSession), 403);
        abort_unless($classSession->hasMaterialFile(), 404);

        $disk = Storage::disk('local');
        $path = $classSession->path_file_materi;

        abort_if($path === null || ! $disk->exists($path), 404);

        return $disk->response($path, $classSession->materialDisplayName(), [
            'Content-Type' => $classSession->mime_file_materi ?: ($disk->mimeType($path) ?: 'application/octet-stream'),
            'Content-Disposition' => 'inline; filename="'.$this->sanitizeMaterialFilename($classSession->materialDisplayName()).'"',
        ]);
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
            ->orderBy('nama')
            ->get();
    }

    private function sessions(?string $month = null)
    {
        return ClassSession::query()
            ->with(['scores.student'])
            ->withCount('scores')
            ->withCount([
                'scores as absent_count' => fn ($query) => $query->absent(),
                'scores as assessed_count' => fn ($query) => $query->scorable(),
            ])
            ->withAvg('scores as average_score', 'nilai')
            ->when($month !== null, function ($query) use ($month): void {
                $query->whereYear('tanggal', (int) substr($month, 0, 4))
                    ->whereMonth('tanggal', (int) substr($month, 5, 2));
            })
            ->orderByDesc('tanggal')
            ->get();
    }

    private function validatedSession(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'file_materi' => [$isUpdate ? 'nullable' : 'required', 'file', 'extensions:pdf,ppt,pptx', 'max:20480'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'exists:siswa,id'],
            'kehadiran' => ['nullable', 'array'],
            'kehadiran.*' => ['nullable', 'in:present,absent'],
            'nilai' => ['nullable', 'array'],
            'nilai.*' => ['nullable', 'integer', 'between:0,100'],
        ], [
            'file_materi.required' => 'File materi wajib diunggah.',
            'file_materi.extensions' => 'File materi hanya boleh berupa PDF, PPT, atau PPTX.',
            'file_materi.max' => 'Ukuran file materi maksimal 20 MB.',
        ]);
    }

    private function normalizedAttendanceValues(array $siswaIds, array $kehadiran): array
    {
        $values = [];

        foreach ($siswaIds as $studentId) {
            $studentId = (int) $studentId;
            $values[$studentId] = data_get($kehadiran, $studentId) === 'absent'
                ? StudentScore::STATUS_ABSENT
                : StudentScore::STATUS_PRESENT;
        }

        return $values;
    }

    private function validateSelectedScores(array $validated, ?ClassSession $classSession = null): void
    {
        $selectedStudentIds = collect($validated['siswa_ids'])
            ->map(fn ($studentId) => (int) $studentId)
            ->values();
        $selectedStudents = Student::query()
            ->whereIn('id', $selectedStudentIds)
            ->get()
            ->keyBy('id');
        $existingScores = $classSession?->scores()
            ->get()
            ->keyBy('siswa_id')
            ?? collect();

        foreach ($validated['siswa_ids'] as $studentId) {
            $attendanceStatus = data_get($validated, "kehadiran.{$studentId}", StudentScore::STATUS_PRESENT);
            $score = data_get($validated, "nilai.{$studentId}");

            if ($attendanceStatus === StudentScore::STATUS_PRESENT && ($score === null || $score === '')) {
                throw ValidationException::withMessages([
                    "nilai.{$studentId}" => 'Nilai wajib diisi untuk setiap siswa yang dipilih.',
                ]);
            }

            $student = $selectedStudents->get((int) $studentId);

            if (! $student || $student->status === 'Aktif') {
                continue;
            }

            $existingScore = $existingScores->get((int) $studentId);

            if (! $existingScore) {
                throw ValidationException::withMessages([
                    'siswa_ids' => 'Siswa nonaktif tidak bisa dipilih untuk penilaian.',
                ]);
            }

            if ($existingScore->status_kehadiran !== $attendanceStatus) {
                throw ValidationException::withMessages([
                    "kehadiran.{$studentId}" => 'Status kehadiran siswa nonaktif yang sudah tersimpan tidak bisa diubah.',
                ]);
            }

            if (
                $existingScore->hasScore()
                && $attendanceStatus === StudentScore::STATUS_PRESENT
                && (int) $existingScore->nilai !== (int) $score
            ) {
                throw ValidationException::withMessages([
                    "nilai.{$studentId}" => 'Nilai siswa nonaktif yang sudah tersimpan tidak bisa diubah.',
                ]);
            }
        }
    }

    private function syncScores(ClassSession $classSession, array $validated): void
    {
        foreach ($validated['siswa_ids'] as $studentId) {
            $attendanceStatus = data_get($validated, "kehadiran.{$studentId}", StudentScore::STATUS_PRESENT);

            StudentScore::create([
                'sesi_kelas_id' => $classSession->id,
                'siswa_id' => $studentId,
                'status_kehadiran' => $attendanceStatus,
                'nilai' => $attendanceStatus === StudentScore::STATUS_ABSENT
                    ? null
                    : (int) data_get($validated, "nilai.{$studentId}"),
            ]);
        }
    }

    private function storeUploadedMaterial(UploadedFile $materialFile): array
    {
        $storedPath = $materialFile->store('materials', 'local');
        $originalName = $materialFile->getClientOriginalName();

        return [
            'materi' => $originalName,
            'path_file_materi' => $storedPath,
            'nama_file_materi' => $originalName,
            'mime_file_materi' => $materialFile->getClientMimeType(),
        ];
    }

    private function deleteMaterialFile(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    private function canViewMaterialFile(Request $request, ClassSession $classSession): bool
    {
        if (auth()->check()) {
            return true;
        }

        $guardianStudentId = GuardianSession::studentId($request);

        if ($guardianStudentId === null) {
            return false;
        }

        return $classSession->scores()
            ->where('siswa_id', $guardianStudentId)
            ->exists();
    }

    private function sanitizeMaterialFilename(string $filename): string
    {
        return str_replace(['"', "\r", "\n"], '', $filename);
    }
}
