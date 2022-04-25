@component('mail::message')
# Here's your application #: {{ $appId }}

You can track your application by clicking the button below!

@component('mail::button', ['url' => env('FRONTEND_URL', 'http://localhost:3000/')])
Track
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
