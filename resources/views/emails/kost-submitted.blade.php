@extends('emails.layouts.base', [
    'greeting' => 'Halo Super Admin,',
])

@section('content')
    <p style="margin:0 0 16px 0;">
        Kost baru telah disubmit untuk ditinjau.
    </p>

    @component('emails.components.panel')
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Nama Kost:</strong> {{ $kost->name }}</p>
        <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Pemilik:</strong> {{ $kost->owner->name }}</p>
        <p style="margin:0;"><strong style="color:#111827;">Kategori:</strong> {{ $kost->categories->pluck('name')->join(', ') }}</p>
    @endcomponent

    <p style="margin:16px 0;">
        Silakan tinjau detail submission dan setujui atau tolak.
    </p>

    @include('emails.components.button', [
        'url' => url('/super-admin/kost-submissions/' . $kost->id),
        'text' => 'Tinjau Submission',
        'variant' => 'primary'
    ])
@endsection
