<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\Bed;
use App\Models\InterviewSlot;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $dormMaster = User::factory()->create([
            'name' => 'Dorm Master',
            'email' => 'dormmaster@ksu.test',
            'role' => User::ROLE_DORM_MASTER,
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Student Director',
            'email' => 'director@ksu.test',
            'role' => User::ROLE_STUDENT_DIRECTOR,
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'University President',
            'email' => 'president@ksu.test',
            'role' => User::ROLE_PRESIDENT,
            'password' => Hash::make('password'),
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Sample Employee',
            'email' => 'employee@ksu.test',
            'role' => User::ROLE_EMPLOYEE,
            'password' => Hash::make('password'),
        ]);

        $employeeUser->tenant()->create([
            'full_name' => 'Sample Employee',
            'nickname' => 'Emp',
            'gender' => Tenant::GENDER_MALE,
            'dob' => Carbon::now()->subYears(30)->subMonths(2),
            'home_address' => 'KSU Administration Building',
            'age' => 30,
            'place_of_birth' => 'Tabuk City',
            'father_name' => 'Employee Father',
            'father_contact' => '0917-500-5000',
            'mother_name' => 'Employee Mother',
            'mother_contact' => '0918-600-6000',
            'course_year' => 'University Administration Office',
            'cellphone' => '0917-777-8888',
            'policy_accepted_at' => Carbon::now()->subDay(),
            'type' => Tenant::TYPE_EMPLOYEE,
            'employee_id_number' => 'EMP-1001',
            'university_id_no' => 'EMP-' . Str::upper(Str::random(6)),
            'program' => null,
            'year_level' => null,
            'phone' => '0917-777-8888',
            'emergency_contact_name' => 'Employee Emergency',
            'emergency_contact_phone' => '0918-555-4444',
            'medical_notes' => null,
            'onboarding_status' => Tenant::STATUS_FOR_APPROVAL,
            'admission_form_json' => ['reason' => 'Access dorm systems'],
        ]);

        $rooms = collect([
            ['code' => 'A-101', 'building' => 'Main', 'floor' => '1', 'wing' => 'North', 'gender' => Room::GENDER_MIXED],
            ['code' => 'B-201', 'building' => 'Main', 'floor' => '2', 'wing' => 'East', 'gender' => Room::GENDER_FEMALE],
            ['code' => 'C-301', 'building' => 'Annex', 'floor' => '3', 'wing' => 'West', 'gender' => Room::GENDER_MALE],
        ])->map(function (array $attributes) {
            $room = Room::create(array_merge($attributes, [
                'capacity' => 6,
                'status' => Room::STATUS_OPEN,
            ]));

            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $label) {
                Bed::updateOrCreate([
                    'room_id' => $room->id,
                    'bed_label' => $label,
                ]);
            }

            return $room;
        });

        $slots = collect();
        $baseDate = Carbon::now()->addDay()->setTime(9, 0);
        for ($i = 0; $i < 8; $i++) {
            $start = (clone $baseDate)->addDays(intdiv($i, 2))->addHours(($i % 2) * 2);
            $slots->push(InterviewSlot::create([
                'starts_at' => $start,
                'ends_at' => (clone $start)->addMinutes($i % 2 === 0 ? 30 : 60),
                'capacity' => $i % 2 === 0 ? 1 : 3,
                'status' => 'open',
            ]));
        }

        $draftUser = User::factory()->create([
            'name' => 'Draft Applicant',
            'email' => 'applicant@ksu.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_TENANT,
        ]);

        $draftUser->tenant()->create([
            'full_name' => 'Draft Applicant',
            'nickname' => 'Applicant',
            'gender' => Tenant::GENDER_FEMALE,
            'dob' => Carbon::now()->subYears(18)->subMonths(3),
            'home_address' => 'Tabuk City, Kalinga',
            'age' => 18,
            'place_of_birth' => 'Tabuk City',
            'father_name' => 'Juan Applicant',
            'father_contact' => '0917-800-0000',
            'mother_name' => 'Maria Applicant',
            'mother_contact' => '0918-900-0000',
            'course_year' => 'BSIT 1',
            'cellphone' => '0917-123-4567',
            'policy_accepted_at' => null,
            'type' => Tenant::TYPE_STUDENT,
            'university_id_no' => 'KSU-0000',
            'program' => 'BSIT',
            'year_level' => '1',
            'phone' => '0917-123-4567',
            'emergency_contact_name' => 'Guardian Applicant',
            'emergency_contact_phone' => '0918-123-4567',
            'medical_notes' => null,
            'onboarding_status' => Tenant::STATUS_DRAFT,
            'admission_form_json' => ['focus' => 'Initial application'],
        ]);

        $tenants = collect();
        for ($i = 1; $i <= 9; $i++) {
            $user = User::factory()->create([
                'name' => 'Tenant ' . $i,
                'email' => 'tenant' . $i . '@ksu.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TENANT,
            ]);

            $status = $i <= 5 ? Tenant::STATUS_APPROVED : Tenant::STATUS_FOR_INTERVIEW;

            $tenant = $user->tenant()->create([
                'full_name' => 'Tenant ' . $i,
                'nickname' => 'T' . $i,
                'gender' => $i % 2 === 0 ? Tenant::GENDER_MALE : Tenant::GENDER_FEMALE,
                'dob' => Carbon::now()->subYears(18 + $i)->subMonths($i),
                'home_address' => 'Barangay ' . $i . ', Tabuk City',
                'age' => 18 + $i,
                'place_of_birth' => 'Tabuk City',
                'father_name' => 'Father ' . $i,
                'father_contact' => '0917-111-11' . $i,
                'mother_name' => 'Mother ' . $i,
                'mother_contact' => '0918-222-22' . $i,
                'course_year' => 'BSIT ' . (($i % 4) + 1),
                'cellphone' => '0917-000-00' . $i,
                'policy_accepted_at' => Carbon::now()->subDays(rand(1, 15)),
                'type' => Tenant::TYPE_STUDENT,
                'university_id_no' => 'KSU-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'program' => 'BSIT',
                'year_level' => (string) (($i % 4) + 1),
                'phone' => '0917-000-00' . $i,
                'emergency_contact_name' => 'Contact ' . $i,
                'emergency_contact_phone' => '0918-100-00' . $i,
                'medical_notes' => Arr::random([null, 'Allergic to peanuts', 'Requires lower bunk']),
                'onboarding_status' => $status,
                'admission_form_json' => ['interest' => Arr::random(['Sports', 'Music', 'Reading'])],
            ]);

            $tenants->push($tenant);
        }

        $approvedTenants = $tenants->where('onboarding_status', Tenant::STATUS_APPROVED)->values();
        foreach ($approvedTenants->take(3) as $i => $tenant) {
            $room = $rooms[$i % $rooms->count()];
            $bed = $room->beds()->where('is_occupied', false)->first();

            if ($bed) {
                $bed->update([
                    'is_occupied' => true,
                    'occupant_tenant_id' => $tenant->id,
                ]);

                Assignment::create([
                    'tenant_id' => $tenant->id,
                    'room_id' => $room->id,
                    'bed_id' => $bed->id,
                    'start_date' => Carbon::today()->subDays(rand(5, 15)),
                    'is_active' => true,
                ]);
            }

            $tenant->reservations()->create([
                'room_id' => $room->id,
                'bed_id' => $bed?->id,
                'type' => Reservation::TYPE_INITIAL,
                'status' => Reservation::STATUS_APPROVED,
                'requested_at' => Carbon::now()->subDays(rand(10, 20)),
                'decided_at' => Carbon::now()->subDays(rand(1, 5)),
                'decided_by' => $dormMaster->id,
            ]);
        }

        $slotCapacities = $slots
            ->mapWithKeys(fn (InterviewSlot $slot) => [$slot->id => 0])
            ->toArray();

        $pendingTenants = $tenants->where('onboarding_status', Tenant::STATUS_FOR_INTERVIEW)->values();
        foreach ($pendingTenants as $tenant) {
            foreach ($slots as $slot) {
                if ($slotCapacities[$slot->id] < $slot->capacity) {
                    $tenant->interviews()->create([
                        'slot_id' => $slot->id,
                        'scheduled_at' => $slot->starts_at,
                        'notes' => 'Bring valid ID.',
                    ]);

                    $slotCapacities[$slot->id]++;
                    break;
                }
            }

            $tenant->reservations()->create([
                'room_id' => $rooms->random()->id,
                'type' => Reservation::TYPE_INITIAL,
                'status' => Reservation::STATUS_PENDING,
                'requested_at' => Carbon::now()->subDays(rand(1, 3)),
            ]);
        }

        foreach ($approvedTenants->take(3) as $tenant) {
            for ($i = 0; $i < 4; $i++) {
                $type = $i % 2 === 0 ? 'in' : 'out';
                AttendanceLog::create([
                    'tenant_id' => $tenant->id,
                    'type' => $type,
                    'timestamp' => Carbon::now()->subDays(rand(0, 6))->setTime(rand(6, 22), rand(0, 59)),
                    'mode' => 'manual',
                    'remarks' => null,
                ]);
            }
        }
    }
}




