<x-guest-layout>
    <div class="space-y-4">
        <p class="text-sm text-slate-600">
            {{ __('Forgot your password? No problem. Enter your email address and we will email you a password reset link.') }}
        </p>

        @if (session('status'))
            <div class="rounded-2xl border border-ksu-600/20 bg-ksu-100/60 px-4 py-3 text-sm font-medium text-ksu-800">
                {{ session('status') }}
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-6">
        @csrf

        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-ksu-button type="submit" full>
            {{ __('Email Password Reset Link') }}
        </x-ksu-button>
    </form>
</x-guest-layout>
