@component('mail::message')
# Dokumen Ditolak

Halo {{ $document->rental->user->name }},

Dokumen **{{ $document->document_type }}** untuk rental **{{ $document->rental->room->roomType->kost->name }}** ditolak.

@component('mail::panel')
**Dokumen:** {{ $document->document_type }}

**Alasan Penolakan:**

{{ $document->rejection_reason }}
@endcomponent

## Langkah Selanjutnya

Silakan upload ulang dokumen yang sesuai.

@component('mail::button', ['url' => route('rentals.show', $document->rental)])
Upload Ulang Dokumen
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
