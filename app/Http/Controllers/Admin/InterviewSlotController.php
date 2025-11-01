<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterviewSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InterviewSlotController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dorm_master')->except(['index']);
    }

    public function index(): View
    {
        $slots = InterviewSlot::withCount('interviews')
            ->orderByDesc('starts_at')
            ->paginate(15);

        return view('admin.interview-slots.index', [
            'slots' => $slots,
        ]);
    }

    public function create(): View
    {
        return view('admin.interview-slots.create', [
            'slot' => new InterviewSlot(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSlot($request);

        InterviewSlot::create($data);

        return redirect()->route('admin.interview-slots.index')->with('status', 'Interview slot created successfully.');
    }

    public function edit(InterviewSlot $interviewSlot): View
    {
        return view('admin.interview-slots.edit', [
            'slot' => $interviewSlot,
        ]);
    }

    public function update(Request $request, InterviewSlot $interviewSlot): RedirectResponse
    {
        $data = $this->validateSlot($request, $interviewSlot);

        $interviewSlot->update($data);

        return redirect()->route('admin.interview-slots.index')->with('status', 'Interview slot updated successfully.');
    }

    public function destroy(InterviewSlot $interviewSlot): RedirectResponse
    {
        if ($interviewSlot->interviews()->exists()) {
            return redirect()
                ->route('admin.interview-slots.index')
                ->withErrors('Cannot delete a slot that already has interviews booked.');
        }

        $interviewSlot->delete();

        return redirect()->route('admin.interview-slots.index')->with('status', 'Interview slot removed.');
    }

    private function validateSlot(Request $request, ?InterviewSlot $slot = null): array
    {
        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::in(['open', 'closed'])],
        ]);

        return $data;
    }
}
