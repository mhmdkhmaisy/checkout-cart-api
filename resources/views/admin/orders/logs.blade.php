@extends('admin.layout')

@section('title', 'Order Logs - RSPS Donation Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold gradient-green bg-clip-text text-transparent">
            Order Logs
        </h2>
        <div class="flex space-x-4">
            <input type="text" 
                   id="search-logs" 
                   placeholder="Search logs..." 
                   class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <select id="filter-action" 
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All Actions</option>
                <option value="CHECKOUT.ORDER.APPROVED">Order Approved</option>
                <option value="PAYMENT.CAPTURE.COMPLETED">Payment Completed</option>
                <option value="charge:confirmed">Charge Confirmed</option>
                <option value="charge:failed">Charge Failed</option>
                <option value="charge:pending">Charge Pending</option>
            </select>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="glass-effect rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-surface">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">Order ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">Username</th>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">Action</th>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">Timestamp</th>
                        <th class="px-6 py-4 text-left font-semibold text-green-primary">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700" id="logs-tbody">
                    @forelse($logs as $log)
                        <tr class="hover:bg-dark-surface transition-colors">
                            <td class="px-6 py-4">{{ $log->id }}</td>
                            <td class="px-6 py-4">
                                @if($log->order)
                                    <a href="{{ route('admin.orders.show', $log->order) }}" 
                                       class="text-green-primary hover:underline">
                                        #{{ $log->order_id }}
                                    </a>
                                @else
                                    #{{ $log->order_id }}
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $log->username ?: 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @switch($log->status)
                                        @case('paid')
                                            bg-green-600 text-green-100
                                            @break
                                        @case('pending')
                                            bg-yellow-600 text-yellow-100
                                            @break
                                        @case('failed')
                                            bg-red-600 text-red-100
                                            @break
                                        @case('refunded')
                                            bg-orange-600 text-orange-100
                                            @break
                                        @default
                                            bg-gray-600 text-gray-100
                                    @endswitch">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if(str_contains($log->last_event, 'COMPLETED') || str_contains($log->last_event, 'confirmed'))
                                        bg-green-600 text-green-100
                                    @elseif(str_contains($log->last_event, 'APPROVED') || str_contains($log->last_event, 'pending'))
                                        bg-blue-600 text-blue-100
                                    @elseif(str_contains($log->last_event, 'failed') || str_contains($log->last_event, 'DENIED'))
                                        bg-red-600 text-red-100
                                    @else
                                        bg-gray-600 text-gray-100
                                    @endif">
                                    {{ str_replace(['_', ':'], [' ', ' '], ucwords(strtolower($log->last_event))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $log->updated_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <button onclick="showLogDetails({{ $log->id }})" 
                                        class="text-blue-400 hover:text-blue-300"
                                        data-log-id="{{ $log->id }}"
                                        data-log-action="{{ $log->last_event }}"
                                        data-log-status="{{ $log->status }}"
                                        data-log-timestamp="{{ $log->updated_at->format('Y-m-d H:i:s') }}"
                                        data-log-details="{{ base64_encode(json_encode($log->payload, JSON_PRETTY_PRINT)) }}">
                                    View Details
                                </button>
                                @if($log->order)
                                <a href="{{ route('admin.orders.events', $log->order_id) }}" 
                                   class="text-green-400 hover:text-green-300">
                                    View Timeline
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                No logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="flex justify-center">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<!-- Log Details Modal -->
<div id="log-details-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-white">Log Details</h3>
                <button onclick="closeLogModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Log ID</label>
                        <p id="modal-log-id" class="text-white bg-gray-700 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Event Type</label>
                        <p id="modal-log-action" class="text-white bg-gray-700 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                        <p id="modal-log-status" class="text-white bg-gray-700 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Timestamp</label>
                        <p id="modal-log-timestamp" class="text-white bg-gray-700 p-2 rounded"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Payload Details</label>
                    <div id="modal-log-details" class="bg-gray-700 p-4 rounded-lg text-white whitespace-pre-wrap text-sm max-h-96 overflow-y-auto font-mono"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showLogDetails(logId) {
    const button = document.querySelector(`button[data-log-id="${logId}"]`);
    if (!button) return;
    
    const action = button.getAttribute('data-log-action');
    const status = button.getAttribute('data-log-status');
    const timestamp = button.getAttribute('data-log-timestamp');
    const detailsBase64 = button.getAttribute('data-log-details');
    
    // Decode base64 details
    let details = 'No details available';
    try {
        details = atob(detailsBase64);
    } catch (e) {
        console.error('Failed to decode details:', e);
    }
    
    document.getElementById('modal-log-id').textContent = logId;
    document.getElementById('modal-log-action').textContent = action;
    document.getElementById('modal-log-status').textContent = status.toUpperCase();
    document.getElementById('modal-log-timestamp').textContent = timestamp;
    document.getElementById('modal-log-details').textContent = details;
    document.getElementById('log-details-modal').classList.remove('hidden');
}

function closeLogModal() {
    document.getElementById('log-details-modal').classList.add('hidden');
}

// Search and filter functionality
document.getElementById('search-logs').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#logs-tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('filter-action').addEventListener('change', function() {
    const filterValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#logs-tbody tr');
    
    rows.forEach(row => {
        if (!filterValue) {
            row.style.display = '';
            return;
        }
        
        const actionCell = row.querySelector('td:nth-child(5)');
        if (actionCell) {
            const actionText = actionCell.textContent.toLowerCase();
            row.style.display = actionText.includes(filterValue.replace('.', ' ').replace(':', ' ')) ? '' : 'none';
        }
    });
});

// Close modal when clicking outside
document.getElementById('log-details-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogModal();
    }
});
</script>
@endsection