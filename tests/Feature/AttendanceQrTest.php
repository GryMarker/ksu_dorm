<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\AttendanceQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_dorm_master_can_view_the_qr_attendance_screen(): void
    {
        $dormMaster = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);

        $this->actingAs($dormMaster)
            ->get(route('admin.attendance.qr'))
            ->assertOk()
            ->assertSee('QR Attendance Screen');
    }

    public function test_tenant_can_submit_attendance_from_a_valid_qr_scan(): void
    {
        $tenantUser = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenantUser->tenant()->create([
            'full_name' => 'QR Tenant',
            'sex' => Tenant::SEX_FEMALE,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'university_id_no' => 'QR-1001',
            'program' => 'BSIT',
            'year_level' => '2',
            'phone' => '09170000000',
            'cellphone' => '09170000000',
            'emergency_contact_name' => 'Guardian',
            'emergency_contact_phone' => '09181111111',
        ]);

        $scanUrl = app(AttendanceQrService::class)->currentPayload()['scan_url'];

        $this->actingAs($tenantUser)
            ->get($scanUrl)
            ->assertOk()
            ->assertSee('Confirm Attendance');

        $this->actingAs($tenantUser)
            ->post($scanUrl)
            ->assertRedirect(route('tenant.attendance.index'));

        $this->assertDatabaseHas('attendance_logs', [
            'tenant_id' => $tenantUser->tenant->id,
            'type' => 'in',
            'mode' => 'qr',
        ]);
    }
}
