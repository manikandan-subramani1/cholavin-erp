@extends('emails.layouts.cholavin')

@section('title', 'Reset your '.$companyName.' ERP password')

@section('content')
    <div style="margin-bottom:10px;color:#a47700;font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;">Security request</div>
    <h1 style="margin:0 0 14px;color:#4a0012;font-size:28px;line-height:1.25;">Reset your password</h1>
    <p style="margin:0 0 18px;color:#51484b;font-size:15px;line-height:1.7;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 25px;color:#51484b;font-size:15px;line-height:1.7;">
        We received a request to reset the password for your {{ $companyName }} ERP account. Select the button below to choose a new password.
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:9px;background:#800020;">
                <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 30px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;border-radius:9px;">Reset password</a>
            </td>
        </tr>
    </table>

    <div style="padding:15px 17px;background:#fff8e1;border:1px solid #efdfaa;border-radius:10px;color:#62575b;font-size:13px;line-height:1.6;">
        This secure link expires in <strong>{{ $expiryMinutes }} minutes</strong>. If you did not request a password reset, you can safely ignore this email; your current password will remain unchanged.
    </div>
    <p style="margin:22px 0 0;color:#81767a;font-size:12px;line-height:1.6;">
        If the button does not work, copy and paste this address into your browser:<br>
        <a href="{{ $resetUrl }}" style="color:#800020;word-break:break-all;">{{ $resetUrl }}</a>
    </p>
@endsection
