@component('mail::message')
# We got your message!

Thank you for contacting us! Confirming we received your email and we will get back to you!

{{-- @component('mail::button', ['url' => ''])
Button Text
@endcomponent --}}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
