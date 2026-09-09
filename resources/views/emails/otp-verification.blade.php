@extends('emails.layouts.base', [
    'greeting' => "Halo {$user->first_name},",
    'footerNote' => 'Jika Anda tidak meminta kode ini, abaikan email ini.'
])

@section('content')
    <p style="margin:0 0 16px 0;">
        {{ $purpose === 'password-reset' ? 'Gunakan kode berikut untuk mengatur ulang password Anda:' : 'Gunakan kode berikut untuk verifikasi email Anda:' }}
    </p>

    @include('emails.components.otp-code', ['code' => $code])

    <p style="margin:0;font-size:14px;color:#6B7280;text-align:center;">
        Kode ini berlaku selama 15 menit.
    </p>
@endsection
