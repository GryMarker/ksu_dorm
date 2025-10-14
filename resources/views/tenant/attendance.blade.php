<x-ksu-layout page-title="Attendance">
    <div
        class="space-y-8"
        x-data="{
            from: '',
            to: '',
            type: 'all',
        }"
    >
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Attendance Logs</h1>
                <p class="mt-1 text-sm text-slate-600">Track your dorm check-ins and check-outs. Use the filters to narrow your view.</p>
            </div>
            <x-ksu-badge variant="info" class="bg-slate-100 text-slate-600">
                Showing {{ $logs->count() }} of {{ $logs->total() }} records
            </x-ksu-badge>
        </div>

        <x-ksu-card title="Filter Logs">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-600">From</label>
                    <input
                        type="date"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        x-model="from"
                    >
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-600">To</label>
                    <input
                        type="date"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        x-model="to"
                    >
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-600">Type</label>
                    <select
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        x-model="type"
                    >
                        <option value="all">All</option>
                        <option value="in">Check-in</option>
                        <option value="out">Check-out</option>
                    </select>
                </div>
            </div>
        </x-ksu-card>

        <x-ksu-card title="Logs">
            @if ($logs->isEmpty())
                <div class="flex flex-col items-center justify-center gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-10 text-center">
                    <svg class="h-12 w-12 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-ksu-900">No attendance records yet</p>
                        <p class="text-sm text-slate-500">Your check-ins and check-outs will appear here as soon as you start logging them.</p>
                    </div>
                </div>
            @else
                <x-ksu-table :headers="['Date & Time', 'Type', 'Mode', 'Remarks']">
                    @foreach ($logs as $log)
                        @php
                            $dateValue = $log->timestamp->format('Y-m-d');
                            $badgeVariant = $log->type === 'in' ? 'approved' : 'pending';
                        @endphp
                        <tr
                            x-show="
                                (type === 'all' || type === '{{ $log->type }}') &&
                                (!from || '{{ $dateValue }}' >= from) &&
                                (!to || '{{ $dateValue }}' <= to)
                            "
                            x-cloak
                        >
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $log->timestamp->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ksu-badge :variant="$badgeVariant" size="sm" uppercase>
                                    {{ strtoupper($log->type) }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold text-ksu-800">
                                {{ strtoupper($log->mode) }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $log->remarks ?? 'None' }}
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>

                <div class="mt-6">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
