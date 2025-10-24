<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ReservationStatusMail;
use App\Mail\TransferStatusMail;
use App\Models\Assignment;
use App\Models\Bed;
use App\Models\Reservation;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservationDecisionController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::with(['tenant.user', 'room.beds', 'bed'])
            ->where('status', Reservation::STATUS_PENDING)
            ->orderBy('requested_at')
            ->paginate(20);

        return view('admin.reservations.index', [
            'reservations' => $reservations,
        ]);
    }

    public function approve(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->back()->withErrors('Reservation is no longer pending.');
        }

        $validated = $request->validate([
            'bed_id' => ['nullable', 'exists:beds,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenant = $reservation->tenant()->with(['user', 'activeAssignment.bed'])->firstOrFail();
        $deciderId = $request->user()->id;

        DB::transaction(function () use ($reservation, $tenant, $validated, $deciderId) {
            $room = $reservation->room()->with('beds')->lockForUpdate()->firstOrFail();

            $bed = $this->resolveBed($room->beds, $validated['bed_id'] ?? $reservation->bed_id);

            if (!$bed) {
                abort(400, 'No available beds for this room.');
            }

            if ($reservation->type === Reservation::TYPE_TRANSFER) {
                $this->handleTransfer($tenant, $reservation, $bed);
            } else {
                $this->handleInitialAssignment($tenant, $reservation, $bed);
            }

            $reservation->update([
                'status' => Reservation::STATUS_APPROVED,
                'bed_id' => $bed->id,
                'decided_at' => Carbon::now(),
                'decided_by' => $deciderId,
                'notes' => $validated['notes'] ?? $reservation->notes,
            ]);
        });

        $reservation->loadMissing(['room', 'bed', 'tenant.user']);

        $mail = $reservation->type === Reservation::TYPE_TRANSFER
            ? new TransferStatusMail($reservation, 'approved')
            : new ReservationStatusMail($reservation, 'approved');

        NotificationService::queueMail(
            $reservation->tenant->user,
            $mail,
            $reservation->type === Reservation::TYPE_TRANSFER ? 'transfer.approved' : 'reservation.approved',
            [
                'reservation_id' => $reservation->id,
                'status' => 'approved',
                'room_id' => $reservation->room_id,
                'bed_id' => $reservation->bed_id,
            ]
        );

        return redirect()->route('admin.reservations.index')->with('status', 'Reservation approved.');
    }

    public function decline(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->back()->withErrors('Reservation is no longer pending.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $reservation->update([
            'status' => Reservation::STATUS_DECLINED,
            'decided_at' => Carbon::now(),
            'decided_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? $reservation->notes,
        ]);

        $reservation->loadMissing(['room', 'bed', 'tenant.user']);

        $mail = $reservation->type === Reservation::TYPE_TRANSFER
            ? new TransferStatusMail($reservation, 'declined')
            : new ReservationStatusMail($reservation, 'declined');

        NotificationService::queueMail(
            $reservation->tenant->user,
            $mail,
            $reservation->type === Reservation::TYPE_TRANSFER ? 'transfer.declined' : 'reservation.declined',
            [
                'reservation_id' => $reservation->id,
                'status' => 'declined',
            ]
        );

        return redirect()->route('admin.reservations.index')->with('status', 'Reservation declined.');
    }

    private function resolveBed(Collection $beds, ?int $bedId): ?Bed
    {
        if ($bedId) {
            $bed = $beds->firstWhere('id', $bedId);
            if (!$bed || $bed->is_occupied) {
                abort(400, 'Selected bed is not available.');
            }

            return $bed;
        }

        return $beds->first(fn (Bed $bed) => $bed->is_occupied === false);
    }

    private function handleInitialAssignment($tenant, Reservation $reservation, Bed $bed): void
    {
        $currentAssignment = $tenant->activeAssignment;

        if ($currentAssignment) {
            if ($currentAssignment->room_id === $reservation->room_id && $currentAssignment->bed_id === $bed->id) {
                if ($bed->is_occupied === false || $bed->occupant_tenant_id !== $tenant->id) {
                    $this->activateBed($bed, $tenant->id);
                }

                return;
            }

            $this->releaseAssignment($currentAssignment, 'reassignment');
        }

        $this->activateBed($bed, $tenant->id);

        Assignment::create([
            'tenant_id' => $tenant->id,
            'room_id' => $reservation->room_id,
            'bed_id' => $bed->id,
            'start_date' => Carbon::today(),
            'is_active' => true,
        ]);
    }

    private function handleTransfer($tenant, Reservation $reservation, Bed $bed): void
    {
        $currentAssignment = $tenant->activeAssignment;

        if (!$currentAssignment) {
            abort(400, 'Tenant has no active assignment to transfer from.');
        }

        $this->releaseAssignment($currentAssignment, 'transfer');

        $this->activateBed($bed, $tenant->id);

        Assignment::create([
            'tenant_id' => $tenant->id,
            'room_id' => $reservation->room_id,
            'bed_id' => $bed->id,
            'start_date' => Carbon::today(),
            'is_active' => true,
        ]);
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


