<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input
                id="remember_me"
                type="checkbox"
                class="rounded border-slate-300 text-ksu-600 focus:ring-ksu-400"
                name="remember"
            >
            <span>{{ __('Remember me') }}</span>
        </label>

        <div class="flex flex-wrap items-center justify-between gap-3">
            @if (Route::has('password.request'))
                <a
                    class="text-sm font-semibold text-ksu-600 underline-offset-2 hover:underline focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2"
                    href="{{ route('password.request') }}"
                >
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-ksu-button type="submit">
                {{ __('Log in') }}
            </x-ksu-button>
        </div>
    </form>
</x-guest-layout>
