@extends('admin.layout')

@section('title', 'Sending to Discord - Aragon RSPS Admin')

@section('header', 'Sending Update to Discord')

@section('content')
<div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-lg p-8">
    <div class="text-center">
        <div class="mb-6">
            <i class="fab fa-discord text-6xl text-indigo-500 mb-4"></i>
            <h2 class="text-2xl font-bold text-dragon-silver mb-2">Capturing Screenshot...</h2>
            <p class="text-dragon-silver-dark">Please wait while we capture and send your update to Discord.</p>
        </div>
        
        <div class="flex items-center justify-center gap-3 mb-6">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-dragon-red"></div>
            <span class="text-dragon-silver">Processing...</span>
        </div>
        
        <div class="bg-dragon-black rounded-lg p-4 text-left max-w-md mx-auto">
            <h3 class="font-semibold text-dragon-silver mb-2">{{ $update->title }}</h3>
            <p class="text-sm text-dragon-silver-dark">
                <i class="far fa-calendar mr-2"></i>{{ $update->created_at->format('M d, Y') }}
            </p>
        </div>
        
        <div id="status-message" class="mt-6 hidden">
            <div class="p-4 rounded-lg" id="status-box">
                <p id="status-text" class="font-semibold"></p>
            </div>
        </div>
        
        <a href="{{ route('admin.updates.index') }}" 
           class="mt-6 inline-block px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Updates
        </a>
    </div>
</div>

<script>
    let captureWindow = null;
    
    function openCaptureWindow() {
        const url = '{{ route("admin.updates.screenshot-view", $update) }}';
        const width = 1200;
        const height = 800;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        
        captureWindow = window.open(
            url,
            'screenshot-capture',
            `width=${width},height=${height},left=${left},top=${top},scrollbars=yes`
        );
        
        // Check if window closed successfully
        const checkClosed = setInterval(function() {
            if (captureWindow && captureWindow.closed) {
                clearInterval(checkClosed);
                showStatus('success', 'Update sent to Discord successfully!');
                setTimeout(function() {
                    window.location.href = '{{ route("admin.updates.index") }}';
                }, 2000);
            }
        }, 500);
        
        // Timeout after 30 seconds
        setTimeout(function() {
            if (captureWindow && !captureWindow.closed) {
                captureWindow.close();
                showStatus('error', 'Screenshot capture timed out. Please try again.');
            }
        }, 30000);
    }
    
    function showStatus(type, message) {
        const statusBox = document.getElementById('status-box');
        const statusText = document.getElementById('status-text');
        const statusMessage = document.getElementById('status-message');
        
        statusText.textContent = message;
        
        if (type === 'success') {
            statusBox.className = 'p-4 rounded-lg bg-green-600 text-white';
        } else {
            statusBox.className = 'p-4 rounded-lg bg-red-600 text-white';
        }
        
        statusMessage.classList.remove('hidden');
    }
    
    // Auto-start capture when page loads
    window.addEventListener('load', function() {
        setTimeout(openCaptureWindow, 500);
    });
</script>
@endsection
