<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const DEFAULT_BEDS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function __construct()
    {
        $this->authorizeResource(Room::class, 'room');
    }

    public function index(): View
    {
        $rooms = Room::withCount([
            'beds as occupied_beds_count' => fn ($query) => $query->where('is_occupied', true),
            'beds as bed_count',
            'assignments as active_assignments_count' => fn ($query) => $query->where('is_active', true),
        ])->orderBy('building')->orderBy('code')->get();

        return view('admin.rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): View
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRoom($request);

        $room = Room::create($data);

        $this->syncBeds($room);

        return redirect()->route('admin.rooms.index')->with('status', 'Room created successfully.');
    }

    public function show(Room $room): View
    {
        $room->load([
            'beds' => fn ($query) => $query->with('occupant.user')->orderBy('bed_label'),
            'assignments' => fn ($query) => $query->with(['tenant.user', 'bed'])->orderByDesc('start_date'),
        ]);

        return view('admin.rooms.show', [
            'room' => $room,
            'assignableStudents' => Tenant::query()
                ->with(['user', 'activeAssignment.room'])
                ->where('type', Tenant::TYPE_STUDENT)
                ->where('onboarding_status', Tenant::STATUS_APPROVED)
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    public function assign(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('assign', $room);

        if ($room->status !== Room::STATUS_OPEN) {
            return redirect()->back()->withErrors('Only open rooms can receive direct student assignments.');
        }

        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'bed_id' => ['required', 'exists:beds,id'],
        ]);

        $tenant = Tenant::query()
            ->with(['user', 'activeAssignment.bed'])
            ->where('id', $validated['tenant_id'])
            ->where('type', Tenant::TYPE_STUDENT)
            ->where('onboarding_status', Tenant::STATUS_APPROVED)
            ->first();

        if (! $tenant) {
            return redirect()->back()->withErrors('Selected student is not eligible for direct room assignment.');
        }

        DB::transaction(function () use ($room, $tenant, $validated) {
            $lockedRoom = Room::query()
                ->whereKey($room->id)
                ->with('beds')
                ->lockForUpdate()
                ->firstOrFail();

            $bed = $lockedRoom->beds->firstWhere('id', (int) $validated['bed_id']);

            if (! $bed) {
                abort(400, 'Selected bed does not belong to this room.');
            }

            if ($bed->is_occupied && $bed->occupant_tenant_id !== $tenant->id) {
                abort(400, 'Selected bed is already occupied.');
            }

            $currentAssignment = $tenant->activeAssignment;

            if ($currentAssignment) {
                if ($currentAssignment->room_id === $lockedRoom->id && $currentAssignment->bed_id === $bed->id) {
                    if (! $bed->is_occupied || $bed->occupant_tenant_id !== $tenant->id) {
                        $this->activateBed($bed, $tenant->id);
                    }

                    return;
                }

                $this->releaseAssignment($currentAssignment, 'admin_reassignment');
            }

            $this->activateBed($bed, $tenant->id);

            Assignment::create([
                'tenant_id' => $tenant->id,
                'room_id' => $lockedRoom->id,
                'bed_id' => $bed->id,
                'start_date' => Carbon::today(),
                'is_active' => true,
            ]);
        });

        return redirect()
            ->route('admin.rooms.show', $room)
            ->with('status', 'Student assigned to room successfully.');
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.edit', [
            'room' => $room,
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $data = $this->validateRoom($request, $room);

        $room->update($data);

        $this->syncBeds($room);

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Room updated successfully.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->assignments()->where('is_active', true)->exists()) {
            return redirect()->back()->withErrors('Cannot delete a room with active assignments.');
        }

        $room->beds()->delete();
        $room->delete();

        return redirect()->route('admin.rooms.index')->with('status', 'Room deleted.');
    }

    private function validateRoom(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique(Room::class)->ignore($room?->id)],
            'building' => ['required', 'string', 'max:100'],
            'floor' => ['required', 'string', 'max:50'],
            'wing' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::in([Room::GENDER_MALE, Room::GENDER_FEMALE, Room::GENDER_MIXED])],
            'capacity' => ['required', 'integer', 'min:1', 'max:6'],
            'status' => ['required', Rule::in([Room::STATUS_OPEN, Room::STATUS_CLOSED, Room::STATUS_MAINTENANCE])],
        ]);
    }

    private function syncBeds(Room $room): void
    {
        foreach (self::DEFAULT_BEDS as $label) {
            Bed::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'bed_label' => $label,
                ],
                []
            );
        }

        $room->beds()->whereNotIn('bed_label', self::DEFAULT_BEDS)->delete();
    }

    private function activateBed(Bed $bed, int $tenantId): void
    {
        $bed->update([
            'is_occupied' => true,
            'occupant_tenant_id' => $tenantId,
        ]);
    }

    private function releaseAssignment(Assignment $assignment, string $reason): void
    {
        $assignment->update([
            'is_active' => false,
            'end_date' => Carbon::today(),
            'moved_out_reason' => $reason,
        ]);

        if ($assignment->bed) {
            $assignment->bed->update([
                'is_occupied' => false,
                'occupant_tenant_id' => null,
            ]);
        }
    }
}
