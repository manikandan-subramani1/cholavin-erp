@extends('backend.layouts.app')
@section('title', 'Activity Logs | Cholavin ERP')
@section('content')
<div class="page-title-box"><h4>Login & Activity Logs</h4></div><div class="card"><div class="card-body table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Time</th><th>User</th><th>Event</th><th>Request</th><th>IP</th><th>Details</th></tr></thead><tbody>@foreach($logs as $log)<tr><td>{{ $log->created_at?->format('d M Y H:i:s') }}</td><td>{{ $log->user?->name ?? 'Guest' }}</td><td><span class="badge bg-info-subtle text-info">{{ $log->event }}</span></td><td>{{ $log->method }} {{ $log->route }}</td><td>{{ $log->ip_address }}</td><td><small>{{ $log->properties ? json_encode($log->properties) : '' }}</small></td></tr>@endforeach</tbody></table>{{ $logs->links() }}</div></div>
@endsection
