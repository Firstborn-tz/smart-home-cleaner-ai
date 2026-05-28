<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth" x-data="cleanerShell()" x-init="init()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cleaner Dashboard') — SmartClean AI</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] },
                    animation: {
                        'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'pulse-ring': 'pulseRing 2.5s infinite',
                    },
                    keyframes: {
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        pulseRing: { '0%': { boxShadow: '0 0 0 0 rgba(34,197,94,0.4)' }, '70%': { boxShadow: '0 0 0 20px rgba(34,197,94,0)' }, '100%': { boxShadow: '0 0 0 0 rgba(34,197,94,0)' } },
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Font Awesome 6.5.1 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Google Fonts - Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Dark Mode Flash Prevention --}}
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        /* ============================================ */
        /* GLOBAL RESET */
        /* ============================================ */
        * { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }

        /* ============================================ */
        /* SIDEBAR */
        /* ============================================ */
        .sidebar {
            width: 260px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            box-shadow: 4px 0 30px rgba(0,0,0,0.04);
        }
        .dark .sidebar { background: #0f172a; border-color: #1e293b; box-shadow: 4px 0 30px rgba(0,0,0,0.4); }
        .sidebar.collapsed { width: 72px; }
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .user-info-text,
        .sidebar.collapsed .sidebar-section-title { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 12px; border-radius: 14px; }
        .sidebar.collapsed .nav-icon { margin: 0 auto; }

        /* Main Content */
        .main-content { margin-left: 260px; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .main-content.expanded { margin-left: 72px; }

        /* Hover Expand on Desktop */
        @media (min-width: 1024px) {
            .sidebar-wrapper:hover .sidebar.collapsed { width: 260px; }
            .sidebar-wrapper:hover .sidebar.collapsed .nav-text,
            .sidebar-wrapper:hover .sidebar.collapsed .user-info-text,
            .sidebar-wrapper:hover .sidebar.collapsed .sidebar-section-title { display: block; }
            .sidebar-wrapper:hover .sidebar.collapsed .nav-link { justify-content: flex-start; padding: 10px 16px; }
            .sidebar-wrapper:hover .sidebar.collapsed .nav-icon { margin: 0; }
        }

        /* Mobile */
        @media (max-width: 1023px) {
            .sidebar-wrapper { position: fixed; left: -280px; transition: left 0.3s ease; width: 280px; z-index: 50; }
            .sidebar-wrapper.mobile-open { left: 0; }
            .sidebar { width: 280px; }
            .main-content { margin-left: 0 !important; }
            .mobile-overlay { display: none; }
            .mobile-overlay.active { display: block; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 49; }
        }

        /* ============================================ */
        /* GLASS MORPHISM */
        /* ============================================ */
        .glass {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
        }
        .dark .glass { background: rgba(15,23,42,0.85); }

        /* ============================================ */
        /* CARD HOVER */
        /* ============================================ */
        .card-hover-lift {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -8px rgba(0,0,0,0.12);
        }
        .dark .card-hover-lift:hover { box-shadow: 0 16px 32px -8px rgba(0,0,0,0.5); }

        /* ============================================ */
        /* TEXT UTILITIES */
        /* ============================================ */
        .text-heading { color: #0f172a; }
        .dark .text-heading { color: #f1f5f9; }
        .text-body { color: #475569; }
        .dark .text-body { color: #cbd5e1; }
        .text-muted { color: #64748b; }
        .dark .text-muted { color: #94a3b8; }

        /* ============================================ */
        /* STAT NUMBER */
        /* ============================================ */
        .stat-number { font-variant-numeric: tabular-nums; letter-spacing: -0.03em; }

        /* ============================================ */
        /* PULSE GREEN (for availability dot) */
        /* ============================================ */
        .pulse-green {
            animation: pulseGreen 2s infinite;
        }
        @keyframes pulseGreen {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.7); }
            50% { box-shadow: 0 0 0 12px rgba(34,197,94,0); }
        }

        /* ============================================ */
        /* SCROLLBAR */
        /* ============================================ */
        .sidebar::-webkit-scrollbar { width: 3px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .dark .sidebar::-webkit-scrollbar-thumb { background: #334155; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* ============================================ */
        /* SCROLLBAR HIDE */
        /* ============================================ */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* ============================================ */
        /* DARK MODE OVERRIDES */
        /* ============================================ */
        .dark body { background-color: #020617; }
        .dark .bg-white { background-color: #1e293b; }
        .dark .bg-gray-50 { background-color: #0f172a; }
        .dark .bg-gray-100 { background-color: #1e293b; }
        .dark .text-gray-800 { color: #f1f5f9; }
        .dark .text-gray-700 { color: #e2e8f0; }
        .dark .text-gray-600 { color: #cbd5e1; }
        .dark .text-gray-500 { color: #94a3b8; }
        .dark .text-gray-400 { color: #64748b; }
        .dark .border-gray-200, .dark .border-gray-100 { border-color: #334155; }
        .dark .border-gray-300 { border-color: #475569; }
        .dark .hover\:bg-gray-50:hover { background-color: #1e293b; }
        .dark .hover\:bg-gray-100:hover { background-color: #334155; }
        .dark .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5); }
        .dark .shadow-xl { box-shadow: 0 20px 25px -5px rgba(0,0,0,0.6); }
        .dark input:not([type="checkbox"]):not([type="radio"]),
        .dark select,
        .dark textarea { background-color: #1e293b; color: #e2e8f0; border-color: #334155; }
        .dark input::placeholder,
        .dark textarea::placeholder { color: #64748b; }
        .dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: #334155; }
        .dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: #1e293b; }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-gray-50 dark:bg-[#020617] text-gray-800 dark:text-gray-200">

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" :class="{ 'active': sidebarMobileOpen }" @click="sidebarMobileOpen = false"></div>

    {{-- ============================================ --}}
    {{-- SIDEBAR --}}
    {{-- ============================================ --}}
    <div class="sidebar-wrapper fixed left-0 top-0 h-full z-50" :class="{ 'mobile-open': sidebarMobileOpen }">
        <aside class="sidebar flex flex-col h-full overflow-y-auto" :class="{ 'collapsed': !sidebarOpen && !isMobile }">
            
            {{-- Brand Header --}}
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between flex-shrink-0">
                <a href="/cleaner/dashboard" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-green-400 to-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-green-500/25">
                        <i class="fas fa-broom text-white text-sm"></i>
                    </div>
                    <div class="nav-text">
                        <h1 class="font-black text-base text-heading leading-tight tracking-tight">SmartClean <span class="text-green-600 dark:text-green-400">AI</span></h1>
                        <p class="text-[10px] text-muted font-medium uppercase tracking-wider">Cleaner Panel</p>
                    </div>
                </a>
                <button @click="toggleSidebar()" class="hidden lg:flex w-8 h-8 items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all flex-shrink-0" title="Toggle Sidebar">
                    <i class="fas fa-outdent text-muted text-sm"></i>
                </button>
                <button @click="sidebarMobileOpen = false" class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all flex-shrink-0">
                    <i class="fas fa-times text-muted text-lg"></i>
                </button>
            </div>

            {{-- User Info --}}
            @php $cleaner = Auth::user()->cleaner ?? null; @endphp
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->full_name ?? 'C') . '&background=22c55e&color=fff&size=40&bold=true' }}" 
                             class="w-10 h-10 rounded-xl ring-2 ring-green-100 dark:ring-green-500/20">
                        @if($cleaner && $cleaner->availability_status === 'online')
                        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white dark:border-gray-900 pulse-green"></span>
                        @endif
                    </div>
                    <div class="user-info-text min-w-0">
                        <p class="font-bold text-sm text-heading truncate">{{ Auth::user()->full_name ?? 'Cleaner' }}</p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold 
                            {{ ($cleaner->availability_status ?? 'offline') === 'online' ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20' : 'bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-500/20' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ ($cleaner->availability_status ?? 'offline') === 'online' ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ ($cleaner->availability_status ?? 'offline') === 'online' ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto scrollbar-hide">
                @php
                    $currentRouteName = request()->route()->getName();
                    $navGroups = [
                        'Main' => [
                            ['route' => 'cleaner.dashboard', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
                            ['route' => 'cleaner.bookings', 'icon' => 'fa-calendar-check', 'label' => 'My Jobs'],
                            ['route' => 'cleaner.registration-status', 'icon' => 'fa-clipboard-list', 'label' => 'Reg. Status'],
                        ],
                        'Earnings' => [
                            ['route' => 'cleaner.earnings', 'icon' => 'fa-chart-line', 'label' => 'Earnings'],
                            ['route' => 'cleaner.services', 'icon' => 'fa-tools', 'label' => 'My Services'],
                        ],
                        'Account' => [
                            ['route' => 'cleaner.business-profile', 'icon' => 'fa-store', 'label' => 'Business Profile'],
                            ['route' => 'cleaner.profile', 'icon' => 'fa-user-circle', 'label' => 'Profile'],
                        ],
                    ];
                @endphp

                @foreach($navGroups as $groupName => $items)
                <div class="sidebar-section-title px-2 pt-4 pb-1">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $groupName }}</p>
                </div>
                @foreach($items as $item)
                @php 
                    $isActive = $currentRouteName === ($item['route'] ?? '') || request()->routeIs(($item['route'] ?? '') . '*'); 
                @endphp
                <a href="{{ isset($item['route']) && Route::has($item['route']) ? route($item['route']) : '#' }}" 
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                          {{ $isActive ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/25' : 'text-body hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <i class="fas {{ $item['icon'] }} w-5 text-center nav-icon flex-shrink-0"></i>
                    <span class="nav-text flex-1">{{ $item['label'] }}</span>
                </a>
                @endforeach
                @endforeach
            </nav>

            {{-- Sidebar Footer --}}
            <div class="flex-shrink-0 p-3 border-t border-gray-100 dark:border-gray-800 space-y-1">
                {{-- Theme Toggle --}}
                <button @click="toggleTheme()" 
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 bg-gray-50 dark:bg-gray-800 text-body hover:bg-gray-100 dark:hover:bg-gray-700">
                    <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                        <i class="fas text-lg transition-all duration-500" :class="darkMode ? 'fa-sun text-yellow-400' : 'fa-moon text-gray-500'"></i>
                    </div>
                    <span class="nav-text" x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                </button>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" 
                            class="flex items-center gap-3 w-full px-4 py-3 rounded-xl font-medium text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </div>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </div>
        </aside>
    </div>

    {{-- ============================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================================ --}}
    <div class="main-content" :class="{ 'expanded': !sidebarOpen && !isMobile }">
        
        {{-- Top Bar --}}
        <header class="glass sticky top-0 z-30 border-b border-gray-200/50 dark:border-gray-800/50">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                {{-- Left --}}
                <div class="flex items-center gap-4">
                    <button @click="sidebarMobileOpen = !sidebarMobileOpen" 
                            class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-bars text-body text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg sm:text-xl font-black text-heading tracking-tight">@yield('page_title', 'Dashboard')</h2>
                        @hasSection('page_subtitle')
                        <p class="text-xs text-muted hidden sm:block">@yield('page_subtitle')</p>
                        @endif
                    </div>
                </div>

                {{-- Right --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Mobile Theme Toggle --}}
                    <button @click="toggleTheme()" 
                            class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                            title="Toggle Theme">
                        <i class="fas text-lg transition-all duration-500" :class="darkMode ? 'fa-sun text-yellow-400' : 'fa-moon text-gray-500'"></i>
                    </button>

                    {{-- Notification Bell --}}
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen" 
                                class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                            <i class="fas fa-bell text-body text-lg"></i>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold shadow-lg">3</span>
                        </button>
                    </div>

                    {{-- User Avatar --}}
                    <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->full_name ?? 'C') . '&background=22c55e&color=fff&size=36&bold=true' }}" 
                         class="w-9 h-9 rounded-xl ring-2 ring-gray-100 dark:ring-gray-700 flex-shrink-0 hidden sm:block">
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="p-4 sm:p-6 lg:p-8 animate-fade-in">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-200 dark:border-gray-800 px-6 py-4 text-center">
            <p class="text-xs text-muted">&copy; {{ date('Y') }} SmartClean AI. All rights reserved.</p>
        </footer>
    </div>

    {{-- ============================================ --}}
    {{-- TOAST SYSTEM --}}
    {{-- ============================================ --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-[9999] space-y-2 flex flex-col items-end"></div>

    {{-- ============================================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================================ --}}
    <script>
        function cleanerShell() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true',
                sidebarOpen: localStorage.getItem('sidebarCollapsed') !== 'true',
                sidebarMobileOpen: false,
                isMobile: window.innerWidth < 1024,
                
                init() {
                    if (this.darkMode) document.documentElement.classList.add('dark');
                    if (this.isMobile) this.sidebarOpen = true;
                    
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 1024;
                        if (!this.isMobile) this.sidebarMobileOpen = false;
                    });
                },
                
                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                    localStorage.setItem('sidebarCollapsed', !this.sidebarOpen);
                },
                
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    document.documentElement.classList.toggle('dark', this.darkMode);
                }
            };
        }

        // Global Toast
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = { 
                success: 'bg-gradient-to-r from-green-500 to-emerald-600', 
                error: 'bg-gradient-to-r from-red-500 to-rose-600', 
                warning: 'bg-gradient-to-r from-yellow-500 to-amber-600', 
                info: 'bg-gradient-to-r from-blue-500 to-blue-600' 
            };
            const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
            
            toast.className = `${colors[type] || colors.success} px-5 py-3.5 rounded-2xl shadow-2xl text-white flex items-center gap-3 animate-slide-up text-sm font-semibold`;
            toast.innerHTML = `<i class="fas ${icons[type] || icons.success} text-base"></i><span>${message}</span>`;
            container.appendChild(toast);
            
            setTimeout(() => { 
                toast.style.opacity = '0'; 
                toast.style.transform = 'translateX(100px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300); 
            }, 3000);
        };
    </script>

    @stack('scripts')
</body>
</html>
