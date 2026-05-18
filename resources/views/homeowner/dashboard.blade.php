@extends('layouts.app')

@section('title', 'Homeowner Dashboard')
@section('user_role', 'Homeowner')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Manage your cleaning services')

@section('content')
<div class="space-y-6">
    
    @php
        $homeowner = Auth::user()->homeowner;
        $activeBookings = App\Models\Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['cleaner_assigned', 'cleaner_accepted', 'in_progress'])
            ->latest()->get();
        $recentBookings = App\Models\Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->latest()->limit(5)->get();
    @endphp

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Bookings</p>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white mt-1">{{ $homeowner->total_bookings ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Active</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $activeBookings->count() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Completed</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ $homeowner->total_completed_bookings ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Rating</p>
            <p class="text-2xl font-extrabold text-yellow-600 mt-1"><i class="fas fa-star mr-1"></i> {{ number_format($homeowner->rating ?? 0, 1) }}</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">Quick Actions</h3>
        <a href="/homeowner/bookings/create" class="inline-flex items-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold transition">
            <i class="fas fa-plus-circle mr-2"></i> Book a Cleaner
        </a>
    </div>

    <!-- Active Bookings -->
    @if($activeBookings->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">Active Bookings</h3>
        <div class="space-y-4">
            @foreach($activeBookings as $booking)
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">{{ $booking->service->name ?? 'Service' }}</p>
                    <p class="text-sm text-gray-500">{{ $booking->created_at->diffForHumans() }}</p>
                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                </div>
                <a href="/homeowner/bookings/{{ $booking->id }}/track" class="px-4 py-2 bg-blue-500 text-white rounded-xl text-sm font-bold">Track</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Bookings -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">Recent Bookings</h3>
        @if($recentBookings->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-600">
                    <tr><th class="text-left py-2 text-gray-500">Booking</th><th class="text-left py-2 text-gray-500">Service</th><th class="text-left py-2 text-gray-500">Status</th><th class="text-left py-2 text-gray-500">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $b)
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-2 font-mono text-xs">{{ $b->booking_number }}</td>
                        <td class="py-2">{{ $b->service->name ?? 'N/A' }}</td>
                        <td class="py-2"><span class="px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-600">{{ ucfirst($b->status) }}</span></td>
                        <td class="py-2"><a href="/homeowner/bookings/{{ $b->id }}/track" class="text-blue-600">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">No bookings yet. <a href="/homeowner/bookings/create" class="text-blue-600 font-bold">Book now!</a></p>
        @endif
    </div>
</div>
@endsection