<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('settings.view');

        return view('backend.settings.index', [
            'settings' => Setting::values(),
            'branches' => Shop::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('settings.update');
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'], 'tagline' => ['nullable', 'string', 'max:190'],
            'brand_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'], 'auth_brand_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:6144'],
            'contact_phone' => ['nullable', 'string', 'max:40'], 'whatsapp_number' => ['nullable', 'string', 'max:40'], 'contact_email' => ['nullable', 'email', 'max:190'], 'company_address' => ['nullable', 'string', 'max:1000'],
            'facebook' => ['nullable', 'url', 'max:500'], 'instagram' => ['nullable', 'url', 'max:500'], 'youtube' => ['nullable', 'url', 'max:500'], 'linkedin' => ['nullable', 'url', 'max:500'],
            'mail_host' => ['nullable', 'string', 'max:190'], 'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'], 'mail_username' => ['nullable', 'string', 'max:190'], 'mail_password' => ['nullable', 'string', 'max:500'], 'mail_encryption' => ['nullable', 'in:tls,ssl'], 'mail_from_address' => ['nullable', 'email', 'max:190'], 'mail_from_name' => ['nullable', 'string', 'max:190'],
        ]);

        foreach (['general' => ['company_name', 'tagline'], 'contact' => ['contact_phone', 'whatsapp_number', 'contact_email', 'company_address'], 'social' => ['facebook', 'instagram', 'youtube', 'linkedin'], 'mail' => ['mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'mail_from_name']] as $group => $keys) {
            foreach ($keys as $key) {
                Setting::setValue($key, $data[$key] ?? null, $group);
            }
        }

        if ($request->filled('mail_password')) {
            Setting::setValue('mail_password', $data['mail_password'], 'mail');
        }
        foreach (['brand_logo', 'auth_brand_image'] as $key) {
            if ($request->hasFile($key)) {
                $old = Setting::where('key', $key)->value('value');
                if ($old && str_starts_with($old, 'uploads/')) {
                    Helper::unlinkImage($old);
                }
                $upload = Helper::uploadImage($request->file($key), 'branding');
                Setting::setValue($key, $upload['name'], 'general');
            }
        }

        return back()->with('success', 'Company settings updated successfully.');
    }
}
