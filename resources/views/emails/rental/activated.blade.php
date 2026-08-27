@component('mail::message')
# Rental Aktif

Halo {{ $rental->user->name }},

Rental Anda untuk **{{ $rental->room->roomType->kost->name }}** telah aktif hari ini!

@component('mail::panel')
**Kamar:** {{ $rental->room->roomType->name }} - {{ $rental->room->code }}

**Periode:** {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}
@endcomponent

Selamat menempati kost. Jika ada pertanyaan, hubungi pemilik kost.

@component('mail::button', ['url' => route('rentals.show', $rental)])
Lihat Detail Rental
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
