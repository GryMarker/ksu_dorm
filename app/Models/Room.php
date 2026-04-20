<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const SEX_MALE = 'male';
    public const SEX_FEMALE = 'female';
    public const SEX_MIXED = 'mixed';

    protected $fillable = [
        'code',
        'building',
        'floor',
        'wing',
        'sex',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function vacantBedCount(): int
    {
        return $this->beds()->where('is_occupied', false)->count();
    }
}
