<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ClassSession extends Model
{
    protected $fillable = [
        'title',
        'material',
        'session_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    protected function title(): Attribute
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
