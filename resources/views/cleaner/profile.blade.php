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
        
        // Fix skills
        $skills = $cleaner->service_skills ?? [];
        if (is_string($skills)) {
            $skills = json_decode($skills, true) ?? [];
        }
        
        // Fix working days
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $workingDays = $cleaner->working_days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        if (is_string($workingDays)) {
            $workingDays = json_decode($workingDays, true) ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        }
        
        $allServices = \App\Models\Service::where('is_active', true)->get();
        $allCities = \App\Models\City::where('is_active', true)->get();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT COLUMN -->
        <div class="space-y-6">
            
            <!-- Profile Picture -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
                <div class="relative inline-block">
                    <img id="profileImage" src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=3b82f6&color=fff&bold=true&size=150' }}" 
                         class="w-32 h-32 rounded-2xl object-cover border-4 border-white dark:border-gray-700 shadow-xl mx-auto">
                    <label for="avatarUpload" class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-xl flex items-center justify-center cursor-pointer shadow-lg transition">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatarUpload" class="hidden" accept="image/*" onchange="uploadAvatar(event)">
                </div>
                <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-4">{{ $user->full_name }}</h3>
                <p class="text-gray-500 dark:text-gray-400">{{ $cleaner->cleaner_id ?? 'No ID' }}</p>
            </div>

            <!-- Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h4 class="font-bold text-gray-800 dark:text-white mb-4">My Stats</h4>
                <div class="space-y-3">
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Rating</span><span class="font-bold text-yellow-600"><i class="fas fa-star mr-1"></i>{{ number_format($cleaner->rating ?? 0, 1) }}</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Completed Jobs</span><span class="font-bold text-blue-600">{{ $cleaner->total_completed_jobs ?? 0 }}</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Completion Rate</span><span class="font-bold text-green-600">{{ number_format($cleaner->completion_rate ?? 0, 1) }}%</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Experience</span><span class="font-bold text-purple-600">{{ $cleaner->experience_days_active ?? 0 }} days</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">City</span><span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->city->name ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Personal Information -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h4 class="font-bold text-lg text-gray-800 dark:text-white mb-6"><i class="fas fa-user-circle text-blue-500 mr-2"></i> Personal Information</h4>
                
                <form onsubmit="updateProfile(event)" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                            <input type="text" id="firstName" value="{{ $user->first_name }}" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                            <input type="text" id="lastName" value="{{ $user->last_name }}" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" value="{{ $user->email }}" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                        <input type="tel" id="phone" value="{{ $user->phone }}" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City</label>
                            <select id="cityId" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($allCities as $city)
                                <option value="{{ $city->id }}" {{ $cleaner->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Radius (km)</label>
                            <input type="number" id="maxRadius" value="{{ $cleaner->max_service_radius_km ?? 30 }}" min="5" max="100"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                        <textarea id="fullAddress" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $cleaner->full_address ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Skills</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($allServices as $service)
                            <label class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <input type="checkbox" value="{{ $service->id }}" class="skill-checkbox rounded"
                                       {{ in_array($service->id, $skills) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($service->name, 20) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold transition">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h4 class="font-bold text-lg text-gray-800 dark:text-white mb-6"><i class="fas fa-lock text-red-500 mr-2"></i> Change Password</h4>
                <form onsubmit="changePassword(event)" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                        <input type="password" id="currentPassword" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                        <input type="password" id="newPassword" required minlength="8"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                        <input type="password" id="confirmPassword" required minlength="8"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- Shift Schedule -->
            </div>
</div>

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
            if (data.success) { document.getElementById('profileImage').src = data.avatar_url; window.showToast('Updated!', 'success'); }
        } catch (e) { window.showToast('Failed', 'error'); }
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
            if (data.success) window.showToast('Profile updated!', 'success');
        } catch (e) { window.showToast('Failed', 'error'); }
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
                body: JSON.stringify({ current_password: document.getElementById('currentPassword').value, password: np, password_confirmation: cp })
            });
            const data = await res.json();
            if (data.success) { window.showToast('Password changed!', 'success'); }
            else { window.showToast(data.message, 'error'); }
        } catch (e) { window.showToast('Failed', 'error'); }
    }

   
</script>
@endpush
@endsection
