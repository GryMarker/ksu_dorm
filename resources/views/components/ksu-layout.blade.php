@props([
    'pageTitle' => null,
    'container' => true,
])

@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $tenant = $user?->tenant;
    $tenantApproved = $tenant && $tenant->onboarding_status === \App\Models\Tenant::STATUS_APPROVED;

    $navItems = [];

    if (! $user) {
        $navItems = [
            ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home')],
            ['label' => 'Apply', 'href' => route('tenant.apply.form'), 'active' => request()->routeIs('tenant.apply.*')],
            ['label' => 'Admin Login', 'href' => url('/login?guard=admin'), 'active' => false],
        ];
    } elseif ($user->isTenant()) {
        if ($tenantApproved) {
            $navItems = [
                ['label' => 'Dashboard', 'href' => route('tenant.dashboard'), 'active' => request()->routeIs('tenant.dashboard')],
                ['label' => 'Room Availability', 'href' => route('tenant.availability'), 'active' => request()->routeIs('tenant.availability')],
                ['label' => 'My Room', 'href' => route('tenant.myroom'), 'active' => request()->routeIs('tenant.myroom')],
                ['label' => 'Attendance', 'href' => route('tenant.attendance.index'), 'active' => request()->routeIs('tenant.attendance.*')],
                ['label' => 'Profile', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
            ];
        } else {
            $navItems = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Application Form', 'href' => route('tenant.apply.form'), 'active' => request()->routeIs('tenant.apply.form')],
                ['label' => 'Interview Slots', 'href' => route('tenant.apply.slots'), 'active' => request()->routeIs('tenant.apply.slots')],
                ['label' => 'Status', 'href' => route('tenant.apply.status'), 'active' => request()->routeIs('tenant.apply.status')],
            ];
        }
    } elseif ($user->isEmployee()) {
        if ($tenantApproved) {
            $navItems = [
                ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'active' => request()->routeIs('employee.dashboard')],
                ['label' => 'Cottages', 'href' => route('employee.cottages.index'), 'active' => request()->routeIs('employee.cottages.*')],
                ['label' => 'Payments', 'href' => route('employee.payments.index'), 'active' => request()->routeIs('employee.payments.*')],
                ['label' => 'Status', 'href' => route('employee.status'), 'active' => request()->routeIs('employee.status')],
                ['label' => 'Profile', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
            ];
        } else {
            $navItems = [
                ['label' => 'Application', 'href' => route('employee.apply.form'), 'active' => request()->routeIs('employee.apply.form')],
                ['label' => 'Status', 'href' => route('employee.status'), 'active' => request()->routeIs('employee.status')],
                ['label' => 'Profile', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
            ];
        }
    } elseif ($user->isPresident()) {
        $navItems = [
            ['label' => 'Onboarding', 'href' => route('president.approvals.employees.index'), 'active' => request()->routeIs('president.approvals.employees.*')],
            ['label' => 'Payments', 'href' => route('president.payments.index'), 'active' => request()->routeIs('president.payments.*')],
            ['label' => 'Cottages', 'href' => route('management.cottages.index'), 'active' => request()->routeIs('management.cottages.*')],
            ['label' => 'Profile', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
        ];
    } elseif (in_array($user->role, ['dorm_master', 'student_director'])) {
        $navItems = [
            ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ['label' => 'Students', 'href' => route('admin.students.index'), 'active' => request()->routeIs('admin.students.*')],
            ['label' => 'Applications', 'href' => route('admin.applications.index'), 'active' => request()->routeIs('admin.applications.*')],
            ['label' => 'Rooms', 'href' => route('admin.rooms.index'), 'active' => request()->routeIs('admin.rooms.*')],
            ['label' => 'Reservations', 'href' => route('admin.reservations.index'), 'active' => request()->routeIs('admin.reservations.*')],
            ['label' => 'Interview Slots', 'href' => route('admin.interview-slots.index'), 'active' => request()->routeIs('admin.interview-slots.*')],
            ['label' => 'Interviews', 'href' => route('admin.interviews.index'), 'active' => request()->routeIs('admin.interviews.*')],
            ['label' => 'Attendance', 'href' => route('admin.attendance.index'), 'active' => request()->routeIs('admin.attendance.*')],
        ];
    } else {
        $navItems = [
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ['label' => 'Profile', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
        ];
    }

    $title = $pageTitle ? "{$pageTitle} | " : '';
    $title .= config('app.name', 'KSU Dorms');

    $toastMessages = collect([
        ['type' => 'success', 'message' => session('status') ?? session('success')],
        ['type' => 'error', 'message' => session('error')],
    ])->filter(fn ($toast) => filled($toast['message']));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-ksu-100/30">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans text-slate-800 antialiased bg-ksu-100/30">
        <div x-data="{ mobileOpen: false }" class="relative flex min-h-screen flex-col">
            <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/95 backdrop-blur">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 items-center justify-between gap-6">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('home') }}" class="flex items-center gap-3">
                                <img src="{{ asset('images/ksu-logo.png') }}" alt="KSU Dorms logo" class="h-10 w-10 rounded-xl">
                                <span class="text-lg font-semibold text-ksu-800">KSU Dorms</span>
                            </a>
                        </div>

                        <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-ksu-800">
                            @foreach($navItems as $item)
                                <a
                                    href="{{ $item['href'] }}"
                                    class="group relative pb-2 transition-colors hover:text-ksu-600 {{ $item['active'] ? 'text-ksu-600' : 'text-ksu-800' }}"
                                >
                                    <span>{{ $item['label'] }}</span>
                                    <span class="absolute inset-x-0 -bottom-0.5 h-0.5 rounded-full transition {{ $item['active'] ? 'bg-ksu-600' : 'bg-transparent group-hover:bg-ksu-400' }}"></span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="hidden lg:flex items-center gap-3">
                            @guest
                                <a href="{{ route('register') }}">
                                    <x-ksu-button variant="outline" size="sm">Student/Employee Register</x-ksu-button>
                                </a>
                                <a href="{{ route('login') }}">
                                    <x-ksu-button size="sm">Student/Employee Login</x-ksu-button>
                                </a>
                            @else
                                <span class="hidden text-sm font-medium text-ksu-800 xl:block">
                                    {{ \Illuminate\Support\Str::limit($user->name, 24) }}
                                </span>
                                <a href="{{ route('profile.edit') }}">
                                    <x-ksu-button variant="subtle" size="sm">Profile</x-ksu-button>
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-ksu-button type="submit" size="sm" variant="solid">
                                        Logout
                                    </x-ksu-button>
                                </form>
                            @endguest
                        </div>

                        <button
                            type="button"
                            @click="mobileOpen = !mobileOpen"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 p-2 text-ksu-800 transition hover:bg-ksu-100/80 lg:hidden"
                            aria-label="Toggle navigation"
                        >
                            <svg x-show="!mobileOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                            <svg x-cloak x-show="mobileOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M6 18 18 6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    x-cloak
                    x-show="mobileOpen"
                    x-transition
                    class="border-t border-slate-200/70 bg-white lg:hidden"
                >
                    <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                        <nav class="flex flex-col gap-3 text-sm font-medium text-ksu-800">
                            @foreach($navItems as $item)
                                <a
                                    href="{{ $item['href'] }}"
                                    @click="mobileOpen = false"
                                    class="rounded-xl px-4 py-2 transition {{ $item['active'] ? 'bg-ksu-100 text-ksu-700' : 'hover:bg-ksu-100/80' }}"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-4 flex flex-col gap-3">
                            @guest
                                <a href="{{ route('register') }}">
                                    <x-ksu-button variant="outline" full>Student/Employee Register</x-ksu-button>
                                </a>
                                <a href="{{ route('login') }}">
                                    <x-ksu-button full>Student/Employee Login</x-ksu-button>
                                </a>
                            @else
                                <a href="{{ route('profile.edit') }}" @click="mobileOpen = false">
                                    <x-ksu-button variant="subtle" full>Profile</x-ksu-button>
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-ksu-button type="submit" full>
                                        Logout
                                    </x-ksu-button>
                                </form>
                            @endguest
                        </div>
                    </div>
                </div>
            </header>

            @if($toastMessages->isNotEmpty())
                <div class="pointer-events-none fixed top-6 right-6 z-50 flex w-full max-w-sm flex-col gap-3">
                    @foreach($toastMessages as $toast)
                        <div
                            x-data="{ visible: true }"
                            x-init="setTimeout(() => visible = false, 5000)"
                            x-show="visible"
                            x-transition.opacity.duration.300ms
                            x-cloak
                            class="pointer-events-auto rounded-2xl border border-ksu-600/20 bg-white p-4 shadow-ksu {{ $toast['type'] === 'error' ? 'border-crimson/30' : '' }}"
                        >
                            <div class="flex items-start gap-3">
                                <span class="mt-1 flex h-8 w-8 items-center justify-center rounded-full {{ $toast['type'] === 'error' ? 'bg-crimson/10 text-crimson' : 'bg-ksu-600/10 text-ksu-700' }}">
                                    @if($toast['type'] === 'error')
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5" />
                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13 9 17 19 7" />
                                        </svg>
                                    @endif
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-ksu-900">
                                        {{ $toast['type'] === 'error' ? 'Something went wrong' : 'Success' }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-600">
                                        {{ $toast['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <main class="flex-1 py-10 sm:py-12">
                <div class="dashboard-container {{ $container ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' : '' }}">
                    {{ $slot }}
                </div>
            </main>

            <footer class="border-t border-slate-200/70 bg-white">
                <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-slate-600 sm:px-6 lg:px-8">
                    &copy; {{ now()->year }} Kalinga State University. All rights reserved.
                </div>
            </footer>
        </div>
    </body>
</html>

