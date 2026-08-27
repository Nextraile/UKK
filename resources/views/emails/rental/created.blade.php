@component('mail::message')
# Booking Berhasil Dibuat

Halo {{ $rental->user->name }},

Booking Anda untuk **{{ $rental->room->roomType->kost->name }}** berhasil dibuat.

@component('mail::panel')
**Detail Booking:**

- Kamar: {{ $rental->room->roomType->name }} ({{ $rental->room->code }})
- Tanggal mulai: {{ $rental->start_date->format('d M Y') }}
- Durasi: {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
- Total pembayaran: Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
@endcomponent

## Langkah Selanjutnya

Selesaikan pembayaran sebelum **{{ $rental->payment->expired_at->format('d M Y H:i') }}** (48 jam).

@component('mail::button', ['url' => route('rentals.show', $rental)])
Lihat Detail & Upload Bukti Bayar
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
