<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aragon RSPS Donation Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dragon-black': '#0a0a0a',
                        'dragon-surface': '#1a1a1a',
                        'dragon-red': '#d40000',
                        'dragon-red-bright': '#ff0000',
                        'dragon-silver': '#e8e8e8',
                        'dragon-silver-dark': '#c0c0c0',
                        'dragon-border': '#333333'
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-red {
            background: linear-gradient(135deg, #ff0000, #d40000);
            color: #e8e8e8;
        }
        .glass-effect {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid #333333;
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background: rgba(212, 0, 0, 0.1);
            border-left: 4px solid #d40000;
        }
        .nav-link.active {
            background: rgba(212, 0, 0, 0.2);
            border-left: 4px solid #d40000;
            color: #d40000;
        }
        .dragon-logo {
            filter: drop-shadow(0 0 15px #d40000);
            animation: dragonGlow 3s ease-in-out infinite alternate;
            transition: all 0.3s ease;
        }
        .dragon-logo:hover {
            filter: drop-shadow(0 0 25px #ff0000);
            transform: scale(1.05);
        }
        @keyframes dragonGlow {
            from { filter: drop-shadow(0 0 10px #d40000); }
            to { filter: drop-shadow(0 0 20px #ff0000); }
        }
        .dragon-text-glow {
            text-shadow: 0 0 10px #d40000;
        }
    </style>
</head>
<body class="bg-dragon-black text-dragon-silver min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-dragon-surface min-h-screen shadow-lg border-r border-dragon-border">
            <div class="p-6">
                <!-- Aragon Dragon Logo Header -->
                <div class="flex flex-col items-center mb-6">
                    <div class="dragon-logo mb-3">
                        <img src="{{ asset('assets/aragon_logo.png') }}" alt="Aragon Dragon Logo" class="h-16 w-auto object-contain">
                    </div>
                    <div class="text-center">
                        <h1 class="text-xl font-bold dragon-text-glow text-dragon-red">
                            Admin Panel
                        </h1>
                        <p class="text-dragon-silver-dark text-sm">Dragon's Donation System</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-4">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-link flex items-center px-4 py-3 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.products.index') }}" 
                           class="nav-link flex items-center px-4 py-3 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fas fa-box mr-3"></i>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" 
                           class="nav-link flex items-center px-4 py-3 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.logs') }}" 
                           class="nav-link flex items-center px-4 py-3 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.orders.logs') ? 'active' : '' }}">
                            <i class="fas fa-list-alt mr-3"></i>
                            Order Logs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.api-docs') }}" 
                           class="nav-link flex items-center px-4 py-3 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.api-docs') ? 'active' : '' }}">
                            <i class="fas fa-code mr-3"></i>
                            API Documentation
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>