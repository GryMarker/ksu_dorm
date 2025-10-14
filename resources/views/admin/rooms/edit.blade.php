<x-ksu-layout page-title="Edit Room">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Edit Room</h1>
            <p class="mt-1 text-sm text-slate-600">Update room information to keep the dorm inventory accurate.</p>
        </div>

        <x-ksu-card>
            <form method="POST" action="{{ route('admin.rooms.update', $room) }}" class="space-y-8">
                @method('PUT')
                @include('admin.rooms._form', ['room' => $room])

                <div class="flex items-center gap-3">
                    <x-ksu-button as="a" href="{{ route('admin.rooms.show', $room) }}" variant="outline">
                        Cancel
                    </x-ksu-button>
                    <x-ksu-button type="submit">
                        Update Room
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
