@component('mail::message')
# Rental Dibatalkan

Halo {{ $rental->user->name }},

Rental Anda untuk **{{ $rental->room->roomType->kost->name }}** telah dibatalkan.

@component('mail::panel')
**Alasan Pembatalan:**

{{ $rental->cancelled_reason }}

**Dibatalkan pada:** {{ $rental->cancelled_at->format('d M Y H:i') }}
@endcomponent

@if($rental->payment->status === 'success')
**Catatan:** Untuk proses refund (jika ada), silakan hubungi pemilik kost langsung.
@endif

@component('mail::button', ['url' => route('marketplace.index')])
Cari Kost Lainnya
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
