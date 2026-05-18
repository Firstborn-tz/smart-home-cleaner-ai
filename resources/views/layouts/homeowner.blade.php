<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Smart Home Cleaner AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { transition: all 0.3s ease; width: 250px; }
        .sidebar.collapsed { width: 70px; }
        .sidebar.collapsed .nav-text { display: none; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: true }">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="sidebar fixed left-0 top-0 h-full bg-white dark:bg-gray-800 shadow-xl z-50 overflow-y-auto" :class="{ 'collapsed': !sidebarOpen }">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3" x-show="sidebarOpen">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-home text-white"></i>
                        </div>
                        <div class="nav-text">
                            <h1 class="font-bold text-gray-800 dark:text-white">SmartClean</h1>
                            <p class="text-xs text-gray-500">Customer Panel</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-bars text-gray-500"></i>
                    </button>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="{{ route('homeowner.dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('homeowner.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <i class="fas fa-th-large w-5 text-center"></i>
                    <span class="nav-text font-medium">Dashboard</span>
                </a>
                <a href="{{ route('homeowner.bookings.create') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('homeowner.bookings.create') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <i class="fas fa-plus-circle w-5 text-center"></i>
                    <span class="nav-text font-medium">Book Service</span>
                </a>
                <a href="{{ route('homeowner.profile') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('homeowner.profile') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <i class="fas fa-user-circle w-5 text-center"></i>
                    <span class="nav-text font-medium">Profile</span>
                </a>
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 dark:border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-3 w-full px-4 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        <span class="nav-text font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-[250px] transition-all duration-300" :class="{ 'ml-[70px]': !sidebarOpen }">
            <div class="sticky top-0 z-40 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-700">
                <div class="px-8 py-4">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">@yield('page_title', 'Dashboard')</h2>
                </div>
            </div>
            <div class="p-8">@yield('content')</div>
        </main>
    </div>
    
    <div id="toast-container" class="fixed bottom-4 right-4 z-[9999] space-y-2"></div>
    
    <script>
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = { success: 'bg-green-500', error: 'bg-red-500', warning: 'bg-yellow-500' };
            toast.className = `px-6 py-4 rounded-2xl shadow-2xl text-white flex items-center space-x-3 animate-slide-up ${colors[type]}`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        };
    </script>
    @stack('scripts')
</body>
</html>