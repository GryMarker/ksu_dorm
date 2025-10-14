<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApplyRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ApplyController extends Controller
{
    public function showForm(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        return view('tenant.apply.form', [
            'tenant' => $tenant,
        ]);
    }

    public function submit(TenantApplyRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();
        $data = $request->validated();

        $tenant->fill([
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'] ?? null,
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
            'policy_accepted_at' => Carbon::now(),
            'phone' => $data['cellphone'],
        ]);

        if (in_array($tenant->admission_status, [Tenant::STATUS_DRAFT, Tenant::STATUS_RECHECK], true)) {
            $tenant->admission_status = Tenant::STATUS_FOR_INTERVIEW;
        }

        $tenant->save();

        $user = $request->user();
        if ($user->name !== $data['full_name']) {
            $user->forceFill(['name' => $data['full_name']])->save();
        }

        return redirect()->route('tenant.apply.slots')->with('status', 'Application submitted. Please choose an interview slot.');
    }

    public function status(Request $request): View
    {
        $tenant = $request->user()->tenant()->with(['interviews.slot' => fn ($query) => $query->orderBy('starts_at', 'desc')])->firstOrFail();
        $latestInterview = $tenant->interviews()->latest('scheduled_at')->first();

        return view('tenant.apply.status', [
            'tenant' => $tenant,
            'latestInterview' => $latestInterview,
        ]);
    }
}
