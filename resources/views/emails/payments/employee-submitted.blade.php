@component('mail::message')
# New Employee Payment

An employee submitted a housing payment record for president review.

@component('mail::panel')
**Employee:** {{ $payment->tenant->full_name ?? $payment->tenant->user?->name }}  
**Billing Month:** {{ $payment->billing_month->format('F Y') }}  
**Amount:** &#8369; {{ number_format($payment->amount, 2) }}  
**Salary Deduction:** {{ $payment->salary_deduction ? 'Yes' : 'No' }}  
**Email:** {{ $payment->tenant->user?->email }}
@endcomponent

@if($payment->employee_note)
**Employee note:** {{ $payment->employee_note }}
@endif

Please review the payment record in the president payment approvals page.

Thanks,  
{{ config('app.name') }}
@endcomponent
