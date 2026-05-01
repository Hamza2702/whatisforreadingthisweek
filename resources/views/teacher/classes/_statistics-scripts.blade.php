<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // monthly reading activity
        const activityData = @json($chartData);
        const activityEl = document.getElementById('activityChart');
        if (activityEl) {
            new Chart(activityEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(activityData),
                    datasets: [{
                        label: 'Books Finished',
                        data: Object.values(activityData),
                        backgroundColor: '#fb923c',
                        hoverBackgroundColor: '#f97316',
                        borderRadius: 4,
                        barThickness: 28,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#6D4423',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: '#6D4423' } },
                        x: { ticks: { color: '#6D4423' }, grid: { display: false } }
                    }
                }
            });
        }

        // horizontal bars reading lvl distribution
        const levelData = @json($levelDistribution);
        const levelEl = document.getElementById('levelChart');
        if (levelEl) {
            new Chart(levelEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(levelData).map(l => 'Level ' + l),
                    datasets: [{
                        label: 'Students',
                        data: Object.values(levelData),
                        backgroundColor: '#6D4423',
                        hoverBackgroundColor: '#5a371d',
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#6D4423', padding: 12, cornerRadius: 8, displayColors: false }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: '#6D4423' } },
                        y: { ticks: { color: '#6D4423' }, grid: { display: false } }
                    }
                }
            });
        }

        // weekly goal performance
        const weeks = @json($weeks);
        const weeklyEl = document.getElementById('weeklyChart');
        if (weeklyEl) {
            new Chart(weeklyEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: weeks.map(w => w.label),
                    datasets: [{
                        label: '% of class hitting goal',
                        data: weeks.map(w => w.percentage),
                        backgroundColor: weeks.map(w => w.class_met ? '#22c55e' : '#ef4444'),
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#6D4423',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                title: function(ctx) { return weeks[ctx[0].dataIndex].label + ' (' + weeks[ctx[0].dataIndex].date_range + ')'; },
                                label: function(ctx) {
                                    const w = weeks[ctx.dataIndex];
                                    return [
                                        w.percentage + '% hit goal',
                                        w.hit + ' students hit, ' + w.missed + ' missed'
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { color: '#6D4423', callback: v => v + '%' } },
                        x: { ticks: { color: '#6D4423', maxRotation: 0, autoSkip: true, autoSkipPadding: 8 }, grid: { display: false } }
                    }
                }
            });
        }
    });
</script>