@component('mail::message')
# Tenant Membatalkan Rental

Halo {{ $rental->room->roomType->kost->owner->name }},

Tenant **{{ $rental->user->name }}** telah membatalkan rental untuk kost **{{ $rental->room->roomType->kost->name }}**.

@component('mail::panel')
**Tenant:** {{ $rental->user->name }} ({{ $rental->user->email }})

**Kamar:** {{ $rental->room->roomType->name }} - {{ $rental->room->code }}

**Alasan Pembatalan:**

{{ $rental->cancelled_reason }}

**Dibatalkan pada:** {{ $rental->cancelled_at->format('d M Y H:i') }}
@endcomponent

@if($rental->payment->status === 'success')
**Catatan:** Jika tenant meminta refund, silakan proses sesuai kebijakan Anda.
@endif

@component('mail::button', ['url' => route('admin.rentals.show', $rental)])
Lihat Detail Rental
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
