<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDormMaster() || $user->isDirector() || $user->isTenant();
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->isDormMaster() || $user->isDirector()) {
            return true;
        }

        return $user->isTenant() && $reservation->tenant?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->isDormMaster();
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        if ($user->isDormMaster()) {
            return true;
        }

        return $user->isTenant() && $reservation->status === Reservation::STATUS_PENDING && $reservation->tenant?->user_id === $user->id;
    }
}
