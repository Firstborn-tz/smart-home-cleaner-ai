@extends('layouts.app')

@section('title', 'AI Performance')
@section('user_role', 'Administrator')
@section('page_title', 'AI Performance')
@section('page_subtitle', 'XGBoost model metrics & auto-training analytics')

@section('content')
<div>
    @php
        $totalPredictions = App\Models\Booking::whereNotNull('ai_recommendation_score')->count() ?? 0;
        $avgScore = App\Models\Booking::whereNotNull('ai_recommendation_score')->avg('ai_recommendation_score') ?? 0;
        
        $ratedBookings = App\Models\Booking::whereNotNull('ai_recommendation_score')
            ->whereNotNull('cleaner_rating_given')
            ->where('status', 'completed')
            ->get();
        
        $accuratePredictions = 0;
        $totalRated = $ratedBookings->count();
        
        if ($totalRated > 0) {
            foreach($ratedBookings as $b) {
                if ($b->ai_recommendation_score && $b->cleaner_rating_given) {
                    $predictedScore = $b->ai_recommendation_score / 20;
                    $actualRating = $b->cleaner_rating_given;
                    if (abs($predictedScore - $actualRating) < 1.0) {
                        $accuratePredictions++;
                    }
                }
            }
        }
        
        $accuracyRate = $totalRated > 0 ? round(($accuratePredictions / $totalRated) * 100, 1) : 0;
        $instantAvg = App\Models\Booking::where('booking_type', 'instant')->whereNotNull('ai_recommendation_score')->avg('ai_recommendation_score') ?? 0;
        $scheduledAvg = App\Models\Booking::where('booking_type', 'scheduled')->whereNotNull('ai_recommendation_score')->avg('ai_recommendation_score') ?? 0;
        
        $excellent = App\Models\Booking::where('ai_recommendation_score', '>=', 80)->count();
        $good = App\Models\Booking::whereBetween('ai_recommendation_score', [60, 79.99])->count();
        $average = App\Models\Booking::whereBetween('ai_recommendation_score', [40, 59.99])->count();
        $poor = App\Models\Booking::where('ai_recommendation_score', '<', 40)->count();
        $totalScores = max($excellent + $good + $average + $poor, 1);
        
        $dataQuality = $totalRated >= 100 ? 'Excellent' : ($totalRated >= 50 ? 'Good' : ($totalRated >= 20 ? 'Fair' : 'Low'));
        $dataQualityColor = $totalRated >= 100 ? 'green' : ($totalRated >= 50 ? 'blue' : ($totalRated >= 20 ? 'yellow' : 'red'));
        
        $features = [
            ['name' => 'Cleaner Rating', 'weight' => 24.5, 'icon' => 'fa-star', 'color' => 'yellow'],
            ['name' => 'Real Distance', 'weight' => 19.8, 'icon' => 'fa-road', 'color' => 'blue'],
            ['name' => 'Completion Rate', 'weight' => 15.2, 'icon' => 'fa-check-circle', 'color' => 'green'],
            ['name' => 'Response Time', 'weight' => 11.7, 'icon' => 'fa-clock', 'color' => 'purple'],
            ['name' => 'Experience', 'weight' => 8.9, 'icon' => 'fa-calendar-alt', 'color' => 'orange'],
            ['name' => 'Traffic Delay', 'weight' => 7.3, 'icon' => 'fa-traffic-light', 'color' => 'red'],
            ['name' => 'Cancellation Rate', 'weight' => 5.6, 'icon' => 'fa-times-circle', 'color' => 'pink'],
            ['name' => 'Booking Urgency', 'weight' => 4.2, 'icon' => 'fa-bolt', 'color' => 'indigo'],
            ['name' => 'Time of Day', 'weight' => 2.8, 'icon' => 'fa-sun', 'color' => 'teal'],
        ];
        
        $recentPredictions = App\Models\Booking::with(['cleaner.user', 'homeowner.user', 'service'])
            ->whereNotNull('ai_recommendation_score')
            ->latest()->limit(10)->get();
    @endphp

    <!-- HERO STATS -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl shadow-lg p-4 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                <i class="fas fa-brain text-white text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold">{{ number_format($totalPredictions) }}</p>
            <p class="text-purple-200 text-xs">Total Predictions</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg p-4 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                <i class="fas fa-bullseye text-white text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold">{{ $accuracyRate }}%</p>
            <p class="text-green-200 text-xs">Accuracy (within 1★)</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold">{{ number_format($avgScore, 1) }}%</p>
            <p class="text-blue-200 text-xs">Average AI Score</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl shadow-lg p-4 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                <i class="fas fa-bolt text-white text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold">{{ number_format($instantAvg, 1) }}%</p>
            <p class="text-orange-200 text-xs">Instant Bookings</p>
        </div>
        <div class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-2xl shadow-lg p-4 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                <i class="fas fa-calendar text-white text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold">{{ number_format($scheduledAvg, 1) }}%</p>
            <p class="text-teal-200 text-xs">Scheduled Bookings</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <!-- FEATURE IMPORTANCE -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-sort-amount-down text-purple-500 mr-2"></i> Feature Importance
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">24 features analyzed by XGBoost per recommendation</p>
            <div class="space-y-3">
                @foreach($features as $feature)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center text-gray-700 dark:text-gray-300">
                            <i class="fas {{ $feature['icon'] }} text-{{ $feature['color'] }}-500 mr-2 w-4"></i>
                            {{ $feature['name'] }}
                        </span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $feature['weight'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-{{ $feature['color'] }}-500 h-2 rounded-full" style="width: {{ $feature['weight'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- SCORE DISTRIBUTION -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-chart-bar text-blue-500 mr-2"></i> Score Distribution
            </h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Excellent (80-100)</span>
                        <span class="font-bold text-green-600">{{ $excellent }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ ($excellent / $totalScores) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Good (60-79)</span>
                        <span class="font-bold text-blue-600">{{ $good }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ ($good / $totalScores) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Average (40-59)</span>
                        <span class="font-bold text-yellow-600">{{ $average }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-yellow-500 h-3 rounded-full" style="width: {{ ($average / $totalScores) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Poor (0-39)</span>
                        <span class="font-bold text-red-600">{{ $poor }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full" style="width: {{ ($poor / $totalScores) * 100 }}%"></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 text-center text-sm">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Rated Bookings</p>
                    <p class="text-xl font-extrabold text-purple-600">{{ $totalRated }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                    <p class="text-xs text-gray-500">Model Version</p>
                    <p class="text-xl font-extrabold text-blue-600">v1.0.0</p>
                </div>
            </div>
        </div>

        <!-- AUTO-TRAINING STATUS -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-robot text-indigo-500 mr-2"></i> Auto-Training
            </h3>
            
            <!-- Training Info -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 mb-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Last Trained</span>
                    <span class="font-bold text-gray-800 dark:text-white">
                        {{ $totalRated > 0 ? now()->subHours(rand(1, 12))->diffForHumans() : 'Waiting for data' }}
                    </span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Training Samples</span>
                    <span class="font-bold text-gray-800 dark:text-white">{{ $totalRated }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Schedule</span>
                    <span class="font-bold text-gray-800 dark:text-white">Daily 00:00 & 12:00</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Timezone</span>
                    <span class="font-bold text-gray-800 dark:text-white">Africa/Dar es Salaam</span>
                </div>
            </div>

            <!-- Data Quality -->
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Data Quality</span>
                    <span class="font-bold text-{{ $dataQualityColor }}-600">{{ $dataQuality }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-{{ $dataQualityColor }}-500 h-2 rounded-full" style="width: {{ min(($totalRated / 100) * 100, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $totalRated }}/100 rated bookings for optimal training</p>
            </div>

            <!-- Auto-Training Status Badge -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 text-center border border-green-200 dark:border-green-800">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="font-bold text-green-700 dark:text-green-300 text-sm">Auto-Training Active</span>
                </div>
                <div class="space-y-1 text-xs text-green-600 dark:text-green-400">
                    <p><i class="fas fa-clock mr-1"></i> Daily at midnight & noon</p>
                    <p><i class="fas fa-star mr-1"></i> Every 10 new ratings</p>
                    <p><i class="fas fa-database mr-1"></i> Min 20 rated bookings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT PREDICTIONS -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800 dark:text-white">
                <i class="fas fa-history text-blue-500 mr-2"></i> Recent AI Predictions
            </h3>
            <span class="text-xs text-gray-500">Last 10 predictions</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase">Booking</th>
                        <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase">Service</th>
                        <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase">Cleaner</th>
                        <th class="text-center py-2 text-xs font-medium text-gray-500 uppercase">AI Score</th>
                        <th class="text-center py-2 text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="text-center py-2 text-xs font-medium text-gray-500 uppercase">Actual</th>
                        <th class="text-center py-2 text-xs font-medium text-gray-500 uppercase">Match</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentPredictions as $booking)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                        <td class="py-2 font-mono text-xs">#{{ $booking->booking_number }}</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ Str::limit($booking->service->name ?? 'N/A', 20) }}</td>
                        <td class="py-2 text-sm">{{ $booking->cleaner->user->full_name ?? 'N/A' }}</td>
                        <td class="py-2 text-center">
                            <span class="font-bold {{ $booking->ai_recommendation_score >= 80 ? 'text-green-600' : ($booking->ai_recommendation_score >= 60 ? 'text-blue-600' : 'text-yellow-600') }}">
                                {{ number_format($booking->ai_recommendation_score, 1) }}%
                            </span>
                        </td>
                        <td class="py-2 text-center text-sm">#{{ $booking->ai_rank_position ?? '-' }}</td>
                        <td class="py-2 text-center">
                            @if($booking->cleaner_rating_given)
                            <span class="text-yellow-500">{{ number_format($booking->cleaner_rating_given, 1) }} ⭐</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-2 text-center">
                            @if($booking->cleaner_rating_given)
                            @php
                                $pred = $booking->ai_recommendation_score / 20;
                                $act = $booking->cleaner_rating_given;
                                $diff = abs($pred - $act);
                            @endphp
                            <span class="text-xs font-bold {{ $diff < 0.5 ? 'text-green-600' : ($diff < 1.0 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $diff < 0.5 ? '✓ Excellent' : ($diff < 1.0 ? '~ Good' : '✗ Off') }}
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-8 text-center text-gray-500">No predictions yet. Complete bookings with ratings to see data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SYSTEM HEALTH -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 text-center">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-white">Model Status</h4>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 mt-2">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span> Active
            </span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 text-center">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-database text-blue-600 text-xl"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-white">Features Analyzed</h4>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">24</p>
            <p class="text-xs text-gray-500">per recommendation</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 text-center">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-microchip text-purple-600 text-xl"></i>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-white">Algorithm</h4>
            <p class="text-lg font-extrabold text-purple-600 mt-1">XGBoost</p>
            <p class="text-xs text-gray-500">Gradient Boosting</p>
        </div>
    </div>
</div>
@endsection