{{-- Status Badge Component
Usage: @include('emails.components.badge', ['type' => 'success', 'text' => 'Approved'])
Types: success, warning, error, info
--}}

@php
$badgeConfig = [
    'success' => ['bg' => '#D1FAE5', 'color' => '#047857'],
    'warning' => ['bg' => '#FEF3C7', 'color' => '#B45309'],
    'error' => ['bg' => '#FEE2E2', 'color' => '#B91C1C'],
    'info' => ['bg' => '#DBEAFE', 'color' => '#1D4ED8'],
];
$config = $badgeConfig[$type] ?? $badgeConfig['info'];
@endphp

<span style="display:inline-block;padding:4px 12px;background-color:{{ $config['bg'] }};color:{{ $config['color'] }};font-size:12px;font-weight:600;border-radius:12px;text-transform:uppercase;letter-spacing:0.5px;">{{ $text }}</span>
