<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Smart Home Cleaner AI</title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    animation: {
                        'slide-in': 'slideIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'fade-in': 'fadeIn 0.2s ease-out',
                        'pulse-green': 'pulseGreen 2s infinite',
                        'count-up': 'countUp 0.5s ease-out',
                    },
                    keyframes: {
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        pulseGreen: {
                            '0%': { boxShadow: '0 0 0 0 rgba(34, 197, 94, 0.7)' },
                            '70%': { boxShadow: '0 0 0 10px rgba(34, 197, 94, 0)' },
                            '100%': { boxShadow: '0 0 0 0 rgba(34, 197, 94, 0)' },
                        },
                        countUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 280px;
        }
        .sidebar.collapsed {
            width: 80px;
        }
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .user-info {
            display: none;
        }
        .sidebar.collapsed .nav-icon {
            margin: 0 auto;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass-effect {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(75, 85, 99, 0.3);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .toast {
            animation: slideInRight 0.3s ease-out, fadeOut 0.3s ease-in 2.7s forwards;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900" x-data="{ 
    sidebarOpen: true, 
    darkMode: localStorage.getItem('darkMode') === 'true',
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        document.documentElement.classList.toggle('dark', this.darkMode);
    }
}" x-init="document.documentElement.classList.toggle('dark', darkMode)">
    
    <div class="flex h-full">
        <!-- Left Sidebar -->
        <aside class="sidebar glass-effect fixed left-0 top-0 h-full z-50 overflow-y-auto"
               :class="{ 'collapsed': !sidebarOpen }">
            <div class="p-6">
                <!-- Logo -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3" x-show="sidebarOpen">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-broom text-white text-lg"></i>
                        </div>
                        <div class="nav-text">
                            <h1 class="text-lg font-bold text-gray-800 dark:text-white">SmartClean AI</h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Admin Panel</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-bars text-gray-600 dark:text-gray-300"></i>
                    </button>
                </div>
                
                <!-- User Info -->
                <div class="user-info mb-8 p-4 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-2xl">
                    <div class="flex items-center space-x-3">
                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->full_name) . '&background=3b82f6&color=fff' }}" 
                             class="w-12 h-12 rounded-xl border-2 border-white shadow-lg">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ Auth::user()->full_name }}</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ Auth::user()->user_type }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 
                              {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-th-large w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Dashboard</span>
                    </a>
                    
                    <a href="{{ route('admin.cleaners') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 
                              {{ request()->routeIs('admin.cleaners*') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-users w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Cleaners</span>
                        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full nav-text">
                            {{ $stats['online_cleaners'] ?? 0 }} online
                        </span>
                    </a>
                    
                    <a href="{{ route('admin.cleaners.monitor') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 
                              {{ request()->routeIs('admin.cleaners.monitor') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-map-marker-alt w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Live Monitor</span>
                    </a>
                    
                    <a href="{{ route('admin.cities.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.cities*') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-city w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Cities</span>
                    </a>
                    
                    <a href="{{ route('admin.commissions') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.commissions*') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-money-bill-wave w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Commissions</span>
                    </a>
                    
                    <a href="{{ route('admin.ai.performance') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.ai*') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-brain w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">AI Performance</span>
                    </a>
                    
                    <a href="{{ route('admin.reports') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.reports*') ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-chart-bar w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Reports</span>
                    </a>
                </nav>
            </div>
            
            <!-- Sidebar Footer -->
            <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-gray-200 dark:border-gray-700">
                <button @click="toggleDarkMode()" 
                        class="flex items-center space-x-3 w-full px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-moon w-5 text-center nav-icon"></i>
                    <span class="nav-text font-medium">Dark Mode</span>
                </button>
                
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" 
                            class="flex items-center space-x-3 w-full px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i class="fas fa-sign-out-alt w-5 text-center nav-icon"></i>
                        <span class="nav-text font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-[280px] transition-all duration-300" :class="{ 'ml-[80px]': !sidebarOpen }">
            <!-- Top Bar -->
            <div class="sticky top-0 z-40 glass-effect border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-8 py-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">@yield('page_title', 'Dashboard')</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">@yield('page_subtitle', 'Overview')</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative">
                            <input type="text" placeholder="Search..." 
                                   class="w-64 px-4 py-2 pl-10 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <!-- Notifications -->
                        <button class="relative p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-bell text-gray-600 dark:text-gray-300 text-xl"></i>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse">3</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="p-8 animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[9999] space-y-2"></div>
    
    <script>
        // Toast notification system
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            toast.className = `toast px-6 py-4 rounded-2xl shadow-2xl text-white flex items-center space-x-3 ${colors[type] || colors.success}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
                <span class="font-medium">${message}</span>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        };
    </script>
    
    @stack('scripts')
</body>
</html>