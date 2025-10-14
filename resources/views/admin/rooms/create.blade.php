<x-ksu-layout page-title="Create Room">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Add New Room</h1>
            <p class="mt-1 text-sm text-slate-600">Define the room details so tenants can request assignments and admins can track occupancy.</p>
        </div>

        <x-ksu-card>
            <form method="POST" action="{{ route('admin.rooms.store') }}" class="space-y-8">
                @include('admin.rooms._form', ['room' => new \App\Models\Room()])

                <div class="flex items-center gap-3">
                    <x-ksu-button as="a" href="{{ route('admin.rooms.index') }}" variant="outline">
                        Cancel
                    </x-ksu-button>
                    <x-ksu-button type="submit">
                        Save Room
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
