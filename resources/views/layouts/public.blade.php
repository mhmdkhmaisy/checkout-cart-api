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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        :root {
            --primary-color: #d40000;
            --primary-bright: #ff0000;
            --secondary-color: #1a1a1a;
            --accent-color: #0a0a0a;
            --text-light: #e8e8e8;
            --text-dark: #333333;
            --text-muted: #c0c0c0;
            --background-dark: #0a0a0a;
            --background-gradient: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #333333 100%);
            --card-background: rgba(26, 26, 26, 0.8);
            --border-color: #333333;
            --hover-color: rgba(212, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background-gradient);
            color: var(--text-light);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .header {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
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
            color: var(--primary-color);
            text-decoration: none;
            text-shadow: 0 0 10px var(--primary-color);
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
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: var(--hover-color);
            color: var(--primary-color);
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
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background: var(--primary-bright);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: var(--text-light);
        }

        .btn-dark {
            background: var(--secondary-color);
            color: var(--text-light);
        }

        .btn-dark:hover {
            background: var(--accent-color);
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
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(26, 26, 26, 0.9);
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
            background: rgba(26, 26, 26, 0.95);
            border-top: 1px solid var(--border-color);
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
                <a href="{{ route('vote.index') }}" class="logo">
                    <img src="{{ asset('assets/aragon_rsps_icon.png') }}" alt="Aragon RSPS">
                    Aragon RSPS
                </a>
                
                <ul class="nav-links" id="navLinks">
                    <li><a href="{{ route('vote.index') }}" class="{{ request()->routeIs('vote.index') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    <li><a href="{{ route('vote.index') }}" class="{{ request()->routeIs('vote.*') ? 'active' : '' }}">
                        <i class="fas fa-vote-yea"></i> Vote
                    </a></li>
                    <li><a href="{{ route('vote.stats') }}" class="{{ request()->routeIs('vote.stats') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a></li>
                    <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> Admin
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