<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\InterviewScheduleRequest;
use App\Http\Requests\TenantAdmissionRequest;
use App\Mail\InterviewScheduledMail;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user()->tenant()->with(['interviews' => fn ($query) => $query->latest('scheduled_at'), 'activeAssignment.room', 'activeAssignment.bed'])->firstOrFail();

        $upcomingInterview = $tenant->interviews->first();

        return view('tenant.admission', [
            'tenant' => $tenant,
            'upcomingInterview' => $upcomingInterview,
        ]);
    }

    public function update(TenantAdmissionRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        $data = $request->validated();

        $tenant->fill([
            'type' => $data['type'],
            'university_id_no' => $data['university_id_no'],
            'program' => $data['program'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'phone' => $data['phone'],
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
            'medical_notes' => $data['medical_notes'] ?? null,
            'admission_form_json' => $data['admission_form'] ?? [],
        ]);

        $action = $request->input('action', 'save');

        if ($action === 'submit' && in_array($tenant->admission_status, [Tenant::STATUS_DRAFT, Tenant::STATUS_RECHECK], true)) {
            $tenant->admission_status = Tenant::STATUS_FOR_INTERVIEW;
        }

        $tenant->save();

        return redirect()->route('tenant.admission.edit')->with('status', 'Admission details updated.');
    }

    public function schedule(InterviewScheduleRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        $scheduledAt = Carbon::parse($request->input('scheduled_at'));

        $interview = $tenant->interviews()->create([
            'scheduled_at' => $scheduledAt,
            'notes' => $request->input('notes'),
        ]);

        if ($tenant->admission_status !== Tenant::STATUS_APPROVED) {
            $tenant->admission_status = Tenant::STATUS_FOR_INTERVIEW;
            $tenant->save();
        }

        NotificationService::queueMail(
            $tenant->user,
            new InterviewScheduledMail($tenant, $interview),
            'interview.scheduled',
            [
                'tenant_id' => $tenant->id,
                'interview_id' => $interview->id,
                'scheduled_at' => $scheduledAt->toIso8601String(),
            ]
        );

        return redirect()->route('tenant.admission.edit')->with('status', 'Interview schedule saved.');
    }
}

