@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('user_role', 'Administrator')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Real-time platform analytics')

@section('content')
<div>
    @php
        // Core metrics
        $totalCleaners = App\Models\Cleaner::count();
        $onlineCleaners = App\Models\Cleaner::where('availability_status', 'online')->count();
        $totalHomeowners = App\Models\Homeowner::count();
        $totalBookings = App\Models\Booking::count();
        $completedBookings = App\Models\Booking::where('status', 'completed')->count();
        $pendingRegistrations = App\Models\Cleaner::where('is_verified', false)->count();
        $totalServices = App\Models\Service::where('is_active', true)->count();
        
        // Revenue calculations
        $todayRevenue = App\Models\Booking::whereDate('created_at', today())->whereIn('status', ['completed', 'in_progress'])->sum('total_amount');
        $weekRevenue = App\Models\Booking::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->whereIn('status', ['completed', 'in_progress'])->sum('total_amount');
        $monthRevenue = App\Models\Booking::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereIn('status', ['completed', 'in_progress'])->sum('total_amount');
        $totalRevenue = App\Models\Booking::whereIn('status', ['completed', 'in_progress'])->sum('total_amount');
        
        // Commission
        $monthCommission = App\Models\Booking::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('commission_amount');
        
        // Booking stats
        $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100) : 0;
        $instantBookings = App\Models\Booking::where('booking_type', 'instant')->count();
        $scheduledBookings = App\Models\Booking::where('booking_type', 'scheduled')->count();
        
        // 30-day chart data
        $chartLabels = [];
        $chartRevenue = [];
        $chartBookings = [];
        $chartCommission = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartRevenue[] = App\Models\Booking::whereDate('created_at', $date)->whereIn('status', ['completed', 'in_progress'])->sum('total_amount');
            $chartBookings[] = App\Models\Booking::whereDate('created_at', $date)->count();
            $chartCommission[] = App\Models\Booking::whereDate('created_at', $date)->whereIn('status', ['completed', 'in_progress'])->sum('commission_amount');
        }
        
        // City distribution
        $cityStats = App\Models\City::withCount(['cleaners', 'bookings'])->orderByDesc('bookings_count')->limit(8)->get();
        $maxCityBookings = $cityStats->max('bookings_count') ?: 1;
        
        // Cleaner status distribution
        $onlineCount = App\Models\Cleaner::where('availability_status', 'online')->count();
        $busyCount = App\Models\Cleaner::where('availability_status', 'online_busy')->count();
        $offlineCount = App\Models\Cleaner::where('availability_status', 'offline')->count();
        $scheduledOnlyCount = App\Models\Cleaner::where('availability_status', 'scheduled_only')->count();
        
        // Recent activities
        $recentBookings = App\Models\Booking::with(['service', 'cleaner.user', 'homeowner.user'])->latest()->limit(8)->get();
        $recentCleaners = App\Models\Cleaner::with('user')->where('is_verified', false)->latest()->limit(5)->get();
        
        // Rating distribution
        $rating5 = App\Models\Cleaner::where('rating', '>=', 4.5)->count();
        $rating4 = App\Models\Cleaner::whereBetween('rating', [3.5, 4.49])->count();
        $rating3 = App\Models\Cleaner::whereBetween('rating', [2.5, 3.49])->count();
        $rating2 = App\Models\Cleaner::where('rating', '<', 2.5)->count();
        $totalRated = max($rating5 + $rating4 + $rating3 + $rating2, 1);
        
        // Top performing cleaners
        $topCleaners = App\Models\Cleaner::with('user')->orderByDesc('rating')->orderByDesc('total_completed_jobs')->limit(5)->get();
    @endphp

    <!-- ============================================ -->
    <!-- WELCOME HEADER -->
    <!-- ============================================ -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl shadow-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between">
            <div>
                <p class="text-blue-200 text-sm mb-1">{{ now()->format('l, F d, Y') }}</p>
                <h2 class="text-2xl md:text-3xl font-black">Welcome back, {{ Auth::user()->first_name }}!</h2>
                <p class="text-blue-200 mt-1">Here's what's happening with your platform today.</p>
            </div>
            <div class="flex space-x-3 mt-4 md:mt-0">
                <a href="/admin/cleaner-requests" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-bold backdrop-blur transition">
                    <i class="fas fa-user-clock mr-1"></i> {{ $pendingRegistrations }} Pending
                </a>
                <a href="/admin/cleaners" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-bold backdrop-blur transition">
                    <i class="fas fa-users mr-1"></i> View Cleaners
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- KPI CARDS ROW 1 -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <!-- Revenue -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-blue-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div class="text-right">
                    <span class="text-xs text-green-600 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full font-bold">
                        <i class="fas fa-arrow-up mr-1"></i>+{{ $weekRevenue > 0 ? round(($todayRevenue / max($weekRevenue/7, 1)) * 100) : 0 }}%
                    </span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Revenue</p>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">TZS {{ number_format($monthRevenue, 0) }}</p>
            <div class="flex justify-between text-xs mt-2">
                <span class="text-gray-500">Today: TZS {{ number_format($todayRevenue, 0) }}</span>
                <span class="text-purple-600 font-bold">Commission: TZS {{ number_format($monthCommission, 0) }}</span>
            </div>
        </div>

        <!-- Cleaners -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-green-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-users text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-white dark:border-gray-800 flex items-center justify-center text-white text-xs font-bold">{{ $onlineCount }}</div>
                    <div class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-white dark:border-gray-800 flex items-center justify-center text-white text-xs font-bold">{{ $busyCount }}</div>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Cleaners</p>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">{{ $totalCleaners }}</p>
            <div class="flex justify-between text-xs mt-2">
                <span class="text-green-600 font-bold"><i class="fas fa-circle text-xs mr-1"></i> {{ $onlineCount }} Online</span>
                <span class="text-gray-500">{{ $totalHomeowners }} Homeowners</span>
            </div>
        </div>

        <!-- Bookings -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-purple-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-1 rounded-full font-bold">
                    {{ $completionRate }}% Done
                </span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Bookings</p>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">{{ $totalBookings }}</p>
            <div class="flex justify-between text-xs mt-2">
                <span class="text-orange-600">⚡ {{ $instantBookings }} Instant</span>
                <span class="text-blue-600">📅 {{ $scheduledBookings }} Scheduled</span>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-orange-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-tools text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
                <span class="text-xs bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 px-2 py-1 rounded-full font-bold">
                    Active
                </span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Services</p>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">{{ $totalServices }}</p>
            <div class="flex justify-between text-xs mt-2">
                <span class="text-gray-500">{{ App\Models\City::where('is_active', true)->count() }} Cities</span>
                <span class="text-gray-500">⭐ {{ number_format(App\Models\Cleaner::avg('rating') ?? 0, 1) }} Avg</span>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CHARTS ROW -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- 30-Day Revenue Chart (Bar Chart) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i> 30-Day Revenue & Bookings
                </h3>
                <div class="flex items-center space-x-3 text-xs">
                    <span class="flex items-center"><span class="w-3 h-3 bg-blue-500 rounded mr-1"></span> Revenue</span>
                    <span class="flex items-center"><span class="w-3 h-3 bg-purple-500 rounded mr-1"></span> Bookings</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-[600px]">
                    <!-- Chart Area -->
                    <div class="flex items-end space-x-1 h-48 mb-2">
                        @php $maxRevenue = max(max($chartRevenue), 1); @endphp
                        @foreach($chartRevenue as $index => $value)
                        @php $height = ($value / $maxRevenue) * 100; @endphp
                        <div class="flex-1 flex flex-col items-center justify-end group relative">
                            <div class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-md hover:from-blue-600 hover:to-blue-500 transition-all cursor-pointer"
                                 style="height: {{ max($height, 2) }}%"
                                 title="TZS {{ number_format($value, 0) }}"></div>
                            @if($index % 5 == 0)
                            <span class="text-xs text-gray-500 mt-1 absolute -bottom-5">{{ $chartLabels[$index] }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="flex items-end space-x-1 h-16 mt-6">
                        @php $maxBookings = max(max($chartBookings), 1); @endphp
                        @foreach($chartBookings as $index => $value)
                        @php $height = ($value / $maxBookings) * 100; @endphp
                        <div class="flex-1 flex flex-col items-center justify-end group relative">
                            <div class="w-full bg-gradient-to-t from-purple-500 to-purple-400 rounded-t-md hover:from-purple-600 hover:to-purple-500 transition-all cursor-pointer"
                                 style="height: {{ max($height, 2) }}%"
                                 title="{{ $value }} bookings"></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-center text-sm">
                <div><p class="text-xs text-gray-500">Total Revenue</p><p class="font-extrabold text-green-600">TZS {{ number_format($totalRevenue, 0) }}</p></div>
                <div><p class="text-xs text-gray-500">Avg/Day</p><p class="font-extrabold text-blue-600">TZS {{ number_format($monthRevenue / max(now()->day, 1), 0) }}</p></div>
                <div><p class="text-xs text-gray-500">Commission</p><p class="font-extrabold text-purple-600">TZS {{ number_format($monthCommission, 0) }}</p></div>
                <div><p class="text-xs text-gray-500">Avg Booking</p><p class="font-extrabold text-orange-600">TZS {{ number_format($totalBookings > 0 ? $totalRevenue / $totalBookings : 0, 0) }}</p></div>
            </div>
        </div>

        <!-- Cleaner Status Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-chart-pie text-purple-500 mr-2"></i> Cleaner Status
            </h3>
            @php $totalStatus = max($onlineCount + $busyCount + $offlineCount + $scheduledOnlyCount, 1); @endphp
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center text-gray-600 dark:text-gray-400"><span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>Online</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $onlineCount }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ ($onlineCount / $totalStatus) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center text-gray-600 dark:text-gray-400"><span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>Busy</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $busyCount }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-yellow-500 h-3 rounded-full" style="width: {{ ($busyCount / $totalStatus) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center text-gray-600 dark:text-gray-400"><span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>Scheduled</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $scheduledOnlyCount }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ ($scheduledOnlyCount / $totalStatus) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center text-gray-600 dark:text-gray-400"><span class="w-3 h-3 bg-gray-500 rounded-full mr-2"></span>Offline</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $offlineCount }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gray-500 h-3 rounded-full" style="width: {{ ($offlineCount / $totalStatus) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Rating Distribution -->
            <h3 class="font-bold text-gray-800 dark:text-white mb-4 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <i class="fas fa-star text-yellow-500 mr-2"></i> Rating Distribution
            </h3>
            <div class="space-y-2">
                <div class="flex items-center text-sm">
                    <span class="w-8 text-gray-500">5★</span>
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mx-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($rating5 / $totalRated) * 100 }}%"></div>
                    </div>
                    <span class="w-8 text-right text-xs text-gray-500">{{ $rating5 }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-8 text-gray-500">4★</span>
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mx-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ ($rating4 / $totalRated) * 100 }}%"></div>
                    </div>
                    <span class="w-8 text-right text-xs text-gray-500">{{ $rating4 }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-8 text-gray-500">3★</span>
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mx-2">
                        <div class="bg-yellow-300 h-2 rounded-full" style="width: {{ ($rating3 / $totalRated) * 100 }}%"></div>
                    </div>
                    <span class="w-8 text-right text-xs text-gray-500">{{ $rating3 }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-8 text-gray-500">1-2★</span>
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mx-2">
                        <div class="bg-gray-400 h-2 rounded-full" style="width: {{ ($rating2 / $totalRated) * 100 }}%"></div>
                    </div>
                    <span class="w-8 text-right text-xs text-gray-500">{{ $rating2 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- BOTTOM ROW - City Distribution, Top Cleaners, Recent Bookings -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- City Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-city text-blue-500 mr-2"></i> Top Cities
            </h3>
            @foreach($cityStats as $city)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $city->name }}</span>
                    <span class="text-xs text-gray-500">{{ $city->bookings_count }} bookings | {{ $city->cleaners_count }} cleaners</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2.5 rounded-full" 
                         style="width: {{ ($city->bookings_count / $maxCityBookings) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Top Cleaners -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top Cleaners
            </h3>
            @foreach($topCleaners as $index => $tc)
            <div class="flex items-center space-x-3 py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0
                    {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : ($index === 2 ? 'bg-orange-600' : 'bg-blue-500')) }}">
                    {{ $index + 1 }}
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode($tc->user->full_name) }}&background=3b82f6&color=fff&size=28" class="w-7 h-7 rounded-lg flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-gray-800 dark:text-white truncate">{{ $tc->user->full_name }}</p>
                    <p class="text-xs text-gray-500">{{ $tc->total_completed_jobs }} jobs | {{ number_format($tc->completion_rate, 0) }}%</p>
                </div>
                <span class="text-yellow-500 font-bold text-sm">⭐ {{ number_format($tc->rating, 1) }}</span>
            </div>
            @endforeach
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-history text-blue-500 mr-2"></i> Recent Bookings
            </h3>
            @forelse($recentBookings->take(6) as $b)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 dark:text-white truncate">{{ $b->service->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $b->cleaner->user->full_name ?? 'Unassigned' }} → {{ $b->homeowner->user->full_name ?? 'N/A' }}</p>
                </div>
                <div class="text-right flex-shrink-0 ml-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold
                        @if($b->status === 'completed') bg-green-100 text-green-700
                        @elseif($b->status === 'cancelled') bg-red-100 text-red-700
                        @else bg-blue-100 text-blue-700 @endif">
                        {{ ucfirst($b->status) }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">TZS {{ number_format($b->total_amount) }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">No bookings yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection