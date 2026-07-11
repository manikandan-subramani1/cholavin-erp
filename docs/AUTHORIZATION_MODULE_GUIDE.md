# Authentication and Module Authorization Standard

This document is mandatory for every Cholavin ERP backend module. A hidden menu is not security: every module must enforce authorization in its controller or route and repeat the same permission check in Blade for a clear user interface.

## Authentication flow

All staff use `/admin/login` and may identify themselves with username, email, or mobile. Login requires a valid password, an active user, and an active role. Successful login regenerates the session and stores `user_id`, `role_id`, `permitted_shop_ids`, `permitted_godown_ids`, `permitted_modules`, `permitted_actions`, and `login_timestamp`.

The database remains the source of truth. Session values are useful context and must not replace gates, policies, or scoped database queries. The Super Admin role has `is_super_admin = true`; `Gate::before` grants it all abilities. Do not add hard-coded user IDs. Role permissions can be overridden per user with an explicit Allow or Deny; Inherit keeps the role result.

## Required process for every module

Assume a new `purchases` module with view, create, update, and delete actions.

### 1. Seed stable permission codes

Add this entry to `AccessControlSeeder`:

```php
'purchases' => ['view', 'create', 'update', 'delete'],
```

Permission checks use codes such as `purchases.view`, never numeric permission IDs. Run `php artisan db:seed --class=AccessControlSeeder` after adding codes.

### 2. Create and register a policy

```php
class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('purchases.view');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $user->hasPermission('purchases.view')
            && $user->canAccessShop($purchase->shop_id)
            && $user->canAccessGodown($purchase->godown_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('purchases.create');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $user->hasPermission('purchases.update')
            && $user->canAccessShop($purchase->shop_id)
            && $user->canAccessGodown($purchase->godown_id);
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->hasPermission('purchases.delete')
            && $user->canAccessShop($purchase->shop_id)
            && $user->canAccessGodown($purchase->godown_id);
    }
}
```

Register the policy in `AppServiceProvider::boot()` with `Gate::policy(...)`.

### 3. Authorize every controller action

```php
public function index(Request $request)
{
    $this->authorize('viewAny', Purchase::class);
    $purchases = Purchase::accessibleBy($request->user())->paginate();
}

public function store(Request $request)
{
    $this->authorize('create', Purchase::class);
    // Validate location IDs and verify both user assignments before create.
}

public function update(Request $request, Purchase $purchase)
{
    $this->authorize('update', $purchase);
}

public function destroy(Purchase $purchase)
{
    $this->authorize('delete', $purchase);
}
```

Use `App\Models\Concerns\ScopesUserLocations` on transactional models containing `shop_id` and `godown_id`. Index queries must call `accessibleBy(auth()->user())`; this limits results to the shop and optional godown currently selected in the header. The access-context middleware refreshes assignments and effective permissions from the database on every request, so changes apply without logout.

When creating or changing location ownership, validate submitted IDs and reject IDs for which `canAccessShop()` or `canAccessGodown()` returns false.

### 4. Protect routes

All ERP routes belong inside the existing `['auth', 'active', 'activity']` admin middleware group. Guest-accessible backend routes are limited to authentication and future password recovery endpoints.

### 5. Apply the same checks in Blade

```blade
@can('viewAny', App\Models\Purchase::class)
    <a href="{{ route('admin.purchases.index') }}">Purchases</a>
@endcan

@can('create', App\Models\Purchase::class)
    <a href="{{ route('admin.purchases.create') }}">Add Purchase</a>
@endcan

@can('update', $purchase)
    <a href="{{ route('admin.purchases.edit', $purchase) }}">Edit</a>
@endcan

@can('delete', $purchase)
    <button type="submit">Delete</button>
@endcan
```

Server-side DataTable action HTML must also check `$request->user()->can(...)` before rendering buttons.

### 6. Test the authorization boundary

Every module needs feature tests proving:

- guests are redirected to the common login page;
- inactive accounts cannot log in;
- a role without the module permission receives HTTP 403;
- each allowed action succeeds;
- users cannot read or mutate records belonging to unassigned shops or godowns;
- Super Admin can perform every action;
- menus and action buttons are absent when permission is missing.

### 7. Add dashboard readiness and quick actions

Every new master or transactional module must add a permission-aware dashboard metric or readiness item. When required master data has a count of zero, show a short explanation and a **Fix now** link to the authorized create/index route. Never render a shortcut the user cannot access.

Examples include missing customers before sales, missing suppliers before purchases, missing units/categories before products, and missing ledgers before accounting entries. Quick actions should link directly to the smallest useful task rather than a generic dashboard.

## Administration and audit

Super Admin manages users, roles, permission assignments, shops, godowns, and activity logs under the **Access Control** sidebar section. Action permissions come through a user's role; location access comes through direct shop/godown assignments.

Role, direct user permission, shop, and godown changes apply on the user's next request. No logout is required. The header location selector is populated with every active location for Super Admin and only assigned active locations for other users.

`LogUserActivity` records authenticated page views and mutations. `AuthController` separately records successful, failed, and blocked login attempts plus logout. Never store passwords, CAPTCHA answers, tokens, or full request payloads in activity-log properties.
