@component('mail::message')
# Interview Result Update

An interview decision has been recorded for a student applicant.

@component('mail::panel')
**Student:** {{ $tenant->full_name ?? $tenant->user?->name }}  
**Student ID:** {{ $tenant->university_id_no }}  
**Result:** {{ \Illuminate\Support\Str::headline($interview->result) }}  
**Scheduled:** {{ $interview->scheduled_at?->format('M d, Y h:i A') }}
@endcomponent

If needed, review the student's profile for follow-up actions.

Thanks,  
{{ config('app.name') }}
@endcomponent
