document.addEventListener('DOMContentLoaded', function () {
    const dataEl = document.getElementById('dashboard-data');
    if (!dataEl) return;

    const payload = JSON.parse(dataEl.textContent);
    const trend = payload.trend;
    const summary = payload.summary;
    const selectedLabel = payload.selectedLabel;

    const COLORS = {
        total: '#64748b',
        open: '#0ea5e9',
        draft: '#d97706',
        submitted: '#2563eb',
        approved: '#059669',
        rejected: '#ef4444',
    };

    // Plugin garis vertikal untuk highlight tanggal terpilih
    const verticalLinePlugin = {
        id: 'verticalLine',
        afterDraw(chart) {
            if (!selectedLabel) return;

            const labels = chart.data.labels;
            const index = labels.indexOf(selectedLabel);
            if (index === -1) return;

            const ctx = chart.ctx;
            const xPos = chart.scales.x.getPixelForValue(index);
            const topY = chart.scales.y.top;
            const bottomY = chart.scales.y.bottom;

            ctx.save();
            ctx.beginPath();
            ctx.moveTo(xPos, topY);
            ctx.lineTo(xPos, bottomY);
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#f59e0b';
            ctx.setLineDash([6, 3]);
            ctx.stroke();
            ctx.restore();

            // Label di atas garis
            ctx.save();
            ctx.fillStyle = '#f59e0b';
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('▼ ' + selectedLabel, xPos, topY - 3);
            ctx.restore();
        }
    };

    // ---------------- Trend Line Chart ----------------
    const trendCanvas = document.getElementById('trendChart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            plugins: [verticalLinePlugin],
            data: {
                labels: trend.labels,
                datasets: [
                    {
                        label: 'Total Assignment',
                        data: trend.total,
                        borderColor: COLORS.total,
                        backgroundColor: COLORS.total,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Open',
                        data: trend.open,
                        borderColor: COLORS.open,
                        backgroundColor: COLORS.open,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Draft',
                        data: trend.draft,
                        borderColor: COLORS.draft,
                        backgroundColor: COLORS.draft,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Submitted by Pencacah',
                        data: trend.submitted,
                        borderColor: COLORS.submitted,
                        backgroundColor: COLORS.submitted,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Approved by Pengawas',
                        data: trend.approved,
                        borderColor: COLORS.approved,
                        backgroundColor: COLORS.approved,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Rejected by Pengawas',
                        data: trend.rejected,
                        borderColor: COLORS.rejected,
                        backgroundColor: COLORS.rejected,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                animation: false,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    // ---------------- Donut Chart ----------------
    const donutCanvas = document.getElementById('donutChart');
    if (donutCanvas) {
        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'Draft', 'Submitted by Pencacah', 'Approved by Pengawas', 'Rejected by Pengawas'],
                datasets: [{
                    data: [summary.open, summary.draft, summary.submitted, summary.approved, summary.rejected],
                    backgroundColor: [COLORS.open, COLORS.draft, COLORS.submitted, COLORS.approved, COLORS.rejected],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                animation: false,
                maintainAspectRatio: true,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                },
            },
        });
    }
});