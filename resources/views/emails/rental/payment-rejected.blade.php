@component('mail::message')
# Pembayaran Ditolak

Halo {{ $rental->user->name }},

Bukti pembayaran untuk booking **{{ $rental->room->roomType->kost->name }}** ditolak oleh admin.

@component('mail::panel')
**Alasan Penolakan:**

{{ $rental->payment->rejection_reason }}
@endcomponent

## Langkah Selanjutnya

Silakan upload ulang bukti pembayaran yang sesuai.

@component('mail::button', ['url' => route('rentals.show', $rental)])
Upload Ulang Bukti Pembayaran
@endcomponent

**Deadline:** {{ $rental->payment->expired_at->format('d M Y H:i') }}

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
