<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="appShell()" x-init="init()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SmartClean AI</title>

    <!-- ANTI-FLASH: Apply dark mode immediately before page renders -->
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' }
                    }
                }
            }
        }
    </script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        
        /* Sidebar */
        .sidebar { width: 250px; transition: width 0.3s ease; }
        .sidebar.collapsed { width: 60px; }
        .sidebar.collapsed .nav-label, .sidebar.collapsed .brand-text, .sidebar.collapsed .user-details { display: none; }
        .sidebar.collapsed .sidebar-bottom-text { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 10px; }
        
        /* Main Content */
        .main-content { margin-left: 250px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 60px; }
        
        /* Mobile */
        @media (max-width: 1023px) {
            .sidebar { position: fixed; left: -280px; top: 0; width: 280px; height: 100dvh; z-index: 50; transition: left 0.3s ease; }
            .sidebar.mobile-open { left: 0; }
            .main-content { margin-left: 0 !important; }
            .mobile-overlay { display: none; }
            .mobile-overlay.active { display: block; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 45; }
            .bottom-nav { display: flex; }
            .page-content { padding-bottom: 80px; }
        }
        
        @media (min-width: 1024px) {
            .bottom-nav { display: none; }
            .page-content { padding-bottom: 1.5rem; }
            .sidebar { position: fixed; left: 0; top: 0; height: 100vh; z-index: 40; overflow-y: auto; }
        }
        
        /* Bottom Nav */
        .bottom-nav {
            display: none; position: fixed; bottom: 0; left: 0; right: 0; 
            background: white; border-top: 1px solid #e5e7eb; z-index: 45;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .dark .bottom-nav { background: #1f2937; border-color: #374151; }
        .bottom-nav a, .bottom-nav button { 
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 6px 2px; color: #6b7280; font-size: 10px; min-height: 56px; text-decoration: none;
            background: none; border: none; cursor: pointer; font-family: 'Inter', sans-serif;
        }
        .bottom-nav a.active, .bottom-nav button.active { color: #3b82f6; }
        .bottom-nav i { font-size: 20px; margin-bottom: 2px; }
        
        /* Glass */
        .glass { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .dark .glass { background: rgba(31,41,55,0.85); }
        
        /* Animations */
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        .animate-slide-up { animation: slideUp 0.3s ease-out; }
        .animate-fade-in { animation: fadeIn 0.2s ease-out; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width:4px; } ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:10px; }
        
        /* Dark mode */
        .dark .bg-white { background-color: #1f2937; }
        .dark .text-gray-800 { color: #f9fafb; }
        .dark .text-gray-700 { color: #e5e7eb; }
        .dark .text-gray-600 { color: #d1d5db; }
        .dark .text-gray-500 { color: #9ca3af; }
        .dark .border-gray-100, .dark .border-gray-200 { border-color: #374151; }
        .dark .bg-gray-50 { background-color: #111827; }
        .dark .hover\:bg-gray-100:hover { background-color: #374151; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" :class="{ 'active': sidebarMobileOpen }" @click="sidebarMobileOpen = false"></div>

    <!-- ============================================ -->
    <!-- SIDEBAR -->
    <!-- ============================================ -->
    <aside class="sidebar bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-xl overflow-y-auto"
           :class="{ 'collapsed': !sidebarOpen && !isMobile, 'mobile-open': sidebarMobileOpen }">
        
        <!-- Brand + Close button (mobile) -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-broom text-white text-sm"></i>
                </div>
                <div class="brand-text">
                    <h1 class="font-extrabold text-base text-gray-800 dark:text-white">SmartClean AI</h1>
                </div>
            </div>
            <!-- Mobile close button -->
            <button @click="sidebarMobileOpen = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg lg:hidden">
                <i class="fas fa-times text-gray-500 dark:text-gray-400 text-lg"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name ?? 'U') }}&background=3b82f6&color=fff&size=36" class="w-9 h-9 rounded-lg flex-shrink-0">
                <div class="user-details min-w-0">
                    <p class="font-semibold text-sm truncate text-gray-800 dark:text-white">{{ Auth::user()->full_name ?? 'User' }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-3 space-y-0.5">
            @php
                $userType = Auth::user()->user_type ?? 'homeowner';
                if(in_array($userType, ['admin', 'super_admin'])) {
                    $navItems = [
                        ['route' => 'admin.dashboard', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'admin.cleaner-requests', 'icon' => 'fa-user-clock', 'label' => 'Reg. Requests'],
                        ['route' => 'admin.cleaners', 'icon' => 'fa-users', 'label' => 'All Cleaners'],
                        ['route' => 'admin.commissions', 'icon' => 'fa-money-bill-wave', 'label' => 'Commissions'],
                        ['route' => 'admin.cities', 'icon' => 'fa-city', 'label' => 'Cities'],
                        ['route' => 'admin.ai-status', 'icon' => 'fa-brain', 'label' => 'AI Status'],
                        ['route' => 'admin.services', 'icon' => 'fa-tools', 'label' => 'Services'],
                    ];
                } elseif($userType === 'cleaner') {
                    $navItems = [
                        ['route' => 'cleaner.dashboard', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'cleaner.bookings', 'icon' => 'fa-calendar-check', 'label' => 'My Jobs'],
                        ['route' => 'cleaner.services', 'icon' => 'fa-tools', 'label' => 'My Services'],
                        ['route' => 'cleaner.earnings', 'icon' => 'fa-chart-line', 'label' => 'Earnings'],
                        ['route' => 'cleaner.business-profile', 'icon' => 'fa-store', 'label' => 'Business Profile'],
                        ['route' => 'cleaner.profile', 'icon' => 'fa-user-circle', 'label' => 'Profile'],
                        ['route' => 'cleaner.registration-status', 'icon' => 'fa-clipboard-list', 'label' => 'Registration Status'],
                    ];
                } else {
                    $navItems = [
                        ['route' => 'homeowner.dashboard', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'homeowner.bookings.create', 'icon' => 'fa-plus-circle', 'label' => 'Book Service'],
                        ['route' => 'homeowner.profile', 'icon' => 'fa-user-circle', 'label' => 'Profile'],
                    ];
                }
            @endphp
            @foreach($navItems as $item)
            <a href="{{ isset($item['route']) && Route::has($item['route']) ? route($item['route']) : '#' }}" 
               class="nav-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm transition-all
                      {{ request()->routeIs($item['route'] ?? '') ? 'bg-blue-500 text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <i class="fas {{ $item['icon'] }} w-5 text-center flex-shrink-0"></i>
                <span class="nav-label font-medium">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>

        <!-- Sidebar Bottom Controls - ALWAYS VISIBLE -->
        <div class="border-t border-gray-100 dark:border-gray-700 p-3 space-y-1 mt-auto">
            <!-- Collapse toggle (desktop only) -->
            <button @click="toggleSidebar()" class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm hidden lg:flex">
                <i class="fas fa-bars w-5 text-center flex-shrink-0"></i>
                <span class="sidebar-bottom-text">Collapse Menu</span>
            </button>
            
            <!-- Theme Toggle -->
            <button @click="toggleTheme()" class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                <i class="fas w-5 text-center flex-shrink-0" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                <span class="sidebar-bottom-text" x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
            </button>
            
            <!-- Language Toggle -->
            <button @click="toggleLanguage()" class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                <i class="fas fa-language w-5 text-center flex-shrink-0"></i>
                <span class="sidebar-bottom-text" x-text="currentLang === 'en' ? 'Kiswahili' : 'English'"></span>
            </button>
            
            <!-- Logout -->
            <form method="POST" action="/logout" class="w-full">
                @csrf
                <button type="submit" class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm">
                    <i class="fas fa-sign-out-alt w-5 text-center flex-shrink-0"></i>
                    <span class="sidebar-bottom-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ============================================ -->
    <!-- MAIN CONTENT -->
    <!-- ============================================ -->
    <div class="main-content" :class="{ 'expanded': !sidebarOpen && !isMobile }">
        
        <!-- Top Bar -->
        <header class="glass sticky top-0 z-30 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-3">
                    <!-- Hamburger - ALWAYS VISIBLE -->
                    <button @click="sidebarMobileOpen = !sidebarMobileOpen" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas fa-bars text-gray-600 dark:text-gray-300 text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">@yield('page_title', 'Dashboard')</h2>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Notification Bell -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open; if(open) fetchNotifications()" class="relative p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <i class="fas fa-bell text-gray-500 dark:text-gray-400 text-lg"></i>
                            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold" x-show="unreadCount > 0" x-text="unreadCount"></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 max-w-[90vw] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 max-h-80 overflow-y-auto">
                            <div class="p-3 border-b border-gray-100 dark:border-gray-700"><h3 class="font-bold text-sm">Notifications</h3></div>
                            <div x-show="notifications.length === 0" class="p-6 text-center text-gray-400 text-sm">No notifications</div>
                            <template x-for="n in notifications" :key="n.id">
                                <div class="p-3 border-b border-gray-50 dark:border-gray-700 text-sm" :class="{ 'bg-blue-50 dark:bg-blue-900/20': !n.read }">
                                    <p class="font-medium" x-text="n.title"></p><p class="text-xs text-gray-500" x-text="n.body"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name ?? 'U') }}&background=3b82f6&color=fff&size=30" class="w-7 h-7 rounded-lg flex-shrink-0">
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content p-4 md:p-6">
            @yield('content')
        </main>
    </div>

    <!-- ============================================ -->
    <!-- BOTTOM NAVIGATION (Mobile Only) -->
    <!-- ============================================ -->
    <!-- BOTTOM NAVIGATION (Mobile Only) -->
<div class="bottom-nav">
    @php
        $userType = Auth::user()->user_type ?? 'homeowner';
        if(in_array($userType, ['admin', 'super_admin'])) {
            $bottomItems = [
                ['route' => 'admin.dashboard', 'icon' => 'fa-th-large', 'label' => 'Home'],
                ['route' => 'admin.cleaner-requests', 'icon' => 'fa-user-clock', 'label' => 'Requests'],
                ['route' => 'admin.cleaners', 'icon' => 'fa-users', 'label' => 'Cleaners'],
                ['route' => 'admin.commissions', 'icon' => 'fa-money-bill-wave', 'label' => 'Money'],
            ];
        } elseif($userType === 'cleaner') {
            $bottomItems = [
                ['route' => 'cleaner.dashboard', 'icon' => 'fa-th-large', 'label' => 'Home'],
                ['route' => 'cleaner.bookings', 'icon' => 'fa-calendar-check', 'label' => 'Jobs'],
                ['route' => 'cleaner.earnings', 'icon' => 'fa-chart-line', 'label' => 'Earnings'],
                ['route' => 'cleaner.profile', 'icon' => 'fa-user-circle', 'label' => 'Profile'],
            ];
        } else {
            $bottomItems = [
                ['route' => 'homeowner.dashboard', 'icon' => 'fa-th-large', 'label' => 'Home'],
                ['route' => 'homeowner.bookings.create', 'icon' => 'fa-plus-circle', 'label' => 'Book'],
                ['route' => 'homeowner.profile', 'icon' => 'fa-user-circle', 'label' => 'Profile'],
            ];
        }
    @endphp
    
    @foreach($bottomItems as $item)
    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}" 
       class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }}"></i>
        <span>{{ $item['label'] }}</span>
    </a>
    @endforeach
</div>
        
        @foreach($bottomItems as $item)
        <a href="{{ isset($item['route']) && Route::has($item['route']) ? route($item['route']) : '#' }}" 
           class="{{ request()->routeIs($item['route'] ?? '') ? 'active' : '' }}">
            <i class="fas {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach
        
        <!-- More menu button -->
        <button onclick="document.getElementById('sidebarMobileMore').classList.toggle('hidden')" class="relative">
            <i class="fas fa-ellipsis-h"></i>
            <span>More</span>
        </button>
    </div>

    <!-- Mobile More Menu Popup -->
    <div id="sidebarMobileMore" class="hidden fixed bottom-16 right-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-2 z-50 min-w-[160px]">
        <button onclick="document.getElementById('sidebarMobileMore').classList.add('hidden'); appShell().toggleTheme()" class="flex items-center space-x-3 w-full px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
            <i class="fas fa-moon w-5 text-center"></i><span>Dark/Light Mode</span>
        </button>
        <button onclick="document.getElementById('sidebarMobileMore').classList.add('hidden'); appShell().toggleLanguage()" class="flex items-center space-x-3 w-full px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
            <i class="fas fa-language w-5 text-center"></i><span>Kiswahili / English</span>
        </button>
        <form method="POST" action="/logout" class="w-full">
            @csrf
            <button type="submit" class="flex items-center space-x-3 w-full px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm">
                <i class="fas fa-sign-out-alt w-5 text-center"></i><span>Logout</span>
            </button>
        </form>
    </div>

    <!-- Toast -->
    <div id="toast-container" class="fixed bottom-20 lg:bottom-4 right-4 z-[9999] space-y-2"></div>

    <script>
        // Make appShell accessible globally for the More menu
        let appShellInstance;
        
        function appShell() {
            const instance = {
                darkMode: localStorage.getItem('darkMode') === 'true',
                sidebarOpen: window.innerWidth >= 1024,
                sidebarMobileOpen: false,
                isMobile: window.innerWidth < 1024,
                currentLang: localStorage.getItem('lang') || 'en',
                unreadCount: 0,
                notifications: [],
                
                init() {
                    appShellInstance = this;
                    if (this.darkMode) document.documentElement.classList.add('dark');
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 1024;
                        if (!this.isMobile) this.sidebarMobileOpen = false;
                    });
                    this.fetchNotifications();
                    setInterval(() => this.fetchNotifications(), 30000);
                },
                
                toggleSidebar() {
                    if (this.isMobile) {
                        this.sidebarMobileOpen = !this.sidebarMobileOpen;
                    } else {
                        this.sidebarOpen = !this.sidebarOpen;
                        localStorage.setItem('sidebarOpen', this.sidebarOpen);
                    }
                },
                
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    document.documentElement.classList.toggle('dark', this.darkMode);
                },
                
                toggleLanguage() {
                    this.currentLang = this.currentLang === 'en' ? 'sw' : 'en';
                    localStorage.setItem('lang', this.currentLang);
                    window.location.reload();
                },
                
                async fetchNotifications() {
                    try {
                        const res = await fetch('/api/notifications');
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success) {
                                this.notifications = data.notifications || [];
                                this.unreadCount = this.notifications.filter(n => !n.read).length;
                            }
                        }
                    } catch (e) {}
                }
            };
            return instance;
        }

        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = { success: 'bg-green-500', error: 'bg-red-500', warning: 'bg-yellow-500', info: 'bg-blue-500' };
            const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
            toast.className = `${colors[type] || colors.success} px-4 py-3 rounded-xl shadow-xl text-white flex items-center space-x-2 animate-slide-up text-sm`;
            toast.innerHTML = `<i class="fas ${icons[type] || icons.success} text-sm"></i><span class="font-medium">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 2500);
        };
    </script>

    @stack('scripts')
</body>
</html>
