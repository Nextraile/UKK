@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Rental Anda untuk <strong>{{ $rental->room->roomType->kost->name }}</strong> telah aktif hari ini!
    </p>

    @include('emails.components.badge', ['type' => 'success', 'text' => 'Active'])

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Kamar:</strong> {{ $rental->room->roomType->name }} - {{ $rental->room->code }}</p>
        <p style="margin:0;"><strong style="color:#111827;">Periode:</strong> {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}</p>
    @endcomponent

    <p style="margin:16px 0;">
        Selamat menempati kost. Jika ada pertanyaan, hubungi pemilik kost.
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $rental),
        'text' => 'Lihat Detail Rental',
        'variant' => 'primary'
    ])
@endsection
