<x-ksu-layout page-title="Employee Approvals">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Employee Access Approvals</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Review onboarding submissions from employees and approve access once requirements are met.
                </p>
            </div>
        </div>

        @if (session('status'))
            <x-ksu-alert type="success">
                {{ session('status') }}
            </x-ksu-alert>
        @endif

        <x-ksu-card title="Pending Requests">
            @if ($pendingTenants->isEmpty())
                <p class="text-sm text-slate-500">No pending employee requests at the moment.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Employee</th>
                                <th scope="col" class="px-4 py-3">Department / Course</th>
                                <th scope="col" class="px-4 py-3">Submitted</th>
                                <th scope="col" class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($pendingTenants as $tenant)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-ksu-900">{{ $tenant->full_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $tenant->user->email }}</p>
                                            <p class="text-xs text-slate-500">
                                                Employee ID: <span class="font-semibold text-ksu-700">{{ $tenant->employee_id_number ?? 'Pending' }}</span>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $tenant->course_year ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $tenant->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <form method="POST" action="{{ route('president.approvals.employees.approve', $tenant) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-ksu-button type="submit" size="sm">Approve</x-ksu-button>
                                            </form>
                                            <form method="POST" action="{{ route('president.approvals.employees.reject', $tenant) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-ksu-button type="submit" size="sm" variant="outline">Reject</x-ksu-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pendingTenants->links() }}
                </div>
            @endif
        </x-ksu-card>

        <x-ksu-card title="Recently Approved">
            @if ($recentTenants->isEmpty())
                <p class="text-sm text-slate-500">No approved employees yet.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm text-slate-600">
                    @foreach ($recentTenants as $tenant)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-1 py-3">
                            <div>
                                <p class="font-semibold text-ksu-900">{{ $tenant->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $tenant->user->email }}</p>
                            </div>
                            <div class="text-xs text-slate-500">
                                Approved {{ $tenant->updated_at->diffForHumans() }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
