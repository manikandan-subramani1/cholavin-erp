<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'general' => [
                'company_name' => 'Cholavin',
                'tagline' => 'Your Rice Expert',
                'brand_logo' => 'frontend/assets/img/logo/logo-hm62.png',
                'auth_brand_image' => 'frontend/assets/img/logo/logo-hm64.png',
            ],
            'contact' => [
                'contact_phone' => '+91 99652 52555',
                'whatsapp_number' => '919965252555',
                'contact_email' => '',
                'company_address' => 'Pallipalayam & Kumarapalayam',
            ],
            'social' => ['facebook' => 'https://www.facebook.com/cholavinrice', 'instagram' => 'https://www.instagram.com/cholavin_', 'youtube' => '', 'linkedin' => ''],
            'mail' => ['mail_host' => '', 'mail_port' => '', 'mail_username' => '', 'mail_password' => '', 'mail_encryption' => 'tls', 'mail_from_address' => '', 'mail_from_name' => 'Cholavin'],
        ];

        foreach ($settings as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
            }
        }
    }
}
