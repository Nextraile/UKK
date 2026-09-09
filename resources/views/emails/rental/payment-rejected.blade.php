@extends('emails.layouts.base', [
    'greeting' => "Halo {$rental->user->first_name},",
])

@section('content')
    <p style="margin:0 0 8px 0;">
        Bukti pembayaran untuk booking <strong>{{ $rental->room->roomType->kost->name }}</strong> ditolak oleh admin.
    </p>

    @include('emails.components.badge', ['type' => 'error', 'text' => 'Payment Rejected'])

    @component('emails.components.panel')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left-color:#DC2626;">
            <tr>
                <td>
                    <p style="margin:0 0 8px 0;"><strong style="color:#111827;">Alasan Penolakan:</strong></p>
                    <p style="margin:0;color:#DC2626;">{{ $rental->payment->rejection_reason }}</p>
                </td>
            </tr>
        </table>
    @endcomponent

    <p style="margin:16px 0;">
        Silakan upload ulang bukti pembayaran yang sesuai sebelum deadline.
    </p>

    <p style="margin:0 0 16px 0;font-size:14px;color:#DC2626;">
        <strong>Deadline:</strong> {{ $rental->payment->expired_at->format('d M Y H:i') }}
    </p>

    @include('emails.components.button', [
        'url' => route('rentals.show', $rental),
        'text' => 'Upload Ulang Bukti Pembayaran',
        'variant' => 'danger'
    ])
@endsection
