(function (window, $) {
    'use strict';
    if (!$) return;

    var $root = $('#dashboard-module');
    if (!$root.length) return;
    var endpoints = $root.data('endpoints') || {};
    var requests = {};
    var charts = {};
    var state = {
        preset: new URL(window.location.href).searchParams.get('preset') || $root.data('initial-preset') || 'this_month',
        date_from: new URL(window.location.href).searchParams.get('date_from') || '',
        date_to: new URL(window.location.href).searchParams.get('date_to') || ''
    };

    function escapeHtml(value) { return $('<div>').text(value == null ? '' : value).html(); }
    function money(value) { return '₹' + new Intl.NumberFormat('en-IN', {maximumFractionDigits: 0}).format(Number(value) || 0); }
    function params() { var p = {preset: state.preset}; if (state.preset === 'custom') { p.date_from = state.date_from; p.date_to = state.date_to; } return p; }
    function abort(key) { if (requests[key] && requests[key].readyState !== 4) requests[key].abort(); }
    function request(key, url) {
        abort(key);
        requests[key] = $.ajax({url: url, method: 'GET', data: params(), dataType: 'json', headers: {'X-Requested-With': 'XMLHttpRequest'}});
        return requests[key];
    }
    function errorState($target, retry) {
        if (!$target.length) return;
        $target.html('<div class=\'dashboard-component-error\'><i class=\'ri-wifi-off-line\'></i><strong>Could not load this section</strong><button type=\'button\' class=\'btn btn-sm btn-outline-brand\'>Retry</button></div>');
        $target.find('button').on('click', retry);
    }
    function emptyState(message) { return '<div class=\'dashboard-empty\'><i class=\'ri-inbox-2-line\'></i><span>' + escapeHtml(message) + '</span></div>'; }

    function loadKpis() {
        var $target = $('#dashboard-kpis').addClass('is-loading');
        request('kpis', endpoints.kpis).done(function (response) {
            var cards = response.data.cards || [];
            $('#dashboard-period-label').text((response.meta.date_from || '') + ' — ' + (response.meta.date_to || ''));
            if (!cards.length) { $target.html(emptyState('No dashboard metrics are assigned to your role.')); return; }
            $target.html(cards.map(function (card) {
                var change = card.change == null ? '<span class=\'dashboard-kpi-neutral\'>No prior comparison</span>' : '<span class=\'dashboard-kpi-change ' + (card.change >= 0 ? 'is-up' : 'is-down') + '\'><i class=\'' + (card.change >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line') + '\'></i>' + Math.abs(card.change).toFixed(1) + '% <small>' + escapeHtml(card.comparison_label) + '</small></span>';
                var value = card.format === 'money' ? money(card.value) : new Intl.NumberFormat('en-IN').format(card.value || 0);
                return '<a class=\'dashboard-kpi-card tone-' + escapeHtml(card.tone) + '\' href=\'' + escapeHtml(card.url) + '\' title=\'' + escapeHtml(card.tooltip) + '\' data-bs-toggle=\'tooltip\'><span class=\'dashboard-kpi-icon\'><i class=\'' + escapeHtml(card.icon) + '\'></i></span><div><small>' + escapeHtml(card.title) + '</small><strong>' + value + '</strong>' + change + '</div><i class=\'ri-arrow-right-up-line dashboard-kpi-open\'></i></a>';
            }).join(''));
            $target.find('[data-bs-toggle=tooltip]').each(function () { if (window.bootstrap) new window.bootstrap.Tooltip(this); });
        }).fail(function (_, status) { if (status !== 'abort') errorState($target, loadKpis); }).always(function () { $target.removeClass('is-loading'); });
    }

    function chartOptions(data) {
        var base = {chart:{height:310,toolbar:{show:false},fontFamily:'Poppins, sans-serif',animations:{speed:350}},colors:['#800020','#D4AF37','#15803D','#3568c0','#7b3fb2'],dataLabels:{enabled:false},noData:{text:'No data for the selected period'},tooltip:{y:{formatter:money}},grid:{borderColor:'#eee8ea',strokeDashArray:4},legend:{position:'top',horizontalAlign:'right'}};
        if (data.type === 'donut') return $.extend(true, {}, base, {chart:{type:'donut'},series:data.series || [],labels:data.labels || [],plotOptions:{pie:{donut:{size:'68%'}}},legend:{position:'bottom'}});
        return $.extend(true, {}, base, {chart:{type:data.type || 'area'},series:data.series || [],xaxis:{categories:data.labels || [],labels:{trim:true,rotate:-30}},stroke:{curve:'smooth',width:3},fill:{type:'gradient',gradient:{opacityFrom:.3,opacityTo:.03}},plotOptions:{bar:{borderRadius:5,columnWidth:'48%'}},yaxis:{labels:{formatter:money}}});
    }
    function loadChart(element) {
        var $target = $(element), name = $target.data('chart'), key = 'chart-' + name + '-' + $target.closest('.tab-pane').attr('id');
        if (charts[key]) { charts[key].destroy(); delete charts[key]; }
        $target.html('<div class=\'dashboard-chart-skeleton\'></div>');
        request(key, endpoints.chart.replace('__chart__', name)).done(function (response) {
            var data = response.data.chart || {};
            if ((!data.series || !data.series.length) || (data.series.every && data.series.every(function (s) { return Array.isArray(s.data) && !s.data.some(Number); }))) { $target.html(emptyState('No chart data for this selection.')); return; }
            $target.empty(); if (!window.ApexCharts) { errorState($target, function () { loadChart(element); }); return; }
            charts[key] = new window.ApexCharts($target.get(0), chartOptions(data)); charts[key].render();
        }).fail(function (_, status) { if (status !== 'abort') errorState($target, function () { loadChart(element); }); });
    }
    function loadTop(element) {
        var $target=$(element), type=$target.data('top'), key='top-'+type+'-'+$target.closest('.tab-pane').attr('id');
        $target.html('<div class=\'dashboard-list-skeleton\'></div>');
        request(key,endpoints.top.replace('__type__',type)).done(function(response){var items=response.data.items||[];if(!items.length){$target.html(emptyState('No ranking data for this period.'));return;}
            $target.html('<ol>'+items.map(function(item,index){return '<li><b>'+(index+1)+'</b><span><strong>'+escapeHtml(item.name)+'</strong><small>'+escapeHtml(item.code||'')+' · '+new Intl.NumberFormat('en-IN').format(item.quantity||0)+' records/qty</small></span><em>'+money(item.value)+'</em></li>';}).join('')+'</ol>');
        }).fail(function(_,status){if(status!=='abort')errorState($target,function(){loadTop(element);});});
    }
    function loadAlerts(element) {
        var $target=$(element), key='alerts-'+$target.closest('.tab-pane').attr('id');$target.html('<div class=\'dashboard-list-skeleton\'></div>');
        request(key,endpoints.alerts).done(function(response){var items=response.data.items||[];if(!items.length){$target.html(emptyState('Nothing needs attention right now.'));return;}
            $target.html(items.map(function(item){return '<a href=\''+escapeHtml(item.url)+'\' class=\'dashboard-alert tone-'+escapeHtml(item.tone)+'\'><span><i class=\'ri-alarm-warning-line\'></i></span><div><strong>'+escapeHtml(item.title)+'</strong><small>'+escapeHtml(item.message)+'</small></div><b>'+item.count+'</b><i class=\'ri-arrow-right-s-line\'></i></a>';}).join(''));
        }).fail(function(_,status){if(status!=='abort')errorState($target,function(){loadAlerts(element);});});
    }
    function loadActivity(element) {
        var $target=$(element),key='activity-'+$target.closest('.tab-pane').attr('id');$target.html('<div class=\'dashboard-list-skeleton\'></div>');
        request(key,endpoints.activity).done(function(response){var items=response.data.items||[];if(!items.length){$target.html(emptyState('No activity in this period.'));return;}
            $target.html(items.map(function(item){return '<article class=\'dashboard-activity\'><span><i class=\'ri-history-line\'></i></span><div><strong>'+escapeHtml(item.action)+'</strong><small>'+escapeHtml(item.user)+' · '+escapeHtml(item.module)+' · '+escapeHtml(item.reference)+'</small><em>'+escapeHtml(item.godown)+' / '+escapeHtml(item.shop)+'</em></div><time>'+escapeHtml(item.time)+'</time></article>';}).join(''));
        }).fail(function(_,status){if(status!=='abort')errorState($target,function(){loadActivity(element);});});
    }
    function initialiseComponents($scope) {
        $scope.find('[data-chart]').each(function(){loadChart(this);});$scope.find('[data-top]').each(function(){loadTop(this);});
        $scope.find('[data-dashboard-alerts]').each(function(){loadAlerts(this);});$scope.find('[data-dashboard-activity]').each(function(){loadActivity(this);});
    }
    function loadTab(tab, force) {
        if(tab==='overview'){if(force)initialiseComponents($('#dashboard-tab-overview'));return;}
        var $pane=$('#dashboard-tab-'+tab),$loader=$pane.find('[data-lazy-tab]');if(!$loader.length&&!force)return;
        $pane.html('<div class=\'dashboard-tab-loader\'><span class=\'spinner-border spinner-border-sm\'></span> Loading workspace…</div>');
        request('tab-'+tab,endpoints.tab.replace('__tab__',tab)).done(function(response){$pane.html(response.data.html);initialiseComponents($pane);}).fail(function(_,status){if(status!=='abort')errorState($pane,function(){loadTab(tab,true);});});
    }
    function syncUrl(push) {
        var url=new URL(window.location.href);url.searchParams.set('preset',state.preset);
        if(state.preset==='custom'){url.searchParams.set('date_from',state.date_from);url.searchParams.set('date_to',state.date_to);}else{url.searchParams.delete('date_from');url.searchParams.delete('date_to');}
        window.history[push ? 'pushState' : 'replaceState']({dashboard:true},'',url.toString());
    }
    function refresh(push) { Object.keys(requests).forEach(abort); syncUrl(push === true); loadKpis(); initialiseComponents($('.dashboard-tab-content .tab-pane.active')); }

    $('.dashboard-preset').on('click',function(){var preset=$(this).data('preset');$('.dashboard-preset').removeClass('active');$(this).addClass('active');state.preset=preset;$('#dashboard-custom-range').toggleClass('d-none',preset!=='custom');if(preset!=='custom')refresh(true);});
    $('#dashboard-apply-custom').on('click',function(){state.date_from=$('#dashboard-date-from').val();state.date_to=$('#dashboard-date-to').val();if(!state.date_from||!state.date_to){if(window.toastr)window.toastr.warning('Select both custom dates.');return;}if(state.date_from>state.date_to){if(window.toastr)window.toastr.error('The end date must be on or after the start date.');return;}refresh(true);});
    $('[data-dashboard-tab]').on('shown.bs.tab',function(){loadTab($(this).data('dashboard-tab'),false);});
    $(window).off('business-context:changed.dashboard').on('business-context:changed.dashboard',function(){refresh(false);});
    $(window).off('popstate.dashboard').on('popstate.dashboard',function(){var url=new URL(window.location.href);state.preset=url.searchParams.get('preset')||'this_month';state.date_from=url.searchParams.get('date_from')||'';state.date_to=url.searchParams.get('date_to')||'';refresh(false);});

    if (window.CholavinShell) window.CholavinShell.setQuickActions($('.dashboard-action-grid a.dashboard-action').slice(0,5).map(function(){return {label:$(this).find('strong').text(),title:$(this).find('small').text(),icon:$(this).find('i').first().attr('class'),url:$(this).attr('href')};}).get());
    $('#dashboard-custom-range').toggleClass('d-none',state.preset!=='custom');
    loadKpis(); initialiseComponents($('#dashboard-tab-overview'));
})(window,window.jQuery);
