<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\StudentPerformance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $semesterConfig = $this->getSemesterConfig();

        $validated = $request->validate([
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'show_all' => ['nullable', 'boolean'],
            'semester' => ['nullable', 'string'],
        ]);

        $showAll = $validated['show_all'] ?? false;
        
        if ($showAll) {
            $startMonth = null;
            $endMonth = null;
            $selectedRangeLabel = 'Seluruh Waktu';
        } else {
            if (!empty($validated['semester'])) {
                $parts = explode('-', $validated['semester']); // e.g. "ganjil-2026"
                if (count($parts) === 2) {
                    $sem = $parts[0]; // ganjil or genap
                    $year = $parts[1];
                    if (isset($semesterConfig[$sem])) {
                        $startMonth = $year . '-' . str_pad($semesterConfig[$sem]['start'], 2, '0', STR_PAD_LEFT);
                        $endMonth = $year . '-' . str_pad($semesterConfig[$sem]['end'], 2, '0', STR_PAD_LEFT);
                    }
                }
            }

            $startMonth = $startMonth ?? $validated['start_month'] ?? now()->format('Y-m');
            $endMonth = $endMonth ?? $validated['end_month'] ?? now()->format('Y-m');
            
            if ($startMonth > $endMonth) {
                $temp = $startMonth;
                $startMonth = $endMonth;
                $endMonth = $temp;
            }

            $startLabel = ucfirst(Carbon::createFromFormat('Y-m', $startMonth)->locale('id')->translatedFormat('F Y'));
            $endLabel = ucfirst(Carbon::createFromFormat('Y-m', $endMonth)->locale('id')->translatedFormat('F Y'));
            $selectedRangeLabel = $startMonth === $endMonth ? $startLabel : "$startLabel - $endLabel";
        }

        $allReports = $this->buildReports($startMonth, $endMonth);

        $monthRows = $allReports->filter(fn (array $row): bool => $row['range_index'] !== null);
        $bestRow = $monthRows->sortByDesc(fn (array $row): float => $row['range_index']['average'])->first();

        $stats = [
            'students_with_scores' => $monthRows->count(),
            'selected_month_average' => $monthRows->isNotEmpty()
                ? round((float) $monthRows->avg(fn (array $row): float => $row['range_index']['average']), 1)
                : null,
            'best_index' => $bestRow['range_index']['average'] ?? null,
            'best_student' => $bestRow['student']->nama ?? null,
        ];

        $availableMonths = $allReports
            ->flatMap(fn (array $row): Collection => collect($row['monthly_indices'])->pluck('month_key'))
            ->unique()
            ->sortDesc()
            ->values();
            
        $reports = $monthRows;

        $availableYears = $allReports
            ->flatMap(fn (array $row): Collection => collect($row['monthly_indices'])->pluck('month_key'))
            ->map(fn (string $monthKey): string => substr($monthKey, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        return view('reports.index', compact('reports', 'startMonth', 'endMonth', 'showAll', 'selectedRangeLabel', 'stats', 'availableMonths', 'availableYears', 'semesterConfig'));
    }

    public function printAll(Request $request): View
    {
        $semesterConfig = $this->getSemesterConfig();

        $validated = $request->validate([
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'show_all' => ['nullable', 'boolean'],
            'semester' => ['nullable', 'string'],
        ]);

        $showAll = $validated['show_all'] ?? false;
        
        if ($showAll) {
            $startMonth = null;
            $endMonth = null;
            $selectedRangeLabel = 'Seluruh Waktu';
        } else {
            if (!empty($validated['semester'])) {
                $parts = explode('-', $validated['semester']);
                if (count($parts) === 2) {
                    $sem = $parts[0];
                    $year = $parts[1];
                    if (isset($semesterConfig[$sem])) {
                        $startMonth = $year . '-' . str_pad($semesterConfig[$sem]['start'], 2, '0', STR_PAD_LEFT);
                        $endMonth = $year . '-' . str_pad($semesterConfig[$sem]['end'], 2, '0', STR_PAD_LEFT);
                    }
                }
            }

            $startMonth = $startMonth ?? $validated['start_month'] ?? now()->format('Y-m');
            $endMonth = $endMonth ?? $validated['end_month'] ?? now()->format('Y-m');
            
            if ($startMonth > $endMonth) {
                $temp = $startMonth;
                $startMonth = $endMonth;
                $endMonth = $temp;
            }

            $startLabel = ucfirst(Carbon::createFromFormat('Y-m', $startMonth)->locale('id')->translatedFormat('F Y'));
            $endLabel = ucfirst(Carbon::createFromFormat('Y-m', $endMonth)->locale('id')->translatedFormat('F Y'));
            $selectedRangeLabel = $startMonth === $endMonth ? $startLabel : "$startLabel - $endLabel";
        }
        $semesterLabel = null;
        if (!empty($validated['semester'])) {
            $parts = explode('-', $validated['semester']);
            if (count($parts) === 2) {
                $sem = ucfirst($parts[0]);
                $year = $parts[1];
                $semesterLabel = "Semester {$sem} {$year}";
            }
        }
        
        $reports = $this->buildReports($startMonth, $endMonth)->filter(fn (array $row): bool => $row['range_index'] !== null);

        return view('reports.print-all', compact('reports', 'startMonth', 'endMonth', 'showAll', 'selectedRangeLabel', 'semesterLabel'));
    }

    public function show(Request $request, Student $student): View
    {
        $validated = $request->validate([
            'semester' => ['nullable', 'string'],
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m'],
            'show_all' => ['nullable', 'boolean'],
        ]);

        $semesterLabel = null;
        if (!empty($validated['semester'])) {
            $parts = explode('-', $validated['semester']);
            if (count($parts) === 2) {
                $sem = ucfirst($parts[0]);
                $year = $parts[1];
                $semesterLabel = "Semester {$sem} {$year}";
            }
        }
        
        $showAll = $validated['show_all'] ?? false;
        
        if ($showAll) {
            $selectedRangeLabel = 'Seluruh Waktu';
        } else {
            $startMonth = $validated['start_month'] ?? now()->format('Y-m');
            $endMonth = $validated['end_month'] ?? now()->format('Y-m');
            
            if ($startMonth > $endMonth) {
                $temp = $startMonth;
                $startMonth = $endMonth;
                $endMonth = $temp;
            }

            $startLabel = ucfirst(Carbon::createFromFormat('Y-m', $startMonth)->locale('id')->translatedFormat('F Y'));
            $endLabel = ucfirst(Carbon::createFromFormat('Y-m', $endMonth)->locale('id')->translatedFormat('F Y'));
            $selectedRangeLabel = $startMonth === $endMonth ? $startLabel : "$startLabel - $endLabel";
        }

        $student->load(['scores.classSession' => fn ($query) => $query->orderBy('tanggal')]);

        $monthlyIndices = StudentPerformance::monthlyIndices($student);
        $summary = StudentPerformance::summary($student);
        $scores = StudentPerformance::scorableRecords($student->scores)
            ->sortBy(fn ($score) => $score->classSession->tanggal?->format('Y-m-d'))
            ->values();
        $teacherNote = StudentPerformance::teacherNote($summary['average_score']);

        return view('reports.show', compact('student', 'monthlyIndices', 'summary', 'scores', 'teacherNote', 'semesterLabel', 'selectedRangeLabel'));
    }

    public function saveSemesterConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ganjil_start' => ['required', 'integer', 'min:1', 'max:12'],
            'ganjil_end' => ['required', 'integer', 'min:1', 'max:12'],
            'genap_start' => ['required', 'integer', 'min:1', 'max:12'],
            'genap_end' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $config = [
            'ganjil' => [
                'start' => $validated['ganjil_start'],
                'end' => $validated['ganjil_end'],
            ],
            'genap' => [
                'start' => $validated['genap_start'],
                'end' => $validated['genap_end'],
            ],
        ];

        File::put(storage_path('app/semester_config.json'), json_encode($config, JSON_PRETTY_PRINT));

        return redirect()->route('reports.index')->with('success', 'Pengaturan semester berhasil disimpan.');
    }

    private function getSemesterConfig(): array
    {
        $path = storage_path('app/semester_config.json');
        
        if (File::exists($path)) {
            $content = json_decode(File::get($path), true);
            if (is_array($content)) {
                return $content;
            }
        }

        return [
            'ganjil' => ['start' => 7, 'end' => 12],
            'genap' => ['start' => 1, 'end' => 6],
        ];
    }

    private function buildReports(?string $startMonth, ?string $endMonth): Collection
    {
        return Student::with(['scores.classSession' => fn ($query) => $query->orderBy('tanggal')])
            ->orderBy('nama')
            ->get()
            ->map(function (Student $student) use ($startMonth, $endMonth): array {
                $monthlyIndices = StudentPerformance::monthlyIndices($student);
                $summary = StudentPerformance::summary($student);
                $scores = StudentPerformance::scorableRecords($student->scores)
                    ->sortBy(fn ($score) => $score->classSession->tanggal?->format('Y-m-d'))
                    ->values();

                $rangeScores = $scores;
                $rangeMonthlyIndices = $monthlyIndices;
                if ($startMonth !== null && $endMonth !== null) {
                    $rangeScores = $scores->filter(function ($score) use ($startMonth, $endMonth) {
                        if ($score->classSession === null || $score->classSession->tanggal === null) {
                            return false;
                        }
                        $month = $score->classSession->tanggal->format('Y-m');
                        return $month >= $startMonth && $month <= $endMonth;
                    })->values();

                    $rangeMonthlyIndices = $monthlyIndices->filter(function ($index) use ($startMonth, $endMonth) {
                        return $index['month_key'] >= $startMonth && $index['month_key'] <= $endMonth;
                    })->values();
                }

                return [
                    'student' => $student,
                    'scores' => $scores,
                    'range_scores' => $rangeScores,
                    'monthly_indices' => $monthlyIndices,
                    'range_monthly_indices' => $rangeMonthlyIndices,
                    'range_index' => StudentPerformance::rangeIndex($student->scores, $startMonth, $endMonth),
                    'summary' => $summary,
                    'teacher_note' => StudentPerformance::teacherNote($summary['average_score']),
                ];
            });
    }
}
