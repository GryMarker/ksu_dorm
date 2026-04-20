<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class TransferController extends Controller
{
    public function store(TransferRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->with(['activeAssignment.bed', 'activeAssignment.room', 'reservations'])->firstOrFail();

        $currentAssignment = $tenant->activeAssignment;
        if (!$currentAssignment) {
            return redirect()->back()->withErrors('You must be assigned to a room before requesting a transfer.');
        }

        $pendingTransfer = $tenant->reservations->firstWhere(fn ($reservation) => $reservation->type === Reservation::TYPE_TRANSFER && $reservation->status === Reservation::STATUS_PENDING);
        if ($pendingTransfer) {
            return redirect()->back()->withErrors('You already have a pending transfer request.');
        }

        $room = Room::with('beds')->findOrFail($request->input('room_id'));

        if ($room->status !== Room::STATUS_OPEN) {
            return redirect()->back()->withErrors('Selected room is not open for transfers.');
        }

        if ($tenant->sex && $room->sex !== $tenant->sex) {
            return redirect()->back()->withErrors('Selected room is not available for your sex.');
        }

        $selectedBed = null;
        if ($request->filled('bed_id')) {
            $selectedBed = $room->beds->firstWhere('id', (int) $request->input('bed_id'));
            if (!$selectedBed) {
                return redirect()->back()->withErrors('Selected bed does not belong to the chosen room.');
            }

            if ($selectedBed->is_occupied) {
                return redirect()->back()->withErrors('Selected bed is already occupied.');
            }
        }

        if ($room->id === $currentAssignment->room_id && (!$selectedBed || $selectedBed->id === $currentAssignment->bed_id)) {
            return redirect()->back()->withErrors('Please choose a different room or bed for transfer.');
        }

        $tenant->reservations()->create([
            'room_id' => $room->id,
            'bed_id' => $selectedBed?->id,
            'type' => Reservation::TYPE_TRANSFER,
            'status' => Reservation::STATUS_PENDING,
            'requested_at' => Carbon::now(),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('tenant.availability')->with('status', 'Transfer request submitted.');
    }
}

