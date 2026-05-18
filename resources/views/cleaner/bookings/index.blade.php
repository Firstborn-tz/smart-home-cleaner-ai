@extends('layouts.app')

@section('title', 'My Jobs')
@section('user_role', 'Cleaner')
@section('page_title', 'My Jobs')
@section('page_subtitle', 'View and manage your bookings')

@section('content')
<div>
    @php $cleaner = Auth::user()->cleaner; @endphp

    <!-- Tab Navigation -->
    <div class="flex space-x-2 mb-6 overflow-x-auto">
        @php
            $tabs = ['pending' => 'Pending', 'cleaner_assigned' => 'Assigned', 'cleaner_accepted' => 'Accepted', 'in_progress' => 'In Progress', 'completed' => 'Completed'];
            $activeTab = request('tab', 'pending');
        @endphp
        @foreach($tabs as $key => $label)
            <a href="?tab={{ $key }}" 
               class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition
                      {{ $activeTab === $key ? 'bg-blue-500 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @php
        $bookings = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->when($activeTab === 'pending', fn($q) => $q->whereIn('status', ['pending', 'cleaner_assigned']))
            ->when(!in_array($activeTab, ['pending']), fn($q) => $q->where('status', $activeTab))
            ->latest()->get();
    @endphp

    @if($bookings->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($bookings as $booking)
        <a href="/cleaner/bookings/{{ $booking->id }}/detail" 
           class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all border-l-4
                  @if($booking->booking_type === 'instant') border-red-500
                  @elseif($booking->status === 'completed') border-green-500
                  @elseif($booking->status === 'in_progress') border-blue-500
                  @else border-yellow-500 @endif">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">#{{ $booking->booking_number }}</span>
                <span class="px-2 py-1 rounded-full text-xs font-bold
                    @if($booking->booking_type === 'instant') bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300
                    @else bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 @endif">
                    <i class="fas {{ $booking->booking_type === 'instant' ? 'fa-bolt' : 'fa-calendar' }} mr-1"></i>
                    {{ $booking->booking_type === 'instant' ? 'Instant' : 'Scheduled' }}
                </span>
            </div>
            <h3 class="font-bold text-lg text-gray-800 dark:text-white">{{ $booking->service->name ?? 'Service' }}</h3>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mt-2
                @if($booking->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                @elseif($booking->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                @elseif($booking->status === 'cleaner_accepted') bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 @endif">
                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
            </span>
            <div class="space-y-2 mt-3 text-sm">
                <p class="text-gray-500 dark:text-gray-400"><i class="fas fa-map-marker-alt text-red-400 w-4 mr-1"></i> {{ Str::limit($booking->service_address, 35) }}</p>
                <p class="text-gray-500 dark:text-gray-400"><i class="fas fa-road w-4 mr-1"></i> {{ $booking->distance_km ? round($booking->distance_km, 1) . ' km' : 'N/A' }}</p>
                <p class="text-green-600 dark:text-green-400 font-bold"><i class="fas fa-money-bill-wave w-4 mr-1"></i> TZS {{ number_format($booking->cleaner_payout_amount) }}</p>
                <p class="text-gray-400 text-xs"><i class="fas fa-clock w-4 mr-1"></i> {{ $booking->created_at->diffForHumans() }}</p>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-12 text-center">
        <i class="fas fa-inbox text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
        <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300">No {{ $activeTab }} Jobs</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Make sure you are <strong class="text-green-600">ONLINE</strong> to receive requests</p>
    </div>
    @endif
</div>
@endsection