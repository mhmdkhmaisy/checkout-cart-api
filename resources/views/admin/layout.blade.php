<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RSPS Donation Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dark-bg': '#0f0f0f',
                        'dark-surface': '#1a1a1a',
                        'green-primary': '#00ff88',
                        'green-secondary': '#00cc6a'
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-green {
            background: linear-gradient(135deg, #00ff88, #00cc6a);
            color: #000;
        }
        .glass-effect {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background: rgba(0, 255, 136, 0.1);
            border-left: 4px solid #00ff88;
        }
        .nav-link.active {
            background: rgba(0, 255, 136, 0.2);
            border-left: 4px solid #00ff88;
            color: #00ff88;
        }
    </style>
</head>
<body class="bg-dark-bg text-white min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-dark-surface min-h-screen shadow-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold gradient-green bg-clip-text text-transparent">
                    RSPS Admin
                </h1>
                <p class="text-gray-400 text-sm mt-2">Donation Management</p>
            </div>
            
            <nav class="mt-8">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.products.index') }}" 
                           class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fas fa-box mr-3"></i>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" 
                           class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.logs') }}" 
                           class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg {{ request()->routeIs('admin.orders.logs') ? 'active' : '' }}">
                            <i class="fas fa-list-alt mr-3"></i>
                            Order Logs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.api-docs') }}" 
                           class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg {{ request()->routeIs('admin.api-docs') ? 'active' : '' }}">
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