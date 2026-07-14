@extends('pdf.layouts.report')
@section('content')
<div class="report-summary"><strong>{{ $title }}</strong> | Total records: {{ $records->count() }}</div>
<table class="report-table"><thead><tr><th>S.No</th><th>Code</th><th>Name</th><th>Mobile</th><th>Email</th><th>GSTIN</th><th>Group</th><th class="text-right">Credit Limit</th><th class="text-right">Opening Balance</th><th>Status</th></tr></thead><tbody>@forelse($records as $record)<tr><td>{{ $loop->iteration }}</td><td>{{ $record->code }}</td><td>{{ $record->name }}</td><td>{{ $record->mobile }}</td><td>{{ $record->email }}</td><td>{{ $record->gstin }}</td><td>{{ $record->group?->name }}</td><td class="text-right">{{ number_format((float)$record->credit_limit,2) }}</td><td class="text-right">{{ number_format((float)$record->opening_balance,2) }}</td><td>{{ $record->is_active?'Active':'Inactive' }}</td></tr>@empty<tr><td colspan="10">No records found.</td></tr>@endforelse</tbody></table>
@endsection
