@extends('layouts.app')

@section('title', 'Registration Requests')
@section('user_role', 'Administrator')
@section('page_title', 'Registration Requests')
@section('page_subtitle', 'Review and manage cleaner applications')

@section('content')
<div x-data="registrationManager()">
    @php
        $sort = request('sort', 'newest');
        $cityFilter = request('city', '');
        $genderFilter = request('gender', '');
        $tab = request('tab', 'pending');
        
        $pendingQuery = App\Models\Cleaner::with(['user', 'city'])
            ->where('is_verified', false)->where('registration_status', '!=', 'rejected');
        
        if ($cityFilter) $pendingQuery->where('city_id', $cityFilter);
        if ($genderFilter) $pendingQuery->where('gender', $genderFilter);
        
        switch($sort) {
            case 'oldest': $pendingQuery->oldest(); break;
            case 'name_asc': $pendingQuery->join('users', 'cleaners.user_id', '=', 'users.id')->orderBy('users.first_name', 'asc')->select('cleaners.*'); break;
            case 'name_desc': $pendingQuery->join('users', 'cleaners.user_id', '=', 'users.id')->orderBy('users.first_name', 'desc')->select('cleaners.*'); break;
            default: $pendingQuery->latest();
        }
        
        $pendingCleaners = $pendingQuery->get();
        $approvedCleaners = App\Models\Cleaner::with(['user', 'city'])->where('is_verified', true)->latest()->limit(30)->get();
        $rejectedCleaners = App\Models\Cleaner::with(['user'])->where('registration_status', 'rejected')->latest()->limit(20)->get();
        
        $allCities = App\Models\City::where('is_active', true)->orderBy('name')->get();
        $approvalRate = App\Models\Cleaner::count() > 0 ? round(($approvedCleaners->count() / App\Models\Cleaner::count()) * 100) : 0;
    @endphp

    {{-- ============================================ --}}
    {{-- TAB STATS CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 lg:gap-4 mb-6">
        <a href="?tab=pending" class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 transition-all duration-300 p-4 text-center card-hover-lift {{ $tab === 'pending' ? 'border-yellow-500 shadow-yellow-500/10' : 'border-transparent hover:border-yellow-200 dark:hover:border-yellow-500/30' }}">
            @if($tab === 'pending')<div class="absolute -top-1 left-1/2 -translate-x-1/2 w-12 h-1 bg-yellow-500 rounded-b-full"></div>@endif
            <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $pendingCleaners->count() }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Pending</p>
            @if($pendingCleaners->count() > 0)<span class="inline-block mt-2 w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>@endif
        </a>

        <a href="?tab=approved" class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 transition-all duration-300 p-4 text-center card-hover-lift {{ $tab === 'approved' ? 'border-green-500 shadow-green-500/10' : 'border-transparent hover:border-green-200 dark:hover:border-green-500/30' }}">
            @if($tab === 'approved')<div class="absolute -top-1 left-1/2 -translate-x-1/2 w-12 h-1 bg-green-500 rounded-b-full"></div>@endif
            <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $approvedCleaners->count() }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Approved</p>
        </a>

        <a href="?tab=rejected" class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 transition-all duration-300 p-4 text-center card-hover-lift {{ $tab === 'rejected' ? 'border-red-500 shadow-red-500/10' : 'border-transparent hover:border-red-200 dark:hover:border-red-500/30' }}">
            @if($tab === 'rejected')<div class="absolute -top-1 left-1/2 -translate-x-1/2 w-12 h-1 bg-red-500 rounded-b-full"></div>@endif
            <div class="w-10 h-10 bg-linear-to-br from-red-100 to-rose-200 dark:from-red-900/40 dark:to-rose-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $rejectedCleaners->count() }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Rejected</p>
        </a>

        <a href="/admin/cleaners" class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-transparent hover:border-blue-200 dark:hover:border-blue-500/30 transition-all duration-300 p-4 text-center card-hover-lift">
            <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ App\Models\Cleaner::count() }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">All Cleaners</p>
        </a>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-transparent transition-all duration-300 p-4 text-center card-hover-lift">
            <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-pie text-purple-600 dark:text-purple-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $approvalRate }}%</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Approval Rate</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-full rounded-full transition-all duration-700" style="width: {{ $approvalRate }}%"></div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILTERS --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="relative">
                <i class="fas fa-sort-amount-down absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="sort" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                    <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                </select>
            </div>

            <div class="relative">
                <i class="fas fa-city absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="city" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Cities</option>
                    @foreach($allCities as $city)
                    <option value="{{ $city->id }}" {{ $cityFilter == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative">
                <i class="fas fa-venus-mars absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="gender" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Genders</option>
                    <option value="male" {{ $genderFilter === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $genderFilter === 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 transition-all duration-300">
                <i class="fas fa-filter mr-1.5"></i> Apply Filters
            </button>

            @if($sort !== 'newest' || $cityFilter || $genderFilter)
            <a href="?tab={{ $tab }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-300">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- ============================================ --}}
    {{-- PENDING TAB --}}
    {{-- ============================================ --}}
    @if($tab === 'pending')
        @if($pendingCleaners->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
            @foreach($pendingCleaners as $cleaner)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden card-hover-lift group">
                
                {{-- Card Header --}}
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-transparent dark:from-yellow-500/5 dark:to-transparent">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($cleaner->user->full_name) }}&background=f59e0b&color=fff&size=48&bold=true" 
                                 class="w-12 h-12 rounded-xl ring-2 ring-yellow-200 dark:ring-yellow-500/30 flex-shrink-0">
                            <div>
                                <h3 class="font-bold text-heading">{{ $cleaner->user->full_name }}</h3>
                                <p class="text-xs text-muted">{{ $cleaner->user->email }}</p>
                                <p class="text-xs text-muted">{{ $cleaner->user->phone }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1.5 bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 rounded-full text-xs font-bold border border-yellow-200 dark:border-yellow-500/20 flex-shrink-0">
                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span> Pending
                        </span>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 space-y-3">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 transition-all">
                            <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">Gender</p>
                            <p class="text-sm font-bold text-heading">{{ ucfirst($cleaner->gender ?? 'N/A') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-green-50 dark:group-hover:bg-green-900/20 transition-all">
                            <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">City</p>
                            <p class="text-sm font-bold text-heading">{{ $cleaner->city->name ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-purple-50 dark:group-hover:bg-purple-900/20 transition-all">
                            <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">NIDA</p>
                            <p class="text-xs font-bold text-heading truncate">{{ $cleaner->national_id ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($cleaner->street || $cleaner->ward)
                    <div class="bg-blue-50 dark:bg-blue-500/10 rounded-xl p-3 text-xs text-body border border-blue-100 dark:border-blue-500/10">
                        <i class="fas fa-map-marker-alt text-red-400 mr-1.5"></i>
                        {{ $cleaner->street ?? '' }}, {{ $cleaner->ward ?? '' }}, {{ $cleaner->city->name ?? '' }}, {{ $cleaner->region ?? '' }}
                    </div>
                    @endif

                    <p class="text-xs text-muted flex items-center gap-1.5">
                        <i class="fas fa-calendar text-gray-400"></i> Applied {{ $cleaner->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Card Footer --}}
                <div class="p-5 pt-0 flex gap-2">
                    <button @click="showDetailModal({{ $cleaner->id }})" 
                            class="flex-1 px-4 py-3 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-xl font-bold text-xs hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all duration-300">
                        <i class="fas fa-eye mr-1.5"></i> Details
                    </button>
                    <button @click="approveCleaner({{ $cleaner->id }})" 
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-[1.02] transition-all duration-300">
                        <i class="fas fa-check mr-1.5"></i> Approve
                    </button>
                    <button @click="rejectCleaner({{ $cleaner->id }})" 
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-[1.02] transition-all duration-300">
                        <i class="fas fa-times mr-1.5"></i> Reject
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-16 text-center">
            <div class="w-20 h-20 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-green-600 dark:text-green-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-heading mb-2">All Caught Up!</h3>
            <p class="text-muted">No pending cleaner applications to review</p>
        </div>
        @endif
    @endif

    {{-- ============================================ --}}
    {{-- APPROVED TAB --}}
    {{-- ============================================ --}}
    @if($tab === 'approved')
        @if($approvedCleaners->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Cleaner</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">City</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Rating</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Jobs</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($approvedCleaners as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($c->user->full_name) }}&background=22c55e&color=fff&size=36&bold=true" 
                                         class="w-9 h-9 rounded-xl ring-2 ring-green-200 dark:ring-green-500/20">
                                    <div>
                                        <p class="font-semibold text-heading text-sm">{{ $c->user->full_name }}</p>
                                        <p class="text-[11px] text-muted font-mono">{{ $c->cleaner_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-body">{{ $c->city->name ?? 'N/A' }}</td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-star text-yellow-500 text-xs"></i>
                                    <span class="text-sm font-bold text-heading">{{ number_format($c->rating, 1) }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $c->total_completed_jobs }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Active
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="/admin/cleaners/{{ $c->id }}" class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-xl text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all">
                                    View <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-16 text-center">
            <div class="w-20 h-20 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-check text-blue-600 dark:text-blue-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-heading mb-2">No Approved Cleaners</h3>
            <p class="text-muted">No cleaners have been approved yet</p>
        </div>
        @endif
    @endif

    {{-- ============================================ --}}
    {{-- REJECTED TAB --}}
    {{-- ============================================ --}}
    @if($tab === 'rejected')
        @if($rejectedCleaners->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Cleaner</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Reason</th>
                            <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($rejectedCleaners as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($c->user->full_name) }}&background=ef4444&color=fff&size=36&bold=true" 
                                         class="w-9 h-9 rounded-xl ring-2 ring-red-200 dark:ring-red-500/20 opacity-60">
                                    <div>
                                        <p class="font-semibold text-heading text-sm">{{ $c->user->full_name }}</p>
                                        <p class="text-[11px] text-muted">{{ $c->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                                    <i class="fas fa-comment-alt text-xs"></i>
                                    <span>{{ $c->registration_notes ?? 'No reason given' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right text-sm text-muted">{{ $c->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-16 text-center">
            <div class="w-20 h-20 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-slash text-gray-400 dark:text-gray-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-heading mb-2">No Rejected Cleaners</h3>
            <p class="text-muted">No cleaners have been rejected</p>
        </div>
        @endif
    @endif

    {{-- Detail Modal --}}
    <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="modalOpen = false" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto m-4 animate-slide-up border border-gray-100 dark:border-gray-700" @click.stop>
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center"><i class="fas fa-user text-white"></i></div>
                    <h3 class="font-bold text-heading text-lg">Cleaner Details</h3>
                </div>
                <button @click="modalOpen = false" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"><i class="fas fa-times text-muted"></i></button>
            </div>
            <div class="p-6" x-html="modalContent"></div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-6" class="fixed top-6 right-6 z-[9999] px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-sm font-semibold text-white" :class="toast.type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'" style="display: none;">
        <i class="fas text-lg" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function registrationManager() {
        return {
            modalOpen: false,
            modalContent: '',
            toast: { show: false, message: '', type: 'success' },

            async showDetailModal(cleanerId) {
                this.modalOpen = true;
                this.modalContent = `<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i><span class="ml-3 text-muted">Loading...</span></div>`;
                
                try {
                    const res = await fetch(`/admin/cleaners/${cleanerId}/details`);
                    const data = await res.json();
                    if (data.success) {
                        const c = data.cleaner;
                        this.modalContent = `
                            <div class="space-y-5">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl">
                                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.user.full_name)}&background=3b82f6&color=fff&size=56&bold=true" class="w-14 h-14 rounded-xl ring-2 ring-blue-200">
                                    <div><h3 class="font-bold text-heading text-lg">${c.user.full_name}</h3><p class="text-sm text-muted">${c.user.email}</p><p class="text-sm text-muted">${c.user.phone}</p></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center"><p class="text-[10px] text-muted uppercase mb-1">Gender</p><p class="text-sm font-bold text-heading">${c.gender || 'N/A'}</p></div>
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center"><p class="text-[10px] text-muted uppercase mb-1">DOB</p><p class="text-sm font-bold text-heading">${c.date_of_birth || 'N/A'}</p></div>
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center"><p class="text-[10px] text-muted uppercase mb-1">NIDA</p><p class="text-xs font-bold text-heading">${c.national_id || 'N/A'}</p></div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                                    <p class="text-[10px] text-muted uppercase mb-1">Address</p>
                                    <p class="text-sm font-bold text-heading">${c.street || ''}, ${c.ward || ''}, ${c.city?.name || 'N/A'}, ${c.region || ''}</p>
                                </div>
                                <div class="flex gap-2 pt-2">
                                    <button @click="modalOpen = false; approveCleaner(${c.id})" class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] transition-all">Approve</button>
                                    <button @click="modalOpen = false; rejectCleaner(${c.id})" class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] transition-all">Reject</button>
                                </div>
                            </div>`;
                    }
                } catch (e) {
                    this.modalContent = `<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-red-400 text-3xl mb-3"></i><p class="text-red-500 font-medium">Failed to load details</p></div>`;
                }
            },

            async approveCleaner(id) {
                if (!confirm('Approve this cleaner? They will be able to login and accept bookings.')) return;
                try {
                    const res = await fetch(`/admin/cleaners/${id}/approve`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    this.showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) setTimeout(() => location.href = '/admin/cleaner-requests?tab=approved', 1500);
                } catch (e) { this.showToast('Network error', 'error'); }
            },

            async rejectCleaner(id) {
                const reason = prompt('Reason for rejection:');
                if (!reason) return;
                if (!confirm(`Reject this cleaner?\n\nReason: ${reason}`)) return;
                try {
                    const res = await fetch(`/admin/cleaners/${id}/reject`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ reason })
                    });
                    const data = await res.json();
                    this.showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) setTimeout(() => location.href = '/admin/cleaner-requests?tab=rejected', 1500);
                } catch (e) { this.showToast('Network error', 'error'); }
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 3500);
            }
        };
    }
</script>
<style>@keyframes slide-up{from{opacity:0;transform:translateY(30px) scale(0.95)}to{opacity:1;transform:translateY(0) scale(1)}}.animate-slide-up{animation:slide-up 0.3s cubic-bezier(0.16,1,0.3,1)}</style>
@endpush
