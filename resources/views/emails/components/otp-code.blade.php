{{-- OTP Code Block Component
Usage: @include('emails.components.otp-code', ['code' => '123456'])
--}}

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
    <tr>
        <td align="center" style="padding:0;">
            <div style="display:inline-block;padding:16px 40px;background-color:#EEF2FF;border-radius:8px;border:1px solid #C7D2FE;">
                <span style="font-family:'Courier New',Consolas,monospace;font-size:32px;font-weight:700;letter-spacing:8px;color:#4F46E5;">{{ $code }}</span>
            </div>
        </td>
    </tr>
</table>
