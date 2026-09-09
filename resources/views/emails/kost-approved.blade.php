@extends('emails.layouts.base', [
    'greeting' => "Halo {$kost->owner->name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Kabar baik! Kost Anda telah disetujui oleh Super Admin.
    </p>

    @include('emails.components.badge', ['type' => 'success', 'text' => 'Approved'])

    @component('emails.components.panel')
        <p style="margin:0;"><strong style="color:#111827;">Nama Kost:</strong> {{ $kost->name }}</p>
    @endcomponent

    <p style="margin:16px 0 8px 0;"><strong style="color:#111827;">Langkah Selanjutnya:</strong></p>
    <ul style="margin:0 0 16px 0;padding-left:20px;">
        <li style="margin:4px 0;">Anda sekarang dapat mempublikasikan kost agar terlihat oleh penyewa</li>
        <li style="margin:4px 0;">Tinjau detail kost untuk memastikan semuanya akurat</li>
        <li style="margin:4px 0;">Setelah dipublikasikan, penyewa akan dapat melihat dan memesan kost Anda</li>
    </ul>

    @include('emails.components.button', [
        'url' => url('/admin/kosts/' . $kost->id),
        'text' => 'Lihat Kost',
        'variant' => 'primary'
    ])
@endsection
