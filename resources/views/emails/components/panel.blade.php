{{-- Info Panel Component
Usage: @include('emails.components.panel')
  @slot('content')
    Your panel content here
  @endslot
@endincludes
--}}

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">
    <tr>
        <td style="background-color:#F9FAFB;border-left:4px solid #2563EB;border-radius:4px;padding:20px;">
            <div style="font-size:15px;line-height:1.6;color:#4B5563;">
                {{ $slot }}
            </div>
        </td>
    </tr>
</table>
