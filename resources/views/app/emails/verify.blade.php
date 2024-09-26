@component('mail::message')

    Thank you for registering!

    @component('mail::button', ['url' => $url])
        Verify Email
    @endcomponent


    Regards,<br>
    {{ config('app.name') }}

@endcomponent
