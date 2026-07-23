<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Http\Request;

class GuardianSession
{
    public const SESSION_KEY = 'guardian_student_id';

    public static function has(Request $request): bool
    {
        return self::studentId($request) !== null;
    }

    public static function studentId(Request $request): ?int
    {
        $studentId = $request->session()->get(self::SESSION_KEY);

        return is_numeric($studentId) ? (int) $studentId : null;
    }

    public static function student(Request $request): ?Student
    {
        $studentId = self::studentId($request);

        if ($studentId === null) {
            return null;
        }

        return Student::query()->find($studentId);
    }

    public static function login(Request $request, Student $student): void
    {
        $request->session()->put(self::SESSION_KEY, $student->getKey());
    }

    public static function logout(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
