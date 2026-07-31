<x-mail::message>
# Hello!

Your login OTP code is:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

This code will expire in 5 minutes. If you did not request this code, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
