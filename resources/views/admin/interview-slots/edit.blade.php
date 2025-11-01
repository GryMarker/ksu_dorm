<x-ksu-layout page-title="Edit Interview Slot">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Update Interview Slot</h1>
            <p class="mt-1 text-sm text-slate-600">Adjust the schedule, capacity, or visibility of this slot.</p>
        </div>

        <x-ksu-card>
            <form method="POST" action="{{ route('admin.interview-slots.update', $slot) }}" class="space-y-8">
                @method('PUT')
                @include('admin.interview-slots._form', ['slot' => $slot])

                <div class="flex items-center gap-3">
                    <x-ksu-button as="a" href="{{ route('admin.interview-slots.index') }}" variant="outline">
                        Back
                    </x-ksu-button>
                    <x-ksu-button type="submit">
                        Update Slot
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
