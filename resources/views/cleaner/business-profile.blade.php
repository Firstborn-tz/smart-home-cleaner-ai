@extends('layouts.app')

@section('title', 'My Business Profile')
@section('user_role', 'Cleaner')
@section('page_title', 'Business Profile')
@section('page_subtitle', 'Showcase your cleaning business to attract customers')

@section('content')
<div x-data="businessProfile()">
    @php
        $cleaner = Auth::user()->cleaner;
        $user = Auth::user();
        
        $businessName = $cleaner->business_name ?? '';
        $businessDescription = $cleaner->business_description ?? '';
        $businessPhone = $cleaner->business_phone ?? $user->phone;
        $businessEmail = $cleaner->business_email ?? $user->email;
        $teamSize = $cleaner->team_size ?? 1;
        $languages = $cleaner->languages ?? [];
        if (is_string($languages)) { $languages = json_decode($languages, true) ?? []; }
        $certifications = $cleaner->certifications ?? [];
        if (is_string($certifications)) { $certifications = json_decode($certifications, true) ?? []; }
        $portfolioImages = $cleaner->portfolio_images ?? [];
        if (is_string($portfolioImages)) { $portfolioImages = json_decode($portfolioImages, true) ?? []; }
        $serviceAreas = $cleaner->service_areas ?? [];
        if (is_string($serviceAreas)) { $serviceAreas = json_decode($serviceAreas, true) ?? []; }
    @endphp

    {{-- ============================================ --}}
    {{-- COVER PHOTO SECTION --}}
    {{-- ============================================ --}}
    <div class="relative mb-6">
        <div class="h-48 sm:h-56 md:h-64 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-3xl shadow-xl overflow-hidden relative group" id="coverPhoto">
            @if($cleaner->cover_photo)
            <img src="{{ $cleaner->cover_photo }}" class="w-full h-full object-cover" id="coverImage">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            
            {{-- Cover Photo Pattern (if no image) --}}
            @if(!$cleaner->cover_photo)
            <div class="absolute inset-0 opacity-10">
                <svg width="100%" height="100%">
                    <defs>
                        <pattern id="coverPattern" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                            <circle cx="30" cy="30" r="2" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#coverPattern)"/>
                </svg>
            </div>
            @endif
            
            <button onclick="document.getElementById('coverUpload').click()" 
                    class="absolute bottom-4 right-4 px-5 py-2.5 bg-white/90 hover:bg-white text-gray-700 rounded-xl font-semibold text-sm transition-all shadow-lg opacity-0 group-hover:opacity-100">
                <i class="fas fa-camera mr-2"></i> Change Cover
            </button>
            <input type="file" id="coverUpload" class="hidden" accept="image/*" onchange="uploadCover(event)">
        </div>
        
        {{-- Profile Picture (overlapping) --}}
        <div class="absolute -bottom-12 left-6 sm:left-8">
            <div class="relative group">
                <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=3b82f6&color=fff&bold=true&size=120' }}" 
                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-[4px] border-white dark:border-gray-800 shadow-2xl object-cover ring-2 ring-gray-200/50 dark:ring-gray-700/50" 
                     id="profileImg">
                <button onclick="document.getElementById('avatarUpload').click()" 
                        class="absolute -bottom-1.5 -right-1.5 w-9 h-9 bg-linear-to-br from-blue-500 to-purple-600 text-white rounded-xl flex items-center justify-center text-sm shadow-lg hover:scale-110 transition-all opacity-0 group-hover:opacity-100">
                    <i class="fas fa-camera text-xs"></i>
                </button>
                <input type="file" id="avatarUpload" class="hidden" accept="image/*" onchange="uploadAvatar(event)">
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- BUSINESS HEADER CARD --}}
    {{-- ============================================ --}}
    <div class="mt-14 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 sm:p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black text-heading tracking-tight" id="displayBusinessName">
                    {{ $businessName ?: $user->full_name }}
                </h2>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2 text-sm">
                    <div class="flex items-center gap-1.5 text-yellow-500">
                        <i class="fas fa-star"></i>
                        <span class="font-bold text-heading">{{ number_format($cleaner->rating ?? 0, 1) }}</span>
                    </div>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span class="text-muted">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i> {{ $cleaner->total_completed_jobs ?? 0 }} jobs
                    </span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span class="text-muted">
                        <i class="fas fa-map-marker-alt text-red-400 mr-1"></i> {{ $cleaner->city->name ?? 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                    <i class="fas fa-shield-halved mr-1.5"></i> Verified
                </span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20">
                    <i class="fas fa-medal mr-1.5"></i> {{ $cleaner->experience_days_active ?? 0 }} Days
                </span>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MAIN GRID --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- LEFT: EDIT FORMS --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Business Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading text-lg">Business Information</h3>
                            <p class="text-xs text-muted">Tell customers about your business</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <i class="fas fa-store text-blue-500 mr-1.5"></i> Business Name
                        </label>
                        <input type="text" id="businessName" value="{{ $businessName }}" 
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                               placeholder="e.g., Sparkle Clean Services">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <i class="fas fa-align-left text-purple-500 mr-1.5"></i> Description
                        </label>
                        <textarea id="businessDescription" rows="4" 
                                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                                  placeholder="Describe your cleaning business, specialties, and what makes you unique...">{{ $businessDescription }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-phone text-green-500 mr-1.5"></i> Business Phone
                            </label>
                            <input type="tel" id="businessPhone" value="{{ $businessPhone }}" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                                   placeholder="+255 7XX XXX XXX">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-envelope text-orange-500 mr-1.5"></i> Business Email
                            </label>
                            <input type="email" id="businessEmail" value="{{ $businessEmail }}" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                                   placeholder="business@email.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-users text-indigo-500 mr-1.5"></i> Team Size
                            </label>
                            <input type="number" id="teamSize" value="{{ $teamSize }}" min="1" max="100" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-chart-line text-emerald-500 mr-1.5"></i> Completion Rate
                            </label>
                            <input type="text" value="{{ number_format($cleaner->completion_rate ?? 0, 1) }}%" disabled 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-muted text-sm font-medium cursor-not-allowed">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Languages --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-language text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading text-lg">Languages Spoken</h3>
                            <p class="text-xs text-muted">Select all that apply</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @php $allLanguages = ['Swahili', 'English', 'French', 'Arabic', 'Hindi', 'Chinese', 'Spanish', 'Portuguese']; @endphp
                        @foreach($allLanguages as $lang)
                        <label class="flex items-center gap-2.5 p-3 rounded-xl cursor-pointer transition-all duration-200 border-2 
                                      {{ in_array($lang, $languages) ? 'border-purple-500 bg-purple-50 dark:bg-purple-500/10' : 'border-gray-200 dark:border-gray-600 hover:border-purple-300 dark:hover:border-purple-500/30' }}">
                            <input type="checkbox" value="{{ $lang }}" class="language-checkbox rounded-lg w-4 h-4 text-purple-600 focus:ring-purple-500" {{ in_array($lang, $languages) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-heading">{{ $lang }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Certifications --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-certificate text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading text-lg">Certifications</h3>
                            <p class="text-xs text-muted">Add your professional qualifications</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div id="certificationsList" class="space-y-3">
                        @forelse($certifications as $cert)
                        <div class="flex items-center gap-2 animate-slide-up">
                            <div class="relative flex-1">
                                <i class="fas fa-award absolute left-4 top-1/2 -translate-y-1/2 text-yellow-500 text-sm"></i>
                                <input type="text" value="{{ $cert }}" class="cert-input w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300" placeholder="e.g., Professional Cleaning Certificate">
                            </div>
                            <button onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex-shrink-0">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @empty
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <i class="fas fa-award absolute left-4 top-1/2 -translate-y-1/2 text-yellow-500 text-sm"></i>
                                <input type="text" class="cert-input w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300" placeholder="e.g., Professional Cleaning Certificate">
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <button onclick="addCertification()" 
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl font-semibold text-sm transition-all">
                        <i class="fas fa-plus-circle"></i> Add Certification
                    </button>
                </div>
            </div>

            {{-- Service Areas --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-map-marked-alt text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading text-lg">Service Areas</h3>
                            <p class="text-xs text-muted">Areas you cover within your city</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div id="serviceAreasList" class="space-y-3">
                        @forelse($serviceAreas as $area)
                        <div class="flex items-center gap-2 animate-slide-up">
                            <div class="relative flex-1">
                                <i class="fas fa-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-red-500 text-sm"></i>
                                <input type="text" value="{{ $area }}" class="area-input w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300" placeholder="e.g., Kinondoni, Masaki">
                            </div>
                            <button onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex-shrink-0">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @empty
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <i class="fas fa-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-red-500 text-sm"></i>
                                <input type="text" class="area-input w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300" placeholder="e.g., Kinondoni, Masaki">
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <button onclick="addServiceArea()" 
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-500/10 rounded-xl font-semibold text-sm transition-all">
                        <i class="fas fa-plus-circle"></i> Add Service Area
                    </button>
                </div>
            </div>

            {{-- Save Button --}}
            <button onclick="saveBusinessProfile()" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white rounded-2xl font-bold text-base shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.01] transition-all duration-300">
                <i class="fas fa-save mr-2"></i> Save Business Profile
            </button>
        </div>

        {{-- RIGHT: SIDEBAR --}}
        <div class="space-y-5">
            
            {{-- Portfolio Gallery --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-pink-100 to-rose-200 dark:from-pink-900/40 dark:to-rose-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-images text-pink-600 dark:text-pink-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading">Portfolio</h3>
                            <p class="text-xs text-muted">Showcase your best work</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3" id="portfolioGrid">
                        @foreach($portfolioImages as $img)
                        <div class="relative group rounded-xl overflow-hidden aspect-square bg-gray-100 dark:bg-gray-700 shadow-md">
                            <img src="{{ $img }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all"></div>
                            <button onclick="this.parentElement.remove()" 
                                    class="absolute top-2 right-2 w-7 h-7 bg-red-500 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all text-xs shadow-lg">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                        <label class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl aspect-square flex flex-col items-center justify-center cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-all hover:bg-blue-50 dark:hover:bg-blue-500/5 group">
                            <i class="fas fa-plus text-gray-400 dark:text-gray-500 text-2xl mb-1.5 group-hover:text-blue-500 transition-colors"></i>
                            <span class="text-xs text-muted font-medium group-hover:text-blue-500 transition-colors">Add Photo</span>
                            <input type="file" class="hidden" accept="image/*" onchange="uploadPortfolio(event)">
                        </label>
                    </div>
                </div>
            </div>

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
                        $stats = [
                            ['icon' => 'fa-star', 'color' => 'yellow', 'label' => 'Rating', 'value' => number_format($cleaner->rating ?? 0, 1) . ' / 5.0'],
                            ['icon' => 'fa-check-circle', 'color' => 'green', 'label' => 'Jobs Done', 'value' => $cleaner->total_completed_jobs ?? 0],
                            ['icon' => 'fa-calendar', 'color' => 'blue', 'label' => 'Experience', 'value' => ($cleaner->experience_days_active ?? 0) . ' days'],
                            ['icon' => 'fa-percentage', 'color' => 'purple', 'label' => 'Completion', 'value' => number_format($cleaner->completion_rate ?? 0, 1) . '%'],
                        ];
                    @endphp
                    @foreach($stats as $stat)
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

            {{-- Preview Card --}}
            <div class="bg-linear-to-br from-blue-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25 p-6 text-white text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-bl-3xl -mr-8 -mt-8"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3 backdrop-blur">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-lg">Profile Preview</h4>
                    <p class="text-white/70 text-sm mt-1.5 leading-relaxed">See how customers view your business profile</p>
                    <a href="/cleaner/{{ $cleaner->id }}/profile" target="_blank"
                       class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-white text-purple-600 rounded-xl font-bold text-sm shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <i class="fas fa-external-link-alt"></i> Preview Public Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function businessProfile() {
        return { init() {} };
    }

    function addCertification() {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 animate-slide-up';
        div.innerHTML = `<div class="relative flex-1">
            <i class="fas fa-award absolute left-4 top-1/2 -translate-y-1/2 text-yellow-500 text-sm"></i>
            <input type="text" class="cert-input w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300" placeholder="e.g., Professional Cleaning Certificate">
        </div>
        <button onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex-shrink-0"><i class="fas fa-times"></i></button>`;
        document.getElementById('certificationsList').appendChild(div);
    }

    function addServiceArea() {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 animate-slide-up';
        div.innerHTML = `<div class="relative flex-1">
            <i class="fas fa-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-red-500 text-sm"></i>
            <input type="text" class="area-input w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300" placeholder="e.g., Kinondoni, Masaki">
        </div>
        <button onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex-shrink-0"><i class="fas fa-times"></i></button>`;
        document.getElementById('serviceAreasList').appendChild(div);
    }

    async function uploadCover(event) {
        const file = event.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'cover');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        try {
            const res = await fetch('/cleaner/business/upload-image', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                const img = document.getElementById('coverImage');
                if (img) img.src = data.url;
                else {
                    const cover = document.getElementById('coverPhoto');
                    const newImg = document.createElement('img');
                    newImg.src = data.url;
                    newImg.className = 'w-full h-full object-cover';
                    newImg.id = 'coverImage';
                    cover.insertBefore(newImg, cover.firstChild);
                }
                window.showToast('Cover photo updated!', 'success');
            }
        } catch (e) { window.showToast('Upload failed', 'error'); }
    }

    async function uploadAvatar(event) {
        const file = event.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        try {
            const res = await fetch('/cleaner/profile/upload-avatar', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                document.getElementById('profileImg').src = data.avatar_url;
                window.showToast('Profile photo updated!', 'success');
            }
        } catch (e) { window.showToast('Upload failed', 'error'); }
    }

    async function uploadPortfolio(event) {
        const file = event.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'portfolio');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        try {
            const res = await fetch('/cleaner/business/upload-image', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                const grid = document.getElementById('portfolioGrid');
                const div = document.createElement('div');
                div.className = 'relative group rounded-xl overflow-hidden aspect-square bg-gray-100 dark:bg-gray-700 shadow-md animate-slide-up';
                div.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all"></div>
                    <button onclick="this.parentElement.remove()" class="absolute top-2 right-2 w-7 h-7 bg-red-500 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all text-xs shadow-lg"><i class="fas fa-times"></i></button>`;
                grid.insertBefore(div, grid.lastElementChild);
                window.showToast('Portfolio image added!', 'success');
            }
        } catch (e) { window.showToast('Upload failed', 'error'); }
    }

    async function saveBusinessProfile() {
        const certifications = Array.from(document.querySelectorAll('.cert-input')).map(i => i.value).filter(v => v.trim());
        const serviceAreas = Array.from(document.querySelectorAll('.area-input')).map(i => i.value).filter(v => v.trim());
        const languages = Array.from(document.querySelectorAll('.language-checkbox:checked')).map(cb => cb.value);
        const portfolioImages = Array.from(document.querySelectorAll('#portfolioGrid img')).map(img => img.src);

        try {
            const res = await fetch('/cleaner/business/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    business_name: document.getElementById('businessName').value,
                    business_description: document.getElementById('businessDescription').value,
                    business_phone: document.getElementById('businessPhone').value,
                    business_email: document.getElementById('businessEmail').value,
                    team_size: document.getElementById('teamSize').value,
                    languages, certifications, service_areas, portfolio_images,
                })
            });
            const data = await res.json();
            if (data.success) {
                window.showToast('Business profile saved!', 'success');
                document.getElementById('displayBusinessName').textContent = document.getElementById('businessName').value || 'Your Business';
            } else {
                window.showToast(data.message || 'Failed to save', 'error');
            }
        } catch (e) {
            window.showToast('Save failed', 'error');
        }
    }
</script>

<style>
    @keyframes slide-up { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
    .animate-slide-up { animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
</style>
@endpush
