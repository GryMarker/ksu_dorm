@component('mail::message')
# Dorm Application Update

Hello {{ $tenant->full_name ?? $tenant->user?->name }},

After reviewing your dorm application, we are unable to approve it at this time.

@if($notes)
@component('mail::panel')
**Notes from the Dorm Master:**  
{{ $notes }}
@endcomponent
@endif

If you need clarification or wish to reapply, please contact the dormitory office.

Thanks,  
{{ config('app.name') }}
@endcomponent
