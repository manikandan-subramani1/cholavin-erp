{{ $companyName }} ERP - Account created

Hello {{ $user->name }},

{{ $createdBy }} created an ERP account for you.

Username: {{ $user->username }}
Email: {{ $user->email }}
Role: {{ $roleName }}
Shops: {{ $shops ?: 'As assigned' }}
Inventory locations: {{ $godowns ?: 'As assigned' }}
Financial years: {{ $financialYears ?: 'As assigned' }}

Set your private password using this secure link:
{{ $resetUrl }}

The link expires in {{ $expiryMinutes }} minutes and can be used only once. No password is included in this email.

Sign in: {{ $loginUrl }}

{{ $companyName }}
{{ $supportEmail }}
