<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'SewaKost' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F4F5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#4B5563;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F4F5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    {{-- Brand color bar (primary-600) --}}
                    <tr>
                        <td style="background-color:#2563EB;height:6px;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- Header: Text logo --}}
                    <tr>
                        <td style="padding:32px 40px 0 40px;text-align:center;">
                            <span style="font-size:24px;font-weight:700;color:#2563EB;letter-spacing:-0.5px;">SewaKost</span>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    <tr>
                        <td style="padding:24px 40px 0 40px;">
                            <p style="margin:0;font-size:18px;font-weight:600;color:#111827;">{{ $greeting ?? 'Halo,' }}</p>
                        </td>
                    </tr>

                    {{-- Main content (yielded) --}}
                    <tr>
                        <td style="padding:16px 40px 0 40px;">
                            <div style="font-size:15px;line-height:1.6;color:#4B5563;">
                                @yield('content')
                            </div>
                        </td>
                    </tr>

                    {{-- Optional CTA button --}}
                    @if(isset($buttonText) && isset($buttonUrl))
                    <tr>
                        <td align="center" style="padding:24px 40px;">
                            <a href="{{ $buttonUrl }}" style="display:inline-block;padding:14px 28px;background-color:#2563EB;color:#FFFFFF;text-decoration:none;border-radius:6px;font-weight:600;font-size:16px;">{{ $buttonText }}</a>
                        </td>
                    </tr>
                    @endif

                    {{-- Optional footer note --}}
                    @if(isset($footerNote))
                    <tr>
                        <td style="padding:24px 40px 0 40px;border-top:1px solid #F3F4F6;">
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#9CA3AF;text-align:center;">{{ $footerNote }}</p>
                        </td>
                    </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:24px 40px;background-color:#F9FAFB;">
                            <p style="margin:0 0 8px 0;font-size:12px;color:#6B7280;text-align:center;">&copy; {{ date('Y') }} SewaKost</p>
                            <p style="margin:0;font-size:12px;color:#9CA3AF;text-align:center;">
                                <a href="{{ config('app.url') }}/contact" style="color:#6B7280;text-decoration:underline;">Contact</a>
                                &nbsp;|&nbsp;
                                <a href="{{ config('app.url') }}/privacy" style="color:#6B7280;text-decoration:underline;">Privacy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
