<!DOCTYPE html>
<html lang="en" class="scroll-smooth" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); }); document.documentElement.classList.toggle('dark', darkMode)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — SmartClean AI</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 33% { transform: translateY(-15px) rotate(1deg); } 66% { transform: translateY(-5px) rotate(-1deg); } }
        @keyframes float-delayed { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-25px) rotate(-1deg); } }
        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); } 70% { box-shadow: 0 0 0 25px rgba(59,130,246,0); } 100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); } }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        @keyframes gradientShift { 0% { background-position:0% 50%; } 50% { background-position:100% 50%; } 100% { background-position:0% 50%; } }
        @keyframes shimmer { 0% { transform:translateX(-100%); } 100% { transform:translateX(100%); } }
        .animate-float { animation: float 7s ease-in-out infinite; }
        .animate-float-delayed { animation: float-delayed 9s ease-in-out infinite; }
        .animate-slide-up { animation: slideUp 0.7s cubic-bezier(0.16,1,0.3,1); }
        .pulse-ring { animation: pulse-ring 2.5s infinite; }
        .gradient-animate { background-size:200% 200%; animation: gradientShift 5s ease infinite; }
        .glass-card { background:rgba(255,255,255,0.85); backdrop-filter:blur(24px) saturate(180%); -webkit-backdrop-filter:blur(24px) saturate(180%); border:1px solid rgba(0,0,0,0.06); box-shadow:0 8px 40px rgba(0,0,0,0.08); }
        .dark .glass-card { background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.08); box-shadow:0 8px 40px rgba(0,0,0,0.4); }
        .input-premium { transition:all 0.3s cubic-bezier(0.16,1,0.3,1); background:rgba(255,255,255,0.6); }
        .dark .input-premium { background:rgba(30,41,59,0.7); }
        .input-premium:focus { transform:translateY(-2px); box-shadow:0 12px 30px -8px rgba(59,130,246,0.35); border-color:#3b82f6; }
        .btn-submit { position:relative; overflow:hidden; }
        .btn-submit::after { content:''; position:absolute; top:0; left:0; width:100%; height:100%; background:linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent); animation:shimmer 2s infinite; }
        ::-webkit-scrollbar { width:0; }
        .dark body { background-color:#020617; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">

    {{-- Animated Background Orbs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-gradient-to-br from-blue-300/30 to-purple-300/30 dark:from-blue-600/10 dark:to-purple-600/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-gradient-to-tr from-green-300/20 to-teal-300/20 dark:from-green-600/8 dark:to-teal-600/8 rounded-full blur-3xl animate-float-delayed"></div>
        <div class="absolute top-1/2 left-1/3 w-[300px] h-[300px] bg-gradient-to-bl from-pink-300/20 to-rose-300/20 dark:from-pink-600/8 dark:to-rose-600/8 rounded-full blur-3xl animate-float" style="animation-delay:4s;"></div>
    </div>

    {{-- Theme Toggle --}}
    <button @click="darkMode = !darkMode" class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 w-12 h-12 flex items-center justify-center rounded-2xl bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300" title="Toggle theme">
        <i class="fas text-xl transition-all duration-500" :class="darkMode ? 'fa-sun text-yellow-400' : 'fa-moon text-gray-600'"></i>
    </button>

    {{-- Back to Home --}}
    <a href="/" class="fixed top-4 left-4 sm:top-6 sm:left-6 z-50 inline-flex items-center gap-2 px-4 py-2.5 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 rounded-2xl shadow-lg hover:shadow-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300">
        <i class="fas fa-arrow-left text-xs"></i> Home
    </a>

    {{-- Main Container --}}
    <div class="w-full max-w-md relative z-10 animate-slide-up">
        <div class="text-center mb-8">
            <div class="relative inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-3xl shadow-2xl shadow-purple-500/25 mb-5 pulse-ring mx-auto">
                <i class="fas fa-broom text-white text-3xl sm:text-4xl"></i>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                Smart<span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent gradient-animate">Clean</span> <span class="text-gray-400 font-light">AI</span>
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Welcome back! Sign in to your account</p>
        </div>

        <div class="glass-card rounded-3xl p-6 sm:p-8">
            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-300 px-4 py-3.5 rounded-2xl mb-6 text-sm animate-slide-up">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-500/20 rounded-xl flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-circle text-red-500 text-sm"></i></div>
                    <span class="font-medium">{{ $errors->first() }}</span>
                </div>
            </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"><i class="fas fa-envelope text-blue-500 mr-1.5"></i> Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input-premium w-full pl-12 pr-4 py-3.5 rounded-2xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium placeholder:text-gray-400 focus:outline-none" placeholder="your@email.com">
                        <i class="fas fa-envelope absolute left-4 top-4 text-gray-400 dark:text-gray-500"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"><i class="fas fa-lock text-blue-500 mr-1.5"></i> Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" name="password" required class="input-premium w-full pl-12 pr-14 py-3.5 rounded-2xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium placeholder:text-gray-400 focus:outline-none" placeholder="••••••••">
                        <i class="fas fa-lock absolute left-4 top-4 text-gray-400 dark:text-gray-500"></i>
                        <button type="button" @click="show = !show" class="absolute right-3 top-3 w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200"><i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer">
                        <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200 transition-colors">Remember me</span>
                    </label>
                    <a href="/forgot-password" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-semibold transition-colors">Forgot password?</a>
                </div>
                <button type="submit" class="btn-submit w-full px-6 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 hover:from-blue-600 hover:via-purple-600 hover:to-pink-600 text-white rounded-2xl font-bold text-base shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 transform hover:scale-[1.02] transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>
           
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">Don't have an account? <a href="/register" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-bold transition-colors">Create Account</a></p>
        </div>
        <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-6">&copy; {{ date('Y') }} SmartClean AI. Group number 14. All rights reserved.</p>
    </div>
</body>
</html>