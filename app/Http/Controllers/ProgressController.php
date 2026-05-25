<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(): View
    {
        $chartWindowStart = now()->subWeeks(2)->startOfDay();
        $chartWindowLabel = '2 minggu terakhir';

        $sessions = ClassSession::query()
            ->withCount([
                'scores as passed_students_count' => fn ($query) => $query->where('score', '>', 60),
                'scores as participant_count',
            ])
            ->orderBy('session_date')
            ->get();

        $chartSessions = $sessions
            ->filter(fn (ClassSession $session): bool => $session->session_date !== null && $session->session_date->gte($chartWindowStart))
            ->values();

        $studentProgress = Student::query()
            ->withCount([
                'scores as session_count',
            ])
            ->withAvg('scores as average_score', 'score')
            ->orderByDesc('session_count')
            ->orderByDesc('average_score')
            ->orderBy('name')
            ->get();

        $totalScores = StudentScore::count();
        $passedScores = StudentScore::where('score', '>', 60)->count();

        $overview = [
            'materials_count' => $sessions->count(),
            'total_assessments' => $totalScores,
            'passed_assessments' => $passedScores,
            'overall_pass_rate' => $totalScores > 0 ? round(($passedScores / $totalScores) * 100, 1) : 0,
        ];

        $peakPassed = max(1, (int) $chartSessions->max('passed_students_count'));

        return view('progress.index', compact('sessions', 'chartSessions', 'studentProgress', 'overview', 'peakPassed', 'chartWindowLabel'));
    }
}
