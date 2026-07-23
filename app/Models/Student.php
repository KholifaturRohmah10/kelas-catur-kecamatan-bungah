<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'kode_siswa',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'asal_sekolah',
        'nama_wali',
        'telepon',
        'alamat',
        'tanggal_daftar',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_daftar' => 'date',
        ];
    }

    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? Str::upper($value) : null,
        );
    }

    protected function namaWali(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? Str::upper($value) : null,
        );
    }

    protected function asalSekolah(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? Str::upper($value) : null,
        );
    }

    protected function address(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? Str::upper($value) : null,
        );
    }

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class, 'siswa_id');
    }
}
