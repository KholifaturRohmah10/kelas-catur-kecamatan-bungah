<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'kata_sandi',
        'peran',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'kata_sandi' => 'hashed',
            'peran' => UserRole::class,
        ];
    }

    public function getAuthPasswordName()
    {
        return 'kata_sandi';
    }

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            $user->peran ??= UserRole::Admin;
        });
    }

    public function hasRole(UserRole|string $role): bool
    {
        if (is_string($role)) {
            $role = UserRole::tryFrom($role);
        }

        return $role !== null && $this->peran === $role;
    }

    public function getRoleLabelAttribute(): string
    {
        return ($this->peran ?? UserRole::Admin)->label();
    }

    public function getRoleDescriptionAttribute(): string
    {
        return ($this->peran ?? UserRole::Admin)->description();
    }
}
