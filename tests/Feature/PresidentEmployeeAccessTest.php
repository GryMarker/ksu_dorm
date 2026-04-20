<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresidentEmployeeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_president_can_view_employee_dashboard_and_masterlist(): void
    {
        $president = User::factory()->create([
            'role' => User::ROLE_PRESIDENT,
        ]);

        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employeeUser->tenant()->create([
            'full_name' => 'Employee Worker',
            'sex' => Tenant::SEX_MALE,
            'type' => Tenant::TYPE_EMPLOYEE,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'employee_id_number' => 'EMP-1001',
            'university_id_no' => 'EMP-1001',
            'course_year' => 'Facilities',
            'phone' => '09170000001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '09170000002',
        ]);

        $this->actingAs($president)
            ->get(route('president.dashboard'))
            ->assertOk()
            ->assertSee('Employee Housing Overview')
            ->assertSee('Employee Worker');

        $this->actingAs($president)
            ->get(route('president.employees.index'))
            ->assertOk()
            ->assertSee('Employees')
            ->assertSee('Employee Worker');
    }

    public function test_president_employee_history_only_accepts_employee_records(): void
    {
        $president = User::factory()->create([
            'role' => User::ROLE_PRESIDENT,
        ]);

        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employee = $employeeUser->tenant()->create([
            'full_name' => 'History Employee',
            'sex' => Tenant::SEX_FEMALE,
            'type' => Tenant::TYPE_EMPLOYEE,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'employee_id_number' => 'EMP-1002',
            'university_id_no' => 'EMP-1002',
            'phone' => '09170000003',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '09170000004',
        ]);

        $studentUser = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $student = $studentUser->tenant()->create([
            'full_name' => 'Student Record',
            'sex' => Tenant::SEX_FEMALE,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'university_id_no' => '24-000001',
            'phone' => '09170000005',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '09170000006',
        ]);

        $this->actingAs($president)
            ->get(route('president.employees.history', $employee))
            ->assertOk()
            ->assertSee('Employee Audit Trail')
            ->assertSee('History Employee');

        $this->actingAs($president)
            ->get(route('president.employees.history', $student))
            ->assertNotFound();
    }
}
