@extends('layouts.app')

@section('title', 'Cleaner Dashboard')
@section('user_role', 'Cleaner Portal')
@section('page_title', 'Dashboard')

@section('content')
<div x-data="cleanerDashboard()" x-init="init()">
    
    @php
        $cleaner = Auth::user()->cleaner;
        $currentStatus = $cleaner->availability_status ?? 'offline';
        
        $pendingRequests = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->whereIn('status', ['pending', 'cleaner_assigned'])
            ->latest()
            ->take(5)
            ->get();
            
        $activeJobs = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->whereIn('status', ['cleaner_accepted', 'in_progress'])
            ->latest()
            ->get();
            
        $todayEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->whereDate('completed_at', today())
            ->sum('cleaner_payout_amount');
            
        $completedJobs = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();
    @endphp

    <!-- AVAILABILITY TOGGLE CARD -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6 animate-slide-up">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            
            <!-- Cleaner Info -->
            <div class="flex items-center space-x-5 mb-6 lg:mb-0">
                <div class="relative">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-xl">
                        <span class="text-3xl font-extrabold text-white">{{ number_format($cleaner->rating ?? 0, 1) }}</span>
                    </div>
                    <div id="statusDot" class="absolute -bottom-2 -right-2 w-10 h-10 rounded-full border-4 border-white dark:border-gray-800 shadow-lg"
                         style="background-color: {{ $currentStatus === 'online' ? '#22c55e' : ($currentStatus === 'online_busy' ? '#eab308' : '#9ca3af') }}"></div>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ Auth::user()->full_name }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $cleaner->cleaner_id ?? 'No ID' }}</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> {{ $cleaner->city->name ?? 'No City' }}</p>
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-bold" id="statusBadge"
                          style="background-color: {{ $currentStatus === 'online' ? '#dcfce7' : ($currentStatus === 'online_busy' ? '#fef9c3' : '#f3f4f6') }};
                                 color: {{ $currentStatus === 'online' ? '#166534' : ($currentStatus === 'online_busy' ? '#854d0e' : '#4b5563') }}">
                        {{ ucfirst(str_replace('_', ' ', $currentStatus)) }}
                    </span>
                </div>
            </div>

            <!-- Toggle Switch -->
            <div class="text-center">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Availability</p>
                <button id="toggleBtn" onclick="toggleAvailability()"
                        class="relative inline-flex items-center h-16 w-32 rounded-full transition-all duration-300 shadow-xl"
                        style="background-color: {{ $currentStatus === 'online' ? '#22c55e' : '#d1d5db' }}">
                    <span id="toggleKnob" class="inline-block w-14 h-14 transform transition-all duration-300 bg-white rounded-full shadow-md flex items-center justify-center"
                          style="transform: translateX({{ $currentStatus === 'online' ? '64px' : '4px' }})">
                        <i class="fas text-xl" id="toggleIcon"
                           style="color: {{ $currentStatus === 'online' ? '#22c55e' : '#9ca3af' }}"></i>
                    </span>
                </button>
                <p id="toggleText" class="text-lg font-extrabold mt-2"
                   style="color: {{ $currentStatus === 'online' ? '#16a34a' : '#6b7280' }}">
                    {{ $currentStatus === 'online' ? 'ONLINE' : 'OFFLINE' }}
                </p>
            </div>
        </div>

        <!-- Status Quick Select -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-8">
            <button onclick="setStatus('online')" 
                    class="p-4 rounded-xl border-2 transition-all text-center hover:shadow-lg status-card"
                    data-status="online">
                <i class="fas fa-circle text-green-500 text-2xl mb-2"></i>
                <p class="font-bold text-sm text-gray-800 dark:text-white">Online</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Available for bookings</p>
            </button>
            <button onclick="setStatus('online_busy')" 
                    class="p-4 rounded-xl border-2 transition-all text-center hover:shadow-lg status-card"
                    data-status="online_busy">
                <i class="fas fa-clock text-yellow-500 text-2xl mb-2"></i>
                <p class="font-bold text-sm text-gray-800 dark:text-white">Busy</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Currently on a job</p>
            </button>
            <button onclick="setStatus('scheduled_only')" 
                    class="p-4 rounded-xl border-2 transition-all text-center hover:shadow-lg status-card"
                    data-status="scheduled_only">
                <i class="fas fa-calendar-alt text-blue-500 text-2xl mb-2"></i>
                <p class="font-bold text-sm text-gray-800 dark:text-white">Scheduled Only</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Future bookings only</p>
            </button>
            <button onclick="setStatus('offline')" 
                    class="p-4 rounded-xl border-2 transition-all text-center hover:shadow-lg status-card"
                    data-status="offline">
                <i class="fas fa-power-off text-gray-500 text-2xl mb-2"></i>
                <p class="font-bold text-sm text-gray-800 dark:text-white">Offline</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Not receiving requests</p>
            </button>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 animate-slide-up">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today's Earnings</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">TZS {{ number_format($todayEarnings) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed Jobs</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $cleaner->total_completed_jobs ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rating</p>
                    <p class="text-2xl font-extrabold text-yellow-600 mt-1">
                        <i class="fas fa-star text-yellow-400 mr-1"></i> {{ number_format($cleaner->rating ?? 0, 1) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Payout</p>
                    <p class="text-2xl font-extrabold text-purple-600 mt-1">TZS {{ number_format($cleaner->pending_payout ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-wallet text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- PENDING REQUESTS -->
    @if($pendingRequests->count() > 0)
    <div class="mb-6 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-extrabold text-gray-800 dark:text-white">
                <i class="fas fa-bell text-yellow-500 mr-2"></i> Pending Requests
            </h3>
            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold pulse-red">{{ $pendingRequests->count() }} New</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($pendingRequests as $req)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-l-4 border-yellow-500 card-hover">
                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full text-xs font-bold">NEW REQUEST</span>
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400">#{{ $req->booking_number }}</span>
                </div>
                <h4 class="font-bold text-lg text-gray-800 dark:text-white">{{ $req->service->name ?? 'Service' }}</h4>
                <div class="space-y-2 mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt text-red-400 mr-2 w-4"></i> {{ Str::limit($req->service_address, 40) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user text-gray-400 mr-2 w-4"></i> {{ $req->homeowner->user->full_name ?? 'N/A' }}
                    </p>
                </div>
                <div class="flex items-center space-x-4 mt-3 text-sm">
                    <span class="text-green-600 dark:text-green-400 font-bold">TZS {{ number_format($req->cleaner_payout_amount) }}</span>
                    <span class="text-gray-400">|</span>
                    <span class="{{ $req->booking_type === 'instant' ? 'text-red-600' : 'text-blue-600' }} font-medium">
                        <i class="fas {{ $req->booking_type === 'instant' ? 'fa-bolt' : 'fa-calendar' }} mr-1"></i>
                        {{ $req->booking_type === 'instant' ? 'Immediate' : 'Scheduled' }}
                    </span>
                </div>
                <div class="flex space-x-2 mt-4">
                    <a href="/cleaner/bookings/{{ $req->id }}/detail" 
                       class="flex-1 text-center px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-sm transition">
                        <i class="fas fa-eye mr-1"></i> View & Respond
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ACTIVE JOBS -->
    @if($activeJobs->count() > 0)
    <div class="mb-6 animate-slide-up">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-spinner text-blue-500 mr-2"></i> Active Jobs
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($activeJobs as $job)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-l-4 {{ $job->status === 'in_progress' ? 'border-blue-500' : 'border-purple-500' }} card-hover">
                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $job->status === 'in_progress' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300' }}">
                        <i class="fas {{ $job->status === 'in_progress' ? 'fa-tools' : 'fa-check' }} mr-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                    </span>
                    <span class="text-xs font-mono text-gray-500">#{{ $job->booking_number }}</span>
                </div>
                <h4 class="font-bold text-lg text-gray-800 dark:text-white">{{ $job->service->name ?? 'Service' }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    <i class="fas fa-user mr-1"></i> {{ $job->homeowner->user->full_name ?? 'N/A' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-map-marker-alt text-red-400 mr-1"></i> {{ Str::limit($job->service_address, 40) }}
                </p>
                <div class="flex items-center space-x-4 mt-3">
                    <span class="text-green-600 dark:text-green-400 font-bold">TZS {{ number_format($job->cleaner_payout_amount) }}</span>
                    @if($job->distance_km)
                    <span class="text-gray-400">|</span>
                    <span class="text-sm text-gray-500"><i class="fas fa-road mr-1"></i> {{ round($job->distance_km, 1) }} km</span>
                    @endif
                </div>
                <a href="/cleaner/bookings/{{ $job->id }}/detail" 
                   class="block text-center mt-4 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-sm transition">
                    <i class="fas fa-external-link-alt mr-1"></i> View Details
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- COMPLETED JOBS -->
    @if($completedJobs->count() > 0 && $activeJobs->count() == 0 && $pendingRequests->count() == 0)
    <div class="mb-6 animate-slide-up">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-check-circle text-green-500 mr-2"></i> Recent Completed Jobs
        </h3>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Booking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Earning</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($completedJobs as $job)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-4 font-mono text-sm">#{{ $job->booking_number }}</td>
                            <td class="px-6 py-4">{{ $job->service->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $job->completed_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4 font-bold text-green-600">TZS {{ number_format($job->cleaner_payout_amount) }}</td>
                            <td class="px-6 py-4">
                                @if($job->cleaner_rating_given)
                                <span class="text-yellow-500"><i class="fas fa-star"></i> {{ $job->cleaner_rating_given }}</span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- EMPTY STATE -->
    @if($pendingRequests->count() == 0 && $activeJobs->count() == 0 && $completedJobs->count() == 0)
    <div class="text-center py-16 animate-slide-up">
        <div class="w-32 h-32 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-broom text-gray-400 dark:text-gray-500 text-5xl"></i>
        </div>
        <h3 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-2">Ready to Start?</h3>
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
            @if($currentStatus === 'online')
                You're <strong class="text-green-600">ONLINE</strong> and ready to receive booking requests!
            @else
                Turn <strong class="text-green-600">ONLINE</strong> to start receiving booking requests from homeowners.
            @endif
        </p>
        @if($currentStatus !== 'online')
        <button onclick="setStatus('online')" class="px-8 py-4 bg-green-500 hover:bg-green-600 text-white rounded-2xl font-bold text-lg transition pulse-green">
            <i class="fas fa-power-off mr-2"></i> Go Online Now
        </button>
        @endif
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    let currentStatus = '{{ $currentStatus }}';

    function showToast(msg, type) {
        window.showToast(msg, type);
    }

    function updateUI(status) {
        currentStatus = status;
        const dot = document.getElementById('statusDot');
        const badge = document.getElementById('statusBadge');
        const btn = document.getElementById('toggleBtn');
        const knob = document.getElementById('toggleKnob');
        const icon = document.getElementById('toggleIcon');
        const text = document.getElementById('toggleText');

        if (status === 'online') {
            btn.style.backgroundColor = '#22c55e';
            knob.style.transform = 'translateX(64px)';
            icon.className = 'fas fa-check text-xl';
            icon.style.color = '#22c55e';
            text.textContent = 'ONLINE';
            text.style.color = '#16a34a';
            dot.style.backgroundColor = '#22c55e';
            dot.classList.add('pulse-green');
            badge.textContent = 'Online';
            badge.style.backgroundColor = '#dcfce7';
            badge.style.color = '#166534';
        } else if (status === 'online_busy') {
            btn.style.backgroundColor = '#eab308';
            knob.style.transform = 'translateX(64px)';
            icon.className = 'fas fa-clock text-xl';
            icon.style.color = '#eab308';
            text.textContent = 'BUSY';
            text.style.color = '#a16207';
            dot.style.backgroundColor = '#eab308';
            dot.classList.remove('pulse-green');
            badge.textContent = 'Online Busy';
            badge.style.backgroundColor = '#fef9c3';
            badge.style.color = '#854d0e';
        } else if (status === 'scheduled_only') {
            btn.style.backgroundColor = '#3b82f6';
            knob.style.transform = 'translateX(64px)';
            icon.className = 'fas fa-calendar-alt text-xl';
            icon.style.color = '#3b82f6';
            text.textContent = 'SCHEDULED';
            text.style.color = '#1d4ed8';
            dot.style.backgroundColor = '#3b82f6';
            dot.classList.remove('pulse-green');
            badge.textContent = 'Scheduled Only';
            badge.style.backgroundColor = '#eff6ff';
            badge.style.color = '#1e3a5f';
        } else {
            btn.style.backgroundColor = '#d1d5db';
            knob.style.transform = 'translateX(4px)';
            icon.className = 'fas fa-times text-xl';
            icon.style.color = '#9ca3af';
            text.textContent = 'OFFLINE';
            text.style.color = '#6b7280';
            dot.style.backgroundColor = '#9ca3af';
            dot.classList.remove('pulse-green');
            badge.textContent = 'Offline';
            badge.style.backgroundColor = '#f3f4f6';
            badge.style.color = '#4b5563';
        }

        // Update status cards
        document.querySelectorAll('.status-card').forEach(card => {
            card.style.borderColor = card.dataset.status === status ? 
                (status === 'online' ? '#22c55e' : status === 'online_busy' ? '#eab308' : status === 'scheduled_only' ? '#3b82f6' : '#6b7280') 
                : '#e5e7eb';
            card.style.background = card.dataset.status === status ? 
                (status === 'online' ? '#f0fdf4' : status === 'online_busy' ? '#fefce8' : status === 'scheduled_only' ? '#eff6ff' : '#f9fafb') 
                : 'white';
        });
    }

    async function toggleAvailability() {
        const newStatus = currentStatus === 'online' ? 'offline' : 'online';
        await setStatus(newStatus);
    }

    async function setStatus(status) {
        try {
            let data = { status };
            if (status === 'online' && navigator.geolocation) {
                try {
                    const pos = await new Promise((res, rej) => 
                        navigator.geolocation.getCurrentPosition(res, rej, { enableHighAccuracy: true, timeout: 5000 })
                    );
                    data.latitude = pos.coords.latitude;
                    data.longitude = pos.coords.longitude;
                } catch(e) {}
            }

            const res = await fetch('/cleaner/availability/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                updateUI(status);
                showToast(result.message, 'success');
                if (status === 'offline') setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (e) {
            showToast('Failed to update status. Check your connection.', 'error');
        }
    }

    // Auto-refresh every 60 seconds
    setInterval(() => location.reload(), 60000);
</script>
@endpush