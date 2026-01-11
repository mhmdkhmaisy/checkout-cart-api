@extends('layouts.public')

@section('title', 'Vote for Aragon RSPS - Earn Rewards!')
@section('description', 'Vote for Aragon RSPS on top RuneScape private server lists and earn amazing in-game rewards!')

@section('content')
<div class="fade-in-up">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            Vote for Aragon RSPS!
        </h1>
        <p class="text-muted" style="font-size: 1.25rem; max-width: 600px; margin: 0 auto;">
            Help our community grow by voting on the sites below!<br>
            <strong class="text-primary">You will be rewarded for every vote!</strong>
        </p>
    </div>

    <!-- Username Section -->
    <div class="glass-card text-center mb-4" style="max-width: 500px; margin: 0 auto;">
        <!-- Show username form if no username is set -->
        <div id="username-form" style="{{ session('vote_username') ? 'display: none;' : '' }}">
            <h3 class="text-primary mb-3">
                <i class="fas fa-user"></i> Enter Your Username
            </h3>
            <p class="text-muted mb-3">Required to track your votes and deliver rewards</p>
            
            <div class="form-group">
                <input type="text" 
                       id="username" 
                       placeholder="Your in-game username"
                       class="form-input"
                       maxlength="15"
                       pattern="[A-Za-z0-9_ ]+"
                       value="{{ session('vote_username') }}">
            </div>
            
            <button onclick="setUsername()" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-save"></i> Set Username
            </button>
            
            <div id="username-error" class="alert alert-error mt-2" style="display: none;"></div>
            <div id="username-success" class="alert alert-success mt-2" style="display: none;"></div>
        </div>

        <!-- Show current username if set -->
        <div id="username-display" style="{{ session('vote_username') ? '' : 'display: none;' }}">
            <h3 class="text-primary mb-3">
                <i class="fas fa-user-check"></i> Voting as
            </h3>
            <div class="mb-3">
                <span class="text-primary" style="font-size: 1.5rem; font-weight: 600;" id="current-username">
                    {{ session('vote_username') }}
                </span>
            </div>
            <button onclick="changeUsername()" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Click here if you wish to change
            </button>
        </div>
    </div>

    <!-- Vote Sites -->
    <div id="vote-sites" style="{{ session('vote_username') ? '' : 'display: none;' }}">
        <h2 class="text-center text-primary mb-4">
            <i class="fas fa-vote-yea"></i> Vote on These Sites
        </h2>
        
        <div class="grid grid-3">
            @foreach($sites as $site)
                <div class="glass-card text-center">
                    <div class="mb-3">
                        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-vote-yea" style="font-size: 1.5rem; color: white;"></i>
                        </div>
                        <h3 class="text-primary mb-2">{{ $site->title }}</h3>
                        <div id="site-status-{{ $site->id }}" class="text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                    
                    <button id="vote-btn-{{ $site->id }}" 
                            onclick="vote({{ $site->id }})" 
                            class="btn btn-primary"
                            style="width: 100%;"
                            disabled>
                        <i class="fas fa-vote-yea"></i>
                        <span id="vote-text-{{ $site->id }}">Vote Now</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- How to Vote -->
    <div class="glass-card mt-5">
        <h3 class="text-primary text-center mb-4">
            <i class="fas fa-question-circle"></i> How to Vote
        </h3>
        
        <div class="grid grid-3">
            <div class="text-center">
                <div style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; font-weight: bold; color: white;">
                    1
                </div>
                <h4 class="text-primary mb-2">Set Username</h4>
                <p class="text-muted">Enter your in-game username above to track your votes</p>
            </div>
            <div class="text-center">
                <div style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; font-weight: bold; color: white;">
                    2
                </div>
                <h4 class="text-primary mb-2">Click Vote</h4>
                <p class="text-muted">Click the vote button for any available site</p>
            </div>
            <div class="text-center">
                <div style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; font-weight: bold; color: white;">
                    3
                </div>
                <h4 class="text-primary mb-2">Get Rewards</h4>
                <p class="text-muted">Receive your rewards automatically in-game!</p>
            </div>
        </div>
    </div>

    <!-- Vote Statistics -->
    <div class="glass-card mt-4">
        <h3 class="text-primary text-center mb-4">
            <i class="fas fa-chart-bar"></i> Vote Statistics
        </h3>
        
        <div class="grid grid-3">
            <div class="text-center">
                <div class="text-primary" style="font-size: 2.5rem; font-weight: bold;">
                    {{ \App\Models\Vote::whereNotNull('callback_date')->count() }}
                </div>
                <div class="text-muted">Total Votes</div>
            </div>
            <div class="text-center">
                <div class="status-online" style="font-size: 2.5rem; font-weight: bold;">
                    {{ \App\Models\Vote::whereNotNull('callback_date')->whereDate('callback_date', today())->count() }}
                </div>
                <div class="text-muted">Today's Votes</div>
            </div>
            <div class="text-center">
                <div style="color: #3b82f6; font-size: 2.5rem; font-weight: bold;">
                    {{ \App\Models\VoteSite::where('active', true)->count() }}
                </div>
                <div class="text-muted">Active Sites</div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('vote.stats') }}" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i> View Full Statistics
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentUsername = '{{ session("vote_username") }}';

$(document).ready(function() {
    if (currentUsername) {
        $('#current-username').text(currentUsername);
        showVoteSites();
        loadVoteStatus();
    }
    
    $('#username').on('keypress', function(e) {
        if (e.which === 13) {
            setUsername();
        }
    });
});

function setUsername() {
    const username = $('#username').val().trim();
    const errorDiv = $('#username-error');
    const successDiv = $('#username-success');
    
    // Hide previous messages
    errorDiv.hide();
    successDiv.hide();
    
    if (!username) {
        showError('Please enter your username');
        return;
    }
    
    if (username.length < 1 || username.length > 15) {
        showError('Username must be between 1 and 15 characters');
        return;
    }
    
    if (!/^[A-Za-z0-9_ ]+$/.test(username)) {
        showError('Username can only contain letters, numbers, underscores, and spaces');
        return;
    }
    
    // Store username in session via AJAX
    $.post('/vote/set-username', {
        username: username,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            currentUsername = username;
            $('#current-username').text(username);
            successDiv.html('<i class="fas fa-check"></i> Username saved successfully!').show();
            
            // Hide form and show display
            setTimeout(function() {
                $('#username-form').hide();
                $('#username-display').show();
                showVoteSites();
                loadVoteStatus();
            }, 1000);
        } else {
            showError(response.message || 'Failed to save username');
        }
    })
    .fail(function() {
        showError('An error occurred. Please try again.');
    });
}

function changeUsername() {
    $('#username-display').hide();
    $('#username-form').show();
    $('#vote-sites').hide();
    $('#username').focus();
}

function showError(message) {
    $('#username-error').html('<i class="fas fa-exclamation-circle"></i> ' + message).show();
}

function showVoteSites() {
    $('#vote-sites').show();
}

function loadVoteStatus() {
    if (!currentUsername) return;
    
    $.get('/vote/user-votes', { username: currentUsername })
        .done(function(data) {
            data.forEach(function(siteData) {
                const siteId = siteData.site.id;
                const statusDiv = $(`#site-status-${siteId}`);
                const button = $(`#vote-btn-${siteId}`);
                const buttonText = $(`#vote-text-${siteId}`);
                
                if (siteData.can_vote) {
                    statusDiv.html('<span class="status-online"><i class="fas fa-check-circle"></i> Ready to vote</span>');
                    button.prop('disabled', false).removeClass('btn-dark').addClass('btn-primary');
                    buttonText.text('Vote Now');
                } else {
                    statusDiv.html(`<span style="color: #f59e0b;"><i class="fas fa-clock"></i> Next vote in: ${siteData.time_remaining}</span>`);
                    button.prop('disabled', true).removeClass('btn-primary').addClass('btn-dark');
                    buttonText.text('Please Wait');
                }
            });
        })
        .fail(function() {
            console.error('Failed to load vote status');
        });
}

function vote(siteId) {
    if (!currentUsername) {
        showError('Please set your username first');
        return;
    }
    
    const button = $(`#vote-btn-${siteId}`);
    const buttonText = $(`#vote-text-${siteId}`);
    
    button.prop('disabled', true);
    buttonText.text('Processing...');
    
    $.post(`/vote/${siteId}`, {
        username: currentUsername,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            window.open(response.vote_url, '_blank');
            setTimeout(function() {
                loadVoteStatus();
            }, 2000);
        } else {
            alert(response.message);
            button.prop('disabled', false);
            buttonText.text('Vote Now');
        }
    })
    .fail(function() {
        alert('An error occurred. Please try again.');
        button.prop('disabled', false);
        buttonText.text('Vote Now');
    });
}

// Auto-refresh vote status every 30 seconds
setInterval(function() {
    if (currentUsername) {
        loadVoteStatus();
    }
}, 30000);
</script>
@endpush