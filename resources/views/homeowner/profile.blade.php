@extends('layouts.homeowner')

@section('title', 'My Profile')
@section('page_title', 'My Profile')
@section('page_subtitle', 'Manage your account and preferences')

@section('content')
<div>
    @php
        $user = Auth::user();
        $homeowner = $user->homeowner;
        
        $totalBookings = App\Models\Booking::where('homeowner_id', $homeowner->id)->count();
        $completedBookings = App\Models\Booking::where('homeowner_id', $homeowner->id)->where('status', 'completed')->count();
        $totalSpent = App\Models\Booking::where('homeowner_id', $homeowner->id)->where('status', 'completed')->sum('total_amount');
        $favoriteCleaners = is_string($homeowner->favorite_cleaners ?? '') ? json_decode($homeowner->favorite_cleaners, true) ?? [] : ($homeowner->favorite_cleaners ?? []);
    @endphp

    {{-- ============================================ --}}
    {{-- PROFILE HEADER --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-center gap-5">
                <div class="relative group flex-shrink-0">
                    <img id="profileImage" 
                         src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=3b82f6&color=fff&size=100&bold=true' }}" 
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-[4px] border-white dark:border-gray-800 shadow-2xl ring-2 ring-gray-200/50 dark:ring-gray-700/50 object-cover group-hover:ring-blue-300 dark:group-hover:ring-blue-500/50 transition-all">
                    <label for="avatarUpload" 
                           class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 text-white rounded-xl flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-all opacity-0 group-hover:opacity-100">
                        <i class="fas fa-camera text-sm"></i>
                    </label>
                    <input type="file" id="avatarUpload" class="hidden" accept="image/*" onchange="uploadAvatar(event)">
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-black text-heading tracking-tight">{{ $user->full_name }}</h2>
                    <p class="text-sm text-muted">{{ $user->email }}</p>
                    <p class="text-sm text-muted">{{ $user->phone }}</p>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20">
                            <i class="fas fa-home mr-1.5"></i> Homeowner
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                            {{ $completedBookings }} bookings
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATS CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
        @php
            $profileStats = [
                ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'fa-calendar-check', 'color' => 'blue'],
                ['label' => 'Completed', 'value' => $completedBookings, 'icon' => 'fa-check-circle', 'color' => 'green'],
                ['label' => 'Total Spent', 'value' => 'TZS ' . number_format($totalSpent, 0), 'icon' => 'fa-coins', 'color' => 'purple'],
                ['label' => 'Your Rating', 'value' => '⭐ ' . number_format($homeowner->rating ?? 0, 1), 'icon' => 'fa-star', 'color' => 'yellow'],
            ];
        @endphp
        @foreach($profileStats as $stat)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 sm:p-5 card-hover-lift group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] text-muted font-medium uppercase tracking-wider">{{ $stat['label'] }}</p>
                <div class="w-9 h-9 bg-{{ $stat['color'] }}-100 dark:bg-{{ $stat['color'] }}-500/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 text-sm"></i>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-black text-heading stat-number">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ============================================ --}}
    {{-- MAIN GRID --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- LEFT: EDIT FORMS --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Personal Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-circle text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <h4 class="font-bold text-heading text-lg">Personal Information</h4>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2"><i class="fas fa-envelope text-blue-500 mr-1.5"></i> Email</label>
                                <input type="email" id="email" value="{{ $user->email }}" required 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2"><i class="fas fa-phone text-green-500 mr-1.5"></i> Phone</label>
                                <input type="tel" id="phone" value="{{ $user->phone }}" required 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                        </div>
                        <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.02] transition-all duration-300">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            {{-- Home Address --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900/40 dark:to-red-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-red-600 dark:text-red-400"></i>
                        </div>
                        <h4 class="font-bold text-heading text-lg">Home Address</h4>
                    </div>
                </div>
                <div class="p-6">
                    <form onsubmit="updateAddress(event)" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">Street Address</label>
                            <input type="text" id="street" value="{{ $homeowner->street ?? '' }}" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">District/Ward</label>
                                <input type="text" id="ward" value="{{ $homeowner->ward ?? '' }}" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">City</label>
                                <input type="text" id="cityName" value="{{ $homeowner->city->name ?? '' }}" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                            </div>
                        </div>
                        <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.02] transition-all duration-300">
                            <i class="fas fa-save mr-2"></i> Update Address
                        </button>
                    </form>
                </div>
            </div>

            {{-- Change Password --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-rose-200 dark:from-red-900/40 dark:to-rose-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-lock text-red-600 dark:text-red-400"></i>
                        </div>
                        <h4 class="font-bold text-heading text-lg">Change Password</h4>
                    </div>
                </div>
                <div class="p-6">
                    <form onsubmit="changePassword(event)" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-heading mb-2">Current Password</label>
                            <input type="password" id="currentPassword" required 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800 outline-none transition-all duration-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">New Password</label>
                                <input type="password" id="newPassword" required minlength="8" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-heading mb-2">Confirm Password</label>
                                <input type="password" id="confirmPassword" required minlength="8" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800 outline-none transition-all duration-300">
                            </div>
                        </div>
                        <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-[1.02] transition-all duration-300">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT: SIDEBAR --}}
        <div class="space-y-5">
            
            {{-- Quick Links --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-link text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <h4 class="font-bold text-heading">Quick Links</h4>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    <a href="/homeowner/dashboard" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-th-large text-blue-500"></i>
                        <span class="text-sm font-semibold text-heading">Dashboard</span>
                    </a>
                    <a href="/homeowner/bookings/create" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-plus-circle text-green-500"></i>
                        <span class="text-sm font-semibold text-heading">Book a Cleaner</span>
                    </a>
                </div>
            </div>

            {{-- Favorite Cleaners --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-rose-200 dark:from-red-900/40 dark:to-rose-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-heart text-red-600 dark:text-red-400"></i>
                        </div>
                        <h4 class="font-bold text-heading">Favorite Cleaners</h4>
                    </div>
                </div>
                <div class="p-4">
                    @if(!empty($favoriteCleaners))
                        @php $favCleaners = App\Models\Cleaner::with('user')->whereIn('id', $favoriteCleaners)->get(); @endphp
                        <div class="space-y-2">
                            @foreach($favCleaners as $fc)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($fc->user->full_name) }}&background=3b82f6&color=fff&size=36&bold=true" class="w-9 h-9 rounded-lg ring-2 ring-blue-100 dark:ring-blue-500/20 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm text-heading truncate">{{ $fc->user->full_name }}</p>
                                    <p class="text-xs text-yellow-500">⭐ {{ number_format($fc->rating, 1) }}</p>
                                </div>
                                <a href="/cleaner/{{ $fc->id }}/profile" class="text-blue-500 hover:text-blue-600 text-xs font-bold flex-shrink-0">View</a>
                            </div>
                            @endforeach
                        </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-heart text-gray-300 dark:text-gray-600 text-2xl mb-2"></i>
                        <p class="text-sm text-muted">No favorites yet. They'll appear here after you book.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Account Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-info-circle text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <h4 class="font-bold text-heading">Account Info</h4>
                    </div>
                </div>
                <div class="p-5 divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="flex justify-between py-3 first:pt-0 last:pb-0 text-sm">
                        <span class="text-muted">Member Since</span>
                        <span class="font-bold text-heading">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between py-3 first:pt-0 last:pb-0 text-sm">
                        <span class="text-muted">Homeowner ID</span>
                        <span class="font-bold font-mono text-heading text-xs">{{ $homeowner->homeowner_id }}</span>
                    </div>
                    <div class="flex justify-between py-3 first:pt-0 last:pb-0 text-sm">
                        <span class="text-muted">Status</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function uploadAvatar(event) {
        const file = event.target.files[0]; if (!file) return;
        const formData = new FormData(); formData.append('avatar', file); formData.append('_token', '{{ csrf_token() }}');
        try {
            const res = await fetch('/homeowner/profile/upload-avatar', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) { document.getElementById('profileImage').src = data.avatar_url + '?t=' + Date.now(); window.showToast('Profile photo updated!', 'success'); }
        } catch (e) { window.showToast('Upload failed', 'error'); }
    }
    async function updateProfile(e) { e.preventDefault();
        try {
            const res = await fetch('/homeowner/profile/update', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:JSON.stringify({first_name:document.getElementById('firstName').value,last_name:document.getElementById('lastName').value,email:document.getElementById('email').value,phone:document.getElementById('phone').value}) });
            const data = await res.json(); window.showToast(data.message || 'Profile updated!', data.success ? 'success' : 'error');
        } catch (e) { window.showToast('Failed to save', 'error'); }
    }
    async function updateAddress(e) { e.preventDefault();
        try {
            const res = await fetch('/homeowner/profile/update-address', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:JSON.stringify({street:document.getElementById('street').value,ward:document.getElementById('ward').value,city_name:document.getElementById('cityName').value}) });
            const data = await res.json(); window.showToast(data.message || 'Address updated!', data.success ? 'success' : 'error');
        } catch (e) { window.showToast('Failed to save', 'error'); }
    }
    async function changePassword(e) { e.preventDefault();
        const np = document.getElementById('newPassword').value, cp = document.getElementById('confirmPassword').value;
        if (np !== cp) { window.showToast('Passwords do not match', 'error'); return; }
        try {
            const res = await fetch('/homeowner/profile/change-password', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:JSON.stringify({current_password:document.getElementById('currentPassword').value,password:np,password_confirmation:cp}) });
            const data = await res.json(); window.showToast(data.message || 'Password changed!', data.success ? 'success' : 'error');
        } catch (e) { window.showToast('Failed', 'error'); }
    }
</script>
@endpush