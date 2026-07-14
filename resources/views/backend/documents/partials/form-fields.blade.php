<input type="hidden" name="_method" value="POST">
<div class="row g-3 mb-3">
    @if($module['party_type'])
        <div class="col-md-4"><label class="form-label">{{ ucfirst($module['party_type']) }}</label><select name="party_id" class="form-select" required><option value="">Loading...</option></select></div>
    @endif
    <div class="col-md-3"><label class="form-label">Document Date</label><input name="document_date" type="date" value="{{ now()->toDateString() }}" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Due Date</label><input name="due_date" type="date" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="draft">Draft</option><option value="posted">Post now</option></select></div>
    <div class="col-md-3"><label class="form-label">Reference Number</label><input name="reference_number" class="form-control"></div>
    @if($module['group'] === 'Purchases')
        <div class="col-md-3"><label class="form-label">Allocated Expense</label><input name="expense_amount" type="number" min="0" step="0.01" value="0" class="form-control"></div>
    @endif
    <div class="col-md-2"><label class="form-label">Round Off</label><input name="round_off" type="number" step="0.01" min="-10" max="10" value="0" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Notes</label><input name="notes" class="form-control"></div>
</div>
<div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Items</h6><button id="add-document-item" type="button" class="btn btn-sm btn-secondary">Add Item</button></div>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Product</th><th>Qty</th><th>Rate</th><th>Discount</th><th>Unit</th><th>Batch</th><th>Expiry</th><th></th></tr></thead><tbody id="document-items"></tbody></table></div>
<div class="text-end"><strong>Estimated total: Rs. <span id="document-estimated-total">0.00</span></strong><small class="d-block text-muted">Final tax, allocated expense, and totals are recalculated securely on the server.</small></div>
