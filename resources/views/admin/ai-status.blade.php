@extends('layouts.app')

@section('title', 'AI System Status')
@section('user_role', 'Administrator')
@section('page_title', 'AI System Status')
@section('page_subtitle', 'Real-time AI engine monitoring & performance')

@section('content')
<div>
    @php
        $aiService = app(App\Services\AI\XGBoostRecommendationService::class);
        $aiStatus = $aiService->getServiceStatus();
        $isActive = $aiStatus['available'] ?? false;
        $modelType = $aiStatus['model'] ?? 'Unknown';
        
        $totalPredictions = App\Models\Booking::whereNotNull('ai_recommendation_score')->count() ?? 0;
        $avgScore = App\Models\Booking::whereNotNull('ai_recommendation_score')->avg('ai_recommendation_score') ?? 0;
        
        $ratedBookings = App\Models\Booking::whereNotNull('ai_recommendation_score')
            ->whereNotNull('cleaner_rating_given')
            ->where('status', 'completed')->count();
            
        $totalRated = max($ratedBookings, 1);
        $accuracyRate = $totalRated > 0 ? round((App\Models\Booking::whereNotNull('ai_recommendation_score')
            ->whereNotNull('cleaner_rating_given')->where('status', 'completed')
            ->whereRaw('ABS((ai_recommendation_score/20) - cleaner_rating_given) < 1.0')->count() / $totalRated) * 100, 1) : 0;
    @endphp

    
    {{-- AI STATUS HERO CARD --}}
    
    <div class="relative overflow-hidden rounded-3xl shadow-2xl mb-6 p-6 sm:p-8 lg:p-10 
                {{ $isActive ? 'bg-linear-to-br from-emerald-500 via-green-500 to-teal-600' : 'bg-linear-to-br from-amber-500 via-orange-500 to-red-600' }}">
        {{-- Background Pattern Dots --}}
        <div class="absolute inset-0 opacity-[0.07]">
            <svg width="100%" height="100%">
                <defs>
                    <pattern id="dotPattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <circle cx="20" cy="20" r="1.5" fill="white"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#dotPattern)"/>
            </svg>
        </div>
        
        {{-- Decorative Circles --}}
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
            {{-- Left: Status Info --}}
            <div class="flex items-center gap-5">
                <div class="relative flex-shrink-0">
                    <div class="w-20 h-20 lg:w-24 lg:h-24 bg-white/15 backdrop-blur rounded-3xl flex items-center justify-center text-white shadow-2xl">
                        <i class="fas fa-brain text-3xl lg:text-4xl"></i>
                    </div>
                    {{-- Pulse Indicator --}}
                    <span class="absolute -bottom-1.5 -right-1.5 w-9 h-9 rounded-xl border-[3px] border-white/30 flex items-center justify-center
                                 {{ $isActive ? 'bg-green-400' : 'bg-yellow-400' }} shadow-lg">
                        <span class="w-3 h-3 rounded-full animate-pulse {{ $isActive ? 'bg-green-600' : 'bg-yellow-600' }}"></span>
                    </span>
                </div>
                <div class="text-white">
                    <p class="text-white/60 text-xs font-bold uppercase tracking-[0.2em] mb-1">AI Engine Status</p>
                    <h2 class="text-2xl lg:text-4xl font-black tracking-tight">
                        {{ $isActive ? 'XGBoost Active' : 'Fallback Mode' }}
                    </h2>
                    <p class="text-white/70 text-sm mt-2 leading-relaxed max-w-md">
                        {{ $isActive ? 'Full AI mode — 24 features analyzed in real-time per cleaner' : '7-factor heuristic scoring active — AI service unavailable' }}
                    </p>
                </div>
            </div>
            
            {{-- Right: Model Info Card --}}
            <div class="bg-white/15 backdrop-blur-xl rounded-2xl px-6 py-5 border border-white/20 text-center flex-shrink-0 shadow-xl">
                <p class="text-white/60 text-[10px] font-bold uppercase tracking-wider mb-1">Model Type</p>
                <p class="text-3xl font-black text-white tracking-tight">{{ $modelType }}</p>
                <div class="mt-3 flex items-center justify-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-green-300 animate-pulse' : 'bg-yellow-300' }}"></span>
                    <span class="text-white/60 text-[10px] font-semibold uppercase tracking-wider">
                        {{ $isActive ? 'Connected' : 'Degraded' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    
    {{-- STATS CARDS --}}
    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
        {{-- Total Predictions --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-bar text-purple-600 dark:text-purple-400"></i>
                </div>
                <span class="text-xs text-muted font-medium uppercase tracking-wider">Predictions</span>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ number_format($totalPredictions) }}</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-purple-500 h-full rounded-full" style="width: {{ min(($totalPredictions / max($totalPredictions, 1000)) * 100, 100) }}%"></div>
            </div>
        </div>

        {{-- Accuracy Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-bullseye text-green-600 dark:text-green-400"></i>
                </div>
                <span class="text-xs text-muted font-medium uppercase tracking-wider">Accuracy</span>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $accuracyRate }}%</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full {{ $accuracyRate >= 80 ? 'bg-green-500' : ($accuracyRate >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                     style="width: {{ $accuracyRate }}%"></div>
            </div>
        </div>

        {{-- Avg AI Score --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-percent text-blue-600 dark:text-blue-400"></i>
                </div>
                <span class="text-xs text-muted font-medium uppercase tracking-wider">Avg AI Score</span>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ number_format($avgScore, 1) }}%</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-500 h-full rounded-full" style="width: {{ $avgScore }}%"></div>
            </div>
        </div>

        {{-- Rated Bookings --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-orange-100 to-amber-200 dark:from-orange-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-star text-orange-600 dark:text-orange-400"></i>
                </div>
                <span class="text-xs text-muted font-medium uppercase tracking-wider">Rated</span>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $ratedBookings }}</p>
            <div class="flex items-center gap-0.5 mt-1 text-yellow-500">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-[9px] {{ $ratedBookings > 0 ? '' : 'text-gray-300 dark:text-gray-600' }}"></i>
                @endfor
            </div>
        </div>
    </div>

 
    {{-- MAIN CONTENT GRID --}}
  
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        
        {{-- How AI Works --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/25">
                        <i class="fas fa-cogs text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading">How AI Works</h3>
                        <p class="text-xs text-muted">Dual-layer recommendation engine</p>
                    </div>
                </div>
            </div>
            
            <div class="p-5 space-y-1">
                {{-- Primary: XGBoost --}}
                <div class="relative pl-10 pb-5 {{ $isActive ? 'border-l-2 border-green-500' : 'border-l-2 border-gray-300 dark:border-gray-600' }}">
                    <div class="absolute -left-[17px] top-0 w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold text-white shadow-md
                                {{ $isActive ? 'bg-linear-to-br from-green-400 to-emerald-600' : 'bg-linear-to-br from-gray-400 to-gray-600' }}">
                        1
                    </div>
                    <div class="ml-1">
                        <h4 class="font-bold text-sm text-heading">XGBoost AI Engine</h4>
                        <p class="text-xs text-muted mt-1 leading-relaxed">Python FastAPI microservice on port 8001 analyzing <strong>24 features</strong> per cleaner for optimal matching.</p>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold mt-2
                                     {{ $isActive ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20' : 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-500/20' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $isActive ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ $isActive ? 'Connected' : 'Offline' }}
                        </span>
                    </div>
                </div>
                
                {{-- Secondary: Fallback --}}
                <div class="relative pl-10 pb-2 {{ !$isActive ? 'border-l-2 border-yellow-500' : 'border-l-2 border-gray-300 dark:border-gray-600' }}">
                    <div class="absolute -left-[17px] top-0 w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold text-white shadow-md
                                {{ !$isActive ? 'bg-linear-to-br from-yellow-400 to-amber-600' : 'bg-linear-to-br from-gray-400 to-gray-600' }}">
                        2
                    </div>
                    <div class="ml-1">
                        <h4 class="font-bold text-sm text-heading">7-Factor Fallback</h4>
                        <p class="text-xs text-muted mt-1 leading-relaxed">Local scoring using <strong>Rating, Distance, Completion, Experience, Jobs, Response, Profile</strong>.</p>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold mt-2
                                     {{ !$isActive ? 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-500/20' : 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-500/20' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ !$isActive ? 'bg-yellow-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ !$isActive ? 'Active Now' : 'Standby' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feature Weights --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <i class="fas fa-balance-scale text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading">Scoring Weights</h3>
                        <p class="text-xs text-muted">Fallback mode factor weights</p>
                    </div>
                </div>
            </div>
            
            <div class="p-5 space-y-4">
                @php
                $features = [
                    ['name' => 'Rating', 'weight' => 25, 'color' => 'yellow', 'icon' => 'fa-star'],
                    ['name' => 'Distance', 'weight' => 25, 'color' => 'blue', 'icon' => 'fa-location-dot'],
                    ['name' => 'Completion Rate', 'weight' => 20, 'color' => 'green', 'icon' => 'fa-check-circle'],
                    ['name' => 'Experience', 'weight' => 10, 'color' => 'purple', 'icon' => 'fa-award'],
                    ['name' => 'Total Jobs', 'weight' => 10, 'color' => 'orange', 'icon' => 'fa-briefcase'],
                    ['name' => 'Response Time', 'weight' => 5, 'color' => 'pink', 'icon' => 'fa-clock'],
                    ['name' => 'Profile Completion', 'weight' => 5, 'color' => 'indigo', 'icon' => 'fa-user-check'],
                ];
                @endphp
                
                @foreach($features as $f)
                <div class="group">
                    <div class="flex justify-between items-center text-sm mb-1.5">
                        <div class="flex items-center gap-2">
                            <i class="fas {{ $f['icon'] }} text-{{ $f['color'] }}-500 text-xs w-4"></i>
                            <span class="text-body font-medium">{{ $f['name'] }}</span>
                        </div>
                        <span class="font-bold text-{{ $f['color'] }}-600 dark:text-{{ $f['color'] }}-400">{{ $f['weight'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full bg-{{ $f['color'] }}-500 group-hover:opacity-80 transition-all duration-500" 
                             style="width: {{ $f['weight'] * 4 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- System Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                        <i class="fas fa-server text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading">System Info</h3>
                        <p class="text-xs text-muted">Service configuration details</p>
                    </div>
                </div>
            </div>
            
            <div class="p-5">
                @php
                    $infoRows = [
                        ['label' => 'AI Service URL', 'value' => $aiStatus['url'] ?? 'N/A', 'icon' => 'fa-link', 'color' => 'blue', 'isMono' => true],
                        ['label' => 'Status', 'value' => $isActive ? 'Connected' : 'Fallback', 'icon' => 'fa-circle', 'color' => $isActive ? 'green' : 'yellow', 'isStatus' => true],
                        ['label' => 'Model Version', 'value' => 'v1.0.0', 'icon' => 'fa-code-branch', 'color' => 'purple'],
                        ['label' => 'Algorithm', 'value' => 'XGBoost Regressor', 'icon' => 'fa-microchip', 'color' => 'orange'],
                        ['label' => 'Training Schedule', 'value' => 'Daily 00:00 & 12:00', 'icon' => 'fa-clock', 'color' => 'pink'],
                        ['label' => 'Last Checked', 'value' => \Carbon\Carbon::parse($aiStatus['checked_at'] ?? now())->diffForHumans(), 'icon' => 'fa-rotate', 'color' => 'teal'],
                    ];
                @endphp
                
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($infoRows as $row)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0 group hover:bg-gray-50 dark:hover:bg-gray-700/30 -mx-2 px-2 rounded-lg transition-colors">
                        <div class="flex items-center gap-2.5 text-sm text-muted">
                            <i class="fas {{ $row['icon'] }} text-{{ $row['color'] }}-500 text-xs w-4"></i>
                            {{ $row['label'] }}
                        </div>
                        @if(isset($row['isStatus']) && $row['isStatus'])
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-green-500 animate-pulse' : 'bg-yellow-500' }}"></span>
                            {{ $row['value'] }}
                        </span>
                        @elseif(isset($row['isMono']) && $row['isMono'])
                        <span class="text-xs font-mono font-bold text-heading bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-lg max-w-[180px] truncate">
                            {{ $row['value'] }}
                        </span>
                        @else
                        <span class="text-sm font-bold text-heading">{{ $row['value'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    
</div>
@endsection
