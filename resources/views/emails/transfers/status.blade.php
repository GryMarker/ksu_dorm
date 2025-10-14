<x-mail::message>
# Transfer {{ ucfirst($status) }}

Hello {{ $reservation->tenant->user->name }},

Your transfer request for room **{{ $reservation->room->code }}** has been **{{ $status }}**.

@if($status === 'approved')
Please prepare to move on {{ optional($reservation->tenant->activeAssignment)->start_date?->format('F j, Y') ?? now()->format('F j, Y') }}. Your new bed is **{{ $reservation->bed?->bed_label ?? 'TBD' }}**.
@else
Your current assignment remains active. Reach out to the dorm office if you need further assistance.
@endif

Thanks,
{{ config('app.name') }}
</x-mail::message>
