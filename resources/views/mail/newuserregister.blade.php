@component('mail::message')
# Welcome, {{ $name }}!

Thank you for registering with us. Your OTP code is:

@component('mail::panel')
{{ $otp }}
@endcomponent

Please click the button below to verify your email.

@component('mail::button', ['url' => route('verify.otp', ['email' => $email])])
    Verify OTP
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
