<?php

namespace App\Support;

use App\Models\Student;
use App\Models\StudentScore;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentPerformance
{
    public static function scorableRecords(Collection $scores): Collection
    {
        return $scores->filter(fn (StudentScore $score): bool => $score->hasScore())->values();
    }

    public static function rangeIndex(Collection $scores, ?string $startMonth, ?string $endMonth): ?array
    {
        $filteredScores = self::scorableRecords($scores);

        if ($startMonth !== null && $endMonth !== null) {
            $filteredScores = $filteredScores->filter(function (StudentScore $score) use ($startMonth, $endMonth) {
                if ($score->classSession === null || $score->classSession->tanggal === null) {
                    return false;
                }
                $month = $score->classSession->tanggal->format('Y-m');
                return $month >= $startMonth && $month <= $endMonth;
            });
        }

        if ($filteredScores->isEmpty()) {
            return null;
        }

        $average = round((float) $filteredScores->avg('nilai'), 1);

        return [
            'average' => $average,
            'session_count' => $filteredScores->count(),
            'highest' => (int) $filteredScores->max('nilai'),
            'lowest' => (int) $filteredScores->min('nilai'),
            'passed_count' => $filteredScores->where('nilai', '>', 60)->count(),
            'predicate' => self::predicate($average),
        ];
    }

    public static function monthlyIndices(Student $student): Collection
    {
        $student->loadMissing('scores.classSession');

        return self::scorableRecords($student->scores)
            ->filter(fn (StudentScore $score): bool => $score->classSession !== null && $score->classSession->tanggal !== null)
            ->sortBy(fn (StudentScore $score): string => $score->classSession->tanggal->format('Y-m-d'))
            ->groupBy(fn (StudentScore $score): string => $score->classSession->tanggal->format('Y-m'))
            ->map(function (Collection $scores, string $monthKey): array {
                $month = Carbon::createFromFormat('Y-m', $monthKey)->locale('id');
                $average = round((float) $scores->avg('nilai'), 2);

                return [
                    'month_key' => $monthKey,
                    'month_label' => ucfirst($month->translatedFormat('F Y')),
                    'average' => $average,
                    'session_count' => $scores->count(),
                    'highest' => (int) $scores->max('nilai'),
                    'lowest' => (int) $scores->min('nilai'),
                    'passed_count' => $scores->where('nilai', '>', 60)->count(),
                    'predicate' => self::predicate($average),
                ];
            })
            ->values();
    }

    public static function summary(Student $student): array
    {
        $student->loadMissing('scores.classSession');

        $scores = self::scorableRecords($student->scores);
        $monthlyIndices = self::monthlyIndices($student);
        $currentMonthIndex = $monthlyIndices->firstWhere('month_key', now()->format('Y-m'));
        $latestMonthIndex = $monthlyIndices->last();

        return [
            ...self::summaryFromScores($scores),
            'current_month_index' => $currentMonthIndex['average'] ?? null,
            'latest_month_index' => $latestMonthIndex['average'] ?? null,
        ];
    }

    public static function summaryFromScores(Collection $scores): array
    {
        $scores = self::scorableRecords($scores);

        return [
            'total_sessions' => $scores->count(),
            'average_score' => $scores->isNotEmpty() ? round((float) $scores->avg('nilai'), 2) : null,
            'highest_score' => $scores->isNotEmpty() ? (int) $scores->max('nilai') : null,
            'lowest_score' => $scores->isNotEmpty() ? (int) $scores->min('nilai') : null,
            'passed_sessions' => $scores->where('nilai', '>', 60)->count(),
        ];
    }

    public static function predicate(?float $score): string
    {
        if ($score === null) {
            return 'Belum ada nilai';
        }

        return match (true) {
            $score >= 90 => 'Istimewa',
            $score >= 80 => 'Baik Sekali',
            $score >= 70 => 'Baik',
            $score > 60 => 'Cukup',
            default => 'Perlu Pendampingan',
        };
    }

    public static function teacherNote(?float $score): string
    {
        if ($score === null) {
            return 'Belum ada catatan penilaian karena siswa ini masih menunggu input nilai dari pertemuan kelas.';
        }

        return match (true) {
            $score >= 90 => 'Menunjukkan pemahaman strategi catur yang sangat baik, konsisten, dan siap menerima materi lanjutan.',
            $score >= 80 => 'Perkembangan belajar sangat positif. Siswa mampu mengikuti materi dengan baik dan menunjukkan disiplin latihan.',
            $score >= 70 => 'Pemahaman dasar sudah baik. Disarankan menambah latihan rutin agar penguasaan pola permainan semakin matang.',
            $score > 60 => 'Kemampuan mulai berkembang, namun masih perlu penguatan pada konsentrasi, evaluasi langkah, dan latihan terstruktur.',
            default => 'Perlu pendampingan tambahan dan pengulangan materi inti agar fondasi strategi catur menjadi lebih kuat.',
        };
    }
}
