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

    // Expose getAiAnalysis to window for onclick handler (SPA-safe)
    window.getAiAnalysis = function() {
        var btn = document.getElementById('btn-analyze');
        var content = document.getElementById('ai-analysis-content');
        if (!btn || !content) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sedang Menganalisis...';
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-grow text-primary mb-3" role="status"><span class="visually-hidden">Loading...</span></div><p class="text-white-50 small">Gemini AI sedang membaca dan menyimpulkan data penjualan Anda...</p></div>';

        fetch('{{ route('admin.ai_sales_analysis') }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Analisis';

            if (data.error) {
                content.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> ' + data.error + '</div>';
            } else if (data.analysis) {
                content.classList.remove('text-center', 'text-white-50');
                content.innerHTML = '<div class="fs-6 lh-lg text-white">' + data.analysis + '</div>';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Coba Lagi';
            content.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Gagal terhubung ke server AI.</div>';
        });
    };
})();
</script>