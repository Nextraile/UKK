@extends('emails.layouts.base', [
    'greeting' => "Halo {$kost->owner->name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Submission kost Anda telah ditolak oleh Super Admin.
    </p>

    @include('emails.components.badge', ['type' => 'error', 'text' => 'Rejected'])

    @component('emails.components.panel')
        <div style="border-left-color:#DC2626;">
            <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Nama Kost:</strong> {{ $kost->name }}</p>
            <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Alasan Penolakan:</strong></p>
            <p style="margin:0;color:#DC2626;">{{ $kost->rejected_reason }}</p>
        </div>
    @endcomponent

    <p style="margin:16px 0 8px 0;"><strong style="color:#111827;">Yang Perlu Dilakukan:</strong></p>
    <ul style="margin:0 0 16px 0;padding-left:20px;">
        <li style="margin:4px 0;">Tinjau alasan penolakan dengan seksama</li>
        <li style="margin:4px 0;">Edit kost Anda untuk memperbaiki masalah yang disebutkan</li>
        <li style="margin:4px 0;">Submit ulang kost setelah perbaikan selesai</li>
    </ul>

    @include('emails.components.button', [
        'url' => url('/admin/kosts/' . $kost->id . '/edit'),
        'text' => 'Perbaiki Kost',
        'variant' => 'danger'
    ])
@endsection
