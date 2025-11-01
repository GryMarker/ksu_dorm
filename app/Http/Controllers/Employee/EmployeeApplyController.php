<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApplyRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeeApplyController extends Controller
{
    public function showForm(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        return view('employee.apply.form', [
            'tenant' => $tenant,
        ]);
    }

    public function submit(TenantApplyRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        $data = $request->validated();

        $tenant->fill([
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'] ?? null,
            'gender' => $data['gender'],
            'dob' => $data['dob'],
            'home_address' => $data['home_address'],
            'age' => $data['age'],
            'place_of_birth' => $data['place_of_birth'],
            'father_name' => $data['father_name'],
            'father_contact' => $data['father_contact'],
            'mother_name' => $data['mother_name'],
            'mother_contact' => $data['mother_contact'],
            'course_year' => $data['course_year'],
            'cellphone' => $data['cellphone'],
            'phone' => $data['cellphone'],
        ]);

        $tenant->policy_accepted_at = Carbon::now();

        if (in_array($tenant->onboarding_status, [Tenant::STATUS_DRAFT, Tenant::STATUS_RECHECK], true)) {
            $tenant->onboarding_status = Tenant::STATUS_FOR_APPROVAL;
        }

        $tenant->save();

        $user = $request->user();
        if ($user->name !== $data['full_name']) {
            $user->forceFill(['name' => $data['full_name']])->save();
        }

        return redirect()->route('employee.status')
            ->with('status', 'Application submitted. Awaiting president approval.');
    }
}
