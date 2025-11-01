<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationStoreRequest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function availability(Request $request): View
    {
        $tenant = $request->user()->tenant()->with([
            'reservations' => fn ($query) => $query->latest('requested_at'),
            'activeAssignment.room',
            'activeAssignment.bed',
        ])->firstOrFail();

        $rooms = Room::withCount([
            'beds as occupied_beds_count' => fn ($query) => $query->where('is_occupied', true),
            'beds as bed_count',
        ])->with(['beds' => fn ($query) => $query->orderBy('bed_label')])
            ->where('status', Room::STATUS_OPEN)
            ->when($tenant->gender, fn ($query, $gender) => $query->where('gender', $gender))
            ->get();

        return view('tenant.availability', [
            'tenant' => $tenant,
            'rooms' => $rooms,
        ]);
    }

    public function store(ReservationStoreRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->with('reservations')->firstOrFail();

        if ($tenant->onboarding_status !== $tenant::STATUS_APPROVED) {
            return redirect()->back()->withErrors('You must be approved before reserving a room.');
        }

        $room = Room::with(['beds'])->findOrFail($request->input('room_id'));

        if ($room->status !== Room::STATUS_OPEN) {
            return redirect()->back()->withErrors('Room is not available for reservation.');
        }

        if ($tenant->gender && $room->gender !== $tenant->gender) {
            return redirect()->back()->withErrors('Selected room is not available for your gender.');
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

        $currentActive = $room->beds->where('is_occupied', true)->count();
        if ($currentActive >= $room->capacity) {
            return redirect()->back()->withErrors('Room capacity has already been reached.');
        }

        $existingPending = $tenant->reservations->firstWhere(fn ($reservation) => $reservation->status === Reservation::STATUS_PENDING && $reservation->type === Reservation::TYPE_INITIAL);
        if ($existingPending) {
            return redirect()->back()->withErrors('You already have a pending reservation.');
        }

        $tenant->reservations()->create([
            'room_id' => $room->id,
            'bed_id' => $selectedBed?->id,
            'type' => Reservation::TYPE_INITIAL,
            'status' => Reservation::STATUS_PENDING,
            'requested_at' => Carbon::now(),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('tenant.availability')->with('status', 'Reservation submitted for approval.');
    }

    public function myRoom(Request $request): View
    {
        $tenant = $request->user()->tenant()->with(['activeAssignment.room', 'activeAssignment.bed'])->firstOrFail();

        return view('tenant.my-room', [
            'tenant' => $tenant,
        ]);
    }
}
