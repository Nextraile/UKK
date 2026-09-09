@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 16px 0;">
        Booking Anda untuk <strong>{{ $rental->room->roomType->kost->name }}</strong> berhasil dibuat!
    </p>

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Detail Booking:</strong></p>
        <ul style="margin:0;padding-left:20px;">
            <li style="margin:4px 0;">Kamar: {{ $rental->room->roomType->name }} ({{ $rental->room->code }})</li>
            <li style="margin:4px 0;">Tanggal mulai: {{ $rental->start_date->format('d M Y') }}</li>
            <li style="margin:4px 0;">Durasi: {{ $rental->duration_value }} {{ __($rental->duration_unit) }}</li>
            <li style="margin:4px 0;">Total pembayaran: Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}</li>
        </ul>
    @endcomponent

    <p style="margin:16px 0;"><strong style="color:#111827;">Langkah Selanjutnya:</strong></p>
    <p style="margin:0 0 8px 0;">
        Selesaikan pembayaran sebelum <strong style="color:#DC2626;">{{ $rental->payment->expired_at->format('d M Y H:i') }}</strong> (48 jam).
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $rental),
        'text' => 'Lihat Detail & Upload Bukti Bayar',
        'variant' => 'primary'
    ])
@endsection
