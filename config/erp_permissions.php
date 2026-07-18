<?php

$crud = ['view', 'create', 'update', 'delete', 'export'];

return [
    'dashboard' => ['view'],
    'products' => $crud,
    'enquiries' => ['view', 'update'],
    'users' => ['view', 'create', 'update', 'delete'],
    'roles' => ['view', 'create', 'update', 'delete'],
    'shops' => ['view', 'create', 'update', 'delete', 'switch'],
    'godowns' => ['view', 'create', 'update', 'delete', 'switch'],
    'financial-years' => array_merge($crud, ['switch']),
    'activity-logs' => ['view', 'export'],
    'sessions' => ['view', 'revoke'],
    'settings' => ['view', 'update'],
    'maintenance' => ['view', 'create', 'export', 'import'],
    'sales' => ['view'],
    'customers' => $crud,
    'suppliers' => $crud,
    'party-ledger' => ['view', 'export'],
    'stock' => ['view', 'update', 'transfer', 'export'],
    'payments' => $crud,
    'accounts' => $crud,
    'deliveries' => $crud,
    'reports' => ['view', 'export'],
    'notifications' => ['view', 'update'],
] + collect(config('erp_modules.reference', []))
    ->mapWithKeys(fn ($config, $module) => [$module => $crud])
    ->all()
  + collect(config('erp_modules.documents', []))
    ->mapWithKeys(fn ($config, $module) => [$module => array_merge($crud, ['approve', 'print'])])
    ->all();
