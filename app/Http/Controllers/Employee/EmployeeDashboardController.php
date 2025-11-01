<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        return view('employee.dashboard', [
            'tenant' => $tenant,
        ]);
    }
}
