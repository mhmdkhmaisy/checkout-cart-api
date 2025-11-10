<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aragon RSPS - The Ultimate RuneScape Private Server')</title>
    <meta name="description" content="@yield('description', 'Join Aragon RSPS for the ultimate Old School RuneScape experience with custom content, active community, and regular updates.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/aragon_rsps_icon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Press+Start+2P&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        :root {
            --primary-color: #c41e3a;
            --primary-bright: #e63946;
            --primary-dark: #a01729;
            --secondary-color: #141414;
            --accent-color: #0a0a0a;
            --accent-gold: #d4a574;
            --accent-ember: #ff6b35;
            --text-light: #f0f0f0;
            --text-dark: #333333;
            --text-muted: #a0a0a0;
            --text-gold: #d4a574;
            --background-dark: #0d0d0d;
            --background-gradient: linear-gradient(135deg, #0d0d0d 0%, #1a1414 100%);
            --card-background: rgba(20, 16, 16, 0.92);
            --border-color: #3a2a2a;
            --border-gold: rgba(212, 165, 116, 0.25);
            --border-ember: rgba(255, 107, 53, 0.15);
            --hover-color: rgba(196, 30, 58, 0.12);
            --glow-primary: rgba(196, 30, 58, 0.4);
            --glow-gold: rgba(212, 165, 116, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0f0f 50%, #0d0d0d 100%);
            background-image: 
                linear-gradient(135deg, #0a0a0a 0%, #1a0f0f 50%, #0d0d0d 100%),
                radial-gradient(ellipse at 15% 20%, rgba(196, 30, 58, 0.08) 0%, transparent 40%),
                radial-gradient(ellipse at 85% 80%, rgba(212, 165, 116, 0.06) 0%, transparent 45%),
                radial-gradient(ellipse at 50% 50%, rgba(255, 107, 53, 0.04) 0%, transparent 60%);
            background-attachment: fixed;
            color: var(--text-light);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(180deg, rgba(13, 13, 13, 0.98) 0%, rgba(20, 16, 16, 0.95) 100%);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid;
            border-image: linear-gradient(90deg, transparent, var(--primary-color), var(--accent-gold), transparent) 1;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px var(--glow-primary);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-bright) 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            text-shadow: none;
            filter: drop-shadow(0 0 12px var(--glow-primary)) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
        }

        .logo img {
            width: 40px;
            height: 40px;
            margin-right: 0.75rem;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 0.5rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            position: relative;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::before,
        .nav-links a.active::before {
            width: 80%;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--text-light);
            text-shadow: 0 0 10px var(--glow-primary);
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Glass Card Effect */
        .glass-card {
            background: var(--card-background);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary-color), var(--accent-gold), transparent);
            border-radius: 8px 8px 0 0;
        }

        .glass-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 8px 30px var(--glow-primary), 0 0 20px var(--glow-gold), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-bright) 100%);
            color: var(--text-light);
            box-shadow: 0 4px 15px var(--glow-primary), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 25px var(--glow-primary), 0 0 30px var(--glow-gold), inset 0 1px 0 rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--primary-bright) 0%, var(--accent-ember) 100%);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-light);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            border-color: var(--primary-color);
            background: rgba(212, 0, 0, 0.1);
        }

        .btn-dark {
            background: var(--secondary-color);
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }

        .btn-dark:hover {
            background: var(--accent-color);
            border-color: var(--primary-color);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--text-light);
            box-shadow: 0 4px 15px var(--glow-primary);
            transform: translateY(-2px);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(13, 13, 13, 0.9);
            border: 2px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(13, 13, 13, 1);
            box-shadow: 0 0 15px var(--glow-primary), 0 0 5px var(--glow-gold);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        /* Grid System */
        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-primary {
            color: var(--primary-color);
        }

        .text-muted {
            color: var(--text-muted);
        }

        .w-full {
            width: 100%;
        }

        .flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .items-start {
            align-items: flex-start;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }
        .mb-5 { margin-bottom: 3rem; }

        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }
        .mt-5 { margin-top: 3rem; }

        .py-1 { padding: 0.5rem 0; }
        .py-2 { padding: 1rem 0; }
        .py-3 { padding: 1.5rem 0; }
        .py-4 { padding: 2rem 0; }
        .py-5 { padding: 3rem 0; }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Footer */
        .footer {
            background: linear-gradient(180deg, rgba(20, 16, 16, 0.95) 0%, rgba(13, 13, 13, 0.98) 100%);
            border-top: 1px solid;
            border-image: linear-gradient(90deg, transparent, var(--border-color), var(--border-gold), transparent) 1;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .footer-section a {
            color: var(--text-light);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
        }

        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .footer-logo img {
            width: 32px;
            height: 32px;
            margin-right: 0.5rem;
            border-radius: 6px;
        }

        .footer-logo span {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(26, 26, 26, 0.98);
                flex-direction: column;
                padding: 1rem;
                border-top: 1px solid var(--border-color);
            }

            .nav-links.active {
                display: flex;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .container {
                padding: 0 15px;
            }

            .glass-card {
                padding: 1.5rem;
            }
        }

        /* Status Indicators */
        .status-online {
            color: #22c55e;
        }

        .status-offline {
            color: #ef4444;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .status-indicator.online {
            background-color: #22c55e;
        }

        .status-indicator.offline {
            background-color: #ef4444;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #3b82f6;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ asset('assets/aragon_rsps_icon.png') }}" alt="Aragon RSPS">
                    Aragon RSPS
                </a>
                
                <ul class="nav-links" id="navLinks">
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    <li><a href="{{ route('store.index') }}" class="{{ request()->routeIs('store.*') ? 'active' : '' }}">
                        <i class="fas fa-store"></i> Store
                    </a></li>
                    <li><a href="{{ route('vote.index') }}" class="{{ request()->routeIs('vote.*') ? 'active' : '' }}">
                        <i class="fas fa-vote-yea"></i> Vote
                    </a></li>
                    <li><a href="#" class="">
                        <i class="fas fa-user"></i> My Profile
                    </a></li>
                    <li><a href="#" class="">
                        <i class="fas fa-users"></i> Players
                    </a></li>
                    <li><a href="{{ route('play') }}" class="btn btn-primary" style="margin-left: 1rem;">
                        Play Now
                    </a></li>
                </ul>
                
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('warning') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                {{ session('info') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="{{ asset('assets/aragon_rsps_icon.png') }}" alt="Aragon RSPS">
                        <span>Aragon RSPS</span>
                    </div>
                    <p class="text-muted">The ultimate Old School RuneScape private server experience with custom content and an active community.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="{{ route('vote.index') }}">Vote for Us</a>
                    <a href="{{ route('vote.stats') }}">Vote Statistics</a>
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                </div>
                <div class="footer-section">
                    <h3>Community</h3>
                    <a href="#" target="_blank"><i class="fab fa-discord"></i> Discord</a>
                    <a href="#" target="_blank"><i class="fab fa-youtube"></i> YouTube</a>
                    <a href="#" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <a href="#">Help Center</a>
                    <a href="#">Bug Reports</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Aragon RSPS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>