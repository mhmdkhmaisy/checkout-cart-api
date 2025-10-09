@extends('layouts.public')

@section('title', 'Play Aragon RSPS - Download Game Client')
@section('description', 'Download the Aragon RSPS game client for Windows, macOS, Linux, or play with our standalone JAR file.')

@section('content')
<div class="fade-in-up">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            Play Aragon RSPS
        </h1>
        <p class="text-muted" style="font-size: 1.25rem; max-width: 600px; margin: 0 auto;">
            Choose your platform and start your adventure!<br>
            <strong class="text-primary">All downloads are free and safe.</strong>
        </p>
    </div>

    <!-- Download Options Table -->
    <div class="glass-card mb-5">
        <h3 class="text-primary text-center mb-4">
            <i class="fas fa-download"></i> Available Clients
        </h3>
        
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th style="width: 15%;">Platform</th>
                        <th style="width: 15%;">Version</th>
                        <th style="width: 15%;">File Size</th>
                        <th style="width: 25%;">System Requirements</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Download</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(App\Models\Client::OS_TYPES as $os => $displayName)
                        @php
                            $client = $clients->get($os);
                            $isAvailable = $client && $client->enabled;
                        @endphp
                        <tr class="{{ $isAvailable ? '' : 'table-secondary' }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($os === 'windows')
                                        <i class="fab fa-windows text-primary me-2" style="font-size: 1.5rem;"></i>
                                    @elseif($os === 'macos')
                                        <i class="fab fa-apple text-primary me-2" style="font-size: 1.5rem;"></i>
                                    @elseif($os === 'linux')
                                        <i class="fab fa-linux text-primary me-2" style="font-size: 1.5rem;"></i>
                                    @else
                                        <i class="fas fa-coffee text-primary me-2" style="font-size: 1.5rem;"></i>
                                    @endif
                                    <strong>{{ $displayName }}</strong>
                                </div>
                            </td>
                            <td>
                                @if($isAvailable)
                                    <span class="badge bg-success">v{{ $client->version }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($isAvailable)
                                    {{ $client->formatted_size }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    @if($os === 'windows')
                                        Windows 10+, 4GB RAM, DirectX 11
                                    @elseif($os === 'macos')
                                        macOS 10.14+, 4GB RAM, Metal GPU
                                    @elseif($os === 'linux')
                                        Ubuntu 18.04+, 4GB RAM, OpenGL 3.3
                                    @else
                                        Java 8+, Any OS with JVM, 4GB RAM
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($isAvailable)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Available
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-clock"></i> Coming Soon
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isAvailable)
                                    <a href="{{ $client->download_url }}" 
                                       class="btn btn-primary btn-sm"
                                       onclick="trackDownload('{{ $os }}', '{{ $client->version }}')">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-clock"></i> Soon
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed System Requirements -->
    <div class="glass-card mt-4">
        <h3 class="text-primary text-center mb-4">
            <i class="fas fa-desktop"></i> Detailed System Requirements
        </h3>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-dark border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fab fa-windows text-primary mb-3" style="font-size: 2rem;"></i>
                        <h5 class="text-primary">Windows</h5>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Windows 10 or later</li>
                            <li><i class="fas fa-check text-success me-2"></i>4GB RAM minimum</li>
                            <li><i class="fas fa-check text-success me-2"></i>1GB free disk space</li>
                            <li><i class="fas fa-check text-success me-2"></i>DirectX 11 compatible</li>
                            <li><i class="fas fa-check text-success me-2"></i>Internet connection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fab fa-apple text-primary mb-3" style="font-size: 2rem;"></i>
                        <h5 class="text-primary">macOS</h5>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>macOS 10.14 or later</li>
                            <li><i class="fas fa-check text-success me-2"></i>4GB RAM minimum</li>
                            <li><i class="fas fa-check text-success me-2"></i>1GB free disk space</li>
                            <li><i class="fas fa-check text-success me-2"></i>Metal compatible GPU</li>
                            <li><i class="fas fa-check text-success me-2"></i>Internet connection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fab fa-linux text-primary mb-3" style="font-size: 2rem;"></i>
                        <h5 class="text-primary">Linux</h5>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Ubuntu 18.04+ / Similar</li>
                            <li><i class="fas fa-check text-success me-2"></i>4GB RAM minimum</li>
                            <li><i class="fas fa-check text-success me-2"></i>1GB free disk space</li>
                            <li><i class="fas fa-check text-success me-2"></i>OpenGL 3.3 support</li>
                            <li><i class="fas fa-check text-success me-2"></i>Internet connection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-coffee text-primary mb-3" style="font-size: 2rem;"></i>
                        <h5 class="text-primary">Standalone JAR</h5>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Java 8 or higher</li>
                            <li><i class="fas fa-check text-success me-2"></i>Any OS with JVM</li>
                            <li><i class="fas fa-check text-success me-2"></i>4GB RAM minimum</li>
                            <li><i class="fas fa-check text-success me-2"></i>Cross-platform</li>
                            <li><i class="fas fa-check text-success me-2"></i>Internet connection</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Installation Instructions -->
    <div class="glass-card mt-4">
        <h3 class="text-primary text-center mb-4">
            <i class="fas fa-question-circle"></i> Installation Instructions
        </h3>
        
        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                        1
                    </div>
                    <h5 class="text-primary">Download</h5>
                    <p class="text-muted">Select your operating system from the table above and click download. The file will be saved to your default downloads folder.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                        2
                    </div>
                    <h5 class="text-primary">Install</h5>
                    <p class="text-muted">Run the installer and follow the setup wizard. For JAR files, ensure Java is installed and double-click to run.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                        3
                    </div>
                    <h5 class="text-primary">Play</h5>
                    <p class="text-muted">Launch the game and create your account to start your adventure! Login with your credentials or register a new account.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Version History -->
    @if($clients->where('enabled', true)->count() > 0)
        <div class="glass-card mt-4">
            <h3 class="text-primary text-center mb-4">
                <i class="fas fa-history"></i> Latest Updates
            </h3>
            
            <div class="row">
                @foreach($clients->where('enabled', true) as $client)
                    @if($client->changelog)
                        <div class="col-md-6 mb-3">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-{{ $client->os === 'windows' ? 'windows' : ($client->os === 'macos' ? 'apple' : ($client->os === 'linux' ? 'linux' : 'coffee')) }} me-2"></i>
                                        {{ $client->os_display }} v{{ $client->version }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">{{ Str::limit($client->changelog, 150) }}</p>
                                    <small class="text-muted">Released {{ $client->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Additional Links -->
    <div class="text-center mt-5">
        <h4 class="text-primary mb-3">Need Help?</h4>
        <div class="btn-group" role="group">
            <a href="#" class="btn btn-outline-primary">
                <i class="fab fa-discord"></i> Join Discord
            </a>
            <a href="{{ route('vote.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-vote-yea"></i> Vote for Us
            </a>
            <a href="#" class="btn btn-outline-primary">
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