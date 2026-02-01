<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationRejectedMail;
use App\Mail\InterviewScheduledMail;
use App\Models\InterviewSlot;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::with(['user'])
            ->where('type', Tenant::TYPE_STUDENT)
            ->where('onboarding_status', Tenant::STATUS_FOR_APPROVAL)
            ->orderByDesc('updated_at')
            ->paginate(15);

        $openSlots = InterviewSlot::open()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->withCount('interviews')
            ->get();

        return view('admin.applications.index', [
            'tenants' => $tenants,
            'openSlots' => $openSlots,
        ]);
    }

    public function approve(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_if($tenant->type !== Tenant::TYPE_STUDENT, 404);

        if ($tenant->onboarding_status !== Tenant::STATUS_FOR_APPROVAL) {
            return redirect()->back()->withErrors('Application is not pending review.');
        }

        $validated = $request->validate([
            'slot_id' => ['nullable', 'exists:interview_slots,id'],
            'scheduled_at' => ['required_without:slot_id', 'nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $slot = null;
        $scheduledAt = null;

        if ($validated['slot_id'] ?? false) {
            $slot = InterviewSlot::withCount('interviews')->findOrFail($validated['slot_id']);

            if ($slot->interviews_count >= $slot->capacity) {
                return redirect()->back()->withErrors('Selected interview slot is already full.');
            }

            $scheduledAt = $slot->starts_at;
        } else {
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
        }

        $interview = $tenant->interviews()->create([
            'slot_id' => $slot?->id,
            'scheduled_at' => $scheduledAt,
            'notes' => $validated['notes'] ?? null,
        ]);

        $tenant->update(['onboarding_status' => Tenant::STATUS_FOR_INTERVIEW]);

        NotificationService::queueMail(
            $tenant->user,
            new InterviewScheduledMail($tenant, $interview),
            'interview.scheduled',
            [
                'tenant_id' => $tenant->id,
                'interview_id' => $interview->id,
                'scheduled_at' => $scheduledAt->toIso8601String(),
                'slot_id' => $slot?->id,
            ]
        );

        return redirect()
            ->route('admin.applications.index')
            ->with('status', 'Application approved and interview scheduled.');
    }

    public function reject(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_if($tenant->type !== Tenant::TYPE_STUDENT, 404);

        if ($tenant->onboarding_status === Tenant::STATUS_REJECTED) {
            return redirect()->back()->withErrors('Application is already rejected.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $tenant->update([
            'onboarding_status' => Tenant::STATUS_REJECTED,
        ]);

        $payload = [
            'tenant_id' => $tenant->id,
            'notes' => $validated['notes'] ?? null,
        ];

        NotificationService::queueMail(
            $tenant->user,
            new ApplicationRejectedMail($tenant, $validated['notes'] ?? null),
            'application.rejected',
            $payload
        );

        return redirect()
            ->route('admin.applications.index')
            ->with('status', 'Application rejected.');
    }
}
