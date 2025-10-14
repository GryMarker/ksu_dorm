@extends('layouts.public')

@section('content')
<section class="space-y-16 py-12">
    <div class="grid items-center gap-12 lg:grid-cols-2">
        <div class="space-y-6">
            <div class="inline-flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-ksu">
                <img src="{{ asset('images/ksu-logo.png') }}" alt="KSU Dorms logo" class="h-12 w-12 rounded-xl">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ksu-600">Kalinga State University Dorms</p>
                    <p class="text-sm text-slate-600">Stay close to campus with comfort and community.</p>
                </div>
            </div>

            <div class="space-y-4">
                <h1 class="text-4xl font-bold leading-tight text-ksu-900 sm:text-5xl">
                    Live, learn, and thrive in the heart of KSU.
                </h1>
                <p class="text-lg text-slate-600">
                    Apply for admission, reserve your room, and track your dorm life essentials with the official KSU Dorm Management System.
                </p>
            </div>

            <div class="flex flex-wrap gap-4">
                <x-ksu-button as="a" href="{{ route('tenant.apply.form') }}" size="lg">
                    Apply for Dorm Admission
                </x-ksu-button>
                <x-ksu-button as="a" href="{{ route('login') }}" variant="outline" size="lg">
                    Student Login
                </x-ksu-button>
            </div>

            <p class="text-sm text-slate-600">
                Already manage dorm operations? <a class="font-semibold text-ksu-600 underline-offset-2 hover:underline" href="{{ url('/login?guard=admin') }}">Admin Login</a>
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <x-ksu-card title="Apply" description="Complete the guided admission form and submit your requirements.">
                <div class="text-sm text-slate-600">
                    Track requirements, save progress, and see next steps instantly.
                </div>
            </x-ksu-card>
            <x-ksu-card title="Interview" description="Book an interview slot that fits your schedule.">
                <div class="text-sm text-slate-600">
                    Get timely reminders so you never miss your confirmation.
                </div>
            </x-ksu-card>
            <x-ksu-card title="Reserve" description="Browse vacancies and secure your dorm room.">
                <div class="text-sm text-slate-600">
                    Detailed room profiles help you pick the perfect fit.
                </div>
            </x-ksu-card>
            <x-ksu-card title="Monitor" description="Stay on top of attendance and dorm notices.">
                <div class="text-sm text-slate-600">
                    Daily logs keep you accountable and informed.
                </div>
            </x-ksu-card>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white px-6 py-5 text-sm text-slate-600 shadow-ksu sm:px-8 sm:py-6">
        Need help with your application or room assignment? Visit the Dorm Office or email <a href="mailto:dorms@ksu.edu.ph" class="font-semibold text-ksu-600 underline-offset-2 hover:underline">dorms@ksu.edu.ph</a>.
    </div>
</section>
@endsection
