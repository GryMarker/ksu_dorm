<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InterviewResultMail;
use App\Models\Interview;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index(): View
    {
        $interviews = Interview::with(['tenant.user', 'interviewer', 'slot'])
            ->orderByRaw('CASE WHEN conducted_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('scheduled_at')
            ->paginate(20);

        return view('admin.interviews.index', [
            'interviews' => $interviews,
        ]);
    }

    public function result(Request $request, Interview $interview): RedirectResponse
    {
        $validated = $request->validate([
            'result' => ['required', Rule::in(['approved', 'rejected', 'recheck'])],
            'notes' => ['nullable', 'string'],
            'conducted_at' => ['nullable', 'date'],
        ]);

        $interview->fill([
            'result' => $validated['result'],
            'notes' => $validated['notes'] ?? $interview->notes,
            'conducted_at' => $validated['conducted_at'] ? Carbon::parse($validated['conducted_at']) : Carbon::now(),
            'interviewer_user_id' => $request->user()->id,
        ])->save();

        $tenant = $interview->tenant;

        $tenant->admission_status = match ($validated['result']) {
            'approved' => Tenant::STATUS_APPROVED,
            'rejected' => Tenant::STATUS_REJECTED,
            'recheck' => Tenant::STATUS_RECHECK,
        };
        $tenant->save();

        NotificationService::queueMail(
            $tenant->user,
            new InterviewResultMail($tenant, $interview),
            'interview.result',
            [
                'tenant_id' => $tenant->id,
                'interview_id' => $interview->id,
                'result' => $validated['result'],
            ]
        );

        return redirect()->route('admin.interviews.index')->with('status', 'Interview result saved.');
    }
}


