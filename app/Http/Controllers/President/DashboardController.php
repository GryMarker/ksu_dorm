<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCottage;
use App\Models\EmployeePayment;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'total_employees' => Tenant::where('type', Tenant::TYPE_EMPLOYEE)->count(),
            'approved_employees' => Tenant::where('type', Tenant::TYPE_EMPLOYEE)
                ->where('onboarding_status', Tenant::STATUS_APPROVED)
                ->count(),
            'pending_onboarding' => Tenant::where('type', Tenant::TYPE_EMPLOYEE)
                ->whereIn('onboarding_status', [Tenant::STATUS_FOR_APPROVAL, Tenant::STATUS_RECHECK])
                ->count(),
            'pending_payments' => EmployeePayment::where('status', EmployeePayment::STATUS_PENDING)->count(),
            'pending_cottages' => EmployeeCottage::where('status', EmployeeCottage::STATUS_REQUESTED)->count(),
            'occupied_cottages' => EmployeeCottage::where('status', EmployeeCottage::STATUS_OCCUPIED)->count(),
        ];

        $recentEmployees = Tenant::with('user')
            ->where('type', Tenant::TYPE_EMPLOYEE)
            ->latest('updated_at')
            ->take(5)
            ->get();

        $recentPayments = EmployeePayment::with(['tenant.user'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('president.dashboard', [
            'stats' => $stats,
            'recentEmployees' => $recentEmployees,
            'recentPayments' => $recentPayments,
        ]);
    }
}
