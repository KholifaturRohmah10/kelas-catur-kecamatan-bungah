<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $students = Student::query()
            ->whereBetween('registration_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->latest('registration_date')
            ->latest('created_at')
            ->get();

        return view('registrations.index', compact('students', 'startOfWeek', 'endOfWeek'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request, false);
        $validated['student_code'] = $this->generateStudentCode();
        $validated['status'] = 'Aktif';

        Student::create($validated);

        return redirect()
            ->route('registrations.index')
            ->with('success', 'Pendaftaran siswa berhasil disimpan.');
    }

    public function edit(Student $student): View
    {
        return view('registrations.edit', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->update($this->validatedData($request, true));

        return redirect()
            ->route('registrations.index')
            ->with('success', 'Data pendaftaran siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('registrations.index')
            ->with('success', 'Data pendaftaran siswa berhasil dihapus.');
    }

    private function validatedData(Request $request, bool $isEdit): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_date' => ['required', 'date'],
            'school_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'registration_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];

        if ($isEdit) {
            $rules['status'] = ['required', Rule::in(['Aktif', 'Nonaktif'])];
        }

        return $request->validate($rules);
    }

    private function generateStudentCode(): string
    {
        $nextNumber = ((int) Student::max('id')) + 1;

        do {
            $code = 'CATUR-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Student::where('student_code', $code)->exists());

        return $code;
    }
}
