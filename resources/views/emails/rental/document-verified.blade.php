@component('mail::message')
# Dokumen Diverifikasi

Halo {{ $document->rental->user->name }},

Dokumen **{{ $document->document_type }}** untuk rental **{{ $document->rental->room->roomType->kost->name }}** telah diverifikasi.

@component('mail::panel')
**Dokumen:** {{ $document->document_type }}

**Status:** Approved

**Diverifikasi pada:** {{ $document->verified_at->format('d M Y H:i') }}
@endcomponent

@component('mail::button', ['url' => route('rentals.show', $document->rental)])
Lihat Status Rental
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
