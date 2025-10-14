@csrf
<div class="grid gap-5 sm:grid-cols-2">
    <div class="space-y-2">
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" type="text" :value="old('code', $room->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="building" value="Building" />
        <x-text-input id="building" name="building" type="text" :value="old('building', $room->building ?? '')" required />
        <x-input-error :messages="$errors->get('building')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="floor" value="Floor" />
        <x-text-input id="floor" name="floor" type="text" :value="old('floor', $room->floor ?? '')" required />
        <x-input-error :messages="$errors->get('floor')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="wing" value="Wing" />
        <x-text-input id="wing" name="wing" type="text" :value="old('wing', $room->wing ?? '')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="gender" value="Gender" />
        <select
            id="gender"
            name="gender"
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
        >
            @foreach([\App\Models\Room::GENDER_MALE => 'Male', \App\Models\Room::GENDER_FEMALE => 'Female', \App\Models\Room::GENDER_MIXED => 'Mixed'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $room->gender ?? '') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <x-input-label for="capacity" value="Capacity" />
        <x-text-input id="capacity" name="capacity" type="number" min="1" max="12" :value="old('capacity', $room->capacity ?? 6)" required />
        <x-input-error :messages="$errors->get('capacity')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="status" value="Status" />
        <select
            id="status"
            name="status"
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
        >
            @foreach([\App\Models\Room::STATUS_OPEN => 'Open', \App\Models\Room::STATUS_CLOSED => 'Closed', \App\Models\Room::STATUS_MAINTENANCE => 'Maintenance'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $room->status ?? \App\Models\Room::STATUS_OPEN) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</div>
