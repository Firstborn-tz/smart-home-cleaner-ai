@extends('layouts.app')

@section('title', 'Booking #' . $booking->booking_number)
@section('user_role', 'Cleaner')
@section('page_title', 'Booking Details')
@section('page_subtitle', '#' . $booking->booking_number)

@section('content')
<div class="space-y-6">
    
    <!-- Status Progress -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            @php
                $statuses = ['pending' => 'Pending', 'cleaner_assigned' => 'Assigned', 'cleaner_accepted' => 'Accepted', 'in_progress' => 'In Progress', 'completed' => 'Completed'];
                $keys = array_keys($statuses);
                $currentIndex = array_search($booking->status, $keys);
            @endphp
            @foreach($statuses as $key => $label)
                @php $i = array_search($key, $keys); @endphp
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $i < $currentIndex ? 'bg-green-500 text-white' : ($i === $currentIndex ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-500') }}">
                        @if($i < $currentIndex) <i class="fas fa-check"></i> @else {{ $i + 1 }} @endif
                    </div>
                    <span class="text-xs mt-1 {{ $i <= $currentIndex ? 'text-gray-800 dark:text-white font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                </div>
                @if($i < count($statuses) - 1)
                    <div class="flex-1 h-1 mx-1 rounded {{ $i < $currentIndex ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Accept/Decline -->
            @if(in_array($booking->status, ['pending', 'cleaner_assigned']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-2 border-yellow-400 text-center">
                <i class="fas fa-bell text-yellow-500 text-4xl mb-3"></i>
                <h3 class="text-xl font-extrabold text-gray-800 dark:text-white">New Booking Request!</h3>
                <div class="flex space-x-4 mt-6">
                    <button onclick="acceptBooking()" class="flex-1 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold transition">
                        <i class="fas fa-check mr-2"></i> Accept
                    </button>
                    <button onclick="declineBooking()" class="flex-1 px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition">
                        <i class="fas fa-times mr-2"></i> Decline
                    </button>
                </div>
            </div>
            @endif

            <!-- Booking Info -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex justify-between mb-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $booking->booking_type === 'instant' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                        <i class="fas {{ $booking->booking_type === 'instant' ? 'fa-bolt' : 'fa-calendar' }} mr-1"></i> {{ $booking->booking_type === 'instant' ? 'Instant' : 'Scheduled' }}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ $booking->service->name ?? 'Service' }}</h3>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4"><p class="text-xs text-gray-500">Your Earning</p><p class="font-bold text-green-600 text-lg">TZS {{ number_format($booking->cleaner_payout_amount) }}</p></div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4"><p class="text-xs text-gray-500">Duration</p><p class="font-bold text-gray-800 dark:text-white text-lg">{{ $booking->service->estimated_duration_minutes ?? 0 }} min</p></div>
                </div>
            </div>

            <!-- Verification Code -->
            @if(in_array($booking->status, ['cleaner_accepted', 'in_progress']))
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl shadow-xl p-6 text-white">
                <h3 class="text-xl font-bold mb-2"><i class="fas fa-key mr-2"></i> Verification Code</h3>
                <p class="text-purple-100 mb-4">Show this code to the homeowner upon arrival</p>
                <button onclick="generateCode()" class="w-full px-4 py-3 bg-white text-purple-600 rounded-xl font-bold hover:shadow-lg transition" id="generateBtn">
                    <i class="fas fa-sync-alt mr-2"></i> Generate Code
                </button>
                <div id="codeSection" style="display:none;" class="mt-4 bg-white/10 rounded-xl p-4 text-center">
                    <p id="codeDisplay" class="text-5xl font-extrabold tracking-widest font-mono">------</p>
                </div>
            </div>
            @endif

            <!-- Complete Button -->
            @if($booking->status === 'in_progress')
            <button onclick="completeService()" class="w-full px-6 py-4 bg-green-500 hover:bg-green-600 text-white rounded-2xl font-bold text-lg transition">
                <i class="fas fa-check-circle mr-2"></i> Complete Service
            </button>
            @endif
        </div>

        <!-- Sidebar - Location & Homeowner -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Location</h3>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $booking->service_address }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $booking->district ?? '' }}, {{ $booking->street ?? '' }}</p>
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center"><p class="text-xs text-gray-500">Distance</p><p class="font-bold text-blue-700">{{ $booking->distance_km ? round($booking->distance_km, 1) . ' km' : 'N/A' }}</p></div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center"><p class="text-xs text-gray-500">ETA</p><p class="font-bold text-green-700">{{ $booking->estimated_travel_time_minutes ? round($booking->estimated_travel_time_minutes) . ' min' : 'N/A' }}</p></div>
                </div>
                @if($booking->homeowner && $booking->homeowner->user)
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                    <p class="text-sm text-gray-500"><i class="fas fa-user mr-1"></i> Homeowner</p>
                    <p class="font-bold text-gray-800 dark:text-white">{{ $booking->homeowner->user->full_name }}</p>
                    <a href="tel:{{ $booking->homeowner->user->phone }}" class="text-blue-600 text-sm"><i class="fas fa-phone mr-1"></i> {{ $booking->homeowner->user->phone }}</a>
                </div>
                @endif
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $booking->service_latitude }},{{ $booking->service_longitude }}" target="_blank" 
                   class="block text-center mt-4 px-4 py-2.5 bg-blue-500 text-white rounded-xl font-bold hover:bg-blue-600 transition">
                    <i class="fas fa-directions mr-2"></i> Navigate with Google Maps
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const BOOKING_ID = {{ $booking->id }};
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    async function acceptBooking() {
        if(!confirm('Accept this booking?')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/accept`, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            if(data.success) { window.showToast(data.message,'success'); setTimeout(()=>location.reload(),1500); }
            else { window.showToast(data.message,'error'); }
        } catch(e) { window.showToast('Error','error'); }
    }
    async function declineBooking() {
        if(!confirm('Decline this booking?')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/decline`, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            if(data.success) { window.showToast('Booking declined','success'); setTimeout(()=>window.location.href='/cleaner/bookings',1500); }
        } catch(e) { window.showToast('Error','error'); }
    }
    async function generateCode() {
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/generate-code`, {
                method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            if(data.success) {
                document.getElementById('codeDisplay').textContent = data.code;
                document.getElementById('codeSection').style.display = 'block';
                document.getElementById('generateBtn').style.display = 'none';
                window.showToast('Code generated!','success');
            }
        } catch(e) { window.showToast('Error','error'); }
    }
    async function completeService() {
        if(!confirm('Mark as complete?')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/complete`, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            if(data.success) { window.showToast('Service completed!','success'); setTimeout(()=>location.reload(),1500); }
        } catch(e) { window.showToast('Error','error'); }
    }
</script>
@endpush
@endsection