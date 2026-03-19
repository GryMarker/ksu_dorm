<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDormMaster() || $user->isDirector();
    }

    public function view(User $user, Room $room): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isDormMaster();
    }

    public function update(User $user, Room $room): bool
    {
        return $user->isDormMaster();
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->isDormMaster();
    }

    public function assign(User $user, Room $room): bool
    {
        return $this->viewAny($user);
    }
}
