<x-ksu-layout page-title="Welcome">
    <div class="flex flex-col items-center gap-6 py-16 text-center">
        <x-application-logo class="h-16 w-16" />
        <div class="max-w-2xl space-y-4">
            <h1 class="text-4xl font-bold text-ksu-900">KSU Dorm Management System</h1>
            <p class="text-lg text-slate-600">
                Streamline admissions, room assignments, and daily attendance for Kalinga State University dormitories.
                Access the updated dashboards and workflows to keep every tenant supported and every bed accounted for.
            </p>
        </div>
        <div class="flex flex-wrap justify-center gap-4">
            <x-ksu-button as="a" href="{{ route('home') }}">Visit Public Portal</x-ksu-button>
            <x-ksu-button as="a" href="{{ route('login') }}" variant="outline">Sign In</x-ksu-button>
        </div>
    </div>
</x-ksu-layout>
