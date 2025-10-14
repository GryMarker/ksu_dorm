<x-guest-layout>
    <div class="space-y-4">
        <p class="text-sm text-slate-600">
            {{ __('Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-6">
        @csrf

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-ksu-button type="submit" full>
            {{ __('Confirm') }}
        </x-ksu-button>
    </form>
</x-guest-layout>
