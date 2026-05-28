@extends('layouts.app')

@section('title', 'City Details')
@section('user_role', 'Administrator')
@section('page_title', 'City Details')
@section('page_subtitle', 'Detailed analytics for ' . $city->name)

@section('content')
<div>
    @php
        $onlineCleaners = App\Models\Cleaner::where('city_id', $city->id)
            ->where('availability_status', 'online')->count();
        $verifiedCleaners = App\Models\Cleaner::where('city_id', $city->id)
            ->where('is_verified', true)->count();
        $avgRating = App\Models\Cleaner::where('city_id', $city->id)->avg('rating') ?? 0;
        
        $topCleaners = App\Models\Cleaner::with('user')
            ->where('city_id', $city->id)
            ->orderByDesc('rating')
            ->limit(10)->get();
            
        $recentBookings = App\Models\Booking::with(['service', 'cleaner.user', 'homeowner.user'])
            ->where('city_id', $city->id)
            ->latest()->limit(15)->get();
            
        $monthlyStats = App\Models\Booking::where('city_id', $city->id)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total, SUM(total_amount) as revenue, SUM(commission_amount) as commission")
            ->groupBy('month')->orderBy('month', 'desc')->limit(12)->get();
    @endphp

    {{-- ============================================ --}}
    {{-- BACK BUTTON --}}
    {{-- ============================================ --}}
    <a href="/admin/cities" class="inline-flex items-center gap-2 px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 text-body rounded-xl font-semibold text-sm hover:border-blue-300 hover:text-blue-600 dark:hover:border-blue-500 dark:hover:text-blue-400 transition-all duration-300 mb-6 group">
        <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i> 
        Back to Cities
    </a>

    {{-- ============================================ --}}
    {{-- CITY HEADER --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-16 h-16 bg-linear-to-br from-blue-400 via-purple-400 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-purple-500/25 flex-shrink-0">
                {{ strtoupper(substr($city->code, 0, 2)) }}
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-black text-heading tracking-tight">{{ $city->name }}</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                        {{ $city->is_active ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20' : 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $city->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $city->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-muted mt-1">{{ $city->region }}</p>
                
                {{-- Quick Stats Row --}}
                <div class="flex flex-wrap items-center gap-4 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-1.5 text-sm text-muted">
                        <i class="fas fa-map-marker-alt text-red-400"></i>
                        <span class="font-medium text-body">Code:</span> {{ strtoupper($city->code) }}
                    </div>
                    @if($city->latitude && $city->longitude)
                    <div class="flex items-center gap-1.5 text-sm text-muted">
                        <i class="fas fa-globe text-blue-400"></i>
                        <span class="font-medium text-body">GPS:</span> {{ number_format($city->latitude, 4) }}, {{ number_format($city->longitude, 4) }}
                    </div>
                    @endif
                </div>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <button class="px-4 py-2.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-semibold hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all duration-300">
                    <i class="fas fa-edit mr-1.5"></i> Edit
                </button>
                <button class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-body rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300">
                    <i class="fas fa-download mr-1.5"></i> Export
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATS CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 lg:gap-4 mb-6">
        {{-- Total Cleaners --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $city->cleaners_count }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Cleaners</p>
        </div>

        {{-- Online Now --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 relative group-hover:scale-110 transition-transform">
                <i class="fas fa-wifi text-green-600 dark:text-green-400"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></span>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $onlineCleaners }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Online Now</p>
        </div>

        {{-- Verified --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-shield-halved text-purple-600 dark:text-purple-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $verifiedCleaners }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Verified</p>
        </div>

        {{-- Avg Rating --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ number_format($avgRating, 1) }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Avg Rating</p>
            {{-- Star visualization --}}
            <div class="flex items-center justify-center gap-0.5 mt-1 text-yellow-500">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-[10px] {{ $i <= round($avgRating) ? '' : 'text-gray-300 dark:text-gray-600' }}"></i>
                @endfor
            </div>
        </div>

        {{-- Total Bookings --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-orange-100 to-red-200 dark:from-orange-900/40 dark:to-red-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-check text-orange-600 dark:text-orange-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $city->bookings_count }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Bookings</p>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- ONLINE CLEANERS PROGRESS --}}
    {{-- ============================================ --}}
    @php
        $onlinePercent = $city->cleaners_count > 0 ? round(($onlineCleaners / max($city->cleaners_count, 1)) * 100) : 0;
        $verifiedPercent = $city->cleaners_count > 0 ? round(($verifiedCleaners / max($city->cleaners_count, 1)) * 100) : 0;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-wifi text-green-500"></i>
                    <span class="text-sm font-semibold text-heading">Online Cleaners</span>
                </div>
                <span class="text-sm font-bold {{ $onlinePercent > 50 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                    {{ $onlinePercent }}%
                </span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-green-400 to-emerald-500 transition-all duration-700" 
                     style="width: {{ $onlinePercent }}%"></div>
            </div>
            <p class="text-xs text-muted mt-2">{{ $onlineCleaners }} of {{ $city->cleaners_count }} cleaners online</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-halved text-purple-500"></i>
                    <span class="text-sm font-semibold text-heading">Verified Cleaners</span>
                </div>
                <span class="text-sm font-bold {{ $verifiedPercent > 70 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                    {{ $verifiedPercent }}%
                </span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-purple-400 to-purple-600 transition-all duration-700" 
                     style="width: {{ $verifiedPercent }}%"></div>
            </div>
            <p class="text-xs text-muted mt-2">{{ $verifiedCleaners }} of {{ $city->cleaners_count }} cleaners verified</p>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TOP CLEANERS & RECENT BOOKINGS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        {{-- Top Cleaners --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-yellow-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-500/25">
                        <i class="fas fa-medal text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading text-lg">Top Cleaners</h3>
                        <p class="text-xs text-muted">Highest rated in {{ $city->name }}</p>
                    </div>
                </div>
            </div>
            
            @if($topCleaners->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($topCleaners as $index => $c)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                    <div class="flex items-center gap-3">
                        {{-- Rank Badge --}}
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0
                            @if($index === 0) bg-linear-to-br from-yellow-400 to-yellow-600 text-white shadow-md
                            @elseif($index === 1) bg-linear-to-br from-gray-300 to-gray-500 text-white shadow-md
                            @elseif($index === 2) bg-linear-to-br from-orange-300 to-orange-500 text-white shadow-md
                            @else bg-gray-100 dark:bg-gray-700 text-muted @endif">
                            {{ $index + 1 }}
                        </div>
                        
                        {{-- Cleaner Info --}}
                        <div>
                            <p class="font-semibold text-heading text-sm group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $c->user->full_name }}
                            </p>
                            @if($c->availability_status === 'online')
                            <span class="inline-flex items-center gap-1 text-[11px] text-green-600 dark:text-green-400 font-medium">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Online
                            </span>
                            @else
                            <span class="text-[11px] text-muted">Offline</span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Rating --}}
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-0.5 text-yellow-500">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-[10px] {{ $i <= round($c->rating) ? '' : 'text-gray-300 dark:text-gray-600' }}"></i>
                            @endfor
                        </div>
                        <span class="text-sm font-bold text-heading">{{ number_format($c->rating, 1) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-gray-400 dark:text-gray-500 text-2xl"></i>
                </div>
                <p class="text-muted text-sm">No cleaners registered in this city yet</p>
            </div>
            @endif
        </div>

        {{-- Recent Bookings --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <i class="fas fa-clock-rotate-left text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading text-lg">Recent Bookings</h3>
                        <p class="text-xs text-muted">Latest activity in {{ $city->name }}</p>
                    </div>
                </div>
            </div>
            
            @if($recentBookings->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($recentBookings->take(10) as $b)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg flex-shrink-0">
                            #{{ $b->booking_number }}
                        </span>
                        <span class="text-sm font-medium text-heading truncate">
                            {{ $b->service->name ?? 'N/A' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="text-xs text-muted hidden sm:inline">
                            {{ $b->created_at->diffForHumans() }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold
                            @switch($b->status)
                                @case('completed')
                                    bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20
                                    @break
                                @case('in_progress')
                                    bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20
                                    @break
                                @case('pending')
                                    bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-500/20
                                    @break
                                @case('cancelled')
                                    bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20
                                    @break
                                @default
                                    bg-gray-100 dark:bg-gray-500/10 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-500/20
                            @endswitch">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                @switch($b->status)
                                    @case('completed') bg-green-500 @break
                                    @case('in_progress') bg-blue-500 @break
                                    @case('pending') bg-yellow-500 @break
                                    @case('cancelled') bg-red-500 @break
                                    @default bg-gray-500
                                @endswitch"></span>
                            {{ ucfirst($b->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-calendar-xmark text-gray-400 dark:text-gray-500 text-2xl"></i>
                </div>
                <p class="text-muted text-sm">No bookings in this city yet</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MONTHLY STATS (if data exists) --}}
    {{-- ============================================ --}}
    @if($monthlyStats->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mt-6">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-linear-to-br from-emerald-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/25">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-heading text-lg">Monthly Overview</h3>
                    <p class="text-xs text-muted">Last 12 months of booking activity</p>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Month</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-muted uppercase tracking-wider">Bookings</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-muted uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-muted uppercase tracking-wider">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($monthlyStats as $stat)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-6 py-3.5">
                            <span class="text-sm font-semibold text-heading">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $stat->month)->format('M Y') }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $stat->total }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="text-sm font-semibold text-heading">TZS {{ number_format($stat->revenue, 0) }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">TZS {{ number_format($stat->commission, 0) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
