<div class='dashboard-lazy-heading'><div><span>{{ str($tab)->headline() }} workspace</span><h2>{{ $title }}</h2><p>{{ $description }}</p></div></div>
@if($tab === 'alerts')
    <article class='dashboard-panel'><div class='dashboard-alert-list' data-dashboard-alerts></div></article>
@elseif($tab === 'activity')
    <article class='dashboard-panel'><div class='dashboard-activity-list' data-dashboard-activity></div></article>
@elseif($tab === 'deliveries')
    <article class='dashboard-panel'><div class='dashboard-alert-list' data-dashboard-alerts></div></article>
@else
    <div class='dashboard-workspace-grid'>
        @foreach($charts as $chart)<article class='dashboard-panel {{ $loop->first ? 'dashboard-panel-wide' : '' }}'><header><div><span>Interactive analysis</span><h3>{{ str($chart)->headline() }}</h3></div></header><div class='dashboard-chart' data-chart='{{ $chart }}'></div></article>@endforeach
        @foreach($tops as $top)<article class='dashboard-panel'><header><div><span>Ranking</span><h3>Top {{ str($top)->headline() }}</h3></div></header><div class='dashboard-ranking' data-top='{{ $top }}'></div></article>@endforeach
    </div>
@endif
