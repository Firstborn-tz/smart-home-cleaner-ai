@extends('layouts.app')

@section('title', 'City Details')
@section('user_role', 'Administrator')
@section('page_title', 'City Details')
@section('page_subtitle', 'Detailed analytics')

@section('content')
<div>
    @php
       
        
        $onlineCleaners = App\Models\Cleaner::where('city_id', $city->id)
            ->where('availability_status', 'online')->count();
        $verifiedCleaners = App\Models\Cleaner::where('city_id', $city->id)
            ->where('is_verified', true)->count();
        $avgRating = App\Models\Cleaner::where('city_id', $city->id)->avg('rating') ?? 0;
        
        $topCleaners = App\Models\Cleaner::with('user')
            ->where('city_id', $city->id)
            ->orderByDesc('rating')
            ->limit(10)->get();
            
        $recentBookings = App\Models\Booking::with(['service', 'cleaner.user', 'homeowner.user'])
            ->where('city_id', $city->id)
            ->latest()->limit(15)->get();
            
        $monthlyStats = App\Models\Booking::where('city_id', $city->id)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total, SUM(total_amount) as revenue, SUM(commission_amount) as commission")
            ->groupBy('month')->orderBy('month', 'desc')->limit(12)->get();
    @endphp

    <!-- Back Button -->
    <a href="/admin/cities" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
        <i class="fas fa-arrow-left mr-2"></i> Back to Cities
    </a>

    <!-- City Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-2xl flex items-center justify-center text-white text-2xl font-extrabold">
                {{ substr($city->code, 0, 2) }}
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ $city->name }}</h2>
                <p class="text-gray-500">{{ $city->region }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold mt-1
                    {{ $city->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $city->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-blue-600">{{ $city->cleaners_count }}</p>
            <p class="text-xs text-gray-500">Total Cleaners</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-green-600">{{ $onlineCleaners }}</p>
            <p class="text-xs text-gray-500">Online Now</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-purple-600">{{ $verifiedCleaners }}</p>
            <p class="text-xs text-gray-500">Verified</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-yellow-600">{{ number_format($avgRating, 1) }}</p>
            <p class="text-xs text-gray-500">Avg Rating</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-orange-600">{{ $city->bookings_count }}</p>
            <p class="text-xs text-gray-500">Bookings</p>
        </div>
    </div>

    <!-- Top Cleaners & Recent Bookings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">Top Cleaners</h3>
            @foreach($topCleaners as $index => $c)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-bold text-gray-400">{{ $index + 1 }}.</span>
                    <span class="font-medium text-gray-800 dark:text-white">{{ $c->user->full_name }}</span>
                </div>
                <span class="text-yellow-500 text-sm">⭐ {{ number_format($c->rating, 1) }}</span>
            </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">Recent Bookings</h3>
            @foreach($recentBookings->take(10) as $b)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm">
                <div>
                    <span class="font-mono text-xs text-gray-500">#{{ $b->booking_number }}</span>
                    <span class="ml-2 text-gray-800 dark:text-white">{{ $b->service->name ?? 'N/A' }}</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold
                    @if($b->status === 'completed') bg-green-100 text-green-700
                    @else bg-blue-100 text-blue-700 @endif">
                    {{ ucfirst($b->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
