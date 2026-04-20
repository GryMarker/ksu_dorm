<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\InterviewSlot;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InterviewAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_view_open_interview_slots(): void
    {
        $tenantUser = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenantUser->tenant()->create([
            'full_name' => 'Interview Tester',
            'sex' => Tenant::SEX_FEMALE,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_FOR_INTERVIEW,
            'university_id_no' => 'TEST-2025',
            'phone' => '0917-000-0000',
            'emergency_contact_name' => 'Test Guardian',
            'emergency_contact_phone' => '0918-111-2222',
        ]);

        InterviewSlot::create([
            'starts_at' => Carbon::now()->addDay()->setTime(9, 0),
            'ends_at' => Carbon::now()->addDay()->setTime(9, 30),
            'capacity' => 2,
            'status' => 'open',
        ]);

        $response = $this->actingAs($tenantUser)->get(route('tenant.apply.slots'));

        $response->assertOk();
        $response->assertSee('Book Your Interview Slot');
    }

    public function test_dorm_master_can_view_interview_listing(): void
    {
        $dormMaster = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);

        $tenantUser = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenant = $tenantUser->tenant()->create([
            'full_name' => 'Pending Applicant',
            'sex' => Tenant::SEX_MALE,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_FOR_INTERVIEW,
            'university_id_no' => 'TEST-3045',
            'phone' => '0917-123-4567',
            'emergency_contact_name' => 'Guardian Name',
            'emergency_contact_phone' => '0918-765-4321',
        ]);

        $slot = InterviewSlot::create([
            'starts_at' => Carbon::now()->addDay()->setTime(11, 0),
            'ends_at' => Carbon::now()->addDay()->setTime(12, 0),
            'capacity' => 3,
            'status' => 'open',
        ]);

        Interview::create([
            'tenant_id' => $tenant->id,
            'slot_id' => $slot->id,
            'scheduled_at' => $slot->starts_at,
        ]);

        $response = $this->actingAs($dormMaster)->get(route('admin.interviews.index'));

        $response->assertOk();
        $response->assertSee('Interview Management');
    }
}
