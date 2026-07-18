(function (window, $) {
    'use strict';
    if (!$ || !$('#financial-overview-module').length) return;

    var root = $('#financial-overview-module');
    var data = JSON.parse(root.attr('data-chart') || '{}');
    var money = function (value) { return '\u20B9' + Number(value || 0).toLocaleString('en-IN', {maximumFractionDigits: 0}); };

    if (window.CholavinShell) {
        window.CholavinShell.setQuickActions([
            {label: 'Receive', icon: 'ri-arrow-down-circle-line', url: root.data('payments-url') + '?type=customer_collection', variant: 'primary'},
            {label: 'Pay', icon: 'ri-arrow-up-circle-line', url: root.data('payments-url') + '?type=supplier_payment'},
            {label: 'Expense', icon: 'ri-receipt-line', url: root.data('payments-url') + '?type=expense'},
            {label: 'Journal', icon: 'ri-file-list-3-line', url: root.data('vouchers-url')},
            {label: 'P&L', icon: 'ri-file-chart-line', url: root.data('reports-url')}
        ]);
    }

    if (!window.ApexCharts) return;

    var shared = {chart: {fontFamily: 'Poppins, sans-serif', toolbar: {show: false}}, dataLabels: {enabled: false}, grid: {borderColor: '#eee7e8', strokeDashArray: 4}, tooltip: {y: {formatter: money}}};
    new ApexCharts($('#financial-overview-trend').get(0), $.extend(true, {}, shared, {series: [{name: 'Income', data: data.income || []}, {name: 'Expenses', data: data.expenses || []}], colors: ['#15803d', '#b42318'], chart: {type: 'area', height: 350}, stroke: {curve: 'smooth', width: 3}, fill: {type: 'gradient', gradient: {opacityFrom: 0.3, opacityTo: 0.03}}, xaxis: {categories: data.labels || []}, yaxis: {labels: {formatter: money}}})).render();
    var income = (data.income || []).reduce(function (sum, value) { return sum + Number(value || 0); }, 0);
    var expenses = (data.expenses || []).reduce(function (sum, value) { return sum + Number(value || 0); }, 0);
    new ApexCharts($('#financial-overview-composition').get(0), $.extend(true, {}, shared, {series: [income, expenses], labels: ['Income', 'Expenses'], colors: ['#15803d', '#b42318'], chart: {type: 'donut', height: 350}, legend: {position: 'bottom'}, plotOptions: {pie: {donut: {size: '68%'}}}})).render();
})(window, window.jQuery);
