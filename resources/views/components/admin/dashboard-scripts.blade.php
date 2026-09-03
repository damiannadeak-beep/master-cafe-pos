<script>
    const dailyLabels = @json($chartDailyLabels);
    const dailyData = @json($chartDailyData);
    const dailyLaba = @json($chartDailyLaba);
    const monthlyLabels = @json($chartMonthlyLabels);
    const monthlyData = @json($chartMonthlyData);
    const monthlyLaba = @json($chartMonthlyLaba);

    const createSalesChart = (elementId, labels, dataSales, dataLaba) => {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
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
                            label: (context) => context.dataset.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
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
                            callback: (value) => 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                        }
                    }
                }
            }
        });
    };

    createSalesChart('dailySalesChart', dailyLabels, dailyData, dailyLaba);
    createSalesChart('monthlySalesChart', monthlyLabels, monthlyData, monthlyLaba);

    function getAiAnalysis() {
        const btn = document.getElementById('btn-analyze');
        const content = document.getElementById('ai-analysis-content');
        
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
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Analisis';
            
            if (data.error) {
                content.innerHTML = `<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> ${data.error}</div>`;
            } else if (data.analysis) {
                content.classList.remove('text-center', 'text-white-50');
                content.innerHTML = `<div class="fs-6 lh-lg text-white">${data.analysis}</div>`;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Coba Lagi';
            content.innerHTML = `<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Gagal terhubung ke server AI.</div>`;
        });
    }
</script>