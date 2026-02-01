<x-ksu-layout page-title="Monthly Attendance">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Monthly Time In/Out</h1>
                <p class="mt-1 text-sm text-slate-600">Printable report of dormitory time logs for the selected month.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-ksu-button type="button" variant="subtle" onclick="window.print()">
                    Print Report
                </x-ksu-button>
                <x-ksu-badge variant="info">
                    {{ $range[0]->format('F Y') }}
                </x-ksu-badge>
            </div>
        </div>

        <x-ksu-card>
            <form method="GET" class="grid gap-4 sm:grid-cols-4">
                <div class="space-y-2">
                    <x-input-label for="month" value="Month" />
                    <x-text-input
                        id="month"
                        name="month"
                        type="month"
                        :value="$filters['month'] ?? now()->format('Y-m')"
                    />
                </div>
                <div class="space-y-2">
                    <x-input-label for="tenant_id" value="Student (optional)" />
                    <select
                        id="tenant_id"
                        name="tenant_id"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                    >
                        <option value="">All students</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected(($filters['tenant_id'] ?? '') == $tenant->id)>
                                {{ $tenant->full_name ?? $tenant->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <x-ksu-button type="submit" full>Apply</x-ksu-button>
                </div>
                <div class="flex items-end">
                    <x-ksu-button as="a" href="{{ route('admin.attendance.monthly') }}" variant="outline" full>Reset</x-ksu-button>
                </div>
            </form>
        </x-ksu-card>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Month</p>
                <p class="text-xl font-semibold text-ksu-900">{{ $range[0]->format('F Y') }}</p>
                <p class="text-sm text-slate-500">{{ $range[0]->format('M d') }} – {{ $range[1]->format('M d, Y') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Logs</p>
                <p class="text-3xl font-semibold text-ksu-900">{{ $logs->count() }}</p>
                <p class="text-sm text-slate-500">Time In/Out entries captured</p>
            </div>
            <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Students Covered</p>
                <p class="text-3xl font-semibold text-ksu-900">{{ $dailyGrouped->keys()->count() }}</p>
                <p class="text-sm text-slate-500">With at least one log</p>
            </div>
        </div>

        <x-ksu-card title="Log Details">
            @if ($logs->isEmpty())
                <p class="text-sm text-slate-500">No attendance records for this period.</p>
            @else
                <x-ksu-table :headers="['Date', 'Student', 'Type', 'Time', 'Mode', 'Remarks']">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-5 py-3 text-sm text-slate-600">
                                {{ $log->timestamp->timezone(config('app.timezone'))->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold text-ksu-900">
                                {{ $log->tenant->full_name ?? $log->tenant->user->name }}
                            </td>
                            <td class="px-5 py-3">
                                <x-ksu-badge :variant="$log->type === 'in' ? 'approved' : 'pending'" size="sm" uppercase>
                                    {{ strtoupper($log->type) }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-700">
                                {{ $log->timestamp->timezone(config('app.timezone'))->format('h:i A') }}
                            </td>
                            <td class="px-5 py-3 text-xs font-semibold text-ksu-800 uppercase">
                                {{ $log->mode }}
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-600">
                                {{ $log->remarks ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
