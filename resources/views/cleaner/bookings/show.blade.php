@extends('layouts.app')

@section('title', 'Booking #' . $booking->booking_number)
@section('user_role', 'Cleaner')
@section('page_title', 'Booking Details')
@section('page_subtitle', '#' . $booking->booking_number)

@section('content')
<div>
    @php $currentStatus = $booking->status; @endphp

    {{-- STATUS PROGRESS BAR --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 mb-6">
        @php 
            $steps = [
                'pending' => ['label' => 'Pending', 'icon' => 'fa-clock'],
                'cleaner_assigned' => ['label' => 'Assigned', 'icon' => 'fa-user-check'],
                'cleaner_accepted' => ['label' => 'Accepted', 'icon' => 'fa-handshake'],
                'cleaner_arrived' => ['label' => 'Arrived', 'icon' => 'fa-flag-checkered'],
                'in_progress' => ['label' => 'In Progress', 'icon' => 'fa-spinner'],
                'completed' => ['label' => 'Completed', 'icon' => 'fa-check-circle'],
            ];
            $keys = array_keys($steps); 
            $currentIdx = array_search($currentStatus, $keys);
            if ($currentIdx === false) $currentIdx = 0;
        @endphp
        
        <div class="flex items-center">
            @foreach($steps as $key => $step)
                @php $i = array_search($key, $keys); @endphp
                <div class="flex flex-col items-center flex-1 relative">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300
                        {{ $i < $currentIdx ? 'bg-gradient-to-br from-green-400 to-emerald-600 text-white shadow-lg' : 
                           ($i === $currentIdx ? 'bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg' : 
                           'bg-gray-100 dark:bg-gray-700 text-muted') }}">
                        @if($i < $currentIdx) 
                            <i class="fas fa-check"></i> 
                        @elseif($i === $currentIdx) 
                            <i class="fas {{ $step['icon'] }}"></i>
                        @else 
                            <span>{{ $i + 1 }}</span> 
                        @endif
                    </div>
                    <span class="text-[10px] mt-1.5 font-semibold text-center {{ $i <= $currentIdx ? 'text-heading' : 'text-muted' }}">
                        {{ $step['label'] }}
                    </span>
                </div>
                @if($i < count($steps) - 1)
                    <div class="flex-1 h-1.5 mx-1 rounded-full transition-all duration-300 {{ $i < $currentIdx ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- Left: Main Content --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Accept/Decline Card --}}
            @if($currentStatus === 'pending')
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-yellow-400 overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-50 to-transparent p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">New Booking Request!</h3>
                    <p class="text-muted mt-1.5">
                        @if($booking->booking_type === 'instant')
                            <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">
                                <i class="fas fa-bolt mr-1.5"></i> Immediate — 10 min to respond
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                <i class="fas fa-calendar mr-1.5"></i> Scheduled — 30 min to respond
                            </span>
                        @endif
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button onclick="acceptBooking()" 
                                class="flex-1 px-6 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-base shadow-lg hover:scale-[1.02] transition-all">
                            <i class="fas fa-check-circle mr-2"></i> Accept Booking
                        </button>
                        <button onclick="declineBooking()" 
                                class="flex-1 px-6 py-3.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-2xl font-bold text-base shadow-lg hover:scale-[1.02] transition-all">
                            <i class="fas fa-times-circle mr-2"></i> Decline
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Arrival Confirmation --}}
            @if(in_array($currentStatus, ['cleaner_assigned', 'cleaner_accepted']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-blue-400 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-transparent p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-flag-checkered text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">Confirm Your Arrival</h3>
                    <p class="text-muted text-sm mt-1 mb-5">
                        Tap when you arrive at the homeowner's location.<br>
                        Grace window: ETA + 15 minutes. Late arrival may affect your rating.
                    </p>
                    <button onclick="confirmArrival()" 
                            class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl font-bold text-sm shadow-xl hover:scale-105 transition-all">
                        <i class="fas fa-map-pin"></i> I've Arrived
                    </button>
                </div>
            </div>
            @endif

            {{-- Booking Info Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-broom text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-heading text-lg">{{ $booking->service->name ?? 'Service' }}</h3>
                                <span class="text-xs font-mono text-muted">#{{ $booking->booking_number }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold
                            {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-700' : 
                               ($currentStatus === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $currentStatus)) }}
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold
                            {{ $booking->pricing_model === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            <i class="fas {{ $booking->pricing_model === 'fixed' ? 'fa-clock' : 'fa-stopwatch' }} mr-1.5"></i>
                            {{ $booking->pricing_model === 'fixed' ? 'Fixed Block (' . ($booking->booked_hours ?? '?') . ' hrs)' : 'Pay As You Go (30 min billing)' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <p class="text-[11px] text-muted uppercase mb-1">Rate</p>
                            <p class="text-xl font-black text-green-600">TZS {{ number_format($booking->hourly_rate ?? 0) }}/hr</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <p class="text-[11px] text-muted uppercase mb-1">Est. Total</p>
                            <p class="text-xl font-black text-heading">
                                TZS {{ number_format($booking->pricing_model === 'fixed' ? ($booking->booked_hours * $booking->hourly_rate) : ($booking->hourly_rate ?? 0)) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <p class="text-[11px] text-muted uppercase mb-1">Distance</p>
                            <p class="text-xl font-black text-blue-600">{{ round($booking->distance_km ?? 0, 1) }} km</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <p class="text-[11px] text-muted uppercase mb-1">ETA</p>
                            <p class="text-xl font-black text-purple-600">{{ round($booking->estimated_travel_time_minutes ?? 0) }} min</p>
                        </div>
                    </div>

                    @if($booking->special_instructions)
                    <div class="bg-blue-50 rounded-xl p-4 mt-4 border border-blue-200">
                        <p class="text-sm text-blue-700"><strong>Instructions:</strong> {{ $booking->special_instructions }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Verification Code --}}
            @if(in_array($currentStatus, ['cleaner_assigned', 'cleaner_accepted', 'cleaner_arrived', 'in_progress']))
            <div class="bg-gradient-to-br from-purple-500 to-indigo-700 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 text-white">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-key text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Verification Code</h3>
                            <p class="text-purple-200 text-sm">Show this to the homeowner upon arrival</p>
                        </div>
                    </div>
                    
                    <div id="generateSection">
                        <button onclick="generateCode()" 
                                class="w-full px-5 py-3.5 bg-white text-purple-600 rounded-2xl font-bold text-sm shadow-xl hover:scale-[1.01] transition-all">
                            <i class="fas fa-key mr-2"></i> Generate Verification Code
                        </button>
                    </div>

                    <div id="codeSection" style="display:none;">
                        <div class="bg-white/10 backdrop-blur rounded-2xl p-6 text-center mb-4 border border-white/20">
                            <p class="text-purple-200 text-xs uppercase tracking-wider mb-2">Your Code</p>
                            <p id="codeDisplay" class="text-5xl sm:text-6xl font-black tracking-[0.3em] font-mono">------</p>
                            <p class="text-purple-200 text-xs mt-2"><i class="fas fa-clock mr-1"></i> Valid for 30 minutes</p>
                        </div>
                        <button onclick="generateCode()" 
                                class="w-full px-5 py-3 bg-white/15 text-white rounded-2xl font-bold text-sm hover:bg-white/25 transition-all border border-white/20">
                            <i class="fas fa-sync-alt mr-2"></i> Regenerate Code
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Start Service --}}
            @if($currentStatus === 'cleaner_arrived')
            <button onclick="startService()" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl font-bold text-base shadow-xl hover:scale-[1.02] transition-all">
                <i class="fas fa-play-circle mr-2"></i> Start Service (Begin Billing)
            </button>
            @endif

            {{-- Complete Service --}}
            @if($currentStatus === 'in_progress')
            <button onclick="completeService()" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-base shadow-xl hover:scale-[1.02] transition-all">
                <i class="fas fa-check-circle mr-2"></i> Mark Service as Complete
            </button>
            @endif

            {{-- Completed Summary --}}
            @if($currentStatus === 'completed')
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <h3 class="font-bold text-heading text-lg mb-4"><i class="fas fa-receipt text-green-500 mr-2"></i>Payment Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted">Actual Hours</span>
                            <span class="font-bold">{{ $booking->actual_hours ?? '?' }} hrs</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted">Billed Hours</span>
                            <span class="font-bold">{{ $booking->billed_hours ?? '?' }} hrs</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted">Rate</span>
                            <span class="font-bold">TZS {{ number_format($booking->hourly_rate ?? 0) }}/hr</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted">Total Amount</span>
                            <span class="text-lg font-black text-green-600">TZS {{ number_format($booking->final_amount ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-xs">
                            <span class="text-muted">Commission ({{ $booking->commission_percentage ?? 15 }}%)</span>
                            <span class="text-red-500">- TZS {{ number_format($booking->commission_amount ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="font-bold text-heading">You Earn</span>
                            <span class="text-xl font-black text-green-600">TZS {{ number_format($booking->cleaner_payout_amount ?? 0) }}</span>
                        </div>
                    </div>
                    @if($booking->cleaner_rating_given)
                    <div class="mt-4 pt-4 border-t text-center">
                        <p class="text-sm text-muted">Homeowner rated you</p>
                        <div class="flex justify-center gap-1 text-yellow-500 text-xl mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $booking->cleaner_rating_given ? '' : 'opacity-20' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Sidebar --}}
        <div class="space-y-5">
            
            @if($booking->homeowner)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="font-bold text-heading"><i class="fas fa-user text-blue-500 mr-2"></i>Homeowner</h3>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($booking->homeowner->user->full_name) }}&background=3b82f6&color=fff&size=48&bold=true" 
                             class="w-12 h-12 rounded-xl ring-2 ring-blue-100">
                        <div>
                            <p class="font-bold text-heading">{{ $booking->homeowner->user->full_name }}</p>
                            <span class="text-sm text-muted">⭐ {{ number_format($booking->homeowner->rating ?? 0, 1) }}</span>
                        </div>
                    </div>
                    @if($booking->homeowner->user->phone)
                    <a href="tel:{{ $booking->homeowner->user->phone }}" 
                       class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-green-50 text-green-700 rounded-xl font-bold text-sm hover:bg-green-100 transition-all">
                        <i class="fas fa-phone"></i> {{ $booking->homeowner->user->phone }}
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="font-bold text-heading"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i>Service Location</h3>
                </div>
                <div class="p-5">
                    <p class="text-sm text-body mb-2">{{ $booking->service_address }}</p>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $booking->service_latitude }},{{ $booking->service_longitude }}" 
                       target="_blank"
                       class="flex items-center justify-center gap-2 w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] transition-all">
                        <i class="fas fa-directions"></i> Navigate with Google Maps
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h4 class="font-bold text-heading"><i class="fas fa-info-circle text-blue-500 mr-2"></i>Details</h4>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Type</span><span class="font-bold capitalize">{{ $booking->booking_type }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Pricing</span><span class="font-bold">{{ $booking->pricing_model === 'payg' ? 'Pay As You Go' : 'Fixed Block' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Date</span><span class="font-bold">{{ $booking->created_at->format('M d, Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Time</span><span class="font-bold">{{ $booking->created_at->format('h:i A') }}</span></div>
                    @if($booking->grace_window_minutes)
                    <div class="flex justify-between"><span class="text-muted">Grace Window</span><span class="font-bold">{{ $booking->grace_window_minutes }} min</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BOOKING_ID = {{ $booking->id }};
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(msg, type) { window.showToast(msg, type); }

    async function acceptBooking() {
        if (!confirm('Accept this booking?')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/accept`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) { showToast('Error accepting booking', 'error'); }
    }

    async function declineBooking() {
        if (!confirm('Are you sure you want to decline?')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/decline`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => window.location.href = '/cleaner/bookings', 1500);
        } catch (e) { showToast('Error declining booking', 'error'); }
    }

    async function confirmArrival() {
        if (!confirm('Confirm you have arrived at the location?')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/arrive`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) { showToast('Error confirming arrival', 'error'); }
    }

    async function startService() {
        if (!confirm('Start the service? Billing timer will begin.')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/start`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) { showToast('Error starting service', 'error'); }
    }

    async function generateCode() {
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/generate-code`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('codeDisplay').textContent = data.code;
                document.getElementById('codeSection').style.display = 'block';
                document.getElementById('generateSection').style.display = 'none';
                showToast('Verification code generated!', 'success');
            } else { showToast(data.message || 'Failed', 'error'); }
        } catch (e) { showToast('Error generating code', 'error'); }
    }

    async function completeService() {
        if (!confirm('Mark this service as complete? Final billing will be calculated.')) return;
        try {
            const res = await fetch(`/cleaner/bookings/${BOOKING_ID}/complete`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) { showToast('Error completing service', 'error'); }
    }
</script>
@endpush