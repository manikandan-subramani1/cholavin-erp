<input type="hidden" name="_method" value="POST">
<div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required maxlength="190"></div>
<div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control text-uppercase" required maxlength="80"></div>
@if($parents->isNotEmpty())
    <div class="mb-3"><label class="form-label">Parent</label><select name="parent_id" class="form-select" required><option value="">Select</option>@foreach($parents as $parent)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</select></div>
@endif
@if($module['percentage'] ?? false)
    <div class="mb-3"><label class="form-label">Percentage</label><input name="percentage" type="number" min="0" max="100" step="0.0001" class="form-control" required></div>
@endif
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" maxlength="2000"></textarea></div>
<input type="hidden" name="is_active" value="0">
<div class="form-check"><input id="master-active" name="is_active" value="1" type="checkbox" class="form-check-input" checked><label for="master-active" class="form-check-label">Active</label></div>
