<x-mail::message>
# Hello {{ $user->name }}

Thank you for create an account. Please verify your email using this button:

<x-mail::button :url="route('verify',$user->verification_token)">
Verify Account
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
