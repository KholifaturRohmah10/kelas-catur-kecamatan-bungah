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
            ->whereBetween('tanggal_daftar', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->latest('tanggal_daftar')
            ->latest('created_at')
            ->get();

        $nextStudentCode = $this->generateStudentCode();

        return view('registrations.index', compact('students', 'startOfWeek', 'endOfWeek', 'nextStudentCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request, false);
        $validated['kode_siswa'] = $this->generateStudentCode();
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
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'tanggal_lahir' => ['required', 'date'],
            'asal_sekolah' => ['required', 'string', 'max:255'],
            'nama_wali' => ['required', 'string', 'max:255'],
            'telepon' => ['required', 'string', 'max:30'],
            'alamat' => ['required', 'string'],
            'tanggal_daftar' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
        ];

        if ($isEdit) {
            $rules['status'] = ['required', Rule::in(['Aktif', 'Nonaktif'])];
        }

        return $request->validate($rules);
    }

    private function generateStudentCode(): string
    {
        $prefix = date('ym');
        $nextNumber = ((int) Student::max('id')) + 1;

        do {
            $code = $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Student::where('kode_siswa', $code)->exists());

        return $code;
    }
}
