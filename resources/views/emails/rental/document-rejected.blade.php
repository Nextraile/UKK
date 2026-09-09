@extends('emails.layouts.base', [
    'greeting' => "Halo {$document->rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Dokumen <strong>{{ $document->document_type }}</strong> untuk rental Anda ditolak oleh admin.
    </p>

    @include('emails.components.badge', ['type' => 'error', 'text' => 'Document Rejected'])

    @component('emails.components.panel')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left-color:#DC2626;">
            <tr>
                <td>
                    <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Kost:</strong> {{ $document->rental->room->roomType->kost->name }}</p>
                    <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Dokumen:</strong> {{ $document->document_type }}</p>
                    <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Alasan Penolakan:</strong></p>
                    <p style="margin:0;color:#DC2626;">{{ $document->rejection_reason }}</p>
                </td>
            </tr>
        </table>
    @endcomponent

    <p style="margin:16px 0;">
        Silakan upload ulang dokumen yang sesuai.
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $document->rental),
        'text' => 'Upload Ulang Dokumen',
        'variant' => 'danger'
    ])
@endsection
