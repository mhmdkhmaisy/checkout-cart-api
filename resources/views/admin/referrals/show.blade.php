@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <h1 class="h3 text-white">Stats for: {{ $referralLink->name }}</h1>
        <p class="text-muted">Link: <code>{{ route('referral.track', $referralLink->code) }}</code></p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0">Clicks Over Time (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="clicksChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('clicksChart').getContext('2d');
    const data = @json($clicksByDay);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Clicks',
                data: data.map(d => d.count),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' } },
                x: { grid: { color: 'rgba(255,255,255,0.1)' } }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
</script>
@endsection
