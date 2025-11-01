@csrf
<div class="grid gap-5 sm:grid-cols-2">
    <div class="space-y-2">
        <x-input-label for="starts_at" value="Starts At" />
        <x-text-input
            id="starts_at"
            name="starts_at"
            type="datetime-local"
            :value="old('starts_at', optional($slot->starts_at)->format('Y-m-d\TH:i'))"
            required
        />
        <x-input-error :messages="$errors->get('starts_at')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="ends_at" value="Ends At" />
        <x-text-input
            id="ends_at"
            name="ends_at"
            type="datetime-local"
            :value="old('ends_at', optional($slot->ends_at)->format('Y-m-d\TH:i'))"
            required
        />
        <x-input-error :messages="$errors->get('ends_at')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="capacity" value="Capacity" />
        <x-text-input
            id="capacity"
            name="capacity"
            type="number"
            min="1"
            max="50"
            :value="old('capacity', $slot->capacity ?? 1)"
            required
        />
        <x-input-error :messages="$errors->get('capacity')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="status" value="Status" />
        <select
            id="status"
            name="status"
            class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
        >
            @foreach(['open' => 'Open', 'closed' => 'Closed'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $slot->status ?? 'open') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
</div>
