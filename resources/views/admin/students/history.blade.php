<x-ksu-layout page-title="Student History">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Student Audit Trail</h1>
                <p class="mt-1 text-sm text-slate-600">Print-ready history of application, interview, reservation, and attendance records.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-ksu-button type="button" variant="subtle" onclick="window.print()">Print</x-ksu-button>
                <x-ksu-badge :variant="$tenant->onboarding_status === \\App\\Models\\Tenant::STATUS_APPROVED ? 'approved' : 'pending'">
                    {{ \\Illuminate\\Support\\Str::headline($tenant->onboarding_status) }}
                </x-ksu-badge>
            </div>
        </div>

        <x-ksu-card>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student</p>
                    <p class="text-2xl font-semibold text-ksu-900">{{ $tenant->full_name ?? $tenant->user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $tenant->user->email }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Student ID</span>
                        <span class="font-semibold text-ksu-900">{{ $tenant->university_id_no }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Course</span>
                        <span class="font-semibold text-ksu-900">{{ $tenant->program }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Year</span>
                        <span class="font-semibold text-ksu-900">{{ $tenant->year_level }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Contact</span>
                        <span class="font-semibold text-ksu-900">{{ $tenant->cellphone ?? $tenant->phone }}</span>
                    </div>
                </div>
            </div>
        </x-ksu-card>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-ksu-card title="Interviews">
                @if($tenant->interviews->isEmpty())
                    <p class="text-sm text-slate-500">No interviews recorded.</p>
                @else
                    <ul class="space-y-3 text-sm text-slate-700">
                        @foreach($tenant->interviews as $interview)
                            <li class="rounded-xl border border-slate-200/70 bg-white px-3 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-ksu-900">{{ $interview->scheduled_at->format('M d, Y h:i A') }}</span>
                                    @if($interview->result)
                                        <x-ksu-badge :variant="$interview->result === 'approved' ? 'approved' : ($interview->result === 'rejected' ? 'rejected' : 'pending')" size="sm" uppercase>
                                            {{ strtoupper($interview->result) }}
                                        </x-ksu-badge>
                                    @endif
                                </div>
                                @if($interview->notes)
                                    <p class="text-xs text-slate-500 mt-1">{{ $interview->notes }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ksu-card>

            <x-ksu-card title="Reservations & Assignments">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reservations</p>
                        @if($tenant->reservations->isEmpty())
                            <p class="text-sm text-slate-500">No reservation requests.</p>
                        @else
                            <ul class="space-y-2 text-sm text-slate-700">
                                @foreach($tenant->reservations as $reservation)
                                    <li class="rounded-xl border border-slate-200/70 bg-white px-3 py-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-ksu-900">Room {{ $reservation->room->code ?? 'N/A' }}</span>
                                            <x-ksu-badge :variant="$reservation->status === 'approved' ? 'approved' : ($reservation->status === 'pending' ? 'pending' : 'info')" size="sm">
                                                {{ \\Illuminate\\Support\\Str::headline($reservation->status) }}
                                            </x-ksu-badge>
                                        </div>
                                        <p class="text-xs text-slate-500">Requested {{ optional($reservation->requested_at)->format('M d, Y') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assignments</p>
                        @if($tenant->assignments->isEmpty())
                            <p class="text-sm text-slate-500">No bed assignments yet.</p>
                        @else
                            <ul class="space-y-2 text-sm text-slate-700">
                                @foreach($tenant->assignments as $assignment)
                                    <li class="rounded-xl border border-slate-200/70 bg-white px-3 py-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-ksu-900">{{ $assignment->room->code ?? 'Room' }} • Bed {{ $assignment->bed->bed_label ?? 'N/A' }}</span>
                                            @if($assignment->is_active)
                                                <x-ksu-badge variant="approved" size="sm">Active</x-ksu-badge>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500">From {{ optional($assignment->start_date)->format('M d, Y') }} @if($assignment->end_date) &middot; To {{ $assignment->end_date->format('M d, Y') }} @endif</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </x-ksu-card>
        </div>

        <x-ksu-card title="Attendance (last 100 entries)">
            @if($attendanceLogs->isEmpty())
                <p class="text-sm text-slate-500">No attendance recorded.</p>
            @else
                <x-ksu-table :headers="['Date', 'Type', 'Time', 'Mode', 'Remarks']">
                    @foreach($attendanceLogs as $log)
                        <tr>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ $log->timestamp->format('M d, Y') }}</td>
                            <td class="px-5 py-3">
                                <x-ksu-badge :variant="$log->type === 'in' ? 'approved' : 'pending'" size="sm" uppercase>
                                    {{ strtoupper($log->type) }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-3 text-sm text-ksu-900">{{ $log->timestamp->format('h:i A') }}</td>
                            <td class="px-5 py-3 text-xs font-semibold text-ksu-800 uppercase">{{ $log->mode }}</td>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ $log->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                </x-ksu-table>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
