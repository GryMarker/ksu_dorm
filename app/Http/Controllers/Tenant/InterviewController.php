<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\InterviewScheduledMail;
use App\Models\InterviewSlot;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function listOpenSlots(Request $request): View|RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        if (!in_array($tenant->onboarding_status, [Tenant::STATUS_FOR_INTERVIEW, Tenant::STATUS_APPROVED], true)) {
            return redirect()
                ->route('tenant.apply.status')
                ->withErrors('Your application is pending Dorm Master review before you can pick an interview slot.');
        }
        $currentInterview = $tenant->interviews()
            ->whereNull('result')
            ->latest('scheduled_at')
            ->with('slot')
            ->first();
        $now = now();

        $slots = InterviewSlot::open()
            ->where('starts_at', '>=', $now)
            ->withCount('interviews')
            ->orderBy('starts_at')
            ->get()
            ->map(function (InterviewSlot $slot) use ($currentInterview) {
                $bookedCount = $slot->interviews_count;

                if ($currentInterview && $currentInterview->slot_id === $slot->id) {
                    $bookedCount = max(0, $bookedCount - 1);
                }

                $slot->remaining_capacity = max(0, $slot->capacity - $bookedCount);

                return $slot;
            });

        return view('tenant.apply.slots', [
            'tenant' => $tenant,
            'slots' => $slots,
            'currentInterview' => $currentInterview,
        ]);
    }

    public function bookSlot(Request $request, InterviewSlot $slot): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        if (!in_array($tenant->onboarding_status, [Tenant::STATUS_FOR_INTERVIEW, Tenant::STATUS_APPROVED], true)) {
            return redirect()
                ->route('tenant.apply.status')
                ->withErrors('Your application is pending Dorm Master review before you can book an interview.');
        }

        if ($slot->status !== 'open') {
            return redirect()->route('tenant.apply.slots')->withErrors('This slot is no longer available.');
        }

        $currentInterview = $tenant->interviews()
            ->whereNull('result')
            ->latest('scheduled_at')
            ->first();
        $bookedCount = $slot->interviews()->count();

        $isSameSlot = $currentInterview && $currentInterview->slot_id === $slot->id;

        if (!$isSameSlot && $bookedCount >= $slot->capacity) {
            return redirect()->route('tenant.apply.slots')->withErrors('Selected slot is already full.');
        }

        if ($currentInterview) {
            $currentInterview->fill([
                'slot_id' => $slot->id,
                'scheduled_at' => $slot->starts_at,
            ])->save();
        } else {
            $currentInterview = $tenant->interviews()->create([
                'slot_id' => $slot->id,
                'scheduled_at' => $slot->starts_at,
            ]);
        }

        if (in_array($tenant->onboarding_status, [Tenant::STATUS_DRAFT, Tenant::STATUS_RECHECK], true)) {
            $tenant->onboarding_status = Tenant::STATUS_FOR_INTERVIEW;
            $tenant->save();
        }

        $currentInterview->setRelation('slot', $slot);

        NotificationService::queueMail(
            $tenant->user,
            new InterviewScheduledMail($tenant, $currentInterview),
            'interview.scheduled',
            [
                'tenant_id' => $tenant->id,
                'slot_id' => $slot->id,
                'scheduled_at' => $slot->starts_at->toIso8601String(),
            ]
        );

        return redirect()->route('tenant.apply.status')->with('status', 'Interview slot booked successfully.');
    }
}
