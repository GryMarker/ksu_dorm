<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayment;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->with(['cottage', 'cottageRequest'])->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        $recentPayments = $tenant->employeePayments()
            ->orderByDesc('billing_month')
            ->take(6)
            ->get();

        $hasCurrentMonthRecord = $tenant->employeePayments()
            ->whereDate('billing_month', now()->startOfMonth())
            ->exists();

        $pendingPayment = $tenant->employeePayments()
            ->where('status', EmployeePayment::STATUS_PENDING)
            ->orderByDesc('billing_month')
            ->first();

        return view('employee.dashboard', [
            'tenant' => $tenant,
            'recentPayments' => $recentPayments,
            'pendingPayment' => $pendingPayment,
            'hasCurrentMonthRecord' => $hasCurrentMonthRecord,
        ]);
    }
}
