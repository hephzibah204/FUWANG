<x-mail::message>
# Registration Confirmed!

Hello **{{ $registration->full_name }}**,

Your seat has been successfully reserved for **{{ $webinar->title }}**.

<x-mail::panel>
### Access Pass Code: `{{ $registration->access_code }}`
- **Date & Time:** {{ $webinar->scheduled_at->format('F j, Y @ g:i A T') }}
- **Duration:** {{ $webinar->duration_minutes }} minutes
- **Presenter:** {{ $webinar->presenter_name }} ({{ $webinar->presenter_title ?? 'Speaker' }})
</x-mail::panel>

@if($webinar->join_url)
<x-mail::button :url="$webinar->join_url">
Join Live Webinar
</x-mail::button>
@else
<x-mail::button :url="route('webinars.show', $webinar->slug)">
View Webinar Details
</x-mail::button>
@endif

**What to expect:**
{!! nl2br(e($webinar->description)) !!}

If you have any questions before the session, simply reply to this email.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
