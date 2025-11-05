<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    public const TYPE_STUDENT = 'student';
    public const TYPE_EMPLOYEE = 'employee';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FOR_INTERVIEW = 'for_interview';
    public const STATUS_FOR_APPROVAL = 'for_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RECHECK = 'recheck';

    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const DEFAULT_EMPLOYEE_MONTHLY_RATE = 1800.00;

    protected $fillable = [
        'user_id',
        'full_name',
        'nickname',
        'gender',
        'dob',
        'home_address',
        'age',
        'place_of_birth',
        'father_name',
        'father_contact',
        'mother_name',
        'mother_contact',
        'course_year',
        'cellphone',
        'policy_accepted_at',
        'type',
        'employee_id_number',
        'monthly_rate',
        'salary_deduction',
        'family_members',
        'onboarding_status',
        'university_id_no',
        'program',
        'year_level',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
        'admission_form_json',
    ];

    protected function casts(): array
    {
        return [
            'admission_form_json' => 'array',
            'dob' => 'date',
            'policy_accepted_at' => 'datetime',
            'family_members' => 'array',
            'salary_deduction' => 'boolean',
            'monthly_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function employeePayments(): HasMany
    {
        return $this->hasMany(EmployeePayment::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->where('is_active', true)->latestOfMany('start_date');
    }

    public function cottage(): HasOne
    {
        return $this->hasOne(EmployeeCottage::class, 'tenant_id');
    }

    public function cottageRequest(): HasOne
    {
        return $this->hasOne(EmployeeCottage::class, 'requested_tenant_id');
    }
}
