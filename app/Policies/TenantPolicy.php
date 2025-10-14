<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDormMaster() || $user->isDirector() || $user->isTenant();
    }

    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->isDormMaster() || $user->isDirector()) {
            return true;
        }

        return $user->isTenant() && $tenant->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isDormMaster();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->isDormMaster()) {
            return true;
        }

        return $user->isTenant() && $tenant->user_id === $user->id;
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->isDormMaster();
    }
}
