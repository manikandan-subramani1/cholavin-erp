(function (window, $) {
    'use strict';
    if (!$ || !$('#financial-overview-module').length || !window.ApexCharts) return;

    var $root = $('#financial-overview-module');
    var data = JSON.parse($root.attr('data-chart') || '{}');
    var money = function (value) { return '₹' + Number(value || 0).toLocaleString('en-IN', {maximumFractionDigits: 0}); };
    var shared = {chart: {fontFamily: 'Poppins, sans-serif', toolbar: {show: false}}, dataLabels: {enabled: false}, grid: {borderColor: '#eee7e8', strokeDashArray: 4}, tooltip: {y: {formatter: money}}};

    new ApexCharts($('#financial-overview-trend').get(0), $.extend(true, {}, shared, {
        series: [{name: 'Income', data: data.income || []}, {name: 'Expenses', data: data.expenses || []}],
        colors: ['#15803d', '#b42318'], chart: {type: 'area', height: 350}, stroke: {curve: 'smooth', width: 3},
        fill: {type: 'gradient', gradient: {opacityFrom: .3, opacityTo: .03}}, xaxis: {categories: data.labels || []}, yaxis: {labels: {formatter: money}}
    })).render();

    var income = (data.income || []).reduce(function (sum, value) { return sum + Number(value || 0); }, 0);
    var expenses = (data.expenses || []).reduce(function (sum, value) { return sum + Number(value || 0); }, 0);
    new ApexCharts($('#financial-overview-composition').get(0), $.extend(true, {}, shared, {
        series: [income, expenses], labels: ['Income', 'Expenses'], colors: ['#15803d', '#b42318'],
        chart: {type: 'donut', height: 350}, legend: {position: 'bottom'}, plotOptions: {pie: {donut: {size: '68%'}}}
    })).render();
})(window, window.jQuery);
