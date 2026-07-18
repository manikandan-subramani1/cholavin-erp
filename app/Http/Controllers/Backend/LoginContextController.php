<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreLoginContextRequest;
use App\Models\ActivityLog;
use App\Services\Access\BusinessContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginContextController extends Controller
{
    public function shops(Request $request, BusinessContextService $contexts): JsonResponse
    {
        $data = $request->validate(['godown_id' => ['required', 'integer']]);
        $godownId = (int) $data['godown_id'];
        abort_unless($contexts->permittedGodowns($request->user())->contains('id', $godownId), 403, 'You do not have access to the selected godown.');

        return ResponseHelper::success('Assigned shops loaded.', [
            'shops' => $contexts->permittedShopsForGodown($request->user(), $godownId)
                ->map->only(['id', 'name', 'code'])
                ->values(),
        ]);
    }

    public function index(BusinessContextService $contexts): View|RedirectResponse
    {
        if (! (bool) session()->get('auth_location_selection_required', false)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        $user = request()->user();

        return view('backend.auth.location-selection', [
            'shops' => $contexts->permittedShops($user),
            'godowns' => $contexts->permittedGodowns($user),
            'financialYears' => $contexts->permittedFinancialYears($user),
        ]);
    }

    public function store(StoreLoginContextRequest $request, BusinessContextService $contexts): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $shops = $contexts->permittedShops($user);
        $godowns = $contexts->permittedGodowns($user);
        $financialYears = $contexts->permittedFinancialYears($user);
        $shopId = isset($data['shop_id']) ? (int) $data['shop_id'] : null;
        $godownId = isset($data['godown_id']) ? (int) $data['godown_id'] : null;
        $financialYearId = isset($data['financial_year_id']) ? (int) $data['financial_year_id'] : null;

        $errors = [];
        if ($shops->isNotEmpty() && (! $shopId || ! $shops->contains('id', $shopId))) {
            $errors['shop_id'] = ['Choose an assigned shop.'];
        }
        if ($godowns->isNotEmpty() && (! $godownId || ! $godowns->contains('id', $godownId))) {
            $errors['godown_id'] = ['Choose an assigned godown.'];
        }
        if ($financialYears->isNotEmpty() && (! $financialYearId || ! $financialYears->contains('id', $financialYearId))) {
            $errors['financial_year_id'] = ['Choose an assigned financial year.'];
        }
        if ($godownId && $shopId && ! $contexts->permittedShopsForGodown($user, $godownId)->contains('id', $shopId)) {
            $errors['shop_id'] = ['The selected shop does not belong to the selected godown.'];
        }
        if ($shops->isEmpty() || $godowns->isEmpty() || $financialYears->isEmpty()) {
            $errors['context'] = ['Your account does not have a complete shop, godown, and financial-year assignment.'];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $request->session()->put([
            'active_shop_id' => $shopId,
            'active_godown_id' => $godownId,
            'active_financial_year_id' => $financialYearId,
            'auth_location_selection_required' => false,
        ]);
        $request->session()->forget('all_shops_context');

        ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'login.context_selected',
            'module' => 'authentication',
            'action' => 'select-context',
            'shop_id' => $shopId,
            'godown_id' => $godownId,
            'financial_year_id' => $financialYearId,
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $redirect = route('admin.dashboard');

        return $request->expectsJson()
            ? ResponseHelper::success('Workspace selected. Loading dashboard...', ['redirect' => $redirect])
            : redirect()->intended($redirect);
    }
}
