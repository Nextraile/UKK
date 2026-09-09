@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Rental Anda untuk <strong>{{ $rental->room->roomType->kost->name }}</strong> telah dibatalkan.
    </p>

    @include('emails.components.badge', ['type' => 'error', 'text' => 'Cancelled'])

    @component('emails.components.panel')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left-color:#DC2626;">
            <tr>
                <td>
                    <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Alasan Pembatalan:</strong></p>
                    <p style="margin:0 0 8px 0;color:#DC2626;">{{ $rental->cancelled_reason }}</p>
                    <p style="margin:0;"><strong style="color:#111827;">Dibatalkan pada:</strong> {{ $rental->cancelled_at->format('d M Y H:i') }}</p>
                </td>
            </tr>
        </table>
    @endcomponent

    @if($rental->payment->status === 'verified')
    <p style="margin:16px 0;font-size:14px;color:#6B7280;">
        <strong>Catatan:</strong> Untuk proses refund (jika ada), silakan hubungi pemilik kost langsung.
    </p>
    @endif

    @include('emails.components.button', [
        'url' => route('marketplace.index'),
        'text' => 'Cari Kost Lainnya',
        'variant' => 'secondary'
    ])
@endsection
