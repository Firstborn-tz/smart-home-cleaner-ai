@extends('layouts.homeowner')

@section('title', 'Booking Details')
@section('page_title', 'Booking #' . $booking->booking_number)
@section('page_subtitle', 'Track your service in real-time')

@section('content')
<div x-data="bookingTracker()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- ============================================ --}}
        {{-- MAIN CONTENT --}}
        {{-- ============================================ --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Status Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-heading text-lg">Booking Status</h3>
                                <p class="text-xs text-muted">Created {{ $booking->created_at->format('M d, Y - h:i A') }}</p>
                            </div>
                        </div>
                        @php
                            $statusMap = [
                                'completed' => ['bg' => 'bg-green-100 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-500/20'],
                                'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-300', 'dot' => 'bg-red-500', 'border' => 'border-red-200 dark:border-red-500/20'],
                                'in_progress' => ['bg' => 'bg-orange-100 dark:bg-orange-500/10', 'text' => 'text-orange-700 dark:text-orange-300', 'dot' => 'bg-orange-500 animate-pulse', 'border' => 'border-orange-200 dark:border-orange-500/20'],
                                'cleaner_accepted' => ['bg' => 'bg-blue-100 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-500/20'],
                                'cleaner_en_route' => ['bg' => 'bg-indigo-100 dark:bg-indigo-500/10', 'text' => 'text-indigo-700 dark:text-indigo-300', 'dot' => 'bg-indigo-500 animate-pulse', 'border' => 'border-indigo-200 dark:border-indigo-500/20'],
                                'cleaner_arrived' => ['bg' => 'bg-teal-100 dark:bg-teal-500/10', 'text' => 'text-teal-700 dark:text-teal-300', 'dot' => 'bg-teal-500', 'border' => 'border-teal-200 dark:border-teal-500/20'],
                                'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-500/10', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-500/20'],
                            ];
                            $s = $statusMap[$booking->status] ?? $statusMap['pending'];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $s['bg'] }} {{ $s['text'] }} border {{ $s['border'] }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $s['dot'] }}"></span>
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    {{-- Progress Tracker --}}
                    <div class="mb-6">
                        @php
                            $steps = ['pending' => 'Pending', 'cleaner_assigned' => 'Assigned', 'cleaner_accepted' => 'Accepted', 'cleaner_en_route' => 'En Route', 'cleaner_arrived' => 'Arrived', 'in_progress' => 'In Progress', 'completed' => 'Completed'];
                            $keys = array_keys($steps);
                            $currentIdx = array_search($booking->status, $keys);
                            $displaySteps = ['cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress', 'completed'];
                        @endphp
                        <div class="flex items-center">
                            @foreach($displaySteps as $i => $stepKey)
                                @php $stepIdx = array_search($stepKey, $keys); @endphp
                                <div class="flex flex-col items-center flex-1">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all duration-300
                                        {{ $currentIdx > $stepIdx ? 'bg-gradient-to-br from-green-400 to-emerald-600 text-white shadow-lg shadow-green-500/25' : 
                                           ($currentIdx === $stepIdx ? 'bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg shadow-blue-500/25 pulse-ring' : 
                                           'bg-gray-100 dark:bg-gray-700 text-muted') }}">
                                        @if($currentIdx > $stepIdx) <i class="fas fa-check"></i> @else {{ $i + 1 }} @endif
                                    </div>
                                    <span class="text-[10px] mt-1.5 font-semibold text-center {{ $currentIdx >= $stepIdx ? 'text-heading' : 'text-muted' }}">{{ $steps[$stepKey] }}</span>
                                </div>
                                @if($i < count($displaySteps) - 1)
                                <div class="flex-1 h-1.5 mx-1 rounded-full transition-all duration-300 {{ $currentIdx > $stepIdx ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Cleaner Info --}}
                    @if($booking->cleaner)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 mb-6">
                        <h4 class="font-bold text-heading mb-4 flex items-center gap-2">
                            <i class="fas fa-user-check text-blue-500"></i> Your Cleaner
                        </h4>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-purple-500 rounded-2xl flex items-center justify-center text-white text-xl font-black shadow-lg flex-shrink-0">
                                {{ strtoupper(substr($booking->cleaner->user->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-heading text-lg">{{ $booking->cleaner->user->full_name }}</p>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="flex items-center gap-1 text-yellow-500"><i class="fas fa-star text-xs"></i> {{ number_format($booking->cleaner->rating, 1) }}</span>
                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                    <span class="text-muted">{{ $booking->cleaner->total_completed_jobs }} jobs</span>
                                </div>
                                <p class="text-xs text-muted font-mono mt-0.5">{{ $booking->cleaner->cleaner_id }}</p>
                            </div>
                        </div>
                        
                        @if($booking->estimated_travel_time_minutes)
                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-500/10 rounded-xl border border-blue-200 dark:border-blue-500/20">
                            <p class="text-blue-700 dark:text-blue-300 text-sm flex items-center gap-2">
                                <i class="fas fa-truck"></i> Estimated arrival: <strong>{{ $booking->estimated_travel_time_minutes }} minutes</strong>
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Service Details --}}
                    <div>
                        <h4 class="font-bold text-heading mb-4 flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-green-500"></i> Service Details
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">Service</p>
                                <p class="text-sm font-bold text-heading">{{ $booking->service->name }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">Type</p>
                                <p class="text-sm font-bold text-heading capitalize">{{ $booking->booking_type }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 col-span-2">
                                <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">Address</p>
                                <p class="text-sm font-bold text-heading">{{ $booking->service_address }}</p>
                            </div>
                            @if($booking->scheduled_at)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 col-span-2">
                                <p class="text-[10px] text-muted uppercase tracking-wider mb-0.5">Scheduled For</p>
                                <p class="text-sm font-bold text-heading">{{ $booking->scheduled_at->format('M d, Y - h:i A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Tracking Map --}}
            @if(in_array($booking->status, ['cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900/40 dark:to-red-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-map-marked-alt text-red-600 dark:text-red-400"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-heading">Live Tracking</h4>
                            <p class="text-xs text-muted">Real-time cleaner location</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div id="trackingMap" style="height: 350px;" class="rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-200 dark:bg-gray-600"></div>
                </div>
            </div>
            @endif
        </div>

        {{-- ============================================ --}}
        {{-- SIDEBAR --}}
        {{-- ============================================ --}}
        <div class="space-y-5">
            
            {{-- Price Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center">
                            <i class="fas fa-receipt text-green-600 dark:text-green-400"></i>
                        </div>
                        <h4 class="font-bold text-heading">Price Breakdown</h4>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-muted">Base Price</span><span class="font-semibold text-heading">TZS {{ number_format($booking->service_base_price) }}</span></div>
                    @if($booking->instant_booking_fee > 0)
                    <div class="flex justify-between text-sm"><span class="text-muted">Instant Booking Fee</span><span class="font-semibold text-orange-600 dark:text-orange-400">TZS {{ number_format($booking->instant_booking_fee) }}</span></div>
                    @endif
                    @if($booking->distance_fee > 0)
                    <div class="flex justify-between text-sm"><span class="text-muted">Distance Fee</span><span class="font-semibold text-heading">TZS {{ number_format($booking->distance_fee) }}</span></div>
                    @endif
                    <hr class="border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between font-bold text-base"><span class="text-heading">Total</span><span class="text-blue-600 dark:text-blue-400">TZS {{ number_format($booking->total_amount) }}</span></div>
                </div>
            </div>

            {{-- AI Score --}}
            @if($booking->ai_recommendation_score)
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-xl shadow-purple-500/25 p-6 text-white text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-6 -mt-6"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3 backdrop-blur">
                        <i class="fas fa-robot text-white text-2xl"></i>
                    </div>
                    <p class="text-blue-100 text-xs uppercase tracking-wider">AI Score</p>
                    <p class="text-5xl font-black mt-2">{{ number_format($booking->ai_recommendation_score, 1) }}%</p>
                    <p class="text-blue-100 text-sm mt-2">Rank #{{ $booking->ai_rank_position }} of {{ $booking->ai_total_cleaners ?? 'N/A' }}</p>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-5 space-y-3">
                    @if($booking->canBeCancelled())
                    <form action="{{ route('homeowner.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Cancel this booking?')">
                        @csrf
                        <button type="submit" class="w-full px-5 py-3.5 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 rounded-xl font-bold text-sm hover:bg-red-100 dark:hover:bg-red-500/20 transition-all duration-300">
                            <i class="fas fa-times-circle mr-2"></i> Cancel Booking
                        </button>
                    </form>
                    @endif
                    
                    @if($booking->status === 'completed' && !$booking->review)
                    <a href="{{ route('homeowner.reviews.store', $booking) }}" 
                       class="flex items-center justify-center gap-2 w-full px-5 py-3.5 bg-gradient-to-r from-yellow-500 to-amber-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-105 transition-all duration-300">
                        <i class="fas fa-star"></i> Leave Review
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(in_array($booking->status, ['cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress']))
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}"></script>
<script>
function bookingTracker() {
    return {
        booking: @json($booking),
        init() { this.initTrackingMap(); if (this.booking.status !== 'completed' && this.booking.status !== 'cancelled') { this.pollStatus(); } },
        initTrackingMap() {
            const el = document.getElementById('trackingMap'); if (!el || typeof google === 'undefined') return;
            const map = new google.maps.Map(el, { zoom: 14, center: { lat: parseFloat(this.booking.service_latitude), lng: parseFloat(this.booking.service_longitude) }, styles: [{ featureType: "poi", stylers: [{ visibility: "off" }] }] });
            new google.maps.Marker({ position: { lat: parseFloat(this.booking.service_latitude), lng: parseFloat(this.booking.service_longitude) }, map, icon: { url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png' }, title: 'Service Location', animation: google.maps.Animation.DROP });
            @if($booking->cleaner && $booking->cleaner->current_latitude)
            new google.maps.Marker({ position: { lat: {{ $booking->cleaner->current_latitude }}, lng: {{ $booking->cleaner->current_longitude }} }, map, icon: { url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png' }, title: 'Cleaner Location', animation: google.maps.Animation.DROP });
            @endif
        },
        async pollStatus() {
            setInterval(async () => {
                try {
                    const res = await fetch(`/homeowner/bookings/{{ $booking->id }}/track`);
                    const data = await res.json();
                    if (data.success && data.tracking.status !== this.booking.status) {
                        this.booking.status = data.tracking.status;
                        if (data.tracking.status === 'completed') location.reload();
                    }
                } catch (e) {}
            }, 30000);
        }
    }
}
</script>
@endif
@endpush