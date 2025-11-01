<x-ksu-layout page-title="Employee Dashboard">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Welcome, {{ $tenant->full_name ?? auth()->user()->name }}</h1>

        <x-ksu-card class="space-y-4">
            <p class="text-sm text-slate-600">
                You now have access to the employee resources for the dormitory management system. Check back soon for dedicated tools and reports.
            </p>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Approval Date</span>
                <span class="mt-1 block text-base font-semibold text-ksu-900">
                    {{ optional($tenant->updated_at)->format('M d, Y g:i A') ?? '—' }}
                </span>
            </div>
        </x-ksu-card>
    </div>
</x-ksu-layout>
