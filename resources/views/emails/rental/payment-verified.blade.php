@component('mail::message')
# Pembayaran Terverifikasi

Halo {{ $rental->user->name }},

Pembayaran untuk booking **{{ $rental->room->roomType->kost->name }}** telah diverifikasi oleh admin.

@component('mail::panel')
**Total Pembayaran:** Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}

**Tanggal Verifikasi:** {{ $rental->payment->verified_at->format('d M Y H:i') }}
@endcomponent

## Langkah Selanjutnya

Upload dokumen administrasi yang diperlukan untuk melengkapi proses rental.

@component('mail::button', ['url' => route('rentals.show', $rental)])
Upload Dokumen Sekarang
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
