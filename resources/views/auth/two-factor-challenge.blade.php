<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 space-y-2 text-sm text-slate-600">
        <p>Enter the 6-digit code sent to <span class="font-semibold text-slate-800">{{ $email }}</span>.</p>
        <p>This step is required after login and registration before access is granted.</p>
        <p>The code expires in {{ $expiresInMinutes }} minutes and can only be used once.</p>
    </div>

    <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-6">
        @csrf

        <div class="space-y-2">
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input
                id="code"
                type="text"
                name="code"
                :value="old('code')"
                required
                autofocus
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
            />
            <x-input-error :messages="$errors->get('code')" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a
                href="{{ route('login') }}"
                class="text-sm font-semibold text-ksu-600 underline-offset-2 hover:underline focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2"
            >
                Back to login
            </a>

            <x-ksu-button type="submit">
                {{ __('Verify code') }}
            </x-ksu-button>
        </div>
    </form>

    <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-4">
        @csrf
        <button
            type="submit"
            class="text-sm font-semibold text-ksu-600 underline-offset-2 hover:underline focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2"
        >
            {{ __('Send a new code') }}
        </button>
    </form>
</x-guest-layout>
