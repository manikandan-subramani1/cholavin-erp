<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SettingService
{
    private const GROUPS = [
        'general' => ['company_name', 'tagline'],
        'contact' => ['contact_phone', 'whatsapp_number', 'contact_email', 'company_address'],
        'social' => ['facebook', 'instagram', 'youtube', 'linkedin'],
        'mail' => ['mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'mail_from_name'],
    ];

    public function update(array $data, ?UploadedFile $brandLogo, ?UploadedFile $authImage): array
    {
        return DB::transaction(function () use ($data, $brandLogo, $authImage): array {
            foreach (self::GROUPS as $group => $keys) {
                foreach ($keys as $key) {
                    Setting::setValue($key, $data[$key] ?? null, $group);
                }
            }

            if (! empty($data['mail_password'])) {
                Setting::setValue('mail_password', $data['mail_password'], 'mail');
            }

            $updatedImages = [];
            foreach (['brand_logo' => $brandLogo, 'auth_brand_image' => $authImage] as $key => $file) {
                if (! $file) continue;
                $old = Setting::where('key', $key)->value('value');
                $upload = Helper::uploadImage($file, 'branding');
                Setting::setValue($key, $upload['name'], 'general');
                if ($old && str_starts_with($old, 'uploads/')) Helper::unlinkImage($old);
                $updatedImages[$key] = asset($upload['name']);
            }

            return $updatedImages;
        });
    }
}
