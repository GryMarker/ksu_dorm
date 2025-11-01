<x-ksu-layout page-title="Create Interview Slot">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Add Interview Slot</h1>
            <p class="mt-1 text-sm text-slate-600">Set the schedule and capacity so applicants can reserve a time.</p>
        </div>

        <x-ksu-card>
            <form method="POST" action="{{ route('admin.interview-slots.store') }}" class="space-y-8">
                @include('admin.interview-slots._form', ['slot' => $slot])

                <div class="flex items-center gap-3">
                    <x-ksu-button as="a" href="{{ route('admin.interview-slots.index') }}" variant="outline">
                        Cancel
                    </x-ksu-button>
                    <x-ksu-button type="submit">
                        Save Slot
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
