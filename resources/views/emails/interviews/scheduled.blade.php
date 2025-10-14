<x-mail::message>
# Interview Scheduled

Hello {{ $tenant->user->name }},

Your dormitory interview has been scheduled on **{{ $scheduledAt->format('F j, Y g:i A') }}**.

Please arrive at least 15 minutes early and bring any supporting documents requested during your application.

Thanks,
{{ config('app.name') }}
</x-mail::message>
