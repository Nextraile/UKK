@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Pembayaran untuk rental Anda telah diverifikasi oleh admin.
    </p>

    @include('emails.components.badge', ['type' => 'success', 'text' => 'Payment Verified'])

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Kost:</strong> {{ $rental->room->roomType->kost->name }}</p>
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Total Pembayaran:</strong> Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}</p>
        <p style="margin:0;"><strong style="color:#111827;">Tanggal Verifikasi:</strong> {{ $rental->payment->verified_at->format('d M Y H:i') }}</p>
    @endcomponent

    <p style="margin:16px 0;"><strong style="color:#111827;">Langkah Selanjutnya:</strong></p>
    <p style="margin:0 0 16px 0;">
        Upload dokumen administrasi yang diperlukan untuk melengkapi proses rental.
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $rental),
        'text' => 'Upload Dokumen Sekarang',
        'variant' => 'primary'
    ])
@endsection
