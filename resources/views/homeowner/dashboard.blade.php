@extends('layouts.homeowner')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Manage your cleaning services')

@section('content')
<div x-data="homeownerDashboard()" x-init="init()">
    @php
        $homeowner = Auth::user()->homeowner;
        
        $activeBookings = App\Models\Booking::with(['service', 'cleaner.user', 'cleaner.city'])
            ->where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['pending', 'cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress'])
            ->latest()->get();
            
        $completedBookings = App\Models\Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->where('status', 'completed')
            ->latest()->limit(10)->get();
            
        $totalBookings = App\Models\Booking::where('homeowner_id', $homeowner->id)->count();
        $totalSpent = App\Models\Booking::where('homeowner_id', $homeowner->id)->where('status', 'completed')->sum('total_amount');
        $favoriteCleaners = $homeowner->favorite_cleaners ?? [];
        if (is_string($favoriteCleaners)) { $favoriteCleaners = json_decode($favoriteCleaners, true) ?? []; }
    @endphp

    {{-- WELCOME BANNER --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl shadow-2xl shadow-purple-500/25 p-6 sm:p-8 mb-6 text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-white/3 rounded-full"></div>
        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Welcome back, {{ Auth::user()->first_name }}! 👋</h2>
                <p class="text-white/70 mt-1.5 text-sm">Your home deserves the best care</p>
                <div class="flex items-center gap-4 mt-3">
                    <span class="flex items-center gap-1.5 text-white/80 text-sm">
                        <i class="fas fa-map-marker-alt text-red-300"></i>
                        {{ $homeowner->district ?? 'Dodoma' }}, {{ $homeowner->region ?? 'Tanzania' }}
                    </span>
                    <span class="flex items-center gap-1.5 text-white/80 text-sm">
                        <i class="fas fa-star text-yellow-300"></i>
                        {{ number_format($homeowner->rating ?? 0, 1) }} Rating
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="/homeowner/bookings/create" 
                   class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-blue-600 rounded-2xl font-bold text-sm shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <i class="fas fa-plus-circle"></i> Book a Cleaner
                </a>
                <a href="/homeowner/profile" 
                   class="inline-flex items-center gap-2 px-6 py-3.5 bg-white/10 backdrop-blur text-white rounded-2xl font-bold text-sm border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <i class="fas fa-cog"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
        @php
            $statCards = [
                ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'fa-calendar-check', 'color' => 'blue', 'gradient' => 'from-blue-400 to-blue-600'],
                ['label' => 'Active Now', 'value' => $activeBookings->count(), 'icon' => 'fa-spinner', 'color' => 'orange', 'gradient' => 'from-orange-400 to-amber-600'],
                ['label' => 'Completed', 'value' => $homeowner->total_completed_bookings ?? 0, 'icon' => 'fa-check-circle', 'color' => 'green', 'gradient' => 'from-green-400 to-emerald-600'],
                ['label' => 'Total Spent', 'value' => 'TZS ' . number_format($totalSpent, 0), 'icon' => 'fa-wallet', 'color' => 'purple', 'gradient' => 'from-purple-400 to-violet-600'],
            ];
        @endphp
        @foreach($statCards as $stat)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 sm:p-5 card-hover-lift group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] text-muted font-medium uppercase tracking-wider">{{ $stat['label'] }}</p>
                <div class="w-9 h-9 bg-gradient-to-br {{ $stat['gradient'] }} rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                    <i class="fas {{ $stat['icon'] }} text-white text-sm"></i>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-black text-heading stat-number">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- MAIN CONTENT: Active & Pending --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- ACTIVE BOOKINGS --}}
            @if($activeBookings->count() > 0)
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">Active & Pending</h3>
                    <span class="bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full text-xs font-bold">{{ $activeBookings->count() }}</span>
                </div>
                <div class="space-y-4">
                    @foreach($activeBookings as $booking)
                    @php
                        $statusConfig = [
                            'pending' => ['color' => 'yellow', 'icon' => 'fa-clock', 'label' => 'Waiting for Cleaner', 'bg' => 'bg-yellow-50 dark:bg-yellow-500/5', 'border' => 'border-yellow-400'],
                            'cleaner_assigned' => ['color' => 'orange', 'icon' => 'fa-user-check', 'label' => 'Cleaner Assigned', 'bg' => 'bg-orange-50 dark:bg-orange-500/5', 'border' => 'border-orange-400'],
                            'cleaner_accepted' => ['color' => 'purple', 'icon' => 'fa-thumbs-up', 'label' => 'Cleaner Accepted', 'bg' => 'bg-purple-50 dark:bg-purple-500/5', 'border' => 'border-purple-400'],
                            'cleaner_en_route' => ['color' => 'blue', 'icon' => 'fa-truck', 'label' => 'Cleaner En Route', 'bg' => 'bg-blue-50 dark:bg-blue-500/5', 'border' => 'border-blue-400'],
                            'cleaner_arrived' => ['color' => 'green', 'icon' => 'fa-flag-checkered', 'label' => 'Cleaner Arrived', 'bg' => 'bg-green-50 dark:bg-green-500/5', 'border' => 'border-green-400'],
                            'in_progress' => ['color' => 'teal', 'icon' => 'fa-broom', 'label' => 'Service in Progress', 'bg' => 'bg-teal-50 dark:bg-teal-500/5', 'border' => 'border-teal-400'],
                        ];
                        $sc = $statusConfig[$booking->status] ?? $statusConfig['pending'];
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-l-4 {{ $sc['border'] }} overflow-hidden card-hover-lift">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-{{ $sc['color'] }}-100 dark:bg-{{ $sc['color'] }}-500/10 text-{{ $sc['color'] }}-700 dark:text-{{ $sc['color'] }}-300">
                                            <i class="fas {{ $sc['icon'] }} mr-1"></i> {{ $sc['label'] }}
                                        </span>
                                        <span class="text-xs font-mono text-muted">#{{ $booking->booking_number }}</span>
                                    </div>
                                    <h4 class="font-bold text-heading text-lg">{{ $booking->service->name ?? 'Service' }}</h4>
                                    <div class="flex items-center gap-3 text-xs text-muted mt-1">
                                        <span><i class="fas fa-calendar mr-1"></i> {{ $booking->created_at->format('M d, Y') }}</span>
                                        <span><i class="fas fa-clock mr-1"></i> {{ $booking->created_at->format('h:i A') }}</span>
                                        @if($booking->pricing_model)
                                        <span class="font-semibold text-blue-600">{{ $booking->pricing_model === 'fixed' ? 'Fixed Block' : 'Pay As You Go' }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Timer for pending --}}
                                @if($booking->status === 'pending' && $booking->timeout_at)
                                <div class="text-right">
                                    <p class="text-[10px] text-muted uppercase">Expires in</p>
                                    <p class="text-sm font-black text-red-500">
                                        @php
                                            $secondsLeft = max(0, $booking->timeout_at->diffInSeconds(now(), false));
                                        @endphp
                                        {{ floor($secondsLeft / 60) }}:{{ str_pad($secondsLeft % 60, 2, '0', STR_PAD_LEFT) }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            @if($booking->cleaner)
                            <div class="bg-{{ $sc['color'] }}-50 dark:bg-{{ $sc['color'] }}-500/5 rounded-xl p-4 mb-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($booking->cleaner->user->full_name) }}&background=6366f1&color=fff&size=48&bold=true" 
                                                 class="w-12 h-12 rounded-xl ring-2 ring-{{ $sc['color'] }}-200 flex-shrink-0 cursor-pointer"
                                                 @click="viewCleanerProfile({{ $booking->cleaner->id }})"
                                                 title="View Cleaner Profile">
                                            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-{{ $sc['color'] }}-500 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center">
                                                <i class="fas fa-check text-white text-[8px]"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-heading text-sm cursor-pointer hover:text-indigo-600 transition-colors"
                                               @click="viewCleanerProfile({{ $booking->cleaner->id }})">
                                                {{ $booking->cleaner->user->full_name }}
                                            </p>
                                            <div class="flex items-center gap-2 text-xs text-muted">
                                                <span>⭐ {{ number_format($booking->cleaner->rating, 1) }}</span>
                                                <span>|</span>
                                                <span>{{ $booking->cleaner->total_completed_jobs }} jobs</span>
                                                <span>|</span>
                                                <span>{{ round($booking->distance_km ?? 0, 1) }} km</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="viewCleanerProfile({{ $booking->cleaner->id }})"
                                                class="px-3 py-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-all">
                                            <i class="fas fa-building mr-1"></i> Business Profile
                                        </button>
                                        <span class="text-lg font-black text-green-600">
                                            TZS {{ number_format($booking->hourly_rate ?? 0) }}/hr
                                        </span>
                                    </div>
                                </div>
                                @if($booking->status === 'cleaner_en_route' || $booking->status === 'cleaner_arrived')
                                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-{{ $sc['color'] }}-200 dark:border-{{ $sc['color'] }}-500/10">
                                    <div class="flex items-center gap-1.5 text-sm">
                                        <i class="fas fa-road text-blue-500"></i>
                                        <span class="font-semibold">{{ round($booking->distance_km ?? 0, 1) }} km</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-sm">
                                        <i class="fas fa-clock text-purple-500"></i>
                                        <span class="font-semibold">ETA: {{ round($booking->estimated_travel_time_minutes ?? 0) }} min</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif

                            {{-- Pricing Info --}}
                            <div class="flex items-center justify-between text-sm mb-3">
                                <span class="text-muted">Pricing</span>
                                @if($booking->pricing_model === 'fixed')
                                <span class="font-bold text-heading">TZS {{ number_format($booking->hourly_rate * ($booking->booked_hours ?? 1)) }} ({{ $booking->booked_hours }} hrs)</span>
                                @else
                                <span class="font-bold text-heading">TZS {{ number_format($booking->hourly_rate ?? 0) }}/hr (PAYG)</span>
                                @endif
                            </div>

                            <a href="/homeowner/bookings/{{ $booking->id }}/track" 
                               class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-{{ $sc['color'] }}-500 to-{{ $sc['color'] }}-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.01] transition-all">
                                <i class="fas fa-satellite-dish"></i> Track Service
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- COMPLETED BOOKINGS --}}
            @if($completedBookings->count() > 0)
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/25">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">Recent Completed</h3>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase">Booking</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase">Service</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase">Cleaner</th>
                                    <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase">Rating</th>
                                    <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($completedBookings as $b)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <span class="text-xs font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg">#{{ $b->booking_number }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-sm font-semibold text-heading">{{ $b->service->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($b->cleaner)
                                        <button @click="viewCleanerProfile({{ $b->cleaner->id }})" class="text-sm text-body hover:text-indigo-600 transition-colors">
                                            {{ $b->cleaner->user->full_name ?? 'N/A' }}
                                        </button>
                                        @else
                                        <span class="text-sm text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($b->cleaner_rating_given)
                                        <div class="flex items-center justify-center gap-1 text-yellow-500">
                                            <i class="fas fa-star text-xs"></i>
                                            <span class="text-sm font-bold text-heading">{{ $b->cleaner_rating_given }}</span>
                                        </div>
                                        @else
                                        <a href="/homeowner/bookings/{{ $b->id }}/track" class="text-indigo-600 text-xs font-bold hover:underline">Rate</a>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="text-sm font-bold text-green-600">TZS {{ number_format($b->total_amount) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- SIDEBAR: Cleaner Profiles & Quick Actions --}}
        <div class="space-y-6">
            
            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5">
                <h4 class="font-bold text-heading mb-4 flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-500"></i> Quick Actions
                </h4>
                <div class="space-y-2">
                    <a href="/homeowner/bookings/create" class="flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all group">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-heading">Book a Cleaner</p>
                            <p class="text-xs text-muted">AI-matched professionals</p>
                        </div>
                    </a>
                    <a href="/homeowner/bookings" class="flex items-center gap-3 p-3 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-500/5 transition-all group">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-list text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-heading">My Bookings</p>
                            <p class="text-xs text-muted">View all bookings</p>
                        </div>
                    </a>
                    <a href="/homeowner/profile" class="flex items-center gap-3 p-3 rounded-xl hover:bg-green-50 dark:hover:bg-green-500/5 transition-all group">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-500/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-edit text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-heading">Edit Profile</p>
                            <p class="text-xs text-muted">Update your details</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Favorite Cleaners --}}
            @if(count($favoriteCleaners) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5">
                <h4 class="font-bold text-heading mb-4 flex items-center gap-2">
                    <i class="fas fa-heart text-red-500"></i> Favorite Cleaners
                </h4>
                <div class="space-y-3">
                    @foreach($favoriteCleaners as $favId)
                        @php $fc = App\Models\Cleaner::with('user')->find($favId); @endphp
                        @if($fc)
                        <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-all"
                             @click="viewCleanerProfile({{ $fc->id }})">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($fc->user->full_name) }}&background=ef4444&color=fff&size=40&bold=true" class="w-10 h-10 rounded-xl flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-heading truncate">{{ $fc->user->full_name }}</p>
                                <p class="text-xs text-muted">⭐ {{ number_format($fc->rating, 1) }} | {{ $fc->total_completed_jobs }} jobs</p>
                            </div>
                            <i class="fas fa-chevron-right text-muted text-xs"></i>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- CLEANER BUSINESS PROFILE MODAL --}}
    <div x-show="showCleanerModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
         @click.self="showCleanerModal = false" 
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl m-4 max-h-[90vh] overflow-y-auto" @click.stop>
            <template x-if="cleanerProfile">
                <div>
                    {{-- Header --}}
                    <div class="relative bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-t-3xl p-6 sm:p-8 text-white">
                        <button @click="showCleanerModal = false" class="absolute top-4 right-4 w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="flex items-center gap-4">
                            <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(cleanerProfile.name) + '&background=fff&color=6366f1&size=72&bold=true'" 
                                 class="w-18 h-18 rounded-2xl ring-4 ring-white/30">
                            <div>
                                <h3 class="text-xl font-black" x-text="cleanerProfile.name"></h3>
                                <p class="text-white/70 text-sm" x-text="cleanerProfile.cleaner_id_number"></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-yellow-300">⭐</span>
                                    <span class="font-bold" x-text="cleanerProfile.rating"></span>
                                    <span class="text-white/50">|</span>
                                    <span x-text="cleanerProfile.completed_jobs + ' jobs'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-5">
                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-blue-50 dark:bg-blue-500/5 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-muted uppercase">Completion</p>
                                <p class="text-lg font-black text-blue-600" x-text="cleanerProfile.completion_rate + '%'"></p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-500/5 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-muted uppercase">Experience</p>
                                <p class="text-lg font-black text-green-600" x-text="cleanerProfile.experience_days + ' days'"></p>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-500/5 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-muted uppercase">Response</p>
                                <p class="text-lg font-black text-purple-600" x-text="(cleanerProfile.avg_response_time_seconds / 60).toFixed(1) + ' min'"></p>
                            </div>
                        </div>

                        {{-- Business Info --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h4 class="font-bold text-heading text-sm mb-3"><i class="fas fa-building mr-2 text-indigo-500"></i>Business Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted">Business Name</span>
                                    <span class="font-bold text-heading" x-text="cleanerProfile.business_name || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted">Experience</span>
                                    <span class="font-bold text-heading" x-text="(cleanerProfile.years_experience || 0) + ' years'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted">Team Size</span>
                                    <span class="font-bold text-heading" x-text="cleanerProfile.team_size || 1"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted">Location</span>
                                    <span class="font-bold text-heading" x-text="cleanerProfile.district + ', ' + cleanerProfile.region"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Services & Pricing --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h4 class="font-bold text-heading text-sm mb-3"><i class="fas fa-tag mr-2 text-green-500"></i>Services & Pricing</h4>
                            <div class="space-y-2">
                                <template x-for="(price, serviceId) in cleanerProfile.custom_prices" :key="serviceId">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted" x-text="getServiceName(serviceId)"></span>
                                        <span class="font-bold text-green-600">TZS <span x-text="formatNumber(price)"></span>/hr</span>
                                    </div>
                                </template>
                                <div x-show="!cleanerProfile.custom_prices || Object.keys(cleanerProfile.custom_prices).length === 0" class="text-sm text-muted text-center py-2">
                                    No pricing set
                                </div>
                            </div>
                        </div>

                        {{-- Skills --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h4 class="font-bold text-heading text-sm mb-3"><i class="fas fa-tools mr-2 text-orange-500"></i>Skills</h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="skill in (cleanerProfile.service_skills || [])" :key="skill">
                                    <span class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-medium"
                                          x-text="getServiceName(skill)"></span>
                                </template>
                                <span x-show="!cleanerProfile.service_skills || cleanerProfile.service_skills.length === 0" class="text-sm text-muted">No skills listed</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-3 pt-2">
                            <button @click="showCleanerModal = false" 
                                    class="flex-1 px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl font-bold text-sm">
                                Close
                            </button>
                            <a :href="'/homeowner/bookings/create?cleaner=' + cleanerProfile.id"
                               class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm text-center">
                                <i class="fas fa-paper-plane mr-1.5"></i> Book This Cleaner
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- EMPTY STATE --}}
    @if($activeBookings->count() == 0 && $completedBookings->count() == 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-16 text-center mt-6">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-200 dark:from-blue-900/40 dark:to-purple-800/40 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-home text-blue-500 dark:text-blue-400 text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-heading mb-3">Welcome to SmartClean!</h3>
        <p class="text-muted max-w-md mx-auto mb-6">Your home deserves professional care. Book your first cleaning service today.</p>
        <a href="/homeowner/bookings/create" 
           class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-base shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <i class="fas fa-plus-circle"></i> Book Your First Cleaner
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function homeownerDashboard() {
        return {
            showCleanerModal: false,
            cleanerProfile: null,
            servicesMap: {},

            init() {
                this.fetchServices();
            },

            async fetchServices() {
                try {
                    const res = await fetch('/homeowner/services/list');
                    const data = await res.json();
                    if (data.services) {
                        data.services.forEach(s => { this.servicesMap[s.id] = s.name; });
                    }
                } catch (e) {}
            },

            getServiceName(serviceId) {
                return this.servicesMap[serviceId] || 'Service #' + serviceId;
            },

            async viewCleanerProfile(cleanerId) {
                try {
                    const res = await fetch(`/cleaner/${cleanerId}/profile/data`);
                    const data = await res.json();
                    if (data.success) {
                        this.cleanerProfile = data.cleaner;
                        this.showCleanerModal = true;
                    }
                } catch (e) {
                    console.error('Failed to load cleaner profile:', e);
                }
            },

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num || 0);
            }
        };
    }
</script>
@endpush