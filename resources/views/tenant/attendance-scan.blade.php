<x-ksu-layout page-title="QR Attendance Confirmation">
    <div class="mx-auto max-w-2xl space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Confirm Attendance</h1>
            <p class="mt-1 text-sm text-slate-600">You scanned the current attendance QR code. Confirm the action below.</p>
        </div>

        <x-ksu-card>
            <div class="space-y-6">
                <div class="rounded-2xl border border-ksu-200 bg-ksu-50/60 px-5 py-4">
                    <p class="text-sm text-slate-600">Student</p>
                    <p class="text-lg font-semibold text-ksu-900">{{ $tenant->full_name ?? $tenant->user->name }}</p>
                    <p class="mt-3 text-sm text-slate-600">Next action</p>
                    <p class="text-lg font-semibold uppercase text-ksu-900">{{ $nextType }}</p>
                    <p class="mt-2 text-xs text-slate-500">Valid until {{ $expiresAt->timezone(config('app.timezone'))->format('h:i:s A') }}</p>
                </div>

                <form method="POST" action="{{ route('tenant.attendance.scan.store', request()->query()) }}" class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    @csrf
                    <x-ksu-button as="a" href="{{ route('tenant.attendance.index') }}" variant="outline">
                        Cancel
                    </x-ksu-button>
                    <x-ksu-button type="submit">
                        Confirm {{ strtoupper($nextType) }}
                    </x-ksu-button>
                </form>
            </div>
        </x-ksu-card>
    </div>
</x-ksu-layout>
