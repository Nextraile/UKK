@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Rental Anda untuk <strong>{{ $rental->room->roomType->kost->name }}</strong> telah selesai. Terima kasih!
    </p>

    @component('emails.components.panel')
        <p style="margin:0;"><strong style="color:#111827;">Periode:</strong> {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}</p>
    @endcomponent

    <p style="margin:16px 0;"><strong style="color:#111827;">Bagikan Pengalaman Anda:</strong></p>
    <p style="margin:0 0 16px 0;">
        Bantu calon penyewa lain dengan membagikan review tentang kost ini.
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $rental),
        'text' => 'Tulis Review',
        'variant' => 'secondary'
    ])
@endsection
