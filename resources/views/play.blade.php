@extends('layouts.public')

@section('title', 'Play Aragon RSPS - Download Game Client')
@section('description', 'Download the Aragon RSPS game client for Windows, macOS, Linux, or play with our standalone JAR file.')

@section('content')
<div class="fade-in-up" style="max-width: 1000px; margin: 0 auto;">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            <i class="fas fa-download"></i> Download & Play
        </h1>
        <p class="text-muted" style="font-size: 1.25rem;">
            Start your adventure in seconds!<br>
            <strong class="text-primary">All downloads are free and safe.</strong>
        </p>
    </div>

    @php
        $windowsClient = $clients->get('windows');
        $macosClient = $clients->get('macos');
        $linuxClient = $clients->get('linux');
        $jarClient = $clients->get('jar');
    @endphp

    <!-- Main Windows Download Card -->
    @if($windowsClient && $windowsClient->enabled)
    <div class="glass-card text-center mb-5" style="padding: 3rem;">
        <i class="fab fa-windows text-primary" style="font-size: 5rem; margin-bottom: 1.5rem; display: block;"></i>
        <h2 class="text-primary" style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
            Windows Launcher
        </h2>
        <p class="text-muted mb-2" style="font-size: 1.1rem;">
            The recommended way to play Aragon RSPS
        </p>
        <div class="mb-4">
            <span style="display: inline-block; background: rgba(196, 30, 58, 0.2); padding: 0.5rem 1rem; border-radius: 6px; margin: 0.5rem;">
                <i class="fas fa-tag"></i> Version {{ $windowsClient->version }}
            </span>
            <span style="display: inline-block; background: rgba(196, 30, 58, 0.2); padding: 0.5rem 1rem; border-radius: 6px; margin: 0.5rem;">
                <i class="fas fa-file"></i> {{ $windowsClient->formatted_size }}
            </span>
            <span style="display: inline-block; background: rgba(34, 197, 94, 0.2); padding: 0.5rem 1rem; border-radius: 6px; margin: 0.5rem; color: #22c55e;">
                <i class="fas fa-check-circle"></i> Latest
            </span>
        </div>
        <a href="{{ $windowsClient->download_url }}" 
           class="btn btn-primary" 
           onclick="trackDownload('windows', '{{ $windowsClient->version }}')"
           style="font-size: 1.25rem; padding: 1rem 3rem;">
            <i class="fas fa-download"></i> Download for Windows
        </a>
        <p class="text-muted mt-3" style="font-size: 0.9rem;">
            <i class="fas fa-shield-alt"></i> Secure, fast, and optimized for Windows 10+
        </p>
    </div>
    @else
    <div class="glass-card text-center mb-5" style="padding: 3rem;">
        <i class="fab fa-windows" style="font-size: 5rem; margin-bottom: 1.5rem; display: block; color: #666;"></i>
        <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; color: #666;">
            Windows Launcher
        </h2>
        <p class="text-muted mb-4" style="font-size: 1.1rem;">
            Coming soon...
        </p>
        <button class="btn btn-secondary" disabled style="font-size: 1.25rem; padding: 1rem 3rem;">
            <i class="fas fa-clock"></i> Not Available Yet
        </button>
    </div>
    @endif

    <!-- Alternative Downloads -->
    <h3 class="text-center text-primary mb-4" style="font-size: 1.75rem; font-weight: 600;">
        <i class="fas fa-laptop-code"></i> Alternative Downloads
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <!-- macOS Card -->
        <div class="glass-card text-center" style="padding: 2rem;">
            <i class="fab fa-apple text-primary" style="font-size: 3.5rem; margin-bottom: 1rem; display: block;"></i>
            <h4 class="text-primary mb-2" style="font-size: 1.5rem; font-weight: 600;">macOS</h4>
            @if($macosClient && $macosClient->enabled)
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    v{{ $macosClient->version }} • {{ $macosClient->formatted_size }}
                </p>
                <a href="{{ $macosClient->download_url }}" 
                   class="btn btn-outline w-full"
                   onclick="trackDownload('macos', '{{ $macosClient->version }}')">
                    <i class="fas fa-download"></i> Download
                </a>
            @else
                <p class="text-muted mb-3" style="font-size: 0.9rem;">Coming Soon</p>
                <button class="btn btn-outline w-full" disabled>
                    <i class="fas fa-clock"></i> Not Available
                </button>
            @endif
        </div>

        <!-- Linux Card -->
        <div class="glass-card text-center" style="padding: 2rem;">
            <i class="fab fa-linux text-primary" style="font-size: 3.5rem; margin-bottom: 1rem; display: block;"></i>
            <h4 class="text-primary mb-2" style="font-size: 1.5rem; font-weight: 600;">Linux</h4>
            @if($linuxClient && $linuxClient->enabled)
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    v{{ $linuxClient->version }} • {{ $linuxClient->formatted_size }}
                </p>
                <a href="{{ $linuxClient->download_url }}" 
                   class="btn btn-outline w-full"
                   onclick="trackDownload('linux', '{{ $linuxClient->version }}')">
                    <i class="fas fa-download"></i> Download
                </a>
            @else
                <p class="text-muted mb-3" style="font-size: 0.9rem;">Coming Soon</p>
                <button class="btn btn-outline w-full" disabled>
                    <i class="fas fa-clock"></i> Not Available
                </button>
            @endif
        </div>

        <!-- JAR Card -->
        <div class="glass-card text-center" style="padding: 2rem;">
            <i class="fas fa-coffee text-primary" style="font-size: 3.5rem; margin-bottom: 1rem; display: block;"></i>
            <h4 class="text-primary mb-2" style="font-size: 1.5rem; font-weight: 600;">Standalone JAR</h4>
            @if($jarClient && $jarClient->enabled)
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    v{{ $jarClient->version }} • {{ $jarClient->formatted_size }}
                </p>
                <a href="{{ $jarClient->download_url }}" 
                   class="btn btn-outline w-full"
                   onclick="trackDownload('jar', '{{ $jarClient->version }}')">
                    <i class="fas fa-download"></i> Download
                </a>
            @else
                <p class="text-muted mb-3" style="font-size: 0.9rem;">Coming Soon</p>
                <button class="btn btn-outline w-full" disabled>
                    <i class="fas fa-clock"></i> Not Available
                </button>
            @endif
        </div>
    </div>

    <!-- Quick Start Guide -->
    <div class="glass-card" style="padding: 2.5rem;">
        <h3 class="text-primary text-center mb-4" style="font-size: 1.75rem; font-weight: 600;">
            <i class="fas fa-rocket"></i> Quick Start
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div class="text-center">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-color), var(--primary-bright)); border-radius: 50%; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(196, 30, 58, 0.4);">
                    1
                </div>
                <h5 class="text-primary mb-2">Download</h5>
                <p class="text-muted" style="font-size: 0.95rem;">Click the download button for your platform</p>
            </div>
            <div class="text-center">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-color), var(--primary-bright)); border-radius: 50%; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(196, 30, 58, 0.4);">
                    2
                </div>
                <h5 class="text-primary mb-2">Install</h5>
                <p class="text-muted" style="font-size: 0.95rem;">Run the installer and follow the prompts</p>
            </div>
            <div class="text-center">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-color), var(--primary-bright)); border-radius: 50%; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(196, 30, 58, 0.4);">
                    3
                </div>
                <h5 class="text-primary mb-2">Play</h5>
                <p class="text-muted" style="font-size: 0.95rem;">Launch and create your account to begin!</p>
            </div>
        </div>
    </div>

    <!-- Need Help Section -->
    <div class="text-center mt-5">
        <h4 class="text-primary mb-3" style="font-size: 1.5rem;">Need Help?</h4>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="#" class="btn btn-outline">
                <i class="fab fa-discord"></i> Join Discord
            </a>
            <a href="{{ route('vote.index') }}" class="btn btn-outline">
                <i class="fas fa-vote-yea"></i> Vote for Us
            </a>
            <a href="#" class="btn btn-outline">
                <i class="fas fa-question-circle"></i> Support
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function trackDownload(os, version) {
    // Track download analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'download', {
            'event_category': 'client',
            'event_label': os + '_' + version,
            'value': 1
        });
    }
    
    // You could also send to your own analytics endpoint
    fetch('/api/track-download', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            os: os,
            version: version,
            timestamp: new Date().toISOString()
        })
    }).catch(console.error);
}

// Auto-refresh page every 5 minutes to check for new versions
setInterval(function() {
    // Only refresh if user hasn't interacted recently
    if (document.hidden === false) {
        window.location.reload();
    }
}, 300000); // 5 minutes
</script>
@endpush
@endsection