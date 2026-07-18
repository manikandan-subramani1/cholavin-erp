@extends('emails.layouts.cholavin')

@section('title', 'Your '.$companyName.' ERP account is ready')

@section('content')
    <div style="margin-bottom:10px;color:#a47700;font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;">Account created</div>
    <h1 style="margin:0 0 14px;color:#4a0012;font-size:28px;line-height:1.25;">Welcome to {{ $companyName }} ERP</h1>
    <p style="margin:0 0 18px;color:#51484b;font-size:15px;line-height:1.7;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 24px;color:#51484b;font-size:15px;line-height:1.7;">
        {{ $createdBy }} created an ERP account for you. Use the secure button below to create your private password and access your assigned business workspace.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:25px;background:#fffaf0;border:1px solid #ecdca6;border-radius:12px;">
        <tr><td colspan="2" style="padding:18px 20px 8px;color:#800020;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;">Your account</td></tr>
        <tr><td style="padding:5px 20px;color:#74686c;font-size:13px;">Username</td><td style="padding:5px 20px 5px 8px;color:#242424;font-size:13px;font-weight:700;text-align:right;">{{ $user->username }}</td></tr>
        <tr><td style="padding:5px 20px;color:#74686c;font-size:13px;">Email</td><td style="padding:5px 20px 5px 8px;color:#242424;font-size:13px;font-weight:700;text-align:right;">{{ $user->email }}</td></tr>
        <tr><td style="padding:5px 20px;color:#74686c;font-size:13px;">Role</td><td style="padding:5px 20px 5px 8px;color:#242424;font-size:13px;font-weight:700;text-align:right;">{{ $roleName }}</td></tr>
        <tr><td style="padding:5px 20px;color:#74686c;font-size:13px;">Shops</td><td style="padding:5px 20px 5px 8px;color:#242424;font-size:13px;font-weight:700;text-align:right;">{{ $shops ?: 'As assigned' }}</td></tr>
        <tr><td style="padding:5px 20px;color:#74686c;font-size:13px;">Inventory locations</td><td style="padding:5px 20px 5px 8px;color:#242424;font-size:13px;font-weight:700;text-align:right;">{{ $godowns ?: 'As assigned' }}</td></tr>
        <tr><td style="padding:5px 20px 18px;color:#74686c;font-size:13px;">Financial years</td><td style="padding:5px 20px 18px 8px;color:#242424;font-size:13px;font-weight:700;text-align:right;">{{ $financialYears ?: 'As assigned' }}</td></tr>
    </table>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:9px;background:#800020;">
                <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;border-radius:9px;">Set my password</a>
            </td>
        </tr>
    </table>

    <div style="padding:15px 17px;background:#f5f6fa;border-left:4px solid #d4af37;color:#62575b;font-size:13px;line-height:1.6;">
        For your security, no password is included in this email. This link expires in {{ $expiryMinutes }} minutes and can be used only once.
    </div>
    <p style="margin:22px 0 0;color:#81767a;font-size:12px;line-height:1.6;">
        After setting your password, sign in at <a href="{{ $loginUrl }}" style="color:#800020;">{{ $loginUrl }}</a>.
    </p>
@endsection
