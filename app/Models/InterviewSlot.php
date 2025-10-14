<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'starts_at',
        'ends_at',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'slot_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function capacityLeft(): int
    {
        return max(0, $this->capacity - $this->interviews()->count());
    }
}
