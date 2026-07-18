<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>@yield('title', $companyName.' ERP')</title>
</head>
<body style="margin:0;padding:0;background:#f5f6fa;color:#242424;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader ?? '' }}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f5f6fa;">
    <tr>
        <td align="center" style="padding:32px 14px;">
            <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;background:#ffffff;border:1px solid #eadfe2;border-radius:16px;overflow:hidden;box-shadow:0 12px 32px rgba(74,0,18,.08);">
                <tr>
                    <td style="padding:24px 30px;background:#4a0012;border-bottom:4px solid #d4af37;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td valign="middle">
                                    <img src="{{ $logoUrl }}" width="190" alt="{{ $companyName }}" style="display:block;width:190px;max-width:100%;height:auto;border:0;">
                                </td>
                                <td align="right" valign="middle" style="color:#f4df9b;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">
                                    Business Management
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:34px 34px 30px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 30px;background:#fff8e1;border-top:1px solid #efdfaa;color:#74686c;font-size:12px;line-height:1.6;">
                        <strong style="color:#4a0012;">{{ $companyName }}</strong><br>
                        {{ $tagline }}
                        @if($companyAddress)<br>{{ $companyAddress }}@endif
                        @if($supportEmail || $supportPhone)
                            <br>
                            @if($supportEmail)<a href="mailto:{{ $supportEmail }}" style="color:#800020;text-decoration:none;">{{ $supportEmail }}</a>@endif
                            @if($supportEmail && $supportPhone)&nbsp;&nbsp;|&nbsp;&nbsp;@endif
                            @if($supportPhone)<span>{{ $supportPhone }}</span>@endif
                        @endif
                    </td>
                </tr>
            </table>
            <div style="max-width:620px;padding:18px 10px 0;color:#9a8e92;font-size:11px;line-height:1.5;text-align:center;">
                This is an automated security email from {{ $companyName }} ERP. Please do not share secure links from this message.
            </div>
        </td>
    </tr>
</table>
</body>
</html>
