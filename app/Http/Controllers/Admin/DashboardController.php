<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Bed;
use App\Models\Interview;
use App\Models\Reservation;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'vacant_beds' => Bed::where('is_occupied', false)->count(),
            'pending_interviews' => Interview::whereNull('result')->count(),
            'pending_reservations' => Reservation::where('status', Reservation::STATUS_PENDING)->count(),
            'today_in' => AttendanceLog::whereDate('timestamp', Carbon::today())->where('type', 'in')->count(),
            'today_out' => AttendanceLog::whereDate('timestamp', Carbon::today())->where('type', 'out')->count(),
        ];

        $recentReservations = Reservation::with(['tenant.user', 'room'])
            ->latest('requested_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentReservations' => $recentReservations,
        ]);
    }
}
