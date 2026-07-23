<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentScore;
use App\Support\StudentPerformance;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $currentMonthKey = now()->format('Y-m');
        $totalScores = StudentScore::scorable()->count();
        $passedScores = StudentScore::scorable()->where('nilai', '>', 60)->count();

        $stats = [
            'total_students' => Student::count(),
            'total_sessions' => ClassSession::count(),
            'average_score' => round((float) (StudentScore::scorable()->avg('nilai') ?? 0), 1),
            'pass_rate' => $totalScores > 0 ? round(($passedScores / $totalScores) * 100, 1) : 0,
        ];

        $latestSessions = ClassSession::query()
            ->withCount([
                'scores as passed_students_count' => fn ($query) => $query->scorable()->where('nilai', '>', 60),
                'scores as participant_count' => fn ($query) => $query->scorable(),
            ])
            ->withAvg(['scores as average_score' => fn ($query) => $query->scorable()], 'nilai')
            ->orderByDesc('tanggal')
            ->take(2)
            ->get();

        $monthlyLeaders = Student::with('scores.classSession')
            ->get()
            ->map(function (Student $student) use ($currentMonthKey): array {
                $monthlyIndices = StudentPerformance::monthlyIndices($student);
                $summary = StudentPerformance::summary($student);
                $currentMonth = $monthlyIndices->firstWhere('month_key', $currentMonthKey);

                return [
                    'student' => $student,
                    'summary' => $summary,
                    'current_month' => $currentMonth,
                    'predicate' => StudentPerformance::predicate($currentMonth['average'] ?? null),
                ];
            })
            ->filter(fn (array $row): bool => $row['current_month'] !== null)
            ->sortByDesc(fn (array $row): float => $row['current_month']['average'])
            ->take(3)
            ->values();

        return view('dashboard.index', compact('stats', 'latestSessions', 'monthlyLeaders'));
    }
}
