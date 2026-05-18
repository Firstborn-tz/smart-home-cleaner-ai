<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SmartClean AI - Intelligent Home Cleaning Services in Tanzania</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        // Apply dark mode immediately to prevent flash
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes slideUp { from { opacity:0; transform: translateY(40px); } to { opacity:1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-slide-up { animation: slideUp 0.8s ease-out; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        .gradient-animate { background-size: 200% 200%; animation: gradientShift 4s ease infinite; }
        
        .glass { background: rgba(255,255,255,0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .dark .glass { background: rgba(15,23,42,0.8); }
        
        /* Dark Mode Overrides */
        .dark body { background-color: #0f172a; }
        .dark .bg-white { background-color: #1e293b !important; }
        .dark .bg-gray-50 { background-color: #0f172a !important; }
        .dark .text-gray-900 { color: #f8fafc !important; }
        .dark .text-gray-800 { color: #f1f5f9 !important; }
        .dark .text-gray-700 { color: #e2e8f0 !important; }
        .dark .text-gray-600 { color: #cbd5e1 !important; }
        .dark .text-gray-500 { color: #94a3b8 !important; }
        .dark .text-gray-400 { color: #64748b !important; }
        .dark .border-gray-200 { border-color: #334155 !important; }
        .dark .border-gray-100 { border-color: #1e293b !important; }
        .dark .border-gray-300 { border-color: #475569 !important; }
        .dark .from-blue-50 { --tw-gradient-from: #0f172a; }
        .dark .via-purple-50 { --tw-gradient-via: #0f172a; }
        .dark .to-pink-50 { --tw-gradient-to: #0f172a; }
        .dark .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5); }
        .dark .hover\:bg-gray-50:hover { background-color: #1e293b !important; }
        .dark .hover\:shadow-xl:hover { box-shadow: 0 20px 25px -5px rgba(0,0,0,0.6); }
        
        .hero-gradient { 
            background: radial-gradient(circle at 20% 50%, rgba(59,130,246,0.15) 0%, transparent 50%), 
                        radial-gradient(circle at 80% 50%, rgba(147,51,234,0.15) 0%, transparent 50%), 
                        linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #faf5ff 100%); 
        }
        .dark .hero-gradient { 
            background: radial-gradient(circle at 20% 50%, rgba(59,130,246,0.08) 0%, transparent 50%), 
                        radial-gradient(circle at 80% 50%, rgba(147,51,234,0.08) 0%, transparent 50%), 
                        linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); 
        }
    </style>
</head>
<body class="bg-white dark:bg-gray-900">

    <!-- ============================================ -->
    <!-- NAVIGATION -->
    <!-- ============================================ -->
    <nav class="fixed top-0 w-full z-50 glass border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-broom text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-extrabold text-gray-900 dark:text-white">
                        Smart<span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Clean</span> AI
                    </span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#hero" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Home</a>
                    <a href="#how-it-works" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">How It Works</a>
                    <a href="#services" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Services</a>
                    <a href="#why-us" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Why Us</a>
                    <a href="/login" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Login</a>
                    
                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Toggle theme">
                        <i id="themeIcon" class="fas fa-moon text-gray-600 dark:text-yellow-400 text-lg"></i>
                    </button>
                    
                    <a href="/register" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm hover:shadow-xl transition-all transform hover:scale-105">
                        Get Started
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                    <i class="fas fa-bars text-gray-600 dark:text-gray-300 text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden glass border-t border-gray-100 dark:border-gray-700">
            <div class="px-4 py-4 space-y-3">
                <a href="#hero" class="block py-2 text-gray-600 dark:text-gray-300 font-medium">Home</a>
                <a href="#how-it-works" class="block py-2 text-gray-600 dark:text-gray-300 font-medium">How It Works</a>
                <a href="#services" class="block py-2 text-gray-600 dark:text-gray-300 font-medium">Services</a>
                <a href="#why-us" class="block py-2 text-gray-600 dark:text-gray-300 font-medium">Why Us</a>
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Theme</span>
                    <button onclick="toggleTheme()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                        <i id="themeIconMobile" class="fas fa-moon text-gray-600 dark:text-yellow-400"></i>
                    </button>
                </div>
                <a href="/login" class="block py-2 text-gray-600 dark:text-gray-300 font-medium">Login</a>
                <a href="/register" class="block text-center px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- ============================================ -->
    <!-- HERO SECTION -->
    <!-- ============================================ -->
    <section id="hero" class="hero-gradient pt-32 pb-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left Content -->
                <div class="animate-slide-up">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 font-semibold rounded-full text-sm font-bold mb-6">
                        <span class="w-2.5 h-2.5 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        AI-Powered Cleaning Platform
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white leading-tight mb-6 drop-shadow-sm">
                        Your Home,
                        <br>
                        <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent gradient-animate">
                            Sparkling Clean
                        </span>
                    </h1>
                    
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-8 leading-relaxed max-w-lg font-medium">
                        SmartClean AI matches you with the best verified cleaners in Tanzania. Powered by artificial intelligence for faster, smarter, and more reliable home cleaning.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/register/homeowner" class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-lg hover:shadow-2xl transition-all transform hover:scale-105 text-center">
                            <i class="fas fa-home mr-2"></i> Book a Cleaner
                        </a>
                        <a href="/register/cleaner" class="px-8 py-4 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-2xl font-bold text-lg hover:border-blue-400 hover:text-blue-600 transition-all text-center">
                            <i class="fas fa-broom mr-2"></i> Become a Cleaner
                        </a>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="flex flex-wrap items-center gap-6 mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-2">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-white dark:border-gray-800"></div>
                                <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-white dark:border-gray-800"></div>
                                <div class="w-8 h-8 rounded-full bg-purple-500 border-2 border-white dark:border-gray-800"></div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">500+ Cleaners</span>
                        </div>
                        <div class="flex items-center text-yellow-500">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">4.8 Rating</span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-map-marker-alt text-red-400 mr-1"></i> 20+ Cities</span>
                    </div>
                </div>
                
                <!-- Right Content -->
                <div class="hidden lg:block animate-float">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-3xl p-8 shadow-2xl">
                            <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-white/80 text-sm">AI Score</p>
                                        <p class="text-5xl font-black text-white">94.5</p>
                                    </div>
                                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-robot text-white text-2xl"></i>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-4 border-t border-white/20">
                                    <div class="flex justify-between text-white/80 text-sm"><span>⭐ Rating</span><span>4.8</span></div>
                                    <div class="flex justify-between text-white/80 text-sm"><span>📏 Distance</span><span>2.3 km</span></div>
                                    <div class="flex justify-between text-white/80 text-sm"><span>⏱️ ETA</span><span>15 min</span></div>
                                    <div class="flex justify-between text-white/80 text-sm"><span>✅ Completion</span><span>98%</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-full h-full bg-gradient-to-r from-blue-200 to-purple-200 dark:from-blue-900 dark:to-purple-900 rounded-3xl -z-10"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- HOW IT WORKS -->
    <!-- ============================================ -->
    <section id="how-it-works" class="py-20 bg-gray-50 dark:bg-gray-800/50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-gray-900 dark:text-white mb-4">How It Works</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">Get your home cleaned in 4 simple steps</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                @for($i = 1; $i <= 4; $i++)
                <div class="text-center group">
                    @php
                        $icons = ['fa-map-marker-alt', 'fa-calendar-check', 'fa-brain', 'fa-sparkles'];
                        $colors = ['from-blue-400 to-blue-600', 'from-purple-400 to-purple-600', 'from-green-400 to-green-600', 'from-orange-400 to-orange-600'];
                        $titles = ['Set Location', 'Choose Service', 'AI Matches', 'Enjoy!'];
                        $descs = ['Tell us where you need cleaning', 'Select the type of cleaning', 'AI finds the best cleaner', 'Relax while your home shines'];
                    @endphp
                    <div class="w-20 h-20 bg-gradient-to-br {{ $colors[$i-1] }} rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas {{ $icons[$i-1] }} text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $i }}. {{ $titles[$i-1] }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $descs[$i-1] }}</p>
                </div>
                @endfor
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- SERVICES -->
    <!-- ============================================ -->
    <section id="services" class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-gray-900 dark:text-white mb-4">Our Services</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">Professional cleaning for every need</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php $services = App\Models\Service::where('is_active', true)->limit(6)->get(); @endphp
                @foreach($services as $service)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg dark:shadow-gray-900/50 p-8 hover:shadow-2xl transition-all duration-300 border border-gray-100 dark:border-gray-700 group">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-broom text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $service->name }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">{{ Str::limit($service->description, 80) }}</p>
                    <div class="text-center"><span class="text-sm text-gray-400 dark:text-gray-500"><i class="fas fa-clock mr-1"></i> {{ $service->estimated_duration_minutes }} minutes</span></div>
                    <a href="/register/homeowner" class="block text-center mt-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-sm transition">
                        Book Now
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- WHY US -->
    <!-- ============================================ -->
    <section id="why-us" class="py-20 bg-gray-50 dark:bg-gray-800/50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-gray-900 dark:text-white mb-4">Why Choose SmartClean AI?</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">The smartest way to get your home cleaned</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center shadow-lg dark:shadow-gray-900/50 hover:shadow-xl transition border border-gray-100 dark:border-gray-700">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-robot text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">AI-Powered Matching</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">XGBoost AI analyzes 24 features to find your perfect cleaner based on location, rating, and price.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center shadow-lg dark:shadow-gray-900/50 hover:shadow-xl transition border border-gray-100 dark:border-gray-700">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Verified Cleaners</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">All cleaners are background-checked and verified by our team before they can accept bookings.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center shadow-lg dark:shadow-gray-900/50 hover:shadow-xl transition border border-gray-100 dark:border-gray-700">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-bolt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Instant Booking</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Get a cleaner within minutes with our instant booking system across 20+ cities in Tanzania.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CTA -->
    <!-- ============================================ -->
    <section class="py-20 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-4xl font-black text-white mb-6">Ready to Get Started?</h2>
            <p class="text-xl text-white/80 mb-8">Join thousands of satisfied customers. Cleaners set their own competitive prices.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/register/homeowner" class="px-8 py-4 bg-white text-blue-600 rounded-2xl font-bold text-lg hover:shadow-2xl transition-all transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i> Sign Up as Customer
                </a>
                <a href="/register/cleaner" class="px-8 py-4 bg-white/20 text-white rounded-2xl font-bold text-lg hover:bg-white/30 transition-all transform hover:scale-105 border-2 border-white/30">
                    <i class="fas fa-broom mr-2"></i> Become a Cleaner
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FOOTER -->
    <!-- ============================================ -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-broom text-white"></i>
                        </div>
                        <span class="text-xl font-bold">SmartClean AI</span>
                    </div>
                    <p class="text-gray-400 text-sm">Intelligent home cleaning services powered by AI. Available across all major cities in Tanzania.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#how-it-works" class="hover:text-white transition">How It Works</a></li>
                        <li><a href="#services" class="hover:text-white transition">Services</a></li>
                        <li><a href="#why-us" class="hover:text-white transition">Why Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">For Cleaners</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="/register/cleaner" class="hover:text-white transition">Join as Cleaner</a></li>
                        <li><a href="/login" class="hover:text-white transition">Cleaner Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><i class="fas fa-envelope mr-2"></i> info@smartcleaner.co.tz</li>
                        <li><i class="fas fa-phone mr-2"></i> +255 700 000 000</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Dar es Salaam, Tanzania</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} SmartClean AI. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Theme Toggle Script -->
    <script>
        // Initialize theme icon on load
        if (localStorage.getItem('darkMode') === 'true') {
            document.getElementById('themeIcon')?.classList.replace('fa-moon', 'fa-sun');
            document.getElementById('themeIconMobile')?.classList.replace('fa-moon', 'fa-sun');
        }
        
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
            
            // Update desktop icon
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = isDark 
                    ? 'fas fa-sun text-yellow-400 text-lg' 
                    : 'fas fa-moon text-gray-600 text-lg';
            }
            
            // Update mobile icon
            const iconMobile = document.getElementById('themeIconMobile');
            if (iconMobile) {
                iconMobile.className = isDark 
                    ? 'fas fa-sun text-yellow-400' 
                    : 'fas fa-moon text-gray-600';
            }
        }
    </script>
</body>
</html>

