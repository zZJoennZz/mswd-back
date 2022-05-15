@component('mail::message')
# Congratulations!

Your application has been approved! 

@component('mail::button', ['url' => env('FRONTEND_URL') . 'track/' . $appId])
Learn more!
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
