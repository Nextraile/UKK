{{-- Button Component
Usage: @include('emails.components.button', ['url' => 'https://example.com', 'text' => 'Click Me', 'variant' => 'primary'])
Variants: primary, secondary, danger
--}}

@php
$buttonConfig = [
    'primary' => ['bg' => '#2563EB', 'hover' => '#1D4ED8'],
    'secondary' => ['bg' => '#F59E0B', 'hover' => '#D97706'],
    'danger' => ['bg' => '#DC2626', 'hover' => '#B91C1C'],
];
$config = $buttonConfig[$variant ?? 'primary'] ?? $buttonConfig['primary'];
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $url }}" style="display:inline-block;padding:14px 28px;background-color:{{ $config['bg'] }};color:#FFFFFF;text-decoration:none;border-radius:6px;font-weight:600;font-size:16px;">{{ $text }}</a>
        </td>
    </tr>
</table>
