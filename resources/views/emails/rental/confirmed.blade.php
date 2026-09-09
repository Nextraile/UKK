@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Selamat! Rental Anda untuk <strong>{{ $rental->room->roomType->kost->name }}</strong> telah dikonfirmasi.
    </p>

    @include('emails.components.badge', ['type' => 'success', 'text' => 'Confirmed'])

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Kamar:</strong> {{ $rental->room->roomType->name }} - {{ $rental->room->code }}</p>
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Tanggal Mulai:</strong> {{ $rental->start_date->format('d M Y') }}</p>
        <p style="margin:0;"><strong style="color:#111827;">Durasi:</strong> {{ $rental->duration_value }} {{ __($rental->duration_unit) }}</p>
    @endcomponent

    <p style="margin:16px 0;">
        Rental akan otomatis aktif pada tanggal mulai. Silakan koordinasi dengan pemilik kost untuk check-in.
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $rental),
        'text' => 'Lihat Detail Rental',
        'variant' => 'primary'
    ])
@endsection
