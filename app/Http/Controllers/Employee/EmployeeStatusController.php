<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeStatusController extends Controller
{
    public function show(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        return view('employee.status', [
            'tenant' => $tenant,
        ]);
    }
}
