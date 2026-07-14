@extends('pdf.layouts.report')
@section('content')
<div class="report-summary">
    <strong>{{ $module['title'] }}</strong> | Total records: {{ $records->count() }}
    @if(array_filter($filters))<br>Filters: {{ collect($filters)->filter()->map(fn($value, $key) => str($key)->replace('_', ' ')->title().': '.$value)->join(' | ') }}@endif
</div>
<table class="report-table">
    <thead><tr><th style="width:7%">S.No</th><th>Code</th><th>Name</th><th>Parent</th>@if($module['percentage'] ?? false)<th class="text-right">Percentage</th>@endif<th>Status</th><th>Created</th></tr></thead>
    <tbody>
    @forelse($records as $record)
        <tr><td>{{ $loop->iteration }}</td><td>{{ $record->code }}</td><td>{{ $record->name }}</td><td>{{ $record->parent?->name ?? '—' }}</td>@if($module['percentage'] ?? false)<td class="text-right">{{ number_format((float)$record->percentage, 2) }}%</td>@endif<td>{{ $record->is_active ? 'Active' : 'Inactive' }}</td><td>{{ $record->created_at->format('d-m-Y') }}</td></tr>
    @empty<tr><td colspan="7">No records found.</td></tr>@endforelse
    </tbody>
</table>
@endsection
