@component('mail::message')
# Rental Dikonfirmasi

Halo {{ $rental->user->name }},

Selamat! Rental Anda untuk **{{ $rental->room->roomType->kost->name }}** telah dikonfirmasi.

@component('mail::panel')
**Kamar:** {{ $rental->room->roomType->name }} - {{ $rental->room->code }}

**Tanggal Mulai:** {{ $rental->start_date->format('d M Y') }}

**Durasi:** {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
@endcomponent

Rental akan otomatis aktif pada tanggal mulai. Silakan koordinasi dengan pemilik kost untuk check-in.

@component('mail::button', ['url' => route('rentals.show', $rental)])
Lihat Detail Rental
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
