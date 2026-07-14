<div id="erp-page-loader" class="erp-page-loader erp-fullscreen-loader" role="status" aria-live="polite" aria-label="Loading Cholavin ERP">
    <div class="loading-container">
        <div class="loading" aria-hidden="true"></div>
        <div class="loading-icon">
            <img src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}">
        </div>
        <span class="loading-shape shape-one" aria-hidden="true"></span>
        <span class="loading-shape shape-two" aria-hidden="true"></span>
        <span class="loading-shape shape-three" aria-hidden="true"></span>
    </div>
    <div class="loading-copy">
        <span class="loading-label">Loading</span>
        <h2>{{ $commonSettings['company_name'] ?? 'Cholavin' }}</h2>
    </div>
</div>
