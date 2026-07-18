@if($rows->isEmpty())
    @include('backend.products.partials.empty-tab', ['message'=>'No records found in the current scope.'])
@else
<div class="table-responsive"><table class="table product-detail-table"><thead><tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead><tbody>@foreach($rows as $row)<tr>@foreach($row as $cell)<td>{{ $cell ?: '—' }}</td>@endforeach</tr>@endforeach</tbody></table></div>
@endif
