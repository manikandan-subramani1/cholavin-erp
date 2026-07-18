@extends('backend.layouts.app')

@section('title', 'Company Settings | Cholavin ERP')

@section('content')
    <div class="page-title-box"><span class="erp-eyebrow">Administration</span><h4>Company Settings</h4><p class="text-muted">One source for branding, contact, social, mail, and branch information.</p></div>
    <nav class="erp-settings-launchpad" aria-label="Business settings modules">
        @can('settings.update')<a href="#settings-brand"><i class="ri-building-4-line"></i><span><strong>Business Profile</strong><small>Brand and company details</small></span></a>@endcan
        @can('invoice-templates.view')<a href="{{ route('admin.invoice-templates.index') }}"><i class="ri-file-settings-line"></i><span><strong>Invoice Settings</strong><small>Layouts and print formats</small></span></a>@endcan
        @can('tax-configurations.view')<a href="{{ route('admin.tax-configurations.index') }}"><i class="ri-percent-line"></i><span><strong>Tax Settings</strong><small>GST and tax rules</small></span></a>@endcan
        @can('payment-methods.view')<a href="{{ route('admin.payment-methods.index') }}"><i class="ri-bank-card-line"></i><span><strong>Payment Methods</strong><small>Cash, bank and digital modes</small></span></a>@endcan
        @can('invoice-sequences.view')<a href="{{ route('admin.invoice-sequences.index') }}"><i class="ri-list-ordered-2"></i><span><strong>Number Series</strong><small>Document numbering</small></span></a>@endcan
        @can('maintenance.view')<a href="{{ route('admin.maintenance.index') }}"><i class="ri-database-2-line"></i><span><strong>Backup &amp; Data</strong><small>Export and maintenance</small></span></a>@endcan
    </nav>
    @include('backend.access.partials.alerts')
    <form id="settings-form" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" novalidate>
        @csrf @method('PUT')
        <div class="row g-4">
            <div class="col-xl-8">
                <div id="settings-brand" class="card erp-panel"><div class="card-header"><h5>Brand identity</h5></div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label">Company name</label><input name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Tagline</label><input name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Transparent logo</label><input type="file" name="brand_logo" class="form-control" accept="image/png,image/jpeg,image/webp"><div class="brand-preview brand-preview-dark"><img id="brand-logo-preview" src="{{ asset($settings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="Brand logo"></div></div>
                    <div class="col-md-6"><label class="form-label">Authentication image</label><input type="file" name="auth_brand_image" class="form-control" accept="image/png,image/jpeg,image/webp"><div class="brand-preview"><img id="auth-image-preview" src="{{ asset($settings['auth_brand_image'] ?? 'frontend/assets/img/logo/logo-hm64.png') }}" alt="Authentication brand"></div></div>
                </div></div>
                <div class="card erp-panel"><div class="card-header"><h5>Contact & address</h5></div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label">Phone</label><input name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">WhatsApp number</label><input name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Contact email</label><input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Main address</label><textarea name="company_address" class="form-control" rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea></div>
                </div></div>
                <div class="card erp-panel"><div class="card-header"><h5>Social links</h5></div><div class="card-body row g-3">@foreach(['facebook','instagram','youtube','linkedin'] as $social)<div class="col-md-6"><label class="form-label text-capitalize">{{ $social }}</label><input type="url" name="{{ $social }}" value="{{ old($social, $settings[$social] ?? '') }}" class="form-control" placeholder="https://"></div>@endforeach</div></div>
                <div class="card erp-panel"><div class="card-header"><h5>Mail configuration</h5></div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label">SMTP host</label><input name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" class="form-control"></div><div class="col-md-3"><label class="form-label">Port</label><input type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '') }}" class="form-control"></div><div class="col-md-3"><label class="form-label">Encryption</label><select name="mail_encryption" class="form-select"><option value="tls" @selected(($settings['mail_encryption'] ?? '') === 'tls')>TLS</option><option value="ssl" @selected(($settings['mail_encryption'] ?? '') === 'ssl')>SSL</option></select></div>
                    <div class="col-md-6"><label class="form-label">Username</label><input name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" class="form-control"></div><div class="col-md-6"><label class="form-label">Password</label><input type="password" name="mail_password" class="form-control" placeholder="Leave blank to keep current"></div><div class="col-md-6"><label class="form-label">From address</label><input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" class="form-control"></div><div class="col-md-6"><label class="form-label">From name</label><input name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" class="form-control"></div>
                </div></div>
                @can('settings.update')<button class="btn btn-brand btn-lg mb-4"><i class="ri-save-line me-1"></i> Save company settings</button>@endcan
            </div>
            <div class="col-xl-4"><div class="card erp-panel position-sticky" style="top:90px"><div class="card-header"><h5>Branch addresses</h5></div><div class="card-body"><p class="text-muted">Shop records are the ERP branch directory. Manage their address and active status from Locations.</p>@forelse($branches as $branch)<div class="branch-mini"><strong>{{ $branch->name }}</strong><small>{{ $branch->code }}</small><p>{{ $branch->address ?: 'Address not added' }}</p></div>@empty<div class="erp-empty"><i class="ri-store-2-line"></i><p>No branches configured.</p></div>@endforelse @can('shops.update')<a href="{{ route('admin.locations.index') }}" class="btn btn-outline-brand w-100">Manage branches</a>@endcan</div></div></div>
        </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/settings.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/settings.js')) }}"></script>
@endpush
