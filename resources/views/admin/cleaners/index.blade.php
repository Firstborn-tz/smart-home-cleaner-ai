@extends('layouts.app')

@section('title', 'All Cleaners')
@section('user_role', 'Administrator')
@section('page_title', 'All Cleaners')
@section('page_subtitle', 'Manage all registered cleaners')

@section('content')
<div x-data="cleanersManager()">
    @php
        $search = request('search', '');
        $status = request('status', '');
        $city = request('city', '');
        $verified = request('verified', '');
        $sort = request('sort', 'rating');
        
        $query = App\Models\Cleaner::with(['user', 'city'])
            ->when($search, function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('cleaner_id', 'like', "%{$search}%");
            })
            ->when($status, fn($q) => $q->where('availability_status', $status))
            ->when($city, fn($q) => $q->where('city_id', $city))
            ->when($verified !== '', fn($q) => $q->where('is_verified', $verified === 'yes'));
        
        switch($sort) {
            case 'name': $query->join('users', 'cleaners.user_id', '=', 'users.id')->orderBy('users.first_name')->select('cleaners.*'); break;
            case 'jobs': $query->orderByDesc('total_completed_jobs'); break;
            case 'earnings': $query->orderByDesc('total_earnings'); break;
            case 'newest': $query->latest(); break;
            default: $query->orderByDesc('rating');
        }
        
        $cleaners = $query->paginate(15)->appends(request()->query());
        
        $allCities = App\Models\City::where('is_active', true)->orderBy('name')->get();
        
        $totalCleaners = App\Models\Cleaner::count();
        $onlineNow = App\Models\Cleaner::where('availability_status', 'online')->count();
        $verifiedCount = App\Models\Cleaner::where('is_verified', true)->count();
        $pendingCount = App\Models\Cleaner::where('is_verified', false)->count();
        $avgRating = App\Models\Cleaner::avg('rating') ?? 0;
    @endphp

    {{-- ============================================ --}}
    {{-- STATS CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 lg:gap-4 mb-6">
        {{-- Total Cleaners --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $totalCleaners }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Total</p>
        </div>

        {{-- Online Now --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 relative group-hover:scale-110 transition-transform">
                <i class="fas fa-wifi text-green-600 dark:text-green-400"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800 animate-pulse"></span>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $onlineNow }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Online Now</p>
        </div>

        {{-- Verified --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-shield-halved text-purple-600 dark:text-purple-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $verifiedCount }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Verified</p>
        </div>

        {{-- Unverified --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $pendingCount }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Unverified</p>
        </div>

        {{-- Avg Rating --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-orange-100 to-amber-200 dark:from-orange-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-star text-orange-600 dark:text-orange-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ number_format($avgRating, 1) }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Avg Rating</p>
            <div class="flex items-center justify-center gap-0.5 mt-1 text-yellow-500">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-[9px] {{ $i <= round($avgRating) ? '' : 'text-gray-300 dark:text-gray-600' }}"></i>
                @endfor
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- SEARCH & FILTERS --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[220px]">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-muted"></i>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search by name, email, phone, ID..."
                       class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium placeholder:text-muted focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
            </div>
            
            {{-- Status Filter --}}
            <div class="relative">
                <i class="fas fa-circle-dot absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="status" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Status</option>
                    <option value="online" {{ $status === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="online_busy" {{ $status === 'online_busy' ? 'selected' : '' }}>Busy</option>
                    <option value="offline" {{ $status === 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="scheduled_only" {{ $status === 'scheduled_only' ? 'selected' : '' }}>Scheduled Only</option>
                </select>
            </div>
            
            {{-- City Filter --}}
            <div class="relative">
                <i class="fas fa-city absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="city" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Cities</option>
                    @foreach($allCities as $c)
                    <option value="{{ $c->id }}" {{ $city == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- Verification Filter --}}
            <div class="relative">
                <i class="fas fa-shield-halved absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="verified" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Verification</option>
                    <option value="yes" {{ $verified === 'yes' ? 'selected' : '' }}>Verified</option>
                    <option value="no" {{ $verified === 'no' ? 'selected' : '' }}>Unverified</option>
                </select>
            </div>
            
            {{-- Sort --}}
            <div class="relative">
                <i class="fas fa-sort-amount-down absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="sort" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Top Rated</option>
                    <option value="jobs" {{ $sort === 'jobs' ? 'selected' : '' }}>Most Jobs</option>
                    <option value="earnings" {{ $sort === 'earnings' ? 'selected' : '' }}>Highest Earnings</option>
                    <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name A-Z</option>
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                </select>
            </div>
            
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 transition-all duration-300">
                <i class="fas fa-filter mr-1.5"></i> Apply Filters
            </button>
            
            @if($search || $status || $city || $verified !== '' || $sort !== 'rating')
            <a href="/admin/cleaners" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-300">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- ============================================ --}}
    {{-- CLEANERS TABLE --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Cleaner</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">City</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Rating</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Jobs</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Earnings</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Verified</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($cleaners as $cleaner)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        {{-- Cleaner Info --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($cleaner->user->full_name) }}&background=3b82f6&color=fff&size=40&bold=true" 
                                     class="w-10 h-10 rounded-xl ring-2 ring-blue-100 dark:ring-blue-500/20 flex-shrink-0">
                                <div>
                                    <p class="font-bold text-sm text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $cleaner->user->full_name }}
                                    </p>
                                    <p class="text-xs text-muted">{{ $cleaner->user->email }}</p>
                                    <span class="inline-flex items-center mt-0.5 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-mono text-muted">
                                        {{ $cleaner->cleaner_id }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        
                        {{-- City --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5 text-sm text-body">
                                <i class="fas fa-map-marker-alt text-red-400 text-xs"></i>
                                {{ $cleaner->city->name ?? 'N/A' }}
                            </div>
                        </td>
                        
                        {{-- Status Badge --}}
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusColors = [
                                    'online' => ['bg' => 'bg-green-100 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-500/20'],
                                    'online_busy' => ['bg' => 'bg-yellow-100 dark:bg-yellow-500/10', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-500/20'],
                                    'scheduled_only' => ['bg' => 'bg-blue-100 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-500/20'],
                                    'offline' => ['bg' => 'bg-gray-100 dark:bg-gray-500/10', 'text' => 'text-gray-600 dark:text-gray-400', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200 dark:border-gray-500/20'],
                                ];
                                $s = $statusColors[$cleaner->availability_status] ?? $statusColors['offline'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $s['bg'] }} {{ $s['text'] }} border {{ $s['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $s['dot'] }} {{ $cleaner->availability_status === 'online' ? 'animate-pulse' : '' }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $cleaner->availability_status)) }}
                            </span>
                        </td>
                        
                        {{-- Rating --}}
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <i class="fas fa-star text-yellow-500 text-xs"></i>
                                <span class="font-bold text-sm text-heading">{{ number_format($cleaner->rating, 1) }}</span>
                            </div>
                        </td>
                        
                        {{-- Jobs --}}
                        <td class="px-5 py-4 text-center">
                            <span class="font-bold text-sm text-heading">{{ $cleaner->total_completed_jobs }}</span>
                            <div class="mt-0.5">
                                @php $completionRate = number_format($cleaner->completion_rate, 0); @endphp
                                <div class="w-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full {{ $completionRate >= 90 ? 'bg-green-500' : ($completionRate >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                         style="width: {{ $completionRate }}%"></div>
                                </div>
                                <span class="text-[10px] text-muted mt-0.5">{{ $completionRate }}%</span>
                            </div>
                        </td>
                        
                        {{-- Earnings --}}
                        <td class="px-5 py-4 text-center">
                            <span class="font-bold text-sm text-green-600 dark:text-green-400">TZS {{ number_format($cleaner->total_earnings, 0) }}</span>
                            <p class="text-[10px] text-muted">Payout: TZS {{ number_format($cleaner->pending_payout, 0) }}</p>
                        </td>
                        
                        {{-- Verified --}}
                        <td class="px-5 py-4 text-center">
                            @if($cleaner->is_verified)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                                <i class="fas fa-check-circle mr-1 text-xs"></i> Verified
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-500/20">
                                <i class="fas fa-clock mr-1 text-xs"></i> Pending
                            </span>
                            @endif
                        </td>
                        
                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="/admin/cleaners/{{ $cleaner->id }}" 
                                   class="w-9 h-9 flex items-center justify-center bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all duration-200"
                                   title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="/cleaner/{{ $cleaner->id }}/profile" target="_blank"
                                   class="w-9 h-9 flex items-center justify-center bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-500/20 transition-all duration-200"
                                   title="Public Profile">
                                    <i class="fas fa-external-link-alt text-sm"></i>
                                </a>
                                @if(!$cleaner->is_verified)
                                <form method="POST" action="/admin/cleaners/{{ $cleaner->id }}/approve" class="inline" onsubmit="return confirm('Approve this cleaner?')">
                                    @csrf
                                    <button type="submit" 
                                            class="w-9 h-9 flex items-center justify-center bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 rounded-xl hover:bg-green-100 dark:hover:bg-green-500/20 transition-all duration-200"
                                            title="Approve">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                </form>
                                @endif
                                <button @click="toggleStatus({{ $cleaner->id }}, '{{ $cleaner->availability_status }}')"
                                        class="w-9 h-9 flex items-center justify-center bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-xl hover:bg-yellow-100 dark:hover:bg-yellow-500/20 transition-all duration-200"
                                        title="Toggle Status">
                                    <i class="fas fa-power-off text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-20 text-center">
                            <div class="w-20 h-20 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-search text-gray-400 dark:text-gray-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-heading mb-2">No Cleaners Found</h3>
                            <p class="text-muted text-sm">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($cleaners->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            {{ $cleaners->links() }}
        </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- BOTTOM STATS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        @php
            $totalEarnings = App\Models\Cleaner::sum('total_earnings');
            $totalJobs = App\Models\Cleaner::sum('total_completed_jobs');
            $avgCompletion = App\Models\Cleaner::avg('completion_rate') ?? 0;
            $totalPayouts = App\Models\Cleaner::sum('pending_payout');
        @endphp
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center">
                    <i class="fas fa-coins text-green-600 dark:text-green-400"></i>
                </div>
                <p class="text-xs text-muted font-medium uppercase tracking-wider">Total Earnings</p>
            </div>
            <p class="text-xl font-black text-heading">TZS {{ number_format($totalEarnings, 0) }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                    <i class="fas fa-briefcase text-blue-600 dark:text-blue-400"></i>
                </div>
                <p class="text-xs text-muted font-medium uppercase tracking-wider">Total Jobs Done</p>
            </div>
            <p class="text-xl font-black text-heading">{{ number_format($totalJobs) }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 dark:text-purple-400"></i>
                </div>
                <p class="text-xs text-muted font-medium uppercase tracking-wider">Avg Completion</p>
            </div>
            <p class="text-xl font-black text-heading">{{ number_format($avgCompletion, 1) }}%</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-linear-to-br from-orange-100 to-amber-200 dark:from-orange-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center">
                    <i class="fas fa-wallet text-orange-600 dark:text-orange-400"></i>
                </div>
                <p class="text-xs text-muted font-medium uppercase tracking-wider">Pending Payouts</p>
            </div>
            <p class="text-xl font-black text-heading">TZS {{ number_format($totalPayouts, 0) }}</p>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-x-6" 
         x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-x-0" 
         x-transition:leave-end="opacity-0 translate-x-6" 
         class="fixed top-6 right-6 z-[9999] px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-sm font-semibold text-white"
         :class="toast.type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'"
         style="display: none;">
        <i class="fas text-lg" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function cleanersManager() {
        return {
            toast: { show: false, message: '', type: 'success' },

            async toggleStatus(cleanerId, currentStatus) {
                const newStatus = currentStatus === 'online' ? 'offline' : 'online';
                
                try {
                    const res = await fetch(`/admin/cleaners/${cleanerId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    
                    const data = await res.json();
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        this.showToast(data.message || 'Failed to update status', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                }
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 3500);
            }
        };
    }
</script>
@endpush
