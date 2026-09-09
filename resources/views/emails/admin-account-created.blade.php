@extends('emails.layouts.base', [
    'greeting' => "Selamat datang di SewaKost, {$admin->first_name}!",
    'footerNote' => 'Silakan login dan ubah password Anda segera.'
])

@section('content')
    <p style="margin:0 0 16px 0;">
        Akun Admin Anda telah berhasil dibuat oleh Super Administrator. Anda sekarang dapat mengakses dashboard admin SewaKost.
    </p>

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Email:</strong> {{ $admin->email }}</p>
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Phone:</strong> {{ $admin->phone }}</p>
        <p style="margin:0;"><strong style="color:#111827;">Password Sementara:</strong> {{ $password }}</p>
    @endcomponent

    @include('emails.components.button', [
        'url' => route('login'),
        'text' => 'Login Sekarang',
        'variant' => 'primary'
    ])

    <p style="margin:16px 0 0 0;font-size:14px;color:#6B7280;">
        <strong>Penting:</strong> Untuk keamanan akun Anda, harap ubah password ini setelah login pertama kali melalui menu Profile.
    </p>
@endsection
