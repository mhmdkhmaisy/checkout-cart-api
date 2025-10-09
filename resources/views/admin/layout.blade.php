<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aragon RSPS Admin Panel')</title>
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
                        <p class="text-dragon-silver-dark text-sm">Dragon's Management System</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-4">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-link flex items-center px-4 py-3 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Orders Section -->
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-dragon-red uppercase tracking-wider">Payment System</h3>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.orders.index') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') ? 'active' : '' }}">
                                <i class="fas fa-shopping-cart mr-3"></i>
                                All Orders
                            </a>

                            <a href="{{ route('admin.products.index') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.products.show') ? 'active' : '' }}">
                                <i class="fas fa-box mr-3"></i>
                                All Products
                            </a>

                            <a href="{{ route('admin.orders.logs') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.orders.logs') ? 'active' : '' }}">
                                <i class="fas fa-list-alt mr-3"></i>
                                Payment Logs
                            </a>
                        </div>
                    </div>

                    <!-- Vote Management Section -->
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-dragon-red uppercase tracking-wider">Vote System</h3>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.vote.index') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.vote.index') ? 'active' : '' }}">
                                <i class="fas fa-vote-yea mr-3"></i>
                                Vote Sites
                            </a>
                            <a href="{{ route('admin.vote.votes') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.vote.votes') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i>
                                Vote History
                            </a>
                            <a href="{{ route('admin.vote.stats') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.vote.stats') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar mr-3"></i>
                                Vote Statistics
                            </a>
                            <a href="{{ route('vote.index') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg" target="_blank">
                                <i class="fas fa-external-link-alt mr-3"></i>
                                Public Vote Page
                            </a>
                        </div>
                    </div>
                    <!-- API Documentation -->
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-dragon-red uppercase tracking-wider">API Documentation</h3>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.api-docs') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.api-docs') ? 'active' : '' }}">
                                <div class="p-2 bg-dragon-red rounded-lg mr-4 group-hover:bg-dragon-red-bright transition-colors">
                                    <svg class="w-5 h-5 text-dragon-silver" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                </div>
                                Store API Docs
                            </a>
                        </div>
                    </div>
                    <!-- Client Managment Documentation -->
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-dragon-red uppercase tracking-wider">Client Managment</h3>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.clients.index') }}" 
                               class="nav-link flex items-center px-4 py-2 text-dragon-silver-dark rounded-lg {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                                <i class="fas fa-download mr-3"></i>
                               Client Management
                            </a>
                        </div>
                    </div>
                </ul>
            </nav>

            <!-- User Info -->
            <div class="border-t border-dragon-border p-4 mt-8">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-dragon-red rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-dragon-silver text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-dragon-silver">Admin User</div>
                        <div class="text-xs text-dragon-silver-dark">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-dragon-surface border-b border-dragon-border px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-dragon-red dragon-text-glow">
                        @yield('header', 'Admin Dashboard')
                    </h1>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('vote.index') }}" 
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            View Vote Page
                        </a>
                        <span class="text-sm text-dragon-silver-dark">{{ now()->format('M j, Y g:i A') }}</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-600 text-green-100 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-600 text-red-100 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>