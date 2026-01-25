@extends('admin.layout')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.referrals.index') }}" class="text-dragon-silver-dark hover:text-dragon-red transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="text-2xl font-bold text-dragon-red dragon-text-glow uppercase tracking-tight">
                    {{ $referralLink->name }}
                </h2>
            </div>
            <p class="text-dragon-silver-dark flex items-center gap-2">
                <i class="fas fa-link text-xs"></i>
                <code class="text-dragon-red font-mono text-sm bg-dragon-black px-2 py-0.5 rounded border border-dragon-border">
                    {{ route('referral.track', $referralLink->code) }}
                </code>
            </p>
        </div>
        
        <div class="flex gap-4">
            <div class="bg-dragon-surface border border-dragon-border p-4 rounded-xl shadow-lg min-w-[140px]">
                <div class="text-xs font-black text-dragon-red uppercase tracking-widest mb-1">Total Clicks</div>
                <div class="text-2xl font-bold text-dragon-silver">{{ $referralLink->total_clicks_count }}</div>
            </div>
            <div class="bg-dragon-surface border border-dragon-border p-4 rounded-xl shadow-lg min-w-[140px]">
                <div class="text-xs font-black text-green-500 uppercase tracking-widest mb-1">Unique Clicks</div>
                <div class="text-2xl font-bold text-dragon-silver">{{ $referralLink->unique_clicks_count }}</div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-dragon-surface rounded-xl border border-dragon-border shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-dragon-border flex items-center justify-between">
            <h3 class="text-lg font-bold text-dragon-silver flex items-center">
                <i class="fas fa-chart-line mr-3 text-dragon-red"></i> TRAFFIC OVERVIEW (LAST 30 DAYS)
            </h3>
        </div>
        <div class="p-6">
            <div class="relative h-[400px]">
                <canvas id="clicksChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('clicksChart').getContext('2d');
        const data = @json($clicksByDay);
        
        // Format dates for display
        const labels = data.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.reverse(),
                datasets: [{
                    label: 'Clicks',
                    data: data.map(d => d.count).reverse(),
                    borderColor: '#d40000',
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, 'rgba(212, 0, 0, 0)');
                        gradient.addColorStop(1, 'rgba(212, 0, 0, 0.2)');
                        return gradient;
                    },
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#d40000',
                    pointBorderColor: '#0a0a0a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#c0c0c0', font: { weight: 'bold' } }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: '#c0c0c0', font: { weight: 'bold' } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#d40000',
                        bodyColor: '#e8e8e8',
                        borderColor: '#333',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ` Clicks: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
