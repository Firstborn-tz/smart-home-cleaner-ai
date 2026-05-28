<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SmartClean AI — Intelligent Home Cleaning Services in Tanzania</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Font Awesome 6.5.1 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Google Fonts - Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- Landing Page Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    
    {{-- Dark Mode Initializer --}}
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-white antialiased">

    {{-- ============================================ --}}
    {{-- NAVIGATION --}}
    {{-- ============================================ --}}
    <nav class="fixed top-0 inset-x-0 z-50 glass shadow-sm" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-3 group shrink-0">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/25 group-hover:shadow-blue-500/40 transition-all duration-300 group-hover:scale-105">
                        <i class="fas fa-broom text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-black tracking-tight text-heading">
                        Smart<span class="text-blue-600 dark:text-blue-400">Clean</span> <span class="text-gray-400 font-light">AI</span>
                    </span>
                </a>
                
                {{-- Desktop Links --}}
                <div class="hidden lg:flex items-center gap-1">
                    <a href="#hero" class="px-4 py-2 text-sm font-medium text-body hover:text-blue-600 dark:hover:text-blue-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all duration-300">Home</a>
                    <a href="#how-it-works" class="px-4 py-2 text-sm font-medium text-body hover:text-blue-600 dark:hover:text-blue-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all duration-300">How It Works</a>
                    <a href="#services" class="px-4 py-2 text-sm font-medium text-body hover:text-blue-600 dark:hover:text-blue-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all duration-300">Services</a>
                    <a href="#why-us" class="px-4 py-2 text-sm font-medium text-body hover:text-blue-600 dark:hover:text-blue-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all duration-300">Why Us</a>
                </div>
                
                {{-- Desktop Actions --}}
                <div class="hidden lg:flex items-center gap-3">
                    <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300" title="Toggle theme">
                        <i id="themeIcon" class="fas fa-moon text-gray-600 dark:text-yellow-400 text-lg transition-all duration-300"></i>
                    </button>
                    <a href="/login" class="px-5 py-2.5 text-sm font-semibold text-body hover:text-blue-600 dark:hover:text-blue-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all duration-300">
                        Sign In
                    </a>
                    <a href="/register" class="px-6 py-2.5 btn-primary rounded-xl font-semibold text-sm">
                        Get Started <i class="fas fa-arrow-right ml-1.5 text-xs"></i>
                    </a>
                </div>
                
                {{-- Mobile Toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 transition-all duration-300">
                    <i class="fas fa-bars text-gray-600 dark:text-gray-300 text-xl" x-show="!mobileOpen"></i>
                    <i class="fas fa-times text-gray-600 dark:text-gray-300 text-xl" x-show="mobileOpen"></i>
                </button>
            </div>
        </div>
        
        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 -translate-y-2" 
             x-transition:enter-end="opacity-100 translate-y-0" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-2" 
             class="lg:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 shadow-2xl" 
             style="display: none;">
            <div class="px-4 py-5 space-y-1">
                <a href="#hero" @click="mobileOpen = false" class="block px-4 py-3 text-body font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all">Home</a>
                <a href="#how-it-works" @click="mobileOpen = false" class="block px-4 py-3 text-body font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all">How It Works</a>
                <a href="#services" @click="mobileOpen = false" class="block px-4 py-3 text-body font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all">Services</a>
                <a href="#why-us" @click="mobileOpen = false" class="block px-4 py-3 text-body font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all">Why Us</a>
                <hr class="my-3 border-gray-200 dark:border-gray-700">
                <a href="/login" class="block px-4 py-3 text-body font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-all">Sign In</a>
                <a href="/register" class="block text-center mt-2 px-4 py-3 btn-primary rounded-xl font-bold">
                    Get Started <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-sm text-muted">Dark Mode</span>
                    <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700 transition-all">
                        <i id="themeIconMobile" class="fas fa-moon text-gray-600 dark:text-yellow-400"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{-- ============================================ --}}
    {{-- HERO SECTION --}}
    {{-- ============================================ --}}
    <section id="hero" class="hero-gradient pt-28 lg:pt-36 pb-16 lg:pb-24 px-4 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                
                {{-- Left Content --}}
                <div class="space-y-8">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-full text-sm font-semibold border border-blue-200 dark:border-blue-500/20 animate-slide-up">
                        <span class="relative flex h-2.5 w-2.5 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        AI-Powered Cleaning Platform — Now in 20+ Cities
                    </div>
                    
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-black text-heading leading-[1.05] tracking-tight animate-slide-up-delay-1">
                        Your home,
                        <br>
                        <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent gradient-animate">
                            brilliantly clean
                        </span>
                    </h1>
                    
                    <p class="text-lg lg:text-xl text-body leading-relaxed max-w-xl animate-slide-up-delay-2">
                        SmartClean AI uses machine learning to match you with Tanzania's top-rated, verified cleaners. Book in seconds — relax while we handle the rest.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-3 animate-slide-up-delay-3">
                        <a href="/register/homeowner" class="group inline-flex items-center justify-center px-8 py-4 btn-primary rounded-2xl font-bold text-base">
                            <i class="fas fa-home mr-2"></i> Book a Cleaner
                            <i class="fas fa-arrow-right ml-2 text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </a>
                        <a href="/register/cleaner" class="inline-flex items-center justify-center px-8 py-4 btn-outline rounded-2xl font-bold text-base">
                            <i class="fas fa-broom mr-2"></i> Become a Cleaner
                        </a>
                    </div>
                    
                    {{-- Trust Bar --}}
                    <div class="flex flex-wrap items-center gap-6 pt-8 border-t border-gray-200 dark:border-gray-700/50 animate-slide-up-delay-3">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <div class="w-9 h-9 rounded-full bg-linear-to-br from-blue-400 to-blue-600 border-[3px] border-white dark:border-gray-900"></div>
                                <div class="w-9 h-9 rounded-full bg-linear-to-br from-green-400 to-green-600 border-[3px] border-white dark:border-gray-900"></div>
                                <div class="w-9 h-9 rounded-full bg-linear-to-br from-purple-400 to-purple-600 border-[3px] border-white dark:border-gray-900"></div>
                                <div class="w-9 h-9 rounded-full bg-linear-to-br from-pink-400 to-pink-600 border-[3px] border-white dark:border-gray-900 flex items-center justify-center text-white text-[10px] font-bold">500+</div>
                            </div>
                            <span class="text-sm font-semibold text-body">Trusted by <span class="text-heading">500+</span> Cleaners</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-yellow-500">
                            <i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star text-sm"></i><i class="fas fa-star-half-alt text-sm"></i>
                            <span class="text-sm font-semibold text-body ml-1"><span class="text-heading">4.8</span> Rating</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-body">
                            <i class="fas fa-map-marker-alt text-red-400"></i>
                            <span class="text-sm font-semibold">20+ Cities</span>
                        </div>
                    </div>
                </div>
                
                {{-- Right Content — Hero Card --}}
                <div class="hidden lg:flex justify-center animate-float">
                    <div class="relative w-full max-w-md">
                        <div class="relative z-10 bg-linear-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl p-6 shadow-2xl shadow-gray-900/30 border border-white/10">
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-3xl opacity-20 blur-xl -z-10"></div>
                            
                            <div class="flex items-center justify-between mb-5">
                                <div>
                                    <p class="text-gray-400 text-xs font-medium uppercase tracking-wider">AI Match Score</p>
                                    <p class="text-5xl font-black text-white tracking-tight">94<span class="text-purple-400">.5</span></p>
                                </div>
                                <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur border border-white/10">
                                    <i class="fas fa-robot text-white text-xl"></i>
                                </div>
                            </div>
                            
                            <div class="w-full h-2 bg-white/10 rounded-full mb-5 overflow-hidden">
                                <div class="h-full w-[94%] bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 rounded-full animate-pulse"></div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-white/10">
                                <div class="bg-white/5 rounded-xl p-3 backdrop-blur">
                                    <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                                        <i class="fas fa-star text-yellow-400 text-[10px]"></i> Rating
                                    </div>
                                    <p class="text-white font-bold text-lg">4.8</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-3 backdrop-blur">
                                    <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                                        <i class="fas fa-location-dot text-blue-400 text-[10px]"></i> Distance
                                    </div>
                                    <p class="text-white font-bold text-lg">2.3 km</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-3 backdrop-blur">
                                    <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                                        <i class="fas fa-clock text-green-400 text-[10px]"></i> ETA
                                    </div>
                                    <p class="text-white font-bold text-lg">15 min</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-3 backdrop-blur">
                                    <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                                        <i class="fas fa-check-circle text-emerald-400 text-[10px]"></i> Success
                                    </div>
                                    <p class="text-white font-bold text-lg">98%</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Floating Badge --}}
                        <div class="absolute -top-4 -right-4 z-20 glass-card rounded-2xl px-5 py-3 flex items-center gap-3 shadow-xl animate-float" style="animation-delay: 2s;">
                            <div class="w-10 h-10 bg-linear-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/25">
                                <i class="fas fa-shield-halved text-white"></i>
                            </div>
                            <div>
                                <p class="text-xs text-muted font-medium">All Cleaners</p>
                                <p class="text-sm font-bold text-heading">Verified ✓</p>
                            </div>
                        </div>
                        
                        <div class="absolute -bottom-6 -left-6 w-full h-full bg-gradient-to-r from-blue-200/30 to-purple-200/30 dark:from-blue-500/10 dark:to-purple-500/10 rounded-3xl -z-10 blur-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- STATS STRIP --}}
    {{-- ============================================ --}}
    <section class="relative z-10 pb-8 bg-white dark:bg-gray-950">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
                @php
                    $stats = [
                        ['icon' => 'fa-users', 'color' => 'blue', 'value' => '500+', 'label' => 'Verified Cleaners'],
                        ['icon' => 'fa-city', 'color' => 'purple', 'value' => '20+', 'label' => 'Cities Covered'],
                        ['icon' => 'fa-sparkles', 'color' => 'pink', 'value' => '10K+', 'label' => 'Homes Cleaned'],
                        ['icon' => 'fa-face-smile', 'color' => 'green', 'value' => '98%', 'label' => 'Satisfaction Rate'],
                    ];
                @endphp
                
                @foreach($stats as $stat)
                <div class="glass-card rounded-2xl p-5 text-center card-hover-lift">
                    <div class="w-10 h-10 bg-linear-to-br from-{{ $stat['color'] }}-100 to-{{ $stat['color'] }}-200 dark:from-{{ $stat['color'] }}-900/40 dark:to-{{ $stat['color'] }}-800/40 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400"></i>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-heading stat-number">{{ $stat['value'] }}</p>
                    <p class="text-xs text-muted font-medium mt-1">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- HOW IT WORKS --}}
    {{-- ============================================ --}}
    <section id="how-it-works" class="py-20 lg:py-28 bg-gray-50 dark:bg-gray-900/50 section-divider">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-14 lg:mb-20">
                <span class="inline-block px-4 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4">Simple Process</span>
                <h2 class="text-4xl lg:text-5xl font-black text-heading mb-4 tracking-tight">How It Works</h2>
                <p class="text-lg text-muted max-w-2xl mx-auto">Get your home sparkling clean in 4 simple steps — all powered by AI</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                @php
                    $steps = [
                        ['icon' => 'fa-location-dot', 'color' => 'blue', 'bgClass' => 'step-icon-blue', 'title' => 'Set Location', 'desc' => 'Tell us where you need cleaning — we cover 20+ cities across Tanzania.'],
                        ['icon' => 'fa-calendar-check', 'color' => 'purple', 'bgClass' => 'step-icon-purple', 'title' => 'Choose Service', 'desc' => 'Pick from deep cleaning, regular maintenance, or move-in/out cleaning.'],
                        ['icon' => 'fa-brain', 'color' => 'emerald', 'bgClass' => 'step-icon-emerald', 'title' => 'AI Matches', 'desc' => 'Our XGBoost engine analyzes 24 features to find your ideal cleaner instantly.'],
                        ['icon' => 'fa-sparkles', 'color' => 'rose', 'bgClass' => 'step-icon-rose', 'title' => 'Enjoy!', 'desc' => 'Sit back and relax while a verified professional makes your home shine.'],
                    ];
                @endphp
                
                @foreach($steps as $index => $step)
                <div class="relative group">
                    @if($index < 3)
                    <div class="hidden lg:block absolute top-10 left-[60%] w-[80%] h-0.5 bg-gradient-to-r from-gray-200 to-gray-100 dark:from-gray-700 dark:to-gray-800 z-0"></div>
                    @endif
                    
                    <div class="relative z-10 bg-white dark:bg-gray-800 rounded-3xl p-7 text-center shadow-lg dark:shadow-gray-900/30 border border-gray-100 dark:border-gray-700 card-hover-lift">
                        <div class="absolute -top-3 -right-3 w-8 h-8 bg-linear-to-br from-{{ $step['color'] }}-400 to-{{ $step['color'] }}-600 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-lg">
                            {{ $index + 1 }}
                        </div>
                        
                        <div class="w-16 h-16 {{ $step['bgClass'] }} rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:scale-110 transition-all duration-300">
                            <i class="fas {{ $step['icon'] }} text-2xl text-{{ $step['color'] }}-600 dark:text-{{ $step['color'] }}-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-heading mb-2">{{ $step['title'] }}</h3>
                        <p class="text-sm text-body leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- SERVICES --}}
    {{-- ============================================ --}}
    <section id="services" class="py-20 lg:py-28 bg-white dark:bg-gray-950">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-14 lg:mb-20">
                <span class="inline-block px-4 py-1.5 bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4">Our Services</span>
                <h2 class="text-4xl lg:text-5xl font-black text-heading mb-4 tracking-tight">Professional Cleaning</h2>
                <p class="text-lg text-muted max-w-2xl mx-auto">Every service delivered by background-checked, AI-matched professionals</p>
            </div>
            
            @php $services = App\Models\Service::where('is_active', true)->limit(6)->get(); @endphp
            
            @if($services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @foreach($services as $service)
                <div class="group bg-white dark:bg-gray-800 rounded-3xl p-7 shadow-lg dark:shadow-gray-900/30 border border-gray-100 dark:border-gray-700 card-hover-lift">
                    <div class="flex items-start justify-between mb-5">
                        <div class="w-14 h-14 service-icon-bg rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="fas fa-broom text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Available
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-heading mb-2">{{ $service->name }}</h3>
                    <p class="text-body text-sm leading-relaxed mb-4">{{ Str::limit($service->description, 100) }}</p>
                    <div class="flex items-center gap-2 text-xs text-muted mb-5">
                        <i class="fas fa-clock"></i>
                        <span>~{{ $service->estimated_duration_minutes }} minutes</span>
                    </div>
                    <a href="/register/homeowner" class="block w-full text-center px-5 py-3 btn-primary rounded-xl font-semibold text-sm">
                        Book Now <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-broom text-gray-400 dark:text-gray-500 text-2xl"></i>
                </div>
                <p class="text-muted">Services will be available soon. Check back later!</p>
            </div>
            @endif
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- WHY US --}}
    {{-- ============================================ --}}
    <section id="why-us" class="py-20 lg:py-28 bg-gray-50 dark:bg-gray-900/50 section-divider">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-14 lg:mb-20">
                <span class="inline-block px-4 py-1.5 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4">Why SmartClean</span>
                <h2 class="text-4xl lg:text-5xl font-black text-heading mb-4 tracking-tight">Why Choose SmartClean AI?</h2>
                <p class="text-lg text-muted max-w-2xl mx-auto">The smartest, safest, and fastest way to get your home cleaned in Tanzania</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                @php
                    $features = [
                        ['icon' => 'fa-robot', 'color' => 'blue', 'bgClass' => 'step-icon-blue', 'title' => 'AI-Powered Matching', 'desc' => 'Our XGBoost algorithm analyzes 24 features — location, ratings, price, and availability — to find your perfect cleaner in milliseconds.'],
                        ['icon' => 'fa-shield-halved', 'color' => 'green', 'bgClass' => 'step-icon-emerald', 'title' => 'Verified Cleaners', 'desc' => 'Every cleaner undergoes background checks, ID verification, and skills assessment before they can accept a single booking.'],
                        ['icon' => 'fa-bolt', 'color' => 'orange', 'bgClass' => 'step-icon-rose', 'title' => 'Instant Booking', 'desc' => 'Book in under 60 seconds and get a cleaner dispatched to your location within minutes — available across all major Tanzanian cities.'],
                    ];
                @endphp
                
                @foreach($features as $feature)
                <div class="group bg-white dark:bg-gray-800 rounded-3xl p-8 text-center shadow-lg dark:shadow-gray-900/30 border border-gray-100 dark:border-gray-700 card-hover-lift">
                    <div class="w-16 h-16 {{ $feature['bgClass'] }} rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-all duration-300">
                        <i class="fas {{ $feature['icon'] }} text-2xl text-{{ $feature['color'] }}-600 dark:text-{{ $feature['color'] }}-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-heading mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-body text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- TESTIMONIALS --}}
    {{-- ============================================ --}}
    <section class="py-20 lg:py-28 bg-white dark:bg-gray-950">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-14 lg:mb-20">
                <span class="inline-block px-4 py-1.5 bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4">Testimonials</span>
                <h2 class="text-4xl lg:text-5xl font-black text-heading mb-4 tracking-tight">Loved by Thousands</h2>
                <p class="text-lg text-muted max-w-2xl mx-auto">Join 10,000+ happy homeowners who trust SmartClean AI</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                @php
                    $testimonials = [
                        ['name' => 'Grace M.', 'city' => 'Dar es Salaam', 'text' => 'SmartClean AI matched me with an amazing cleaner in under 2 minutes. The AI recommendation was spot-on! My house has never been this clean.', 'rating' => 5],
                        ['name' => 'James K.', 'city' => 'Arusha', 'text' => 'As a busy professional, I love how quickly I can book. The cleaner arrived on time and did a thorough job. Highly recommended!', 'rating' => 5],
                        ['name' => 'Amina H.', 'city' => 'Mwanza', 'text' => 'Finally, a cleaning service that I can trust. All cleaners are verified and the AI makes sure I get the best match for my budget.', 'rating' => 5],
                    ];
                @endphp
                
                @foreach($testimonials as $testimonial)
                <div class="testimonial-card rounded-3xl p-7 card-hover-lift">
                    <div class="flex items-center gap-1 text-yellow-500 mb-4">
                        @for($i = 0; $i < $testimonial['rating']; $i++)
                            <i class="fas fa-star text-sm"></i>
                        @endfor
                    </div>
                    <p class="text-body text-sm leading-relaxed mb-5 italic">"{{ $testimonial['text'] }}"</p>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                            {{ substr($testimonial['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-heading">{{ $testimonial['name'] }}</p>
                            <p class="text-xs text-muted">{{ $testimonial['city'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- CTA --}}
    {{-- ============================================ --}}
    <section class="relative py-20 lg:py-28 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 gradient-animate"></div>
        <div class="absolute inset-0 opacity-30" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.15\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
        
        <div class="relative z-10 max-w-4xl mx-auto text-center px-4">
            <h2 class="text-4xl lg:text-5xl font-black text-white mb-5 tracking-tight">Ready for a Cleaner Home?</h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto leading-relaxed">Join thousands of satisfied customers across Tanzania. Professional cleaners, AI-matched, at competitive prices.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/register/homeowner" class="group inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 rounded-2xl font-bold text-base shadow-2xl shadow-black/20 hover:shadow-black/30 hover:scale-105 transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i> Sign Up as Customer
                    <i class="fas fa-arrow-right ml-2 text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                </a>
                <a href="/register/cleaner" class="inline-flex items-center justify-center px-8 py-4 bg-white/15 text-white rounded-2xl font-bold text-base border-2 border-white/30 hover:bg-white/25 hover:border-white/50 hover:scale-105 transition-all duration-300 backdrop-blur-sm">
                    <i class="fas fa-broom mr-2"></i> Become a Cleaner
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================ --}}
    <footer class="bg-gray-900 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                <div class="lg:col-span-2">
                    <a href="/" class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-broom text-white"></i>
                        </div>
                        <span class="text-xl font-black tracking-tight">SmartClean <span class="text-gray-400 font-light">AI</span></span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6 max-w-sm">Intelligent home cleaning services powered by cutting-edge AI. Available across all major cities in Tanzania.</p>
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center hover:bg-blue-500 transition-all duration-300"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center hover:bg-blue-400 transition-all duration-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center hover:bg-pink-500 transition-all duration-300"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider mb-5 text-gray-300">Quick Links</h4>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li><a href="#how-it-works" class="hover:text-white transition-all duration-300 flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-gray-500"></i> How It Works</a></li>
                        <li><a href="#services" class="hover:text-white transition-all duration-300 flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-gray-500"></i> Services</a></li>
                        <li><a href="#why-us" class="hover:text-white transition-all duration-300 flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-gray-500"></i> Why Us</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider mb-5 text-gray-300">For Cleaners</h4>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li><a href="/register/cleaner" class="hover:text-white transition-all duration-300 flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-gray-500"></i> Join as Cleaner</a></li>
                        <li><a href="/login" class="hover:text-white transition-all duration-300 flex items-center gap-2"><i class="fas fa-chevron-right text-[8px] text-gray-500"></i> Cleaner Login</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider mb-5 text-gray-300">Contact</h4>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li class="flex items-center gap-2"><i class="fas fa-envelope text-blue-400 w-4"></i> info@smartcleaner.co.tz</li>
                        <li class="flex items-center gap-2"><i class="fas fa-phone text-green-400 w-4"></i> +255 700 000 000</li>
                        <li class="flex items-center gap-2"><i class="fas fa-map-marker-alt text-red-400 w-4"></i> Dar es Salaam, Tanzania</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} SmartClean AI. All rights reserved.</p>
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <a href="#" class="hover:text-gray-300 transition-all duration-300">Privacy Policy</a>
                    <a href="#" class="hover:text-gray-300 transition-all duration-300">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ============================================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================================ --}}
    
    {{-- Alpine.js for mobile menu --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Theme Toggle --}}
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            updateIcons(true);
        }
        
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
            updateIcons(isDark);
        }
        
        function updateIcons(isDark) {
            const icon = document.getElementById('themeIcon');
            const iconMobile = document.getElementById('themeIconMobile');
            
            if (icon) {
                icon.className = isDark 
                    ? 'fas fa-sun text-yellow-400 text-lg transition-all duration-300' 
                    : 'fas fa-moon text-gray-600 text-lg transition-all duration-300';
            }
            if (iconMobile) {
                iconMobile.className = isDark 
                    ? 'fas fa-sun text-yellow-400' 
                    : 'fas fa-moon text-gray-600';
            }
        }
        
        // Close mobile menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const mobileMenu = document.querySelector('[x-data]');
                if (mobileMenu && mobileMenu.__x) {
                    mobileMenu.__x.$data.mobileOpen = false;
                }
            }
        });
    </script>
</body>
</html>
