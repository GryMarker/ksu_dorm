<?php

namespace Tests\Feature\Admin;

use App\Models\Assignment;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_directly_assign_an_approved_student_to_a_room(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_STUDENT_DIRECTOR,
        ]);

        $studentUser = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenant = $studentUser->tenant()->create([
            'full_name' => 'Assigned Student',
            'sex' => Tenant::SEX_FEMALE,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'university_id_no' => 'KSU-1001',
            'program' => 'BSIT',
            'year_level' => '2',
            'phone' => '09170000000',
            'cellphone' => '09170000000',
            'emergency_contact_name' => 'Guardian',
            'emergency_contact_phone' => '09181111111',
        ]);

        $room = Room::create([
            'code' => 'A-101',
            'building' => 'Main',
            'floor' => '1',
            'wing' => 'East',
            'sex' => Room::SEX_FEMALE,
            'capacity' => 6,
            'status' => Room::STATUS_OPEN,
        ]);

        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_label' => 'A',
            'is_occupied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.rooms.assign', $room), [
            'tenant_id' => $tenant->id,
            'bed_id' => $bed->id,
        ]);

        $response->assertRedirect(route('admin.rooms.show', $room));

        $this->assertDatabaseHas('assignments', [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'is_occupied' => true,
            'occupant_tenant_id' => $tenant->id,
        ]);
    }

    public function test_direct_assignment_releases_previous_active_assignment(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);

        $studentUser = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenant = $studentUser->tenant()->create([
            'full_name' => 'Transfer Student',
            'sex' => Tenant::SEX_MALE,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'university_id_no' => 'KSU-1002',
            'program' => 'BSED',
            'year_level' => '3',
            'phone' => '09170000001',
            'cellphone' => '09170000001',
            'emergency_contact_name' => 'Guardian',
            'emergency_contact_phone' => '09182222222',
        ]);

        $oldRoom = Room::create([
            'code' => 'A-100',
            'building' => 'Main',
            'floor' => '1',
            'wing' => 'West',
            'sex' => Room::SEX_MALE,
            'capacity' => 6,
            'status' => Room::STATUS_OPEN,
        ]);

        $oldBed = Bed::create([
            'room_id' => $oldRoom->id,
            'bed_label' => 'A',
            'is_occupied' => true,
            'occupant_tenant_id' => $tenant->id,
        ]);

        $oldAssignment = Assignment::create([
            'tenant_id' => $tenant->id,
            'room_id' => $oldRoom->id,
            'bed_id' => $oldBed->id,
            'start_date' => now()->subWeek()->toDateString(),
            'is_active' => true,
        ]);

        $newRoom = Room::create([
            'code' => 'B-200',
            'building' => 'Annex',
            'floor' => '2',
            'wing' => 'North',
            'sex' => Room::SEX_MALE,
            'capacity' => 6,
            'status' => Room::STATUS_OPEN,
        ]);

        $newBed = Bed::create([
            'room_id' => $newRoom->id,
            'bed_label' => 'B',
            'is_occupied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.rooms.assign', $newRoom), [
            'tenant_id' => $tenant->id,
            'bed_id' => $newBed->id,
        ]);

        $response->assertRedirect(route('admin.rooms.show', $newRoom));

        $this->assertDatabaseHas('assignments', [
            'id' => $oldAssignment->id,
            'is_active' => false,
            'moved_out_reason' => 'admin_reassignment',
        ]);

        $this->assertDatabaseHas('beds', [
            'id' => $oldBed->id,
            'is_occupied' => false,
            'occupant_tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assignments', [
            'tenant_id' => $tenant->id,
            'room_id' => $newRoom->id,
            'bed_id' => $newBed->id,
            'is_active' => true,
        ]);
    }
}
