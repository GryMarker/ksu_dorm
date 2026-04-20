<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationSubmittedMail;
use App\Http\Requests\TenantApplyRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ApplyController extends Controller
{
    public function showForm(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_STUDENT, 403);

        return view('tenant.apply.form', [
            'tenant' => $tenant,
            'hasInterviewSlot' => $tenant->interviews()->whereNotNull('slot_id')->exists(),
        ]);
    }

    public function submit(TenantApplyRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_STUDENT, 403);
        $data = $request->validated();

        $tenant->fill([
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'] ?? null,
            'sex' => $data['sex'],
            'dob' => $data['dob'],
            'home_address' => $data['home_address'],
            'age' => $data['age'],
            'place_of_birth' => $data['place_of_birth'],
            'father_name' => $data['father_name'],
            'father_contact' => $data['father_contact'],
            'mother_name' => $data['mother_name'],
            'mother_contact' => $data['mother_contact'],
            'program' => $data['program'],
            'year_level' => $data['year_level'],
            'course_year' => $data['program'] . ' - Year ' . $data['year_level'],
            'university_id_no' => $data['university_id_no'],
            'cellphone' => $data['cellphone'],
            'policy_accepted_at' => Carbon::now(),
            'phone' => $data['cellphone'],
        ]);

        if (in_array($tenant->onboarding_status, [Tenant::STATUS_DRAFT, Tenant::STATUS_RECHECK], true)) {
            // Submission moves the application to Dorm Master review
            $tenant->onboarding_status = Tenant::STATUS_FOR_APPROVAL;
        }

        $tenant->save();

        $user = $request->user();
        if ($user->name !== $data['full_name']) {
            $user->forceFill(['name' => $data['full_name']])->save();
        }

        $dormMasters = User::where('role', User::ROLE_DORM_MASTER)->get();
        foreach ($dormMasters as $dormMaster) {
            NotificationService::queueMail(
                $dormMaster,
                new ApplicationSubmittedMail($tenant),
                'application.submitted',
                [
                    'tenant_id' => $tenant->id,
                    'student_id' => $tenant->university_id_no,
                ]
            );
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
