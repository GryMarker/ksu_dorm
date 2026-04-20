<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $employees = Tenant::with(['user', 'cottage', 'cottageRequest'])
            ->where('type', Tenant::TYPE_EMPLOYEE)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_id_number', 'like', "%{$search}%")
                        ->orWhere('course_year', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                });
            })
            ->orderBy('full_name')
            ->paginate(15);

        return view('president.employees.index', [
            'employees' => $employees,
            'search' => $search,
        ]);
    }

    public function history(Tenant $tenant): View
    {
        abort_unless($tenant->type === Tenant::TYPE_EMPLOYEE, 404);

        $tenant->load([
            'user',
            'cottage',
            'cottageRequest',
            'employeePayments' => fn ($query) => $query->latest('billing_month'),
        ]);

        return view('president.employees.history', [
            'tenant' => $tenant,
        ]);
    }
}
