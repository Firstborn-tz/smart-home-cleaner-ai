<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cleaner->business_name ?? $cleaner->user->full_name }} - SmartClean AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .star-rating { color: #f59e0b; }
    </style>
@include('partials.dark-mode-init')
</head>
<body class="bg-gray-50">

    @php
        $cleaner = $cleaner ?? App\Models\Cleaner::with('user', 'city', 'reviews')->find(request('id'));
        $user = $cleaner->user;
        $businessName = $cleaner->business_name ?? $user->full_name;
        $reviews = $cleaner->reviews()->with('reviewer')->approved()->latest()->limit(20)->get();
        $avgRating = $cleaner->rating ?? 0;
        $totalReviews = $cleaner->reviews()->count();
        $portfolioImages = $cleaner->portfolio_images ?? [];
        if (is_string($portfolioImages)) $portfolioImages = json_decode($portfolioImages, true) ?? [];
        $certifications = $cleaner->certifications ?? [];
        if (is_string($certifications)) $certifications = json_decode($certifications, true) ?? [];
        $serviceAreas = $cleaner->service_areas ?? [];
        if (is_string($serviceAreas)) $serviceAreas = json_decode($serviceAreas, true) ?? [];
        $languages = $cleaner->languages ?? [];
        if (is_string($languages)) $languages = json_decode($languages, true) ?? [];
        $skills = $cleaner->service_skills ?? [];
        if (is_string($skills)) $skills = json_decode($skills, true) ?? [];
    @endphp

    <!-- COVER PHOTO -->
    <div class="relative h-48 md:h-72 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500">
        @if($cleaner->cover_photo)
        <img src="{{ $cleaner->cover_photo }}" class="w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute top-4 left-4">
            <a href="/" class="text-white hover:text-blue-200 transition flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i> <span>Back</span>
            </a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 md:px-6">
        
        <!-- PROFILE HEADER -->
        <div class="relative -mt-16 bg-white rounded-3xl shadow-xl p-6 md:p-8 mb-8 animate-slide-up">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=3b82f6&color=fff&bold=true&size=100' }}" 
                     class="w-24 h-24 md:w-32 md:h-32 rounded-2xl border-4 border-white shadow-xl -mt-20 md:-mt-24 object-cover">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800">{{ $businessName }}</h1>
                            <p class="text-gray-500 mt-1"><i class="fas fa-map-marker-alt text-red-400 mr-1"></i> {{ $cleaner->city->name ?? 'Tanzania' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-extrabold text-yellow-500">{{ number_format($avgRating, 1) }}</p>
                            <div class="flex text-yellow-400 text-lg">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($avgRating))
                                        <i class="fas fa-star"></i>
                                    @elseif($i - 0.5 <= $avgRating)
                                        <i class="fas fa-star-half-alt"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <p class="text-sm text-gray-500">{{ $totalReviews }} reviews</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <span class="px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-bold">
                            <i class="fas fa-shield-alt mr-1"></i> Verified
                        </span>
                        <span class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                            <i class="fas fa-check-circle mr-1"></i> {{ $cleaner->total_completed_jobs }} jobs done
                        </span>
                        <span class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-bold">
                            <i class="fas fa-calendar-alt mr-1"></i> {{ $cleaner->experience_days_active }} days active
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT - Main Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- About -->
                @if($cleaner->business_description)
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-slide-up">
                    <h2 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-info-circle text-blue-500 mr-2"></i> About</h2>
                    <p class="text-gray-600 leading-relaxed">{{ $cleaner->business_description }}</p>
                </div>
                @endif

                <!-- Portfolio Gallery -->
                @if(!empty($portfolioImages))
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-slide-up">
                    <h2 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-images text-pink-500 mr-2"></i> Portfolio</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($portfolioImages as $img)
                        <div class="rounded-xl overflow-hidden aspect-square bg-gray-100">
                            <img src="{{ $img }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Services Offered -->
                @if(!empty($skills))
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-slide-up">
                    <h2 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-tools text-green-500 mr-2"></i> Services Offered</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach(\App\Models\Service::whereIn('id', $skills)->get() as $service)
                        <div class="p-3 bg-gray-50 rounded-xl text-center">
                            <p class="font-medium text-gray-700">{{ $service->name }}</p>
                            <p class="text-sm text-gray-500">TZS {{ number_format($service->base_price) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Reviews Section -->
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-slide-up">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-star text-yellow-500 mr-2"></i> Reviews ({{ $totalReviews }})
                    </h2>
                    
                    @if($reviews->count() > 0)
                    <!-- Rating Summary -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                        @for($i = 5; $i >= 1; $i--)
                        @php
                            $count = $cleaner->reviews()->where('rating', '>=', $i)->where('rating', '<', $i + 1)->count();
                            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="text-center">
                            <p class="text-sm text-gray-500">{{ $i }} <i class="fas fa-star text-yellow-400"></i></p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $count }}</p>
                        </div>
                        @endfor
                    </div>

                    <!-- Review List -->
                    <div class="space-y-6">
                        @foreach($reviews as $review)
                        <div class="border-b border-gray-100 pb-6 last:border-0">
                            <div class="flex items-start space-x-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($review->reviewer->user->full_name ?? 'User') }}&background=3b82f6&color=fff&size=40" class="w-10 h-10 rounded-full">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-gray-800">{{ $review->reviewer->user->full_name ?? 'Anonymous' }}</p>
                                        <span class="text-sm text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex text-yellow-400 text-sm mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-gray-200' }}"></i>
                                        @endfor
                                    </div>
                                    @if($review->body)
                                    <p class="text-gray-600 mt-2">{{ $review->body }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-comment-slash text-4xl mb-3"></i>
                        <p>No reviews yet</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT - Sidebar Info -->
            <div class="space-y-6">
                
                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl shadow-lg p-6 animate-slide-up">
                    <h3 class="font-bold text-gray-800 mb-4">Quick Stats</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between"><span class="text-gray-500"><i class="fas fa-check-circle text-green-500 mr-2"></i>Jobs Done</span><span class="font-bold">{{ $cleaner->total_completed_jobs }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500"><i class="fas fa-percentage text-purple-500 mr-2"></i>Completion</span><span class="font-bold">{{ number_format($cleaner->completion_rate, 1) }}%</span></div>
                        <div class="flex justify-between"><span class="text-gray-500"><i class="fas fa-clock text-blue-500 mr-2"></i>Response</span><span class="font-bold">{{ round($cleaner->avg_response_time_seconds / 60, 1) }} min</span></div>
                        @if($cleaner->team_size > 1)
                        <div class="flex justify-between"><span class="text-gray-500"><i class="fas fa-users text-indigo-500 mr-2"></i>Team</span><span class="font-bold">{{ $cleaner->team_size }} members</span></div>
                        @endif
                    </div>
                </div>

                <!-- Languages -->
                @if(!empty($languages))
                <div class="bg-white rounded-2xl shadow-lg p-6 animate-slide-up">
                    <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-language text-purple-500 mr-2"></i> Languages</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($languages as $lang)
                        <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-sm">{{ $lang }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Certifications -->
                @if(!empty($certifications))
                <div class="bg-white rounded-2xl shadow-lg p-6 animate-slide-up">
                    <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-certificate text-yellow-500 mr-2"></i> Certifications</h3>
                    <ul class="space-y-2">
                        @foreach($certifications as $cert)
                        <li class="flex items-center text-sm text-gray-600"><i class="fas fa-check text-green-500 mr-2"></i> {{ $cert }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Service Areas -->
                @if(!empty($serviceAreas))
                <div class="bg-white rounded-2xl shadow-lg p-6 animate-slide-up">
                    <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-map-marked-alt text-red-500 mr-2"></i> Service Areas</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($serviceAreas as $area)
                        <span class="px-3 py-1 bg-red-50 text-red-700 rounded-full text-sm">{{ $area }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Contact -->
                <div class="bg-white rounded-2xl shadow-lg p-6 animate-slide-up">
                    <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-phone text-green-500 mr-2"></i> Contact</h3>
                    @if($cleaner->business_phone)
                    <a href="tel:{{ $cleaner->business_phone }}" class="flex items-center text-blue-600 hover:text-blue-700 mb-2">
                        <i class="fas fa-phone mr-2"></i> {{ $cleaner->business_phone }}
                    </a>
                    @endif
                    @if($cleaner->business_email)
                    <a href="mailto:{{ $cleaner->business_email }}" class="flex items-center text-blue-600 hover:text-blue-700">
                        <i class="fas fa-envelope mr-2"></i> {{ $cleaner->business_email }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center py-8 text-gray-500 text-sm mt-12 border-t">
        <p>Powered by SmartClean AI</p>
    </div>
</body>
</html>
