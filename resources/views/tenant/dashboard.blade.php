@php
    use App\Models\Bed;
    use App\Models\Tenant as TenantModel;
    use Illuminate\Support\Carbon;

    $nextInterview = $tenant->interviews
        ->sortBy('scheduled_at')
        ->first(fn ($interview) => $interview->scheduled_at && $interview->scheduled_at->isFuture());

    if (! $nextInterview) {
        $nextInterview = $tenant->interviews->sortByDesc('scheduled_at')->first();
    }

    $vacantBeds = Bed::where('is_occupied', false)->count();

    $attendanceThisMonth = $tenant->attendanceLogs()
        ->whereBetween('timestamp', [Carbon::now()->startOfMonth(), Carbon::now()])
        ->count();

    $recentAttendance = $tenant->attendanceLogs()
        ->latest('timestamp')
        ->take(5)
        ->get();

    $statusBadge = match ($tenant->onboarding_status) {
        TenantModel::STATUS_APPROVED => 'approved',
        TenantModel::STATUS_REJECTED => 'rejected',
        TenantModel::STATUS_FOR_INTERVIEW, TenantModel::STATUS_FOR_APPROVAL, TenantModel::STATUS_RECHECK => 'pending',
        default => 'info',
    };

    $displayName = trim($tenant->full_name ?: $tenant->user->name);
    $firstName = explode(' ', $displayName)[0] ?? $displayName;
@endphp

<x-ksu-layout page-title="Tenant Dashboard">
    <div class="space-y-10">
        <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Welcome back, {{ $firstName }}</h1>
            <p class="text-sm text-slate-600">Here is a snapshot of your dorm life this week. Explore quick actions below to stay on track.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4 stats-grid">
            <x-ksu-card>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Admission Status</p>
                        <p class="mt-3 text-2xl font-semibold text-ksu-900">{{ \Illuminate\Support\Str::headline($tenant->onboarding_status) }}</p>
                    </div>
                    <x-ksu-badge :variant="$statusBadge">
                        {{ \Illuminate\Support\Str::headline($tenant->onboarding_status) }}
                    </x-ksu-badge>
                </div>
            </x-ksu-card>

            <x-ksu-card>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">My Room</p>
                        <p class="mt-3 text-2xl font-semibold text-ksu-900">
                            @if($tenant->activeAssignment)
                                {{ $tenant->activeAssignment->room->code }}
                            @else
                                Pending
                            @endif
                        </p>
                        <p class="text-sm text-slate-600">
                            @if($tenant->activeAssignment)
                                Bed {{ $tenant->activeAssignment->bed->bed_label }}
                            @else
                                No active assignment
                            @endif
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-ksu-100 text-ksu-700">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10.5 12 4l9 6.5v7.5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 21v-6h6v6"/>
                        </svg>
                    </div>
                </div>
            </x-ksu-card>

            <x-ksu-card>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Vacant Beds</p>
                        <p class="mt-3 text-2xl font-semibold text-ksu-900"><span class="stat-number updated">{{ $vacantBeds }}</span></p>
                        <p class="text-sm text-slate-600">Available campus-wide right now.</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 text-gold">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 18h16M4 14h16"/>
                        </svg>
                    </div>
                </div>
            </x-ksu-card>

            <x-ksu-card>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Attendance This Month</p>
                        <p class="mt-3 text-2xl font-semibold text-ksu-900"><span class="stat-number updated">{{ $attendanceThisMonth }}</span></p>
                        <p class="text-sm text-slate-600">Logs recorded since {{ Carbon::now()->startOfMonth()->format('M d') }}.</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-ksu-600/10 text-ksu-600">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 7v5l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
            </x-ksu-card>
        </div>

        <div class="grid gap-6 lg:grid-cols-[3fr,2fr]">
            <x-ksu-card title="Recent Attendance">
                @if($recentAttendance->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-8 text-center">
                        <svg class="h-12 w-12 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <p class="text-sm font-medium text-slate-600">No attendance logs yet. Keep your RFID or QR code handy to record your entries.</p>
                    </div>
                @else
                    <x-ksu-table :headers="['Type', 'Timestamp', 'Mode', 'Remarks']">
                        @foreach($recentAttendance as $log)
                            <tr>
                                <td class="px-5 py-4">
                                    <x-ksu-badge :variant="$log->type === 'in' ? 'approved' : 'pending'" size="sm" uppercase>
                                        {{ strtoupper($log->type) }}
                                    </x-ksu-badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $log->timestamp->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-ksu-800">
                                    {{ strtoupper($log->mode) }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $log->remarks ?? '--' }}
                                </td>
                            </tr>
                        @endforeach
                    </x-ksu-table>
                @endif
            </x-ksu-card>

            <div class="space-y-6">
                <x-ksu-card title="Next Interview">
                    @if($nextInterview)
                        <div class="space-y-3 text-sm text-slate-600">
                            <p class="text-lg font-semibold text-ksu-900">
                                {{ $nextInterview->scheduled_at->format('M d, Y \\a\\t h:i A') }}
                            </p>
                            <p>Be at the Dorm Admissions Office at least 15 minutes before your schedule.</p>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No upcoming interviews. Book a slot if you have not yet scheduled one.</p>
                    @endif
                </x-ksu-card>

                <x-ksu-card title="Quick Actions">
                    <div class="grid gap-3">
                        <x-ksu-button as="a" href="{{ route('tenant.apply.form') }}" variant="subtle" full>Update Application</x-ksu-button>
                        <x-ksu-button as="a" href="{{ route('tenant.availability') }}" full>Check Room Availability</x-ksu-button>
                        <x-ksu-button as="a" href="{{ route('tenant.attendance.index') }}" variant="outline" full>View Attendance Logs</x-ksu-button>
                    </div>
                </x-ksu-card>
            </div>
        </div>
    </div>
</x-ksu-layout>

