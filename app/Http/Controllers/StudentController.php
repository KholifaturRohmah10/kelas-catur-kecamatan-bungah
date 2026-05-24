<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $students = Student::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%")
                        ->orWhere('school_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('students.index', compact('students', 'search'));
    }

    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->update($this->validatedData($request));

        return redirect()
            ->route('students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_date' => ['required', 'date'],
            'school_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'registration_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
