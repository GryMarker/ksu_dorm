@php
    use App\Models\EmployeeCottage;

    $activeCottage = $tenant->cottage;
    $pendingRequest = $tenant->cottageRequest;
@endphp

<x-ksu-layout page-title="Employee Cottages">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Employee Cottage Availability</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Review available cottages and request an assignment for your family. Each cottage houses one family.
                </p>
            </div>
            <x-ksu-button :href="route('employee.dashboard')" size="sm" variant="outline">
                Back to Dashboard
            </x-ksu-button>
        </div>

        @if (session('status'))
            <x-ksu-alert type="success">
                {{ session('status') }}
            </x-ksu-alert>
        @endif

        @if ($errors->any())
            <x-ksu-alert type="error">
                {{ $errors->first() }}
            </x-ksu-alert>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-ksu-card>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Current Status</h2>
                <div class="mt-3 space-y-2 text-sm text-slate-600">
                    @if ($activeCottage)
                        <p>
                            <span class="font-semibold text-ksu-900">{{ $activeCottage->code }}</span>
                            <span class="ml-2 rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Assigned</span>
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $activeCottage->building }} • {{ $activeCottage->wing }}
                        </p>
                    @elseif ($pendingRequest)
                        <p>
                            <span class="font-semibold text-ksu-900">{{ $pendingRequest->code }}</span>
                            <span class="ml-2 rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Awaiting Approval</span>
                        </p>
                        <p class="text-xs text-slate-500">
                            Requested {{ optional($pendingRequest->requested_at)->diffForHumans() }}
                        </p>
                    @else
                        <p>No active cottage assignment.</p>
                    @endif
                </div>
            </x-ksu-card>

            <x-ksu-card>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Family Members on File</h2>
                @php
                    $familyMembers = collect($tenant->family_members ?? [])->filter();
                @endphp
                @if ($familyMembers->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">
                        No family members have been listed yet. Update your onboarding form to add them.
                    </p>
                @else
                    <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        @foreach ($familyMembers as $member)
                            <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ $member }}</li>
                        @endforeach
                    </ul>
                @endif
            </x-ksu-card>

            <x-ksu-card>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Summary</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Total cottages: <span class="font-semibold text-ksu-900">{{ $cottages->count() }}</span></li>
                    <li>Available: <span class="font-semibold text-emerald-700">{{ $cottages->where('status', EmployeeCottage::STATUS_AVAILABLE)->count() }}</span></li>
                    <li>Pending: <span class="font-semibold text-amber-700">{{ $cottages->where('status', EmployeeCottage::STATUS_REQUESTED)->count() }}</span></li>
                    <li>Occupied: <span class="font-semibold text-slate-900">{{ $cottages->where('status', EmployeeCottage::STATUS_OCCUPIED)->count() }}</span></li>
                </ul>
            </x-ksu-card>
        </div>

        <x-ksu-card title="Cottage Availability">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Cottage</th>
                            <th scope="col" class="px-4 py-3">Occupant / Request</th>
                            <th scope="col" class="px-4 py-3">Family Members</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($cottages as $cottage)
                            @php
                                $isOwnRequest = $pendingRequest && $pendingRequest->id === $cottage->id;
                                $isOwnAssignment = $activeCottage && $activeCottage->id === $cottage->id;
                                $canRequest = ! $activeCottage && ! $pendingRequest && $cottage->status === EmployeeCottage::STATUS_AVAILABLE;
                            @endphp
                            <tr>
                                <td class="px-4 py-4 font-semibold text-ksu-900">{{ $cottage->code }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    @if ($cottage->tenant)
                                        <div>
                                            <p class="font-semibold text-ksu-900">{{ $cottage->tenant->full_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $cottage->tenant->user->email ?? '—' }}</p>
                                        </div>
                                    @elseif ($cottage->requestedTenant)
                                        <div>
                                            <p class="font-semibold text-ksu-900">{{ $cottage->requestedTenant->full_name }}</p>
                                            <p class="text-xs text-slate-500">Requested {{ optional($cottage->requested_at)->diffForHumans() }}</p>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-500">
                                    @if (! empty($cottage->family_members))
                                        <ul class="space-y-1">
                                            @foreach ($cottage->family_members as $member)
                                                <li>{{ $member }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @php
                                        $badgeVariant = match ($cottage->status) {
                                            EmployeeCottage::STATUS_AVAILABLE => 'approved',
                                            EmployeeCottage::STATUS_REQUESTED => 'pending',
                                            EmployeeCottage::STATUS_OCCUPIED => 'primary',
                                            default => 'neutral',
                                        };
                                        $label = \Illuminate\Support\Str::headline($cottage->status);
                                    @endphp
                                    <x-ksu-badge :variant="$badgeVariant">{{ $label }}</x-ksu-badge>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($canRequest)
                                        <form method="POST" action="{{ route('employee.cottages.request', $cottage) }}">
                                            @csrf
                                            <x-ksu-button type="submit" size="sm">Request</x-ksu-button>
                                        </form>
                                    @elseif ($isOwnRequest)
                                        <span class="text-xs text-amber-600">Awaiting approval</span>
                                    @elseif ($isOwnAssignment)
                                        <span class="text-xs text-emerald-600">Assigned to you</span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ksu-card>
    </div>
</x-ksu-layout>
