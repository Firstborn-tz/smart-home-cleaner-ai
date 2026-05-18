@extends('layouts.homeowner')

@section('title', 'Booking Details')
@section('page_title', 'Booking #' . $booking->booking_number)

@section('content')
<div x-data="bookingTracker()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Booking Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <span class="px-4 py-2 rounded-full text-sm font-medium
                            @if($booking->status === 'completed') bg-green-100 text-green-700
                            @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                            @elseif(in_array($booking->status, ['cleaner_accepted', 'in_progress'])) bg-blue-100 text-blue-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $booking->created_at->format('M d, Y - h:i A') }}</p>
                </div>
                
                <!-- Progress Tracker -->
                <div class="mb-8">
                    @php
                        $statuses = ['pending', 'cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress', 'completed'];
                        $currentIndex = array_search($booking->status, $statuses);
                    @endphp
                    <div class="flex items-center justify-between">
                        @foreach(['Assigned', 'Accepted', 'En Route', 'Arrived', 'In Progress', 'Completed'] as $i => $label)
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $currentIndex > $i ? 'bg-green-500 text-white' : ($currentIndex === $i ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                                    {{ $currentIndex > $i ? '✓' : $i + 1 }}
                                </div>
                                <span class="text-xs mt-1 text-gray-500">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Cleaner Info -->
                @if($booking->cleaner)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 mb-6">
                    <h4 class="font-bold text-lg mb-4">Your Cleaner</h4>
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($booking->cleaner->user->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-xl">{{ $booking->cleaner->user->full_name }}</p>
                            <div class="flex items-center space-x-2">
                                <span class="text-yellow-500">⭐ {{ number_format($booking->cleaner->rating, 1) }}</span>
                                <span class="text-gray-500">|</span>
                                <span class="text-gray-600">{{ $booking->cleaner->total_completed_jobs }} jobs completed</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $booking->cleaner->cleaner_id }}</p>
                        </div>
                    </div>
                    
                    @if($booking->estimated_travel_time_minutes)
                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <p class="text-blue-700 dark:text-blue-300">
                            <i class="fas fa-truck mr-2"></i>
                            Estimated arrival: <strong>{{ $booking->estimated_travel_time_minutes }} minutes</strong>
                        </p>
                    </div>
                    @endif
                </div>
                @endif
                
                <!-- Service Details -->
                <div class="space-y-4">
                    <h4 class="font-bold text-lg">Service Details</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Service</p>
                            <p class="font-semibold">{{ $booking->service->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Type</p>
                            <p class="font-semibold capitalize">{{ $booking->booking_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Address</p>
                            <p class="font-semibold">{{ $booking->service_address }}</p>
                        </div>
                        @if($booking->scheduled_at)
                        <div>
                            <p class="text-sm text-gray-500">Scheduled For</p>
                            <p class="font-semibold">{{ $booking->scheduled_at->format('M d, Y - h:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Map Tracking -->
            @if(in_array($booking->status, ['cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                <h4 class="font-bold text-lg mb-4">Live Tracking</h4>
                <div id="trackingMap" style="height: 400px;" class="rounded-xl"></div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Price Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h4 class="font-bold text-lg mb-4">Price Breakdown</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Base Price</span>
                        <span>TZS {{ number_format($booking->service_base_price) }}</span>
                    </div>
                    @if($booking->instant_booking_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Instant Booking Fee</span>
                        <span class="text-orange-600">TZS {{ number_format($booking->instant_booking_fee) }}</span>
                    </div>
                    @endif
                    @if($booking->distance_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Distance Fee</span>
                        <span>TZS {{ number_format($booking->distance_fee) }}</span>
                    </div>
                    @endif
                    <hr class="border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-blue-600">TZS {{ number_format($booking->total_amount) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- AI Score -->
            @if($booking->ai_recommendation_score)
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white text-center">
                <p class="text-blue-100 text-sm">AI Recommendation Score</p>
                <p class="text-5xl font-bold mt-2">{{ number_format($booking->ai_recommendation_score, 1) }}%</p>
                <p class="text-blue-100 text-sm mt-2">Rank #{{ $booking->ai_rank_position }}</p>
            </div>
            @endif
            
            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                @if($booking->canBeCancelled())
                <form action="{{ route('homeowner.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Cancel this booking?')">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-red-100 text-red-700 rounded-xl hover:bg-red-200 font-medium">
                        Cancel Booking
                    </button>
                </form>
                @endif
                
                @if($booking->status === 'completed' && !$booking->review)
                <a href="{{ route('homeowner.reviews.store', $booking) }}" 
                   class="block text-center w-full px-6 py-3 bg-yellow-500 text-white rounded-xl hover:bg-yellow-600 font-medium mt-3">
                    <i class="fas fa-star mr-2"></i> Leave Review
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(in_array($booking->status, ['cleaner_accepted', 'cleaner_en_route', 'cleaner_arrived', 'in_progress']))
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}"></script>
<script>
function bookingTracker() {
    return {
        booking: @json($booking),
        
        init() {
            this.initTrackingMap();
            if (this.booking.status !== 'completed') {
                this.pollStatus();
            }
        },
        
        initTrackingMap() {
            const map = new google.maps.Map(document.getElementById('trackingMap'), {
                zoom: 14,
                center: {
                    lat: parseFloat(this.booking.service_latitude),
                    lng: parseFloat(this.booking.service_longitude)
                }
            });
            
            // Service location marker
            new google.maps.Marker({
                position: {
                    lat: parseFloat(this.booking.service_latitude),
                    lng: parseFloat(this.booking.service_longitude)
                },
                map: map,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                },
                title: 'Service Location'
            });
            
            @if($booking->cleaner && $booking->cleaner->current_latitude)
            // Cleaner location marker
            new google.maps.Marker({
                position: {
                    lat: {{ $booking->cleaner->current_latitude }},
                    lng: {{ $booking->cleaner->current_longitude }}
                },
                map: map,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                },
                title: 'Cleaner Location'
            });
            @endif
        },
        
        async pollStatus() {
            setInterval(async () => {
                try {
                    const res = await fetch(`/homeowner/bookings/{{ $booking->id }}/track`);
                    const data = await res.json();
                    if (data.success && data.tracking.status !== this.booking.status) {
                        this.booking.status = data.tracking.status;
                        if (data.tracking.status === 'completed') {
                            location.reload();
                        }
                    }
                } catch (e) {
                    console.error('Polling failed:', e);
                }
            }, 30000); // Poll every 30 seconds
        }
    }
}
</script>
@endif
@endpush
@endsection