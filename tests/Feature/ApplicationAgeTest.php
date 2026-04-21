<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationAgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_application_computes_age_from_date_of_birth(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenant = $user->tenant()->create([
            'full_name' => $user->name,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_DRAFT,
            'university_id_no' => 'PENDING1',
            'phone' => '',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'admission_form_json' => [],
        ]);

        $dob = now()->subYears(20)->subDay()->toDateString();

        $this->actingAs($user)->post(route('tenant.apply.submit'), [
            'full_name' => 'Student User',
            'nickname' => 'Student',
            'sex' => Tenant::SEX_FEMALE,
            'dob' => $dob,
            'home_address' => 'Home address',
            'age' => 99,
            'place_of_birth' => 'Tabuk City',
            'father_name' => 'Father User',
            'father_contact' => '09170000000',
            'mother_name' => 'Mother User',
            'mother_contact' => '09170000001',
            'university_id_no' => '12-345678',
            'program' => 'BSIT',
            'year_level' => '2',
            'cellphone' => '09170000002',
            'accept_terms' => '1',
        ])->assertRedirect(route('tenant.apply.slots'));

        $this->assertSame(20, $tenant->fresh()->age);
    }

    public function test_employee_application_computes_age_from_date_of_birth(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $tenant = $user->tenant()->create([
            'full_name' => $user->name,
            'type' => Tenant::TYPE_EMPLOYEE,
            'onboarding_status' => Tenant::STATUS_DRAFT,
            'university_id_no' => 'PENDING2',
            'phone' => '',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'admission_form_json' => [],
        ]);

        $dob = now()->subYears(35)->subDay()->toDateString();

        $this->actingAs($user)->post(route('employee.apply.submit'), [
            'full_name' => 'Employee User',
            'nickname' => 'Employee',
            'sex' => Tenant::SEX_MALE,
            'dob' => $dob,
            'home_address' => 'Employee address',
            'age' => 99,
            'place_of_birth' => 'Tabuk City',
            'department' => 'Facilities',
            'cellphone' => '09170000003',
            'accept_terms' => '1',
        ])->assertRedirect(route('employee.status'));

        $this->assertSame(35, $tenant->fresh()->age);
    }
}
