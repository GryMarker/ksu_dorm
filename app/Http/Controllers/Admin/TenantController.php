<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $tenants = Tenant::with('user')
            ->where('type', Tenant::TYPE_STUDENT)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', "%{$search}%")
                        ->orWhere('university_id_no', 'like', "%{$search}%")
                        ->orWhere('course_year', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                });
            })
            ->orderBy('full_name')
            ->paginate(15);

        return view('admin.students.index', [
            'tenants' => $tenants,
            'search' => $search,
        ]);
    }

    public function history(Tenant $tenant): View
    {
        $tenant->load([
            'user',
            'interviews' => fn ($query) => $query->orderByDesc('scheduled_at'),
            'reservations' => fn ($query) => $query->latest('requested_at'),
            'assignments' => fn ($query) => $query->latest('start_date'),
        ]);

        $attendanceLogs = $tenant->attendanceLogs()
            ->orderByDesc('timestamp')
            ->limit(100)
            ->get()
            ->reverse();

        return view('admin.students.history', [
            'tenant' => $tenant,
            'attendanceLogs' => $attendanceLogs,
        ]);
    }
}
