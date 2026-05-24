<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }
}
