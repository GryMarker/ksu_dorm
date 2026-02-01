<x-ksu-layout page-title="Students">
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Students</h1>
                <p class="mt-1 text-sm text-slate-600">Masterlist of student applicants with quick access to their application details.</p>
            </div>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-text-input
                    name="search"
                    type="search"
                    placeholder="Search name, ID, or email"
                    class="sm:w-64"
                    value="{{ $search }}"
                />
                <x-ksu-button type="submit" size="sm" variant="outline" class="sm:w-auto">Filter</x-ksu-button>
            </form>
        </div>

        <x-ksu-card>
            @if ($tenants->isEmpty())
                <p class="text-sm text-slate-500">No students found.</p>
            @else
                <x-ksu-table :headers="['Student', 'University ID', 'Status', 'Updated', 'Action']">
                    @foreach ($tenants as $tenant)
                        @php
                            $statusVariantMap = [
                                'approved' => 'approved',
                                'rejected' => 'rejected',
                                'recheck' => 'pending',
                            ];
                            $badgeVariant = $statusVariantMap[$tenant->onboarding_status] ?? 'pending';
                        @endphp
                        <tr>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                <div class="font-semibold">{{ $tenant->full_name ?? $tenant->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $tenant->user->email }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $tenant->university_id_no ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ksu-badge :variant="$badgeVariant" size="sm">
                                    {{ \Illuminate\Support\Str::headline($tenant->onboarding_status ?? 'Pending') }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ optional($tenant->updated_at)->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-4">
                            <x-ksu-button
                                type="button"
                                variant="outline"
                                size="sm"
                                x-on:click="$dispatch('open-modal', 'student-profile-{{ $tenant->id }}')"
                            >
                                View Details
                            </x-ksu-button>
                            <x-ksu-button
                                as="a"
                                href="{{ route('admin.students.history', $tenant) }}"
                                variant="subtle"
                                size="sm"
                            >
                                History
                            </x-ksu-button>

                                <x-modal name="student-profile-{{ $tenant->id }}" maxWidth="2xl">
                                    <div class="space-y-4 p-6">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Masterlist</p>
                                                <h2 class="text-lg font-semibold text-ksu-900">Student Profile</h2>
                                            </div>
                                            <button
                                                type="button"
                                                class="text-slate-500 transition hover:text-ksu-700"
                                                x-on:click="$dispatch('close-modal', 'student-profile-{{ $tenant->id }}')"
                                                aria-label="Close"
                                            >
                                                x
                                            </button>
                                        </div>
                                        <x-applicant-details :tenant="$tenant" />
                                    </div>
                                </x-modal>
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>

                <div class="mt-4">
                    {{ $tenants->withQueryString()->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
