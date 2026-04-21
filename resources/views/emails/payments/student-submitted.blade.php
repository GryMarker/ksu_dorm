@component('mail::message')
# New Student Payment

A student submitted a dorm payment record for Dorm Master review.

@component('mail::panel')
**Student:** {{ $payment->tenant->full_name ?? $payment->tenant->user?->name }}  
**Student ID:** {{ $payment->tenant->university_id_no }}  
**Billing Month:** {{ $payment->billing_month->format('F Y') }}  
**Amount:** &#8369; {{ number_format($payment->amount, 2) }}  
**Email:** {{ $payment->tenant->user?->email }}
@endcomponent

@if($payment->student_note)
**Student note:** {{ $payment->student_note }}
@endif

Please review the payment record in the Dorm Master payment reviews page.

Thanks,  
{{ config('app.name') }}
@endcomponent
