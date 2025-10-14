<?php

namespace App\Providers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Policies\ReservationPolicy;
use App\Policies\RoomPolicy;
use App\Policies\TenantPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tenant::class => TenantPolicy::class,
        Room::class => RoomPolicy::class,
        Reservation::class => ReservationPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
