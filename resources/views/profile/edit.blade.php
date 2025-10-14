<x-ksu-layout page-title="Profile">
    <div class="space-y-8">
        <x-ksu-card title="Profile Information" description="Update your account details and contact information.">
            <div class="max-w-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-ksu-card>

        <x-ksu-card title="Update Password" description="Ensure your account remains secure with a strong password.">
            <div class="max-w-2xl">
                @include('profile.partials.update-password-form')
            </div>
        </x-ksu-card>

        <x-ksu-card title="Delete Account" description="Permanently remove your dorm portal account.">
            <div class="max-w-2xl">
                @include('profile.partials.delete-user-form')
            </div>
        </x-ksu-card>
    </div>
</x-ksu-layout>
