@php
    use App\Models\EmployeeCottage;
    use Illuminate\Support\Str;
@endphp

<x-ksu-layout page-title="Employee Cottage Management">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Employee Cottage Management</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Review availability, approve employee cottage requests, and manage family occupancy records.
                </p>
            </div>
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
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Summary</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Total cottages: <span class="font-semibold text-ksu-900">{{ $cottages->count() }}</span></li>
                    <li>Available: <span class="font-semibold text-emerald-700">{{ $available->count() }}</span></li>
                    <li>Pending requests: <span class="font-semibold text-amber-700">{{ $pending->count() }}</span></li>
                    <li>Occupied: <span class="font-semibold text-slate-900">{{ $occupied->count() }}</span></li>
                </ul>
            </x-ksu-card>
        </div>

        <x-ksu-card title="Pending Requests">
            @if ($pending->isEmpty())
                <p class="text-sm text-slate-500">No pending cottage requests at the moment.</p>
            @else
                <div class="space-y-6">
                    @foreach ($pending as $cottage)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-lg font-semibold text-ksu-900">{{ $cottage->code }}</p>
                                    <p class="text-sm text-slate-500">
                                        Requested by {{ $cottage->requestedTenant?->full_name }} • {{ optional($cottage->requested_at)->diffForHumans() }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $cottage->building }} • {{ $cottage->wing }}
                                    </p>
                                </div>
                                <div class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                    Pending
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 lg:grid-cols-[1.2fr,1fr]">
                                <div class="space-y-3">
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Family Members</h3>
                                    <form method="POST" action="{{ route('management.cottages.approve', $cottage) }}" class="space-y-4">
                                        @csrf
                                        @method('PATCH')
                                        <textarea
                                            name="family_members"
                                            rows="4"
                                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                            placeholder="One name per line"
                                        >{{ implode("\n", $cottage->family_members ?? $cottage->requestedTenant?->family_members ?? []) }}</textarea>
                                        <x-ksu-button type="submit" size="sm">Approve</x-ksu-button>
                                    </form>
                                    <form method="POST" action="{{ route('management.cottages.reject', $cottage) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ksu-button type="submit" size="sm" variant="outline">Reject</x-ksu-button>
                                    </form>
                                </div>
                                <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                                    <p class="font-semibold text-slate-700">Employee Contact</p>
                                    <p>{{ $cottage->requestedTenant?->user->email }}</p>
                                    <p>{{ $cottage->requestedTenant?->phone }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ksu-card>

        <x-ksu-card title="Cottage Inventory">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Cottage</th>
                            <th scope="col" class="px-4 py-3">Occupant / Request</th>
                            <th scope="col" class="px-4 py-3">Family Members</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($cottages as $cottage)
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
                                    @endphp
                                    <x-ksu-badge :variant="$badgeVariant">
                                        {{ Str::headline($cottage->status) }}
                                    </x-ksu-badge>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($cottage->status === EmployeeCottage::STATUS_OCCUPIED)
                                        <form method="POST" action="{{ route('management.cottages.release', $cottage) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-ksu-button type="submit" size="sm" variant="outline">Release</x-ksu-button>
                                        </form>
                                    @elseif ($cottage->status === EmployeeCottage::STATUS_AVAILABLE)
                                        <span class="text-xs text-emerald-600">Available</span>
                                    @else
                                        <span class="text-xs text-amber-600">Pending</span>
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
