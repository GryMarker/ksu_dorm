<x-guest-layout>
    <div class="space-y-4">
        <p class="text-sm text-slate-600">
            {{ __('Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you. If you didn\'t receive the email, we will gladly send you another.') }}
        </p>

        @if (session('status') === 'verification-link-sent')
            <div class="rounded-2xl border border-ksu-600/20 bg-ksu-100/60 px-4 py-3 text-sm font-medium text-ksu-800">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
    </div>

    <div class="mt-6 space-y-4">
        <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
            @csrf
            <x-ksu-button type="submit" full>
                {{ __('Resend Verification Email') }}
            </x-ksu-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-ksu-button type="submit" variant="outline" full>
                {{ __('Log Out') }}
            </x-ksu-button>
        </form>
    </div>
</x-guest-layout>
