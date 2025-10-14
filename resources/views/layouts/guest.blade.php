@php
    $pageTitle = $title ?? null;
@endphp

<x-ksu-layout :page-title="$pageTitle" container="false">
    <div class="flex min-h-[70vh] items-center justify-center bg-gradient-to-br from-ksu-100/80 via-white to-ksu-100/40 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <x-application-logo class="mx-auto h-16 w-16" />
                <h1 class="mt-4 text-2xl font-semibold text-ksu-900">KSU Dorms Portal</h1>
                <p class="mt-1 text-sm text-slate-600">Sign in or register to manage your dorm life.</p>
            </div>
            <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-ksu sm:p-8">
                {{ $slot }}
            </div>
            <p class="mt-6 text-center text-xs text-slate-500">
                © {{ now()->year }} Kalinga State University Dormitories. All rights reserved.
            </p>
        </div>
    </div>
</x-ksu-layout>
