<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCottage extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'code',
        'building',
        'wing',
        'status',
        'tenant_id',
        'requested_tenant_id',
        'requested_at',
        'family_members',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'requested_tenant_id' => 'integer',
            'requested_at' => 'datetime',
            'family_members' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function requestedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'requested_tenant_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeRequested($query)
    {
        return $query->where('status', self::STATUS_REQUESTED);
    }
}
