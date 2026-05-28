@extends('layouts.app')

@section('title', 'My Profile')
@section('user_role', 'Cleaner')
@section('page_title', 'My Profile')
@section('page_subtitle', 'Manage your account and personal details')

@section('content')
<div x-data="profileManager()">
    @php
        $cleaner = Auth::user()->cleaner;
        $user = Auth::user();
        
        $skills = $cleaner->service_skills ?? [];
        if (is_string($skills)) { $skills = json_decode($skills, true) ?? []; }
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $workingDays = $cleaner->working_days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        if (is_string($workingDays)) { $workingDays = json_decode($workingDays, true) ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']; }
        
        $allServices = \App\Models\Service::where('is_active', true)->get();
        $allCities = \App\Models\City::where('is_active', true)->get();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- ============================================ --}}
        {{-- LEFT COLUMN — Profile Card & Stats --}}
        {{-- ============================================ --}}
        <div class="space-y-5">
            
            {{-- Profile Picture Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="relative inline-block group">
                        <img id="profileImage" 
                             src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=3b82f6&color=fff&bold=true&size=150' }}" 
                             class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl object-cover border-[4px] border-white dark:border-gray-800 shadow-2xl ring-2 ring-gray-200/50 dark:ring-gray-700/50 mx-auto group-hover:ring-blue-300 dark:group-hover:ring-blue-500/50 transition-all">
                        <label for="avatarUpload" 
                               class="absolute -bottom-2 -right-2 w-10 h-10 bg-linear-to-br from-blue-500 to-purple-600 text-white rounded-xl flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-all opacity-0 group-hover:opacity-100">
                            <i class="fas fa-camera text-sm"></i>
                        </label>
                        <input type="file" id="avatarUpload" class="hidden" accept="image/*" onchange="uploadAvatar(event)">
                    </div>
                    <h3 class="text-xl font-black text-heading mt-4">{{ $user->full_name }}</h3>
                    <span class="inline-flex items-center mt-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-mono text-muted">
                        <i class="fas fa-id-card mr-1.5 text-gray-400"></i> {{ $cleaner->cleaner_id ?? 'No ID' }}
                    </span>
                </div>
            </div>

            {{-- Quick Stats Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-bar text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <h4 class="font-bold text-heading">My Stats</h4>
                    </div>
                </div>
                <div class="p-5 divide-y divide-gray-100 dark:divide-gray-700">
                    @php
                        $statRows = [
                            ['icon' => 'fa-star', 'color' => 'yellow', 'label' => 'Rating', 'value' => number_format($cleaner->rating ?? 0, 1)],
                            ['icon' => 'fa-check-circle', 'color' => 'blue', 'label' => 'Completed Jobs', 'value' => $cleaner->total_completed_jobs ?? 0],
                            ['icon' => 'fa-chart-line', 'color' => 'green', 'label' => 'Completion Rate', 'value' => number_format($cleaner->completion_rate ?? 0, 1) . '%'],
                            ['icon' => 'fa-calendar', 'color' => 'purple', 'label' => 'Experience', 'value' => ($cleaner->experience_days_active ?? 0) . ' days'],
                            ['icon' => 'fa-map-marker-alt', 'color' => 'red', 'label' => 'City', 'value' => $cleaner->city->name ?? 'N/A'],
                        ];
                    @endphp
                    @foreach($statRows as $row)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-2.5 text-sm text-muted">
                            <i class="fas {{ $row['icon'] }} text-{{ $row['color'] }}-500 w-4 text-xs"></i>
                            {{ $row['label'] }}
                        </div>
                        <span class="text-sm font-bold text-heading">{{ $row['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- RIGHT COLUMN — Edit Forms --}}
        {{-- ============================================ --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Personal Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-circle text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-heading text-lg">Personal Information</h4>
                            <p class="text-xs text-muted">Update your basic details</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <form onsubmit="updateProfile(event)" class="space-y-5">
                        @csrf
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">First Name</label>
                                <input type="text" id="firstName" value="{{ $user->first_name }}" required
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">Last Name</label>
                                <input type="text" id="lastName" value="{{ $user->last_name }}" required
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-envelope text-blue-500 mr-1.5"></i> Email
                            </label>
                            <input type="email" id="email" value="{{ $user->email }}" required
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-phone text-green-500 mr-1.5"></i> Phone
                            </label>
                            <input type="tel" id="phone" value="{{ $user->phone }}" required
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">
                                    <i class="fas fa-city text-purple-500 mr-1.5"></i> City
                                </label>
                                <div class="relative">
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-muted text-xs pointer-events-none"></i>
                                    <select id="cityId" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                                        @foreach($allCities as $city)
                                        <option value="{{ $city->id }}" {{ $cleaner->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">
                                    <i class="fas fa-circle-notch text-orange-500 mr-1.5"></i> Max Radius (km)
                                </label>
                                <input type="number" id="maxRadius" value="{{ $cleaner->max_service_radius_km ?? 30 }}" min="5" max="100"
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">
                                <i class="fas fa-map-pin text-red-500 mr-1.5"></i> Address
                            </label>
                            <textarea id="fullAddress" rows="2" 
                                      class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">{{ $cleaner->full_address ?? '' }}</textarea>
                        </div>
                        
                        {{-- Skills --}}
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-3">
                                <i class="fas fa-tools text-indigo-500 mr-1.5"></i> Service Skills
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($allServices as $service)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl cursor-pointer transition-all duration-200 border-2 
                                              {{ in_array($service->id, $skills) ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500/30' }}">
                                    <input type="checkbox" value="{{ $service->id }}" class="skill-checkbox rounded-lg w-4 h-4 text-blue-600 focus:ring-blue-500" {{ in_array($service->id, $skills) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-heading">{{ Str::limit($service->name, 20) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.02] transition-all duration-300">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            {{-- Change Password --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-red-100 to-rose-200 dark:from-red-900/40 dark:to-rose-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-lock text-red-600 dark:text-red-400"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-heading text-lg">Change Password</h4>
                            <p class="text-xs text-muted">Keep your account secure</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <form onsubmit="changePassword(event)" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">Current Password</label>
                            <input type="password" id="currentPassword" required
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800 outline-none transition-all duration-300"
                                   placeholder="Enter current password">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">New Password</label>
                            <input type="password" id="newPassword" required minlength="8"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800 outline-none transition-all duration-300"
                                   placeholder="Min 8 characters">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">Confirm Password</label>
                            <input type="password" id="confirmPassword" required minlength="8"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800 outline-none transition-all duration-300"
                                   placeholder="Re-enter new password">
                        </div>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-[1.02] transition-all duration-300">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function profileManager() { return { init() {} }; }

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
                document.getElementById('profileImage').src = data.avatar_url + '?t=' + Date.now(); 
                window.showToast('Profile photo updated!', 'success'); 
            }
        } catch (e) { window.showToast('Upload failed', 'error'); }
    }

    async function updateProfile(event) {
        event.preventDefault();
        const skills = Array.from(document.querySelectorAll('.skill-checkbox:checked')).map(cb => parseInt(cb.value));
        try {
            const res = await fetch('/cleaner/profile/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({
                    first_name: document.getElementById('firstName').value,
                    last_name: document.getElementById('lastName').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value,
                    city_id: document.getElementById('cityId').value,
                    max_service_radius_km: document.getElementById('maxRadius').value,
                    full_address: document.getElementById('fullAddress').value,
                    service_skills: skills,
                })
            });
            const data = await res.json();
            window.showToast(data.message || 'Profile updated!', data.success ? 'success' : 'error');
        } catch (e) { window.showToast('Failed to save', 'error'); }
    }

    async function changePassword(event) {
        event.preventDefault();
        const np = document.getElementById('newPassword').value;
        const cp = document.getElementById('confirmPassword').value;
        if (np !== cp) { window.showToast('Passwords do not match', 'error'); return; }
        try {
            const res = await fetch('/cleaner/profile/change-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ 
                    current_password: document.getElementById('currentPassword').value, 
                    password: np, 
                    password_confirmation: cp 
                })
            });
            const data = await res.json();
            window.showToast(data.message || 'Password changed!', data.success ? 'success' : 'error');
        } catch (e) { window.showToast('Failed', 'error'); }
    }
</script>
@endpush
