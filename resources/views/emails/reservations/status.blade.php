<x-mail::message>
# Reservation {{ ucfirst($status) }}

Hello {{ $reservation->tenant->user->name }},

Your reservation for room **{{ $reservation->room->code }}** has been **{{ $status }}**.

@if($status === 'approved')
Your assigned bed is **{{ $reservation->bed?->bed_label ?? 'TBD' }}**. Please check in with the dorm master on your start date.
@else
Feel free to contact the dorm office if you have questions about this decision.
@endif

Thanks,
{{ config('app.name') }}
</x-mail::message>
