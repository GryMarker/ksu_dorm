<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCottage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeCottageController extends Controller
{
    public function index(): View
    {
        $cottages = EmployeeCottage::with(['tenant.user', 'requestedTenant.user'])
            ->orderBy('code')
            ->get();

        return view('management.cottages.index', [
            'cottages' => $cottages,
            'pending' => $cottages->where('status', EmployeeCottage::STATUS_REQUESTED),
            'available' => $cottages->where('status', EmployeeCottage::STATUS_AVAILABLE),
            'occupied' => $cottages->where('status', EmployeeCottage::STATUS_OCCUPIED),
        ]);
    }

    public function approve(Request $request, EmployeeCottage $cottage): RedirectResponse
    {
        if ($cottage->status !== EmployeeCottage::STATUS_REQUESTED || ! $cottage->requestedTenant) {
            return redirect()
                ->route('management.cottages.index')
                ->withErrors(['cottage' => 'Only pending cottage requests can be approved.']);
        }

        $data = $request->validate([
            'family_members' => ['nullable', 'string'],
        ]);

        $familyMembers = collect(preg_split('/\r\n|\r|\n/', $data['family_members'] ?? ''))
            ->map(static fn (string $name) => trim($name))
            ->filter()
            ->values()
            ->all();

        $tenant = $cottage->requestedTenant;

        $existingAssignment = EmployeeCottage::query()
            ->where('id', '!=', $cottage->id)
            ->where(static function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id)
                    ->orWhere('requested_tenant_id', $tenant->id);
            })
            ->exists();

        if ($existingAssignment) {
            return redirect()
                ->route('management.cottages.index')
                ->withErrors(['cottage' => "{$tenant->full_name} already has an active cottage record. Release it first."]);
        }

        $resolvedFamily = ! empty($familyMembers)
            ? $familyMembers
            : ($tenant->family_members ?: []);

        $cottage->forceFill([
            'tenant_id' => $tenant->id,
            'requested_tenant_id' => null,
            'requested_at' => null,
            'status' => EmployeeCottage::STATUS_OCCUPIED,
            'family_members' => $resolvedFamily,
        ])->save();

        if (! empty($familyMembers) && $tenant->family_members !== $resolvedFamily) {
            $tenant->forceFill(['family_members' => $resolvedFamily])->save();
        }

        return redirect()
            ->route('management.cottages.index')
            ->with('status', "{$cottage->code} assigned to {$tenant->full_name}.");
    }

    public function reject(Request $request, EmployeeCottage $cottage): RedirectResponse
    {
        if ($cottage->status !== EmployeeCottage::STATUS_REQUESTED) {
            return redirect()
                ->route('management.cottages.index')
                ->withErrors(['cottage' => 'Only pending cottage requests can be rejected.']);
        }

        $cottage->forceFill([
            'status' => EmployeeCottage::STATUS_AVAILABLE,
            'tenant_id' => null,
            'requested_tenant_id' => null,
            'requested_at' => null,
            'family_members' => null,
        ])->save();

        return redirect()
            ->route('management.cottages.index')
            ->with('status', "{$cottage->code} request has been rejected.");
    }

    public function release(Request $request, EmployeeCottage $cottage): RedirectResponse
    {
        if ($cottage->status !== EmployeeCottage::STATUS_OCCUPIED) {
            return redirect()
                ->route('management.cottages.index')
                ->withErrors(['cottage' => 'Only occupied cottages can be released.']);
        }

        $cottage->forceFill([
            'status' => EmployeeCottage::STATUS_AVAILABLE,
            'tenant_id' => null,
            'requested_tenant_id' => null,
            'requested_at' => null,
            'family_members' => null,
        ])->save();

        return redirect()
            ->route('management.cottages.index')
            ->with('status', "{$cottage->code} has been marked available.");
    }
}
