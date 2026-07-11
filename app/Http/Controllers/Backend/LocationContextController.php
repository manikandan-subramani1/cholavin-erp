<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'godown_id' => ['nullable', 'integer', 'exists:godowns,id'],
        ]);

        $user = $request->user();
        $shop = Shop::where('is_active', true)->findOrFail($data['shop_id']);
        abort_unless($user->canAccessShop($shop->id), 403);

        $godown = isset($data['godown_id'])
            ? Godown::where('is_active', true)->findOrFail($data['godown_id'])
            : null;

        if ($godown) {
            abort_unless($user->canAccessGodown($godown->id), 403);
            abort_if($godown->shop_id && $godown->shop_id !== $shop->id, 422, 'The godown does not belong to the selected shop.');
        }

        $request->session()->put([
            'active_shop_id' => $shop->id,
            'active_godown_id' => $godown?->id,
        ]);

        return back()->with('success', 'Working location changed successfully.');
    }
}
