<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\StudentPerformance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class GuardianDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('guardian.dashboard', $this->buildPortalData($request));
    }

    public function materials(Request $request): View
    {
        $portalData = $this->buildPortalData($request);
        
        return view('guardian.materials', array_merge(
            $portalData,
            $this->buildFilterData($request, $portalData['scores']),
        ));
    }

    public function progress(Request $request): View
    {
        return view('guardian.progress', $this->buildPortalData($request));
    }

    public function report(Request $request): View
    {
        $portalData = $this->buildPortalData($request);

        return view('guardian.report', array_merge(
            $portalData,
            $this->buildReportData($request, $portalData['reportScores']),
        ));
    }

    public function print(Request $request): View
    {
        $portalData = $this->buildPortalData($request);
        $reportData = $this->buildReportData($request, $portalData['reportScores']);

        $student = $portalData['student'];
        $scores = $reportData['reportScores'];
        $summary = $reportData['reportSummary'];
        
        $summary['latest_month_index'] = $portalData['summary']['latest_month_index'] ?? null;

        $teacherNote = $reportData['teacherNote'];
        $reportFilters = $reportData['reportFilters'];
        $reportPeriodLabel = $reportData['reportPeriodLabel'];

        $semesterLabel = null;
        $selectedRangeLabel = null;

        if ($reportFilters['semester'] !== null) {
            $parts = explode('-', $reportFilters['semester']);
            if (count($parts) === 2) {
                $sem = ucfirst($parts[0]);
                $year = $parts[1];
                $semesterLabel = "Semester {$sem} {$year}";
            }
        } elseif ($reportFilters['month'] !== null) {
            $selectedRangeLabel = $reportPeriodLabel;
        } else {
            $selectedRangeLabel = 'Semua pertemuan';
        }

        return view('reports.show', compact(
            'student',
            'summary',
            'scores',
            'teacherNote',
            'semesterLabel',
            'selectedRangeLabel'
        ))->with('isGuardian', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPortalData(Request $request): array
    {
        $student = $this->guardianStudent($request);
        $scores = $student->scores
            ->filter(fn ($score) => $score->classSession !== null)
            ->sortByDesc(fn ($score) => $score->classSession->tanggal?->format('Y-m-d') ?? '')
            ->values();
        $scoredRecords = StudentPerformance::scorableRecords($scores)
            ->sortByDesc(fn ($score) => $score->classSession->tanggal?->format('Y-m-d') ?? '')
            ->values();
        $summary = StudentPerformance::summary($student);
        $monthlyIndices = StudentPerformance::monthlyIndices($student);
        $latestScores = $scoredRecords->take(3)->values();
        $latestMaterials = $scores->take(1)->values();
        $reportScores = $scoredRecords->sortBy(fn ($score) => $score->classSession->tanggal?->format('Y-m-d') ?? '')->values();
        $currentMonthIndex = $monthlyIndices->firstWhere('month_key', now()->format('Y-m'));
        $latestMonth = $monthlyIndices->last();
        $teacherNote = StudentPerformance::teacherNote($summary['average_score']);

        $overview = [
            'materials_count' => $scores->count(),
            'average_score' => $summary['average_score'],
            'highest_score' => $summary['highest_score'],
            'current_month_index' => $currentMonthIndex['average'] ?? null,
            'latest_month_label' => $latestMonth['month_label'] ?? null,
            'latest_month_index' => $latestMonth['average'] ?? null,
        ];

        return compact(
            'student',
            'scores',
            'summary',
            'monthlyIndices',
            'latestScores',
            'latestMaterials',
            'reportScores',
            'overview',
            'teacherNote',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilterData(Request $request, Collection $baseScores): array
    {
        $availableMonthKeys = $baseScores
            ->map(fn ($score) => $score->classSession->tanggal?->format('Y-m'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $availableMonths = $availableMonthKeys
            ->map(fn (string $monthKey): array => [
                'value' => $monthKey,
                'label' => $this->formatMonthLabel($monthKey),
            ])
            ->reverse()
            ->values();

        $availableYears = $availableMonthKeys
            ->map(fn (string $monthKey): string => substr($monthKey, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        $reportFilters = [
            'month' => $this->normalizeMonthFilter($request->query('month'), $availableMonthKeys),
            'semester' => $request->query('semester'),
            'month_from' => null,
            'month_to' => null,
        ];

        if ($reportFilters['month'] !== null) {
            $reportFilters['semester'] = null;
        }

        if (!empty($reportFilters['semester'])) {
            $parts = explode('-', $reportFilters['semester']);
            if (count($parts) === 2) {
                $sem = $parts[0];
                $year = $parts[1];
                $semesterConfig = $this->getSemesterConfig();
                if (isset($semesterConfig[$sem])) {
                    $reportFilters['month_from'] = $year . '-' . str_pad($semesterConfig[$sem]['start'], 2, '0', STR_PAD_LEFT);
                    $reportFilters['month_to'] = $year . '-' . str_pad($semesterConfig[$sem]['end'], 2, '0', STR_PAD_LEFT);
                }
            }
        }

        $filteredScores = $this->filterReportScores($baseScores, $reportFilters);
        $reportPeriodLabel = $this->buildReportPeriodLabel($reportFilters);
        $reportAvailabilityLabel = $this->buildReportAvailabilityLabel($availableMonthKeys);

        return [
            'availableMonths' => $availableMonths,
            'availableYears' => $availableYears,
            'reportFilters' => $reportFilters,
            'filteredScores' => $filteredScores,
            'reportPeriodLabel' => $reportPeriodLabel,
            'reportAvailabilityLabel' => $reportAvailabilityLabel,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request, Collection $reportScores): array
    {
        $filterData = $this->buildFilterData($request, $reportScores);
        $filteredReportScores = $filterData['filteredScores'];
        
        $reportSummary = StudentPerformance::summaryFromScores($filteredReportScores);
        $reportMonthlyBreakdown = $this->buildReportMonthlyBreakdown($filteredReportScores);
        $teacherNote = StudentPerformance::teacherNote($reportSummary['average_score']);

        return array_merge($filterData, [
            'reportScores' => $filteredReportScores,
            'reportSummary' => $reportSummary,
            'reportMonthlyBreakdown' => $reportMonthlyBreakdown,
            'teacherNote' => $teacherNote,
        ]);
    }

    private function normalizeMonthFilter(mixed $value, Collection $availableMonthKeys): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return null;
        }

        return $availableMonthKeys->contains($value) ? $value : null;
    }

    private function filterReportScores(Collection $reportScores, array $reportFilters): Collection
    {
        return $reportScores
            ->filter(function ($score) use ($reportFilters): bool {
                $monthKey = $score->classSession->tanggal?->format('Y-m');

                if ($monthKey === null) {
                    return false;
                }

                if ($reportFilters['month'] !== null) {
                    return $monthKey === $reportFilters['month'];
                }

                if ($reportFilters['month_from'] !== null && $monthKey < $reportFilters['month_from']) {
                    return false;
                }

                if ($reportFilters['month_to'] !== null && $monthKey > $reportFilters['month_to']) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    private function buildReportMonthlyBreakdown(Collection $reportScores): Collection
    {
        return $reportScores
            ->groupBy(fn ($score) => $score->classSession->tanggal?->format('Y-m') ?? '')
            ->map(function (Collection $scores, string $monthKey): array {
                $average = round((float) $scores->avg('nilai'), 2);

                return [
                    'month_key' => $monthKey,
                    'month_label' => $this->formatMonthLabel($monthKey),
                    'session_count' => $scores->count(),
                    'average' => $average,
                    'highest' => (int) $scores->max('nilai'),
                    'lowest' => (int) $scores->min('nilai'),
                    'predicate' => StudentPerformance::predicate($average),
                ];
            })
            ->values();
    }

    private function buildReportPeriodLabel(array $reportFilters): string
    {
        if ($reportFilters['month'] !== null) {
            return 'Bulan '.$this->formatMonthLabel($reportFilters['month']);
        }

        if (!empty($reportFilters['semester'])) {
            $parts = explode('-', $reportFilters['semester']);
            if (count($parts) === 2) {
                return 'Semester ' . ucfirst($parts[0]) . ' ' . $parts[1];
            }
        }

        return 'Semua pertemuan';
    }

    private function buildReportAvailabilityLabel(Collection $availableMonthKeys): ?string
    {
        if ($availableMonthKeys->isEmpty()) {
            return null;
        }

        $firstMonth = $availableMonthKeys->first();
        $lastMonth = $availableMonthKeys->last();

        if ($firstMonth === $lastMonth) {
            return 'Data tersedia pada '.$this->formatMonthLabel($firstMonth);
        }

        return 'Data tersedia dari '.$this->formatMonthLabel($firstMonth).' sampai '.$this->formatMonthLabel($lastMonth);
    }

    private function formatMonthLabel(string $monthKey): string
    {
        return ucfirst(Carbon::createFromFormat('Y-m', $monthKey)->locale('id')->translatedFormat('F Y'));
    }

    private function guardianStudent(Request $request): Student
    {
        /** @var Student $student */
        $student = $request->attributes->get('guardianStudent');

        return $student;
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
}
