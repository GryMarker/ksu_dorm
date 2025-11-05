<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeApplyRequest;
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

    public function submit(EmployeeApplyRequest $request): RedirectResponse
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
            'course_year' => $data['department'],
            'cellphone' => $data['cellphone'],
            'phone' => $data['cellphone'],
            'father_name' => null,
            'father_contact' => null,
            'mother_name' => null,
            'mother_contact' => null,
        ]);

        $tenant->monthly_rate = $tenant->monthly_rate ?? Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE;
        $tenant->salary_deduction = $request->boolean('salary_deduction');

        $familyMembers = collect(preg_split('/\r\n|\r|\n/', (string) ($data['family_members'] ?? '')))
            ->map(static fn ($member) => trim($member))
            ->filter()
            ->values()
            ->all();

        $tenant->family_members = ! empty($familyMembers) ? $familyMembers : null;

        $tenant->policy_accepted_at = Carbon::now();

        if (in_array($tenant->onboarding_status, [Tenant::STATUS_DRAFT, Tenant::STATUS_RECHECK], true)) {
            $tenant->onboarding_status = Tenant::STATUS_FOR_APPROVAL;
        }

        $tenant->save();
        $tenant->load('cottage', 'cottageRequest');

        foreach ([$tenant->cottage, $tenant->cottageRequest] as $cottage) {
            if ($cottage) {
                $cottage->forceFill([
                    'family_members' => $tenant->family_members ?: [],
                ])->save();
            }
        }

        $user = $request->user();
        if ($user->name !== $data['full_name']) {
            $user->forceFill(['name' => $data['full_name']])->save();
        }

        return redirect()->route('employee.status')
            ->with('status', 'Application submitted. Awaiting president approval.');
    }
}
