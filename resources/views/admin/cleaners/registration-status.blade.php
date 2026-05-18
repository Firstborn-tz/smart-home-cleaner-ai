@extends('layouts.app')

@section('title', 'Registration Status')
@section('user_role', 'Cleaner')
@section('page_title', 'Registration Status')
@section('page_subtitle', 'Track your application progress')

@section('content')
<div>
    @php
        $cleaner = Auth::user()->cleaner;
        $status = $cleaner->registration_status ?? 'pending';
    @endphp

    <!-- Status Banner -->
    @if($status === 'approved')
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-xl p-6 mb-6 text-white text-center">
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-check-circle text-white text-3xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold">Registration Approved!</h2>
        <p class="text-green-100 mt-1">You can now receive bookings and start earning</p>
        <a href="/cleaner/dashboard" class="inline-block mt-4 px-6 py-2 bg-white text-green-600 rounded-xl font-bold hover:shadow-lg transition">
            <i class="fas fa-th-large mr-2"></i> Go to Dashboard
        </a>
    </div>
    @elseif($status === 'rejected')
    <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-2xl shadow-xl p-6 mb-6 text-white text-center">
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-times-circle text-white text-3xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold">Registration Not Approved</h2>
        <p class="text-red-100 mt-1">{{ $cleaner->registration_notes ?? 'Your application was not approved. You may reapply.' }}</p>
        <a href="/register/cleaner" class="inline-block mt-4 px-6 py-2 bg-white text-red-600 rounded-xl font-bold hover:shadow-lg transition">
            <i class="fas fa-redo mr-2"></i> Reapply
        </a>
    </div>
    @else
    <div class="bg-gradient-to-r from-yellow-500 to-orange-600 rounded-2xl shadow-xl p-6 mb-6 text-white text-center">
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-clock text-white text-3xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold">Application Under Review</h2>
        <p class="text-yellow-100 mt-1">Your application is being reviewed by our team. You will be notified once approved.</p>
        <p class="text-yellow-100 text-sm mt-2">Estimated review time: 24-48 hours</p>
    </div>
    @endif

    <!-- Submitted Details -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-clipboard-list text-blue-500 mr-2"></i> Your Submitted Details
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Personal Info -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-3">
                    <i class="fas fa-user text-blue-500 mr-1"></i> Personal Information
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Full Name</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ Auth::user()->full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Phone</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ Auth::user()->phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gender</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ ucfirst($cleaner->gender ?? 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date of Birth</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->date_of_birth ? \Carbon\Carbon::parse($cleaner->date_of_birth)->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">NIDA Number</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->national_id ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Location Info -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-3">
                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i> Location Details
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">City</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->city->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Region</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->region ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Street</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->street ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ward/District</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->ward ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Service Radius</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->max_service_radius_km ?? 30 }} km</span>
                    </div>
                </div>
            </div>

            <!-- Registration Info -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-3">
                    <i class="fas fa-info-circle text-purple-500 mr-1"></i> Registration Info
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cleaner ID</span>
                        <span class="font-bold text-gray-800 dark:text-white font-mono">{{ $cleaner->cleaner_id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold
                            @if($status === 'approved') bg-green-100 text-green-700
                            @elseif($status === 'rejected') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Submitted</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $cleaner->created_at->format('M d, Y - h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Equipment</span>
                        <span class="font-bold text-gray-800 dark:text-white">Yes, I have my own</span>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-3">
                    <i class="fas fa-list-ol text-blue-500 mr-1"></i> Next Steps
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    @if($status === 'pending')
                    <li><i class="fas fa-clock text-yellow-500 mr-1"></i> Wait for admin review (24-48 hours)</li>
                    <li><i class="fas fa-bell text-blue-500 mr-1"></i> Check notifications for updates</li>
                    <li><i class="fas fa-tools text-green-500 mr-1"></i> After approval, add your services & pricing</li>
                    @elseif($status === 'approved')
                    <li><i class="fas fa-check-circle text-green-500 mr-1"></i> Set your availability to ONLINE</li>
                    <li><i class="fas fa-tools text-blue-500 mr-1"></i> Add your services and set prices</li>
                    <li><i class="fas fa-store text-purple-500 mr-1"></i> Complete your business profile</li>
                    @else
                    <li><i class="fas fa-redo text-blue-500 mr-1"></i> Review the rejection reason</li>
                    <li><i class="fas fa-edit text-green-500 mr-1"></i> Update your information and reapply</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection