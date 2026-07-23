<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClassSession extends Model
{
    protected $table = 'sesi_kelas';

    protected $fillable = [
        'judul',
        'materi',
        'path_file_materi',
        'nama_file_materi',
        'mime_file_materi',
        'tanggal',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    protected function judul(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? Str::upper($value) : null,
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (self $classSession): void {
            if ($classSession->path_file_materi) {
                Storage::disk('local')->delete($classSession->path_file_materi);
            }
        });
    }

    public function hasMaterialFile(): bool
    {
        return filled($this->path_file_materi);
    }

    public function materialDisplayName(): string
    {
        return $this->nama_file_materi ?: $this->materi;
    }

    public function materialPreviewText(): string
    {
        return $this->hasMaterialFile()
            ? 'File materi: '.$this->materialDisplayName()
            : $this->materi;
    }

    public function materialTypeLabel(): ?string
    {
        if (! $this->hasMaterialFile()) {
            return null;
        }

        $extension = Str::lower(pathinfo($this->materialDisplayName(), PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'PDF',
            'ppt' => 'PPT',
            'pptx' => 'PPTX',
            default => Str::upper($extension ?: 'FILE'),
        };
    }

    public function hasLegacyMaterialText(): bool
    {
        return ! $this->hasMaterialFile() && filled($this->materi);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class, 'sesi_kelas_id');
    }
}
