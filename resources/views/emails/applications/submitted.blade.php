@component('mail::message')
# New Student Application

A verified student has submitted a dorm application and is ready for review.

@component('mail::panel')
**Student:** {{ $tenant->full_name ?? $tenant->user?->name }}  
**Student ID:** {{ $tenant->university_id_no }}  
**Program:** {{ $tenant->program }}  
**Year Level:** {{ $tenant->year_level }}  
**Email:** {{ $tenant->user?->email }}
@endcomponent

Please review the application and schedule an interview.

Thanks,  
{{ config('app.name') }}
@endcomponent
