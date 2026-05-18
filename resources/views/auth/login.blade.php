<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SmartClean AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (function() { if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark'); })();
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(59,130,246,0.4); } 70% { box-shadow: 0 0 0 20px rgba(59,130,246,0); } 100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); } }
        @keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .pulse-ring { animation: pulse-ring 2s infinite; }
        .gradient-animate { background-size: 200% 200%; animation: gradientShift 4s ease infinite; }
        
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); }
        .dark .glass { background: rgba(31,41,55,0.7); border: 1px solid rgba(75,85,99,0.3); }
        
        .dark .bg-white { background-color: #1f2937; }
        .dark .text-gray-800 { color: #f9fafb; }
        .dark .text-gray-600 { color: #d1d5db; }
        .dark .text-gray-500 { color: #9ca3af; }
        .dark .border-gray-200 { border-color: #374151; }
        .dark .bg-gray-50 { background-color: #111827; }
        
        .input-focus { transition: all 0.3s ease; }
        .input-focus:focus { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(59,130,246,0.3); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 relative overflow-hidden">

    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-200 to-purple-200 dark:from-blue-900 dark:to-purple-900 rounded-full opacity-30 animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-tr from-green-200 to-teal-200 dark:from-green-900 dark:to-teal-900 rounded-full opacity-20 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-gradient-to-bl from-pink-200 to-orange-200 dark:from-pink-900 dark:to-orange-900 rounded-full opacity-20 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md relative z-10 animate-slide-up">
        
        <!-- Logo & Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl shadow-2xl mb-4 pulse-ring">
                <i class="fas fa-broom text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white">
                Smart<span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent gradient-animate">Clean</span> AI
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Sign in to your account to continue</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-3xl shadow-2xl p-8">
            
            <!-- Error Messages -->
            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-2xl mb-6 text-sm animate-slide-up">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="/login" class="space-y-5">
                @csrf
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-envelope text-blue-500 mr-1"></i> Email Address
                    </label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="input-focus w-full pl-12 pr-4 py-3.5 rounded-2xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white bg-white/50 backdrop-blur text-sm focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none"
                               placeholder="your@email.com">
                        <i class="fas fa-user absolute left-4 top-4 text-gray-400"></i>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-lock text-blue-500 mr-1"></i> Password
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="input-focus w-full pl-12 pr-12 py-3.5 rounded-2xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white bg-white/50 backdrop-blur text-sm focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none"
                               placeholder="••••••••">
                        <i class="fas fa-key absolute left-4 top-4 text-gray-400"></i>
                        <button type="button" @click="show = !show" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600">
                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Forgot password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-600 hover:from-blue-600 hover:via-purple-600 hover:to-indigo-700 text-white rounded-2xl font-bold text-lg shadow-xl hover:shadow-2xl transform hover:scale-[1.02] transition-all duration-300 gradient-animate">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>

            <!-- Register Link -->
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                Don't have an account? 
                <a href="/register" class="text-blue-600 hover:text-blue-700 font-bold">Create Account</a>
            </p>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} SmartClean AI. All rights reserved.
        </p>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>