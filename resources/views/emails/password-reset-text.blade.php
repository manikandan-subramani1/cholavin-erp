{{ $companyName }} ERP - Password reset

Hello {{ $user->name }},

We received a request to reset your ERP password. Use this secure link to choose a new password:

{{ $resetUrl }}

This link expires in {{ $expiryMinutes }} minutes. If you did not request a reset, ignore this email and your current password will remain unchanged.

{{ $companyName }}
{{ $supportEmail }}
