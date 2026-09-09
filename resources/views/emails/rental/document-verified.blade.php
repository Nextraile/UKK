@extends('emails.layouts.base', [
    'greeting' => "Halo {$document->rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Dokumen <strong>{{ $document->document_type }}</strong> untuk rental Anda telah diverifikasi.
    </p>

    @include('emails.components.badge', ['type' => 'success', 'text' => 'Document Verified'])

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Kost:</strong> {{ $document->rental->room->roomType->kost->name }}</p>
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Dokumen:</strong> {{ $document->document_type }}</p>
        <p style="margin:0;"><strong style="color:#111827;">Diverifikasi pada:</strong> {{ $document->verified_at->format('d M Y H:i') }}</p>
    @endcomponent

    @include('emails.components.button', [
        'url' => route('rentals.show', $document->rental),
        'text' => 'Lihat Status Rental',
        'variant' => 'primary'
    ])
@endsection
