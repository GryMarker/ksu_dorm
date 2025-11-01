<x-guest-layout>
    <form
        method="POST"
        action="{{ route('register') }}"
        class="space-y-6"
        x-data="{ userType: @js(old('user_type', 'student')) }"
    >
        @csrf

        <div class="space-y-3">
            <x-input-label value="Registering as" />
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2">
                    <input
                        type="radio"
                        name="user_type"
                        value="student"
                        class="h-4 w-4 text-ksu-600 focus:ring-ksu-500"
                        x-model="userType"
                    >
                    <span class="text-sm font-medium text-slate-700">Student</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input
                        type="radio"
                        name="user_type"
                        value="employee"
                        class="h-4 w-4 text-ksu-600 focus:ring-ksu-500"
                        x-model="userType"
                    >
                    <span class="text-sm font-medium text-slate-700">Employee</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('user_type')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" name="password" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="space-y-2" x-show="userType === 'employee'" x-cloak>
            <x-input-label for="employee_id_number" value="Employee ID Number" />
            <x-text-input
                id="employee_id_number"
                name="employee_id_number"
                type="text"
                :value="old('employee_id_number')"
                autocomplete="employee-id"
                x-bind:required="userType === 'employee'"
            />
            <x-input-error :messages="$errors->get('employee_id_number')" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a
                class="text-sm font-semibold text-ksu-600 underline-offset-2 hover:underline focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2"
                href="{{ route('login') }}"
            >
                {{ __('Already registered?') }}
            </a>

            <x-ksu-button type="submit">
                {{ __('Register') }}
            </x-ksu-button>
        </div>
    </form>
</x-guest-layout>
