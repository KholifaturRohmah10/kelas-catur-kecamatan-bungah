<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = [
        'student_code',
        'name',
        'gender',
        'birth_date',
        'school_name',
        'parent_name',
        'phone',
        'address',
        'registration_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registration_date' => 'date',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? Str::upper($value) : null,
        );
    }

    protected function parentName(): Attribute
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
        return $this->hasMany(StudentScore::class);
    }
}
