@extends('layouts.app')

@section('title', 'My Business Profile')
@section('user_role', 'Cleaner')
@section('page_title', 'Business Profile')
@section('page_subtitle', 'Showcase your cleaning business to attract customers')

@section('content')
<div x-data="businessProfile()" x-init="init()">
    
    @php
        $cleaner = Auth::user()->cleaner;
        $user = Auth::user();
        
        // Business profile data
        $businessName = $cleaner->business_name ?? '';
        $businessDescription = $cleaner->business_description ?? '';
        $businessPhone = $cleaner->business_phone ?? $user->phone;
        $businessEmail = $cleaner->business_email ?? $user->email;
        $yearsExperience = $cleaner->years_experience ?? 0;
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

    <!-- Cover Photo -->
    <div class="relative mb-6">
        <div class="h-48 md:h-64 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-3xl shadow-xl overflow-hidden relative" id="coverPhoto">
            @if($cleaner->cover_photo)
            <img src="{{ $cleaner->cover_photo }}" class="w-full h-full object-cover" id="coverImage">
            @endif
            <div class="absolute inset-0 bg-black/20"></div>
            <button onclick="document.getElementById('coverUpload').click()" 
                    class="absolute bottom-4 right-4 px-4 py-2 bg-white/90 hover:bg-white text-gray-700 rounded-xl font-medium text-sm transition shadow-lg">
                <i class="fas fa-camera mr-2"></i> Change Cover
            </button>
            <input type="file" id="coverUpload" class="hidden" accept="image/*" onchange="uploadCover(event)">
        </div>
        
        <!-- Profile Picture (overlapping) -->
        <div class="absolute -bottom-12 left-8">
            <div class="relative">
                <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=3b82f6&color=fff&bold=true&size=120' }}" 
                     class="w-24 h-24 md:w-28 md:h-28 rounded-2xl border-4 border-white dark:border-gray-800 shadow-xl object-cover" id="profileImg">
                <button onclick="document.getElementById('avatarUpload').click()" 
                        class="absolute -bottom-2 -right-2 w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center text-sm shadow-lg">
                    <i class="fas fa-camera"></i>
                </button>
                <input type="file" id="avatarUpload" class="hidden" accept="image/*" onchange="uploadAvatar(event)">
            </div>
        </div>
    </div>

    <!-- Business Info Header -->
    <div class="mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800 dark:text-white" id="displayBusinessName">{{ $businessName ?: $user->full_name }}</h2>
                <div class="flex items-center space-x-3 mt-1">
                    <span class="text-yellow-500"><i class="fas fa-star"></i> {{ number_format($cleaner->rating ?? 0, 1) }}</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-500"><i class="fas fa-check-circle text-green-500 mr-1"></i> {{ $cleaner->total_completed_jobs ?? 0 }} jobs completed</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-500"><i class="fas fa-map-marker-alt text-red-400 mr-1"></i> {{ $cleaner->city->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="flex items-center space-x-3 mt-4 md:mt-0">
                <span class="px-4 py-2 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded-full text-sm font-bold">
                    <i class="fas fa-shield-alt mr-1"></i> Verified Cleaner
                </span>
                <span class="px-4 py-2 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded-full text-sm font-bold">
                    <i class="fas fa-medal mr-1"></i> {{ $cleaner->experience_days_active ?? 0 }} Days Active
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT COLUMN - Edit Forms -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Business Information -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-6 flex items-center">
                    <i class="fas fa-building text-blue-500 mr-2"></i> Business Information
                </h3>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Name</label>
                        <input type="text" id="businessName" value="{{ $businessName }}" placeholder="e.g., Sparkle Clean Services"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Description</label>
                        <textarea id="businessDescription" rows="4" placeholder="Describe your cleaning business, specialties, and what makes you unique..."
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 transition">{{ $businessDescription }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Phone</label>
                            <input type="tel" id="businessPhone" value="{{ $businessPhone }}" placeholder="+255 7XX XXX XXX"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Email</label>
                            <input type="email" id="businessEmail" value="{{ $businessEmail }}" placeholder="business@email.com"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Team Size</label>
                            <input type="number" id="teamSize" value="{{ $teamSize }}" min="1" max="100"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Completion Rate</label>
                            <input type="text" value="{{ number_format($cleaner->completion_rate ?? 0, 1) }}%" disabled
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Languages -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="fas fa-language text-purple-500 mr-2"></i> Languages Spoken
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @php
                        $allLanguages = ['Swahili', 'English', 'French', 'Arabic', 'Hindi', 'Chinese', 'Spanish', 'Portuguese'];
                    @endphp
                    @foreach($allLanguages as $lang)
                    <label class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="checkbox" value="{{ $lang }}" class="language-checkbox rounded" {{ in_array($lang, $languages) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $lang }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Certifications -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="fas fa-certificate text-yellow-500 mr-2"></i> Certifications & Training
                </h3>
                <div id="certificationsList" class="space-y-3">
                    @foreach($certifications as $cert)
                    <div class="flex items-center space-x-2">
                        <input type="text" value="{{ $cert }}" class="cert-input flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="e.g., Professional Cleaning Certificate">
                        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fas fa-times"></i></button>
                    </div>
                    @endforeach
                    @if(empty($certifications))
                    <div class="flex items-center space-x-2">
                        <input type="text" class="cert-input flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="e.g., Professional Cleaning Certificate">
                    </div>
                    @endif
                </div>
                <button onclick="addCertification()" class="mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium">
                    <i class="fas fa-plus-circle mr-1"></i> Add Certification
                </button>
            </div>

            <!-- Service Areas -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="fas fa-map-marked-alt text-green-500 mr-2"></i> Service Areas
                </h3>
                <div id="serviceAreasList" class="space-y-3">
                    @foreach($serviceAreas as $area)
                    <div class="flex items-center space-x-2">
                        <input type="text" value="{{ $area }}" class="area-input flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="e.g., Kinondoni, Masaki">
                        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fas fa-times"></i></button>
                    </div>
                    @endforeach
                    @if(empty($serviceAreas))
                    <div class="flex items-center space-x-2">
                        <input type="text" class="area-input flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="e.g., Kinondoni, Masaki">
                    </div>
                    @endif
                </div>
                <button onclick="addServiceArea()" class="mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium">
                    <i class="fas fa-plus-circle mr-1"></i> Add Service Area
                </button>
            </div>

            <!-- Save Button -->
            <button onclick="saveBusinessProfile()" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl transition">
                <i class="fas fa-save mr-2"></i> Save Business Profile
            </button>
        </div>

        <!-- RIGHT COLUMN - Portfolio -->
        <div class="space-y-6">
            
            <!-- Portfolio Gallery -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="fas fa-images text-pink-500 mr-2"></i> Portfolio Gallery
                </h3>
                <p class="text-sm text-gray-500 mb-4">Showcase your best work to attract customers</p>
                
                <div class="grid grid-cols-2 gap-3" id="portfolioGrid">
                    @foreach($portfolioImages as $img)
                    <div class="relative group rounded-xl overflow-hidden aspect-square bg-gray-100 dark:bg-gray-700">
                        <img src="{{ $img }}" class="w-full h-full object-cover">
                        <button onclick="removePortfolioImage('{{ $img }}')" class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-xs">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                    <label class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl aspect-square flex flex-col items-center justify-center cursor-pointer hover:border-blue-400 transition">
                        <i class="fas fa-plus text-gray-400 text-2xl mb-1"></i>
                        <span class="text-xs text-gray-400">Add Photo</span>
                        <input type="file" class="hidden" accept="image/*" onchange="uploadPortfolio(event)">
                    </label>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500"><i class="fas fa-star text-yellow-500 mr-2"></i> Rating</span>
                        <span class="font-bold">{{ number_format($cleaner->rating ?? 0, 1) }} / 5.0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500"><i class="fas fa-check-circle text-green-500 mr-2"></i> Jobs Done</span>
                        <span class="font-bold">{{ $cleaner->total_completed_jobs ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500"><i class="fas fa-calendar text-blue-500 mr-2"></i> Experience</span>
                        <span class="font-bold">{{ $cleaner->experience_days_active ?? 0 }} days</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500"><i class="fas fa-percentage text-purple-500 mr-2"></i> Completion</span>
                        <span class="font-bold">{{ number_format($cleaner->completion_rate ?? 0, 1) }}%</span>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white text-center">
                <i class="fas fa-eye text-3xl mb-3"></i>
                <h4 class="font-bold text-lg">Profile Preview</h4>
                <p class="text-blue-100 text-sm mt-1">This is how customers see your business profile</p>
                <button class="mt-4 px-4 py-2 bg-white text-purple-600 rounded-xl font-bold text-sm hover:shadow-lg transition">
                    Preview Public Profile
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function businessProfile() {
        return {
            init() {}
        };
    }

    function addCertification() {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2';
        div.innerHTML = `<input type="text" class="cert-input flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="e.g., Professional Cleaning Certificate">
                         <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fas fa-times"></i></button>`;
        document.getElementById('certificationsList').appendChild(div);
    }

    function addServiceArea() {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2';
        div.innerHTML = `<input type="text" class="area-input flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="e.g., Kinondoni, Masaki">
                         <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fas fa-times"></i></button>`;
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
                document.getElementById('coverImage').src = data.url;
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
                div.className = 'relative group rounded-xl overflow-hidden aspect-square bg-gray-100 dark:bg-gray-700';
                div.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover">
                    <button onclick="removePortfolioImage('${data.url}')" class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-xs"><i class="fas fa-times"></i></button>`;
                grid.insertBefore(div, grid.lastElementChild);
                window.showToast('Image added!', 'success');
            }
        } catch (e) { window.showToast('Upload failed', 'error'); }
    }

    function removePortfolioImage(url) {
        if (confirm('Remove this image?')) {
            event.target.closest('.relative').remove();
        }
    }

    async function saveBusinessProfile() {
        // Collect certifications
        const certifications = Array.from(document.querySelectorAll('.cert-input')).map(i => i.value).filter(v => v);
        // Collect service areas
        const serviceAreas = Array.from(document.querySelectorAll('.area-input')).map(i => i.value).filter(v => v);
        // Collect languages
        const languages = Array.from(document.querySelectorAll('.language-checkbox:checked')).map(cb => cb.value);
        // Collect portfolio images
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
                    languages: languages,
                    certifications: certifications,
                    service_areas: serviceAreas,
                    portfolio_images: portfolioImages,
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
            console.error('Save error:', e);
            window.showToast('Save failed', 'error');
        }
    }
</script>
@endpush
@endsection