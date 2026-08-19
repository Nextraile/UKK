<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $purpose === 'password-reset' ? '[SewaKost] Kode Reset Password Anda' : '[SewaKost] Kode Verifikasi Email Anda' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    {{-- Brand color bar --}}
                    <tr>
                        <td style="background-color:#4f46e5;height:6px;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- Header --}}
                    <tr>
                        <td style="padding:32px 40px 0 40px;text-align:center;">
                            <span style="font-size:24px;font-weight:700;color:#4f46e5;letter-spacing:-0.5px;">SewaKost</span>
                        </td>
                    </tr>

                    {{-- Greeting + instruction --}}
                    <tr>
                        <td style="padding:24px 40px 0 40px;">
                            <p style="margin:0 0 8px 0;font-size:18px;font-weight:600;color:#111827;">Halo {{ $user->first_name }},</p>
                            <p style="margin:0;font-size:15px;line-height:1.6;color:#4b5563;">{{ $purpose === 'password-reset' ? 'Gunakan kode berikut untuk mengatur ulang password Anda:' : 'Gunakan kode berikut untuk verifikasi email Anda:' }}</p>
                        </td>
                    </tr>

                    {{-- OTP code block --}}
                    <tr>
                        <td align="center" style="padding:24px 40px;">
                            <div style="display:inline-block;padding:16px 40px;background-color:#eef2ff;border-radius:8px;border:1px solid #c7d2fe;">
                                <span style="font-family:'Courier New',Consolas,monospace;font-size:32px;font-weight:700;letter-spacing:8px;color:#4f46e5;">{{ $code }}</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Expiry warning --}}
                    <tr>
                        <td style="padding:0 40px 8px 40px;">
                            <p style="margin:0;font-size:14px;color:#6b7280;text-align:center;">Kode ini berlaku selama 15 menit.</p>
                        </td>
                    </tr>

                    {{-- Security notice --}}
                    <tr>
                        <td style="padding:24px 40px 0 40px;border-top:1px solid #f3f4f6;">
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#9ca3af;text-align:center;">Jika Anda tidak meminta kode ini, abaikan email ini.</p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:24px 40px;background-color:#f9fafb;">
                            <p style="margin:0 0 8px 0;font-size:12px;color:#6b7280;text-align:center;">&copy; {{ date('Y') }} SewaKost</p>
                            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
                                <a href="#" style="color:#6b7280;text-decoration:underline;">Contact</a>
                                &nbsp;|&nbsp;
                                <a href="#" style="color:#6b7280;text-decoration:underline;">Privacy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
