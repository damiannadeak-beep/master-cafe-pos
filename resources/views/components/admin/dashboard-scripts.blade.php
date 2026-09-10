<script>
(function() {
    var dailyLabels = @json($chartDailyLabels);
    var dailyData = @json($chartDailyData);
    var dailyLaba = @json($chartDailyLaba);
    var monthlyLabels = @json($chartMonthlyLabels);
    var monthlyData = @json($chartMonthlyData);
    var monthlyLaba = @json($chartMonthlyLaba);

    function createSalesChart(elementId, labels, dataSales, dataLaba) {
        var ctx = document.getElementById(elementId);
        if (!ctx) return;
        if (typeof Chart !== 'undefined') {
            var existing = Chart.getChart(ctx);
            if (existing) existing.destroy();
        }
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan (Kotor)',
                        data: dataSales,
                        fill: true,
                        backgroundColor: 'rgba(13, 110, 253, 0.05)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Laba Bersih',
                        data: dataLaba,
                        fill: true,
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.dataset.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#495057' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e9ecef' },
                        ticks: {
                            color: '#495057',
                            callback: function(value) { return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
                        }
                    }
                }
            }
        });
    }

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initDashboardCharts, 50);
            return;
        }
        createSalesChart('dailySalesChart', dailyLabels, dailyData, dailyLaba);
        createSalesChart('monthlySalesChart', monthlyLabels, monthlyData, monthlyLaba);
    }
    initDashboardCharts();
})();
</script>