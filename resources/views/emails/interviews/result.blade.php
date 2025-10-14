<x-mail::message>
# Interview Decision

Hello {{ $tenant->user->name }},

Your interview has been marked as **{{ ucfirst($interview->result) }}**.

@switch($interview->result)
    @case('approved')
You may now proceed to reserve a room from your tenant portal.
        @break
    @case('recheck')
Please review the notes provided and resubmit the necessary information.
        @break
    @case('rejected')
Unfortunately, we are unable to approve your application at this time.
        @break
@endswitch

@if($interview->notes)
> {{ $interview->notes }}
@endif

Thanks,
{{ config('app.name') }}
</x-mail::message>
