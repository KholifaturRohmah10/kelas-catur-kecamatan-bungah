<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\StudentPerformance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $selectedMonth = $validated['month'] ?? now()->format('Y-m');
        $selectedMonthLabel = ucfirst(Carbon::createFromFormat('Y-m', $selectedMonth)->locale('id')->translatedFormat('F Y'));

        $reports = $this->buildReports($selectedMonth);

        $monthRows = $reports->filter(fn (array $row): bool => $row['selected_index'] !== null);
        $bestRow = $monthRows->sortByDesc(fn (array $row): float => $row['selected_index']['average'])->first();

        $stats = [
            'students_with_scores' => $reports->filter(fn (array $row): bool => $row['summary']['total_sessions'] > 0)->count(),
            'selected_month_average' => $monthRows->isNotEmpty()
                ? round((float) $monthRows->avg(fn (array $row): float => $row['selected_index']['average']), 1)
                : null,
            'best_index' => $bestRow['selected_index']['average'] ?? null,
            'best_student' => $bestRow['student']->name ?? null,
        ];

        $availableMonths = $reports
            ->flatMap(fn (array $row): Collection => collect($row['monthly_indices'])->pluck('month_key'))
            ->unique()
            ->sortDesc()
            ->values();

        return view('reports.index', compact('reports', 'selectedMonth', 'selectedMonthLabel', 'stats', 'availableMonths'));
    }

    public function printAll(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $selectedMonth = $validated['month'] ?? now()->format('Y-m');
        $selectedMonthLabel = ucfirst(Carbon::createFromFormat('Y-m', $selectedMonth)->locale('id')->translatedFormat('F Y'));
        $reports = $this->buildReports($selectedMonth);

        return view('reports.print-all', compact('reports', 'selectedMonth', 'selectedMonthLabel'));
    }

    public function show(Student $student): View
    {
        $student->load(['scores.classSession' => fn ($query) => $query->orderBy('session_date')]);

        $monthlyIndices = StudentPerformance::monthlyIndices($student);
        $summary = StudentPerformance::summary($student);
        $scores = $student->scores
            ->sortBy(fn ($score) => $score->classSession->session_date?->format('Y-m-d'))
            ->values();
        $teacherNote = StudentPerformance::teacherNote($summary['average_score']);

        return view('reports.show', compact('student', 'monthlyIndices', 'summary', 'scores', 'teacherNote'));
    }

    private function buildReports(string $selectedMonth): Collection
    {
        return Student::with(['scores.classSession' => fn ($query) => $query->orderBy('session_date')])
            ->orderBy('name')
            ->get()
            ->map(function (Student $student) use ($selectedMonth): array {
                $monthlyIndices = StudentPerformance::monthlyIndices($student);
                $summary = StudentPerformance::summary($student);
                $scores = $student->scores
                    ->sortBy(fn ($score) => $score->classSession->session_date?->format('Y-m-d'))
                    ->values();

                return [
                    'student' => $student,
                    'scores' => $scores,
                    'monthly_indices' => $monthlyIndices,
                    'selected_index' => $monthlyIndices->firstWhere('month_key', $selectedMonth),
                    'summary' => $summary,
                    'teacher_note' => StudentPerformance::teacherNote($summary['average_score']),
                ];
            });
    }
}
