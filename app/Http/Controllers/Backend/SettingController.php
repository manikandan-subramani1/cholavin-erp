<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingRequest;
use App\Models\Setting;
use App\Models\Shop;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(): View
    {
        Gate::authorize('settings.view');

        return view('backend.settings.index', [
            'settings' => Setting::values(),
            'branches' => Shop::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $images = $this->settings->update(
            $request->safe()->except(['brand_logo', 'auth_brand_image']),
            $request->file('brand_logo'),
            $request->file('auth_brand_image'),
        );

        return ResponseHelper::success('Company settings updated successfully.', ['images' => $images], refresh: [
            'datatable' => false,
            'summary' => true,
            'drawer' => false,
        ]);
    }
}
