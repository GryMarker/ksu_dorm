<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_DORM_MASTER = 'dorm_master';
    public const ROLE_STUDENT_DIRECTOR = 'student_director';
    public const ROLE_TENANT = 'tenant';
    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_PRESIDENT = 'president';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    public function isDormMaster(): bool
    {
        return $this->role === self::ROLE_DORM_MASTER;
    }

    public function isDirector(): bool
    {
        return $this->role === self::ROLE_STUDENT_DIRECTOR;
    }

    public function isTenant(): bool
    {
        return $this->role === self::ROLE_TENANT;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function isPresident(): bool
    {
        return $this->role === self::ROLE_PRESIDENT;
    }

    public function getDisplayRoleAttribute(): string
    {
        return Str::of($this->role)->headline()->toString();
    }
}
