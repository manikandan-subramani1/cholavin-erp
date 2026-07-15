@extends('pdf.layouts.report')

@section('report-content')
<div class="report-meta">
    Total records: {{ $rows->count() }}
    @if(array_filter($filters))
        <br>Filters: {{ collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->map(fn ($value, $key) => str($key)->replace('_', ' ')->title().': '.$value)->join(' | ') }}
    @endif
</div>
<table class="report-table">
    <thead>
        <tr>
            <th style="width:7%">S.No</th>
            @foreach($columns as $label)<th>{{ $label }}</th>@endforeach
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                @foreach(array_keys($columns) as $field)<td>{{ $row[$field] ?? '-' }}</td>@endforeach
                <td>{{ $row['record_status'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) + 2 }}">No records found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
