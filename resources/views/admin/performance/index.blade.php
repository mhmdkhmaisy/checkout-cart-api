@extends('admin.layout')

@section('title', 'Performance Monitor - Aragon RSPS Admin')

@section('content')
<style>
    /* Custom scrollbar styling */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(212, 0, 0, 0.1);
        border-radius: 4px;
        margin: 4px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d40000;
        border-radius: 4px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #ff0000;
    }
    
    /* Chart container with fixed height */
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    /* Sortable column headers */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: color 0.2s;
    }
    
    .sortable-header:hover {
        color: #ff0000;
    }
    
    .sort-indicator {
        display: inline-block;
        margin-left: 4px;
        font-size: 0.75rem;
        opacity: 0.5;
    }
    
    .sort-indicator.active {
        opacity: 1;
    }
    
    /* Custom Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-overlay.active {
        display: flex;
    }
    
    .modal-content {
        background: #1a1a1a;
        border: 2px solid #d40000;
        border-radius: 12px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(212, 0, 0, 0.3);
    }
    
    .modal-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 20px;
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Performance Monitor
            </h2>
            <p class="text-dragon-silver-dark mt-2">Real-time system performance and metrics analysis</p>
        </div>
        <div class="flex items-center space-x-4">
            <div id="last-updated" class="text-sm text-dragon-silver-dark"></div>
            <button onclick="showClearModal()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition-colors border border-red-700">
                Clear All Data
            </button>
            <button onclick="refreshMetrics()" class="bg-dragon-red hover:bg-dragon-red-bright text-white px-4 py-2 rounded-lg transition-colors">
                Refresh
            </button>
        </div>
    </div>

    <!-- Alerts -->
    <div id="alerts-container" class="hidden">
    </div>

    <!-- Clear Confirmation Modal -->
    <div id="clear-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="flex items-center mb-4">
                <svg class="w-12 h-12 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-xl font-bold text-dragon-red">Confirm Clear All Data</h3>
                    <p class="text-dragon-silver-dark text-sm mt-1">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-dragon-silver mb-4">
                Are you sure you want to delete all performance monitoring data? This will permanently remove:
            </p>
            <ul class="list-disc list-inside text-dragon-silver-dark mb-4 space-y-1">
                <li>All request logs and metrics</li>
                <li>Route performance history</li>
                <li>Slow query records</li>
                <li>Performance summaries</li>
            </ul>
            <div class="modal-buttons">
                <button onclick="hideClearModal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Cancel
                </button>
                <button onclick="confirmClearAll()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Yes, Clear All Data
                </button>
            </div>
        </div>
    </div>

    <!-- Live Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- CPU Load -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="flex justify-center mb-3">
                <svg class="w-8 h-8 text-dragon-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
            </div>
            <div id="cpu-load" class="text-3xl font-bold text-dragon-red">--</div>
            <div class="text-dragon-silver-dark mt-2">CPU Load</div>
        </div>
        
        <!-- Memory Usage -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="flex justify-center mb-3">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div id="memory-usage" class="text-3xl font-bold text-blue-400">--</div>
            <div class="text-dragon-silver-dark mt-2">Memory Usage</div>
        </div>
        
        <!-- Avg Response Time -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="flex justify-center mb-3">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div id="avg-response" class="text-3xl font-bold text-green-400">--</div>
            <div class="text-dragon-silver-dark mt-2">Avg Response (5min)</div>
        </div>
        
        <!-- Disk Space -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="flex justify-center mb-3">
                <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                </svg>
            </div>
            <div id="disk-space" class="text-3xl font-bold text-yellow-400">--</div>
            <div class="text-dragon-silver-dark mt-2">Disk Free</div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Request Timeline Chart -->
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <h3 class="text-xl font-semibold mb-4 text-dragon-red">Request Performance (Last Hour)</h3>
            <div class="chart-container">
                <canvas id="request-chart"></canvas>
            </div>
        </div>

        <!-- Route Performance Table -->
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <h3 class="text-xl font-semibold mb-4 text-dragon-red">Route Performance</h3>
            <div class="overflow-y-auto custom-scrollbar" style="max-height: 300px;">
                <table class="w-full">
                    <thead class="sticky top-0 bg-dragon-surface">
                        <tr class="text-left text-dragon-silver-dark text-sm">
                            <th class="pb-2">Route</th>
                            <th class="pb-2 text-right sortable-header" onclick="sortRoutes('avg_time')">
                                Avg Time<span id="sort-avg-time" class="sort-indicator">▼</span>
                            </th>
                            <th class="pb-2 text-right sortable-header" onclick="sortRoutes('request_count')">
                                Requests<span id="sort-requests" class="sort-indicator">▼</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="routes-table" class="text-dragon-silver">
                        <tr>
                            <td colspan="3" class="text-center py-8 text-dragon-silver-dark">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Slow Queries and Queue Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Slow Queries -->
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <h3 class="text-xl font-semibold mb-4 text-dragon-red">Slow Queries (Last Hour)</h3>
            <div class="overflow-y-auto custom-scrollbar" style="max-height: 300px;">
                <div id="slow-queries-container" class="space-y-3">
                    <div class="text-center py-8 text-dragon-silver-dark">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Queue Stats -->
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <h3 class="text-xl font-semibold mb-4 text-dragon-red">Queue Statistics</h3>
            <div id="queue-stats" class="space-y-4">
                <div class="text-center py-8 text-dragon-silver-dark">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let requestChart = null;
let routesData = [];
let currentSortColumn = 'avg_time';
let currentSortDirection = 'desc';

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatTime(ms) {
    // Handle non-numeric values
    if (ms === null || ms === undefined || isNaN(ms)) {
        return '0.00 ms';
    }
    
    // Convert to number if it's a string
    ms = parseFloat(ms);
    
    if (ms < 1000) return ms.toFixed(2) + ' ms';
    return (ms / 1000).toFixed(2) + ' s';
}

function updateLastUpdated() {
    document.getElementById('last-updated').textContent = 'Updated: ' + new Date().toLocaleTimeString();
}

function showAlerts(alerts) {
    const container = document.getElementById('alerts-container');
    if (!alerts || alerts.length === 0) {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');
    container.innerHTML = alerts.map(alert => {
        const color = alert.severity === 'critical' ? 'red' : 'yellow';
        return `
            <div class="glass-effect rounded-xl p-4 border border-${color}-500 bg-${color}-900/20">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-${color}-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <div class="font-semibold text-${color}-200">${alert.type.toUpperCase()}</div>
                        <div class="text-${color}-300">${alert.message}</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function updateLiveMetrics(data) {
    document.getElementById('cpu-load').textContent = data.current_cpu ? data.current_cpu.toFixed(2) : '--';
    document.getElementById('memory-usage').textContent = data.current_memory ? formatBytes(data.current_memory) : '--';
    document.getElementById('avg-response').textContent = data.recent_avg_time ? formatTime(data.recent_avg_time) : '--';
    
    if (data.disk_free && data.disk_total) {
        const freePercent = ((data.disk_free / data.disk_total) * 100).toFixed(1);
        document.getElementById('disk-space').textContent = freePercent + '%';
    }
}

function sortRoutes(column) {
    if (currentSortColumn === column) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortColumn = column;
        currentSortDirection = 'desc';
    }
    
    document.querySelectorAll('.sort-indicator').forEach(indicator => {
        indicator.classList.remove('active');
        indicator.textContent = '▼';
    });
    
    const indicator = document.getElementById(`sort-${column === 'avg_time' ? 'avg-time' : 'requests'}`);
    indicator.classList.add('active');
    indicator.textContent = currentSortDirection === 'asc' ? '▲' : '▼';
    
    const sorted = [...routesData].sort((a, b) => {
        const aVal = column === 'avg_time' ? parseFloat(a.avg_time) : parseInt(a.request_count);
        const bVal = column === 'avg_time' ? parseFloat(b.avg_time) : parseInt(b.request_count);
        
        return currentSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
    });
    
    renderRoutesTable(sorted);
}

function renderRoutesTable(routes) {
    const tbody = document.getElementById('routes-table');
    
    if (!routes || routes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-8 text-dragon-silver-dark">No data available</td></tr>';
        return;
    }

    tbody.innerHTML = routes.map(route => `
        <tr class="border-t border-dragon-border">
            <td class="py-2 text-sm">${route.route || 'Unknown'}</td>
            <td class="py-2 text-sm text-right">${formatTime(route.avg_time)}</td>
            <td class="py-2 text-sm text-right">${route.request_count}</td>
        </tr>
    `).join('');
}

function updateRoutesTable(routes) {
    routesData = routes || [];
    
    const sorted = [...routesData].sort((a, b) => {
        const aVal = currentSortColumn === 'avg_time' ? parseFloat(a.avg_time) : parseInt(a.request_count);
        const bVal = currentSortColumn === 'avg_time' ? parseFloat(b.avg_time) : parseInt(b.request_count);
        
        return currentSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
    });
    
    renderRoutesTable(sorted);
}

function updateSlowQueries(queries) {
    const container = document.getElementById('slow-queries-container');
    
    if (!queries || queries.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-dragon-silver-dark">No slow queries detected</div>';
        return;
    }

    container.innerHTML = queries.slice(0, 5).map(query => `
        <div class="p-3 bg-dragon-surface rounded-lg border border-dragon-border">
            <div class="flex justify-between items-start mb-2">
                <div class="text-sm text-dragon-red font-semibold">${formatTime(query.value)}</div>
                <div class="text-xs text-dragon-silver-dark">${new Date(query.created_at).toLocaleTimeString()}</div>
            </div>
            <div class="text-xs text-dragon-silver-dark font-mono overflow-hidden" style="max-height: 60px;">
                ${query.metadata?.sql ? query.metadata.sql.substring(0, 200) : 'N/A'}...
            </div>
        </div>
    `).join('');
}

function updateQueueStats(stats) {
    const container = document.getElementById('queue-stats');
    
    container.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-4 bg-dragon-surface rounded-lg border border-dragon-border">
                <div class="text-2xl font-bold text-green-400">${stats.successful_jobs || 0}</div>
                <div class="text-sm text-dragon-silver-dark">Successful Jobs</div>
            </div>
            <div class="text-center p-4 bg-dragon-surface rounded-lg border border-dragon-border">
                <div class="text-2xl font-bold text-red-400">${stats.failed_jobs || 0}</div>
                <div class="text-sm text-dragon-silver-dark">Failed Jobs</div>
            </div>
        </div>
    `;

    if (stats.recent_failures && stats.recent_failures.length > 0) {
        container.innerHTML += `
            <div class="mt-4 space-y-2">
                <div class="text-sm font-semibold text-dragon-silver-dark">Recent Failures:</div>
                ${stats.recent_failures.slice(0, 3).map(failure => `
                    <div class="text-xs p-2 bg-dragon-surface rounded border border-red-600/30">
                        <div class="text-dragon-silver">${failure.identifier}</div>
                        <div class="text-dragon-silver-dark">${failure.metadata?.exception || 'Unknown error'}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }
}

function updateRequestChart(history) {
    const ctx = document.getElementById('request-chart').getContext('2d');
    
    if (!history || history.length === 0) {
        return;
    }

    const labels = history.map(h => new Date(h.created_at).toLocaleTimeString());
    const data = history.map(h => h.value);

    if (requestChart) {
        requestChart.destroy();
    }

    requestChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Response Time (ms)',
                data: data,
                borderColor: '#d40000',
                backgroundColor: 'rgba(212, 0, 0, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#e8e8e8'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#333333'
                    },
                    ticks: {
                        color: '#c0c0c0'
                    }
                },
                x: {
                    grid: {
                        color: '#333333'
                    },
                    ticks: {
                        color: '#c0c0c0',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

async function refreshMetrics() {
    try {
        const [liveResponse, routesResponse, queriesResponse, historyResponse, queueResponse] = await Promise.all([
            fetch('/admin/performance/live'),
            fetch('/admin/performance/routes'),
            fetch('/admin/performance/slow-queries'),
            fetch('/admin/performance/history?type=request&minutes=60'),
            fetch('/admin/performance/queue-stats')
        ]);

        const live = await liveResponse.json();
        const routes = await routesResponse.json();
        const queries = await queriesResponse.json();
        const history = await historyResponse.json();
        const queue = await queueResponse.json();

        if (live.success) {
            updateLiveMetrics(live.data);
        }

        if (routes.success) {
            updateRoutesTable(routes.data);
        }

        if (queries.success) {
            updateSlowQueries(queries.data);
        }

        if (history.success) {
            updateRequestChart(history.data);
        }

        if (queue.success) {
            updateQueueStats(queue.data);
        }

        const alertsResponse = await fetch('/admin/performance/alerts');
        const alerts = await alertsResponse.json();
        if (alerts.success) {
            showAlerts(alerts.data);
        }

        updateLastUpdated();
    } catch (error) {
        console.error('Error refreshing metrics:', error);
    }
}

function showClearModal() {
    document.getElementById('clear-modal').classList.add('active');
}

function hideClearModal() {
    document.getElementById('clear-modal').classList.remove('active');
}

async function confirmClearAll() {
    try {
        const response = await fetch('/admin/performance/clear-all', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            hideClearModal();
            alert(`Successfully cleared ${result.data.logs_deleted} logs and ${result.data.summaries_deleted} summaries`);
            refreshMetrics();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error clearing data:', error);
        alert('Failed to clear data. Please try again.');
    }
}

refreshMetrics();
setInterval(refreshMetrics, 5000);
</script>
@endsection
