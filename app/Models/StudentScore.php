<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentScore extends Model
{
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';

    protected $table = 'nilai_siswa';

    protected $fillable = [
        'sesi_kelas_id',
        'siswa_id',
        'status_kehadiran',
        'nilai',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }

    public function scopeScorable($query)
    {
        return $query
            ->where('status_kehadiran', self::STATUS_PRESENT)
            ->whereNotNull('nilai');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status_kehadiran', self::STATUS_ABSENT);
    }

    public function isAbsent(): bool
    {
        return $this->status_kehadiran === self::STATUS_ABSENT;
    }

    public function hasScore(): bool
    {
        return ! $this->isAbsent() && $this->nilai !== null;
    }

    public function attendanceLabel(): string
    {
        return $this->isAbsent() ? 'Tidak masuk' : 'Hadir';
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'sesi_kelas_id');
    }
}
