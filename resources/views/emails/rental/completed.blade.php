@component('mail::message')
# Rental Selesai

Halo {{ $rental->user->name }},

Rental Anda untuk **{{ $rental->room->roomType->kost->name }}** telah selesai.

@component('mail::panel')
**Periode:** {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}
@endcomponent

## Bagikan Pengalaman Anda

Bantu calon penyewa lain dengan membagikan review tentang kost ini.

@component('mail::button', ['url' => route('rentals.show', $rental)])
Tulis Review
@endcomponent

Terima kasih telah menggunakan layanan kami!

{{ config('app.name') }}
@endcomponent
