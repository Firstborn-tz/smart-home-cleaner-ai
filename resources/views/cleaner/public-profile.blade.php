@extends('layouts.app')

@section('title', $cleaner->business_name ?? $cleaner->user->full_name)
@section('user_role', 'Cleaner Profile')
@section('page_title', 'Cleaner Profile')
@section('page_subtitle', 'View details and reviews')

@section('content')
<div>
    @php
        $businessName = $cleaner->business_name ?? $cleaner->user->full_name;
        $reviews = $cleaner->reviews()->with('reviewer.user')->approved()->latest()->limit(20)->get();
        $avgRating = $cleaner->rating ?? 0;
        $totalReviews = $cleaner->reviews()->count();
        $portfolioImages = is_string($cleaner->portfolio_images ?? '') ? json_decode($cleaner->portfolio_images, true) ?? [] : ($cleaner->portfolio_images ?? []);
        $certifications = is_string($cleaner->certifications ?? '') ? json_decode($cleaner->certifications, true) ?? [] : ($cleaner->certifications ?? []);
        $languages = is_string($cleaner->languages ?? '') ? json_decode($cleaner->languages, true) ?? [] : ($cleaner->languages ?? []);
    @endphp

    {{-- ============================================ --}}
    {{-- BACK BUTTON --}}
    {{-- ============================================ --}}
    <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 text-body rounded-xl font-semibold text-sm hover:border-blue-300 hover:text-blue-600 dark:hover:border-blue-500 dark:hover:text-blue-400 transition-all duration-300 mb-6 group">
        <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i> Back
    </a>

    {{-- ============================================ --}}
    {{-- COVER PHOTO --}}
    {{-- ============================================ --}}
    <div class="relative h-48 sm:h-56 md:h-64 rounded-3xl overflow-hidden mb-6 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 shadow-xl group">
        @if($cleaner->cover_photo)
        <img src="{{ $cleaner->cover_photo }}" class="w-full h-full object-cover" alt="Cover photo">
        @else
        {{-- Pattern fallback --}}
        <div class="absolute inset-0 opacity-15">
            <svg width="100%" height="100%">
                <defs><pattern id="coverPat" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="2" fill="white"/></pattern></defs>
                <rect width="100%" height="100%" fill="url(#coverPat)"/>
            </svg>
        </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
        
        {{-- Profile Picture (Overlapping) --}}
        <div class="absolute -bottom-12 left-6 sm:left-8">
            <img src="{{ $cleaner->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($cleaner->user->full_name) . '&background=3b82f6&color=fff&bold=true&size=120' }}" 
                 class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-[4px] border-white dark:border-gray-800 shadow-2xl object-cover ring-2 ring-gray-200/50 dark:ring-gray-700/50"
                 alt="{{ $businessName }}">
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- PROFILE HEADER --}}
    {{-- ============================================ --}}
    <div class="ml-32 sm:ml-36 mb-6 mt-14 sm:mt-16">
        <h2 class="text-2xl sm:text-3xl font-black text-heading tracking-tight">{{ $businessName }}</h2>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2 text-sm">
            {{-- Stars --}}
            <div class="flex items-center gap-1.5 text-yellow-500">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-300 dark:text-gray-600' }} text-sm"></i>
                @endfor
                <span class="font-bold text-heading ml-1">{{ number_format($avgRating, 1) }}</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span class="text-muted"><i class="fas fa-comment mr-1"></i> {{ $totalReviews }} reviews</span>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span class="text-muted"><i class="fas fa-check-circle text-green-500 mr-1"></i> {{ $cleaner->total_completed_jobs }} jobs done</span>
        </div>
        
        {{-- Badges Row --}}
        <div class="flex flex-wrap items-center gap-2 mt-3">
            @if($cleaner->is_verified)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                <i class="fas fa-shield-halved mr-1"></i> Verified
            </span>
            @endif
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20">
                <i class="fas fa-map-marker-alt mr-1"></i> {{ $cleaner->city->name ?? 'N/A' }}
            </span>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MAIN GRID --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- LEFT: MAIN CONTENT --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- About --}}
            @if($cleaner->business_description)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <h3 class="font-bold text-heading text-lg">About</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-body leading-relaxed text-sm">{{ $cleaner->business_description }}</p>
                </div>
            </div>
            @endif

            {{-- Portfolio --}}
            @if(!empty($portfolioImages))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-pink-100 to-rose-200 dark:from-pink-900/40 dark:to-rose-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-images text-pink-600 dark:text-pink-400"></i>
                        </div>
                        <h3 class="font-bold text-heading text-lg">Portfolio</h3>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($portfolioImages as $img)
                        <div class="rounded-xl overflow-hidden aspect-square bg-gray-100 dark:bg-gray-700 shadow-md group cursor-pointer">
                            <img src="{{ $img }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="Portfolio image" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Reviews --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading text-lg">Reviews</h3>
                            <p class="text-xs text-muted">{{ $totalReviews }} total</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    @if($reviews->count() > 0)
                    <div class="space-y-5">
                        @foreach($reviews as $review)
                        <div class="pb-5 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <div class="flex items-start gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($review->reviewer->user->full_name ?? 'User') }}&background=3b82f6&color=fff&size=40&bold=true" 
                                     class="w-10 h-10 rounded-xl ring-2 ring-blue-100 dark:ring-blue-500/20 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-bold text-sm text-heading truncate">{{ $review->reviewer->user->full_name ?? 'Anonymous' }}</p>
                                        <span class="text-xs text-muted flex-shrink-0">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center gap-0.5 text-yellow-500 mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-[10px] {{ $i <= $review->rating ? '' : 'text-gray-200 dark:text-gray-600' }}"></i>
                                        @endfor
                                    </div>
                                    @if($review->body)
                                    <p class="text-body text-sm mt-2 leading-relaxed">{{ $review->body }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-10">
                        <div class="w-14 h-14 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-star text-gray-300 dark:text-gray-500 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-heading">No Reviews Yet</h4>
                        <p class="text-sm text-muted mt-1">Be the first to review this cleaner</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: SIDEBAR --}}
        <div class="space-y-5">
            
            {{-- Quick Stats --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-bar text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <h3 class="font-bold text-heading">Quick Stats</h3>
                    </div>
                </div>
                <div class="p-5 divide-y divide-gray-100 dark:divide-gray-700">
                    @php
                        $sidebarStats = [
                            ['icon' => 'fa-check-circle', 'color' => 'green', 'label' => 'Jobs Done', 'value' => $cleaner->total_completed_jobs],
                            ['icon' => 'fa-chart-line', 'color' => 'green', 'label' => 'Completion Rate', 'value' => number_format($cleaner->completion_rate, 0) . '%'],
                            ['icon' => 'fa-calendar', 'color' => 'blue', 'label' => 'Experience', 'value' => $cleaner->experience_days_active . ' days'],
                            ['icon' => 'fa-clock', 'color' => 'orange', 'label' => 'Response Time', 'value' => round($cleaner->avg_response_time_seconds / 60, 1) . ' min'],
                            ['icon' => 'fa-map-marker-alt', 'color' => 'red', 'label' => 'Location', 'value' => $cleaner->city->name ?? 'N/A'],
                        ];
                    @endphp
                    @foreach($sidebarStats as $stat)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-2.5 text-sm text-muted">
                            <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-500 w-4 text-xs"></i>
                            {{ $stat['label'] }}
                        </div>
                        <span class="text-sm font-bold text-heading">{{ $stat['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Certifications --}}
            @if(!empty($certifications))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-certificate text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <h3 class="font-bold text-heading">Certifications</h3>
                    </div>
                </div>
                <div class="p-5">
                    <ul class="space-y-2.5">
                        @foreach($certifications as $cert)
                        <li class="flex items-center gap-2.5 text-sm text-body">
                            <div class="w-6 h-6 bg-green-50 dark:bg-green-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-green-500 text-xs"></i>
                            </div>
                            {{ $cert }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Languages --}}
            @if(!empty($languages))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-language text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <h3 class="font-bold text-heading">Languages</h3>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex flex-wrap gap-2">
                        @foreach($languages as $lang)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/20">
                            {{ $lang }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Contact --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-phone text-green-600 dark:text-green-400"></i>
                        </div>
                        <h3 class="font-bold text-heading">Contact</h3>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    @if($cleaner->business_phone)
                    <a href="tel:{{ $cleaner->business_phone }}" 
                       class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-xl font-semibold text-sm hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all duration-300">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-phone text-blue-500 text-xs"></i>
                        </div>
                        {{ $cleaner->business_phone }}
                    </a>
                    @endif
                    @if($cleaner->business_email)
                    <a href="mailto:{{ $cleaner->business_email }}" 
                       class="flex items-center gap-3 px-4 py-3 bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 rounded-xl font-semibold text-sm hover:bg-purple-100 dark:hover:bg-purple-500/20 transition-all duration-300">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope text-purple-500 text-xs"></i>
                        </div>
                        {{ $cleaner->business_email }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
