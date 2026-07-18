<?php

namespace App\Support;

use App\Models\Setting;

class MailBrand
{
    public static function details(): array
    {
        $settings = Setting::values();
        $logo = $settings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png';

        return [
            'companyName' => $settings['company_name'] ?? config('app.name', 'Cholavin ERP'),
            'tagline' => $settings['tagline'] ?? 'Rice Trading & Business Management',
            'logoUrl' => asset(ltrim($logo, '/')),
            'supportEmail' => ($settings['contact_email'] ?? null) ?: config('mail.from.address'),
            'supportPhone' => $settings['contact_phone'] ?? null,
            'companyAddress' => $settings['company_address'] ?? null,
        ];
    }
}
