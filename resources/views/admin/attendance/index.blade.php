<x-ksu-layout page-title="Attendance Logs">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Attendance Logs</h1>
            <p class="mt-1 text-sm text-slate-600">Review daily entries and exits across all tenants. Use filters to narrow the results.</p>
        </div>

        <x-ksu-card>
            <form method="GET" class="grid gap-4 sm:grid-cols-4">
                <div class="space-y-2">
                    <x-input-label for="tenant_id" value="Tenant" />
                    <select
                        id="tenant_id"
                        name="tenant_id"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                    >
                        <option value="">All</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected(($filters['tenant_id'] ?? '') == $tenant->id)>
                                {{ $tenant->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <x-input-label for="date" value="Date" />
                    <x-text-input id="date" name="date" type="date" :value="$filters['date'] ?? ''" />
                </div>
                <div class="flex items-end">
                    <x-ksu-button type="submit" full>Filter</x-ksu-button>
                </div>
                <div class="flex items-end">
                    <x-ksu-button as="a" href="{{ route('admin.attendance.index') }}" variant="outline" full>Reset</x-ksu-button>
                </div>
            </form>
        </x-ksu-card>

        <x-ksu-card title="Results">
            @if ($logs->isEmpty())
                <p class="text-sm text-slate-500">No attendance records found. Adjust the filters or check back later.</p>
            @else
                <x-ksu-table :headers="['Tenant', 'Type', 'Timestamp', 'Mode', 'Device / IP', 'Remarks']">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                {{ $log->tenant->user->name }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ksu-badge :variant="$log->type === 'in' ? 'approved' : 'pending'" size="sm" uppercase>
                                    {{ strtoupper($log->type) }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $log->timestamp->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold text-ksu-800 uppercase">
                                {{ $log->mode }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $log->device_id ?? '—' }} / {{ $log->ip ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $log->remarks ?? 'None' }}
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>

                <div class="mt-4">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
