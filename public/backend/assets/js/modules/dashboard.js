(function (window, $) {
    'use strict';
    if (!$) return;
    var $root = $('#dashboard-module');
    if (!$root.length) return;

    $('#dashboard-period').on('change.dashboard', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('period', $(this).val());
            if (window.CholavinNavigation) window.CholavinNavigation.visit(url.toString());
            else window.location.assign(url.toString());
    });

    if (typeof window.ApexCharts === 'undefined') return;
    (window.cholavinDashboardCharts || []).forEach(function (chart) { chart.destroy(); });
    window.cholavinDashboardCharts = [];

    var data;
    try { data = JSON.parse($root.attr('data-dashboard') || '{}'); }
    catch (error) { console.error('Dashboard chart data is invalid.', error); return; }

    var palette = ['#8f0028', '#f4c430', '#0f9f6e', '#3568c0', '#7b3fb2', '#df7a18', '#5e001b'];
    var money = function (value) { return '₹' + new Intl.NumberFormat('en-IN', {maximumFractionDigits: 0}).format(value || 0); };
    var baseChart = {
        chart: {fontFamily: 'Poppins, sans-serif', toolbar: {show: false}, animations: {speed: 450}},
        dataLabels: {enabled: false},
        colors: palette,
        grid: {borderColor: '#eee7e8', strokeDashArray: 4},
        tooltip: {y: {formatter: money}},
        noData: {text: 'No data available'}
    };

    function render(selector, options) {
        var $element = $(selector);
        if (!$element.length) return;
        var chart = new ApexCharts($element.get(0), options);
        window.cholavinDashboardCharts.push(chart);
        chart.render();
    }

    render('#financial-trend-chart', Object.assign({}, baseChart, {
        series: data.trend.series,
        chart: Object.assign({}, baseChart.chart, {type: 'area', height: 350}),
        stroke: {curve: 'smooth', width: 3},
        fill: {type: 'gradient', gradient: {shadeIntensity: 1, opacityFrom: 0.32, opacityTo: 0.03, stops: [0, 95, 100]}},
        xaxis: {categories: data.trend.labels, labels: {rotate: -35, trim: true}},
        yaxis: {labels: {formatter: money}},
        legend: {position: 'top', horizontalAlign: 'right'}
    }));

    render('#top-products-chart', Object.assign({}, baseChart, {
        series: [
            {name: 'Revenue', data: data.products.revenue},
            {name: 'Product Cost', data: data.products.cost},
            {name: 'Profit', data: data.products.profit}
        ],
        colors: ['#8f0028', '#d89d17', '#0f9f6e'],
        chart: Object.assign({}, baseChart.chart, {type: 'bar', height: 410}),
        plotOptions: {bar: {horizontal: true, borderRadius: 4, barHeight: '68%'}},
        xaxis: {categories: data.products.labels, labels: {formatter: money}},
        legend: {position: 'top'}
    }));

    render('#stock-category-chart', Object.assign({}, baseChart, {
        series: data.stock_categories.values,
        labels: data.stock_categories.labels,
        chart: Object.assign({}, baseChart.chart, {type: 'donut', height: 300}),
        plotOptions: {pie: {donut: {size: '67%', labels: {show: true, total: {show: true, label: 'Stock Value', formatter: function (chart) {
            return money(chart.globals.seriesTotals.reduce(function (sum, value) { return sum + value; }, 0));
        }}}}}},
        legend: {position: 'bottom'}
    }));

    render('#godown-stock-chart', Object.assign({}, baseChart, {
        series: [{name: 'Stock Value', data: data.stock_godowns.values}],
        colors: ['#5e001b'],
        chart: Object.assign({}, baseChart.chart, {type: 'bar', height: 300}),
        plotOptions: {bar: {borderRadius: 5, columnWidth: '48%', distributed: true}},
        xaxis: {categories: data.stock_godowns.labels, labels: {trim: true}},
        yaxis: {labels: {formatter: money}},
        legend: {show: false}
    }));
})(window, window.jQuery);
