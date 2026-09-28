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
                        backgroundColor: 'rgba(192, 142, 92, 0.12)',
                        borderColor: '#c08e5c',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#c08e5c',
                        pointBorderColor: '#161b22',
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Laba Bersih',
                        data: dataLaba,
                        fill: true,
                        backgroundColor: 'rgba(52, 211, 153, 0.08)',
                        borderColor: '#34d399',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#34d399',
                        pointBorderColor: '#161b22',
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            color: '#9ca3af',
                            font: { family: 'Poppins', size: 12 },
                            boxWidth: 10,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.dataset.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#8b949e', font: { family: 'Poppins', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.06)' },
                        ticks: {
                            color: '#8b949e',
                            font: { family: 'Poppins', size: 11 },
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