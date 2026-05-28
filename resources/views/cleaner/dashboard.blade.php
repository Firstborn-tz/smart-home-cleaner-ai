@extends('layouts.app')

@section('title', 'Cleaner Dashboard')
@section('user_role', 'Cleaner Portal')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Welcome back, ' . Auth::user()->first_name)

@section('content')
<div x-data="cleanerDashboard()" x-init="init()">
    @php
        $cleaner = Auth::user()->cleaner;
        $currentStatus = $cleaner->availability_status ?? 'offline';
        
        // Only truly pending (not yet responded)
        $pendingRequests = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->where('status', 'pending')
            ->where('timeout_at', '>', now())
            ->latest()->take(5)->get();
            
        // Accepted, arrived, in progress
        $activeJobs = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->whereIn('status', ['cleaner_assigned', 'cleaner_accepted', 'cleaner_arrived', 'in_progress'])
            ->latest()->get();
            
        $todayEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->whereDate('completed_at', today())->sum('cleaner_payout_amount');
            
        $completedJobs = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)->where('status', 'completed')
            ->latest()->limit(5)->get();
            
        $statusConfig = [
            'online' => ['color' => 'green', 'bg' => 'bg-green-500', 'lightBg' => 'bg-green-100', 'text' => 'text-green-700', 'dot' => 'bg-green-500 animate-pulse', 'label' => 'Online', 'icon' => 'fa-check'],
            'online_busy' => ['color' => 'yellow', 'bg' => 'bg-yellow-500', 'lightBg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500', 'label' => 'Busy', 'icon' => 'fa-clock'],
            'scheduled_only' => ['color' => 'blue', 'bg' => 'bg-blue-500', 'lightBg' => 'bg-blue-100', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500', 'label' => 'Scheduled', 'icon' => 'fa-calendar-alt'],
            'offline' => ['color' => 'gray', 'bg' => 'bg-gray-400', 'lightBg' => 'bg-gray-100', 'text' => 'text-gray-600', 'dot' => 'bg-gray-400', 'label' => 'Offline', 'icon' => 'fa-times'],
        ];
        $s = $statusConfig[$currentStatus] ?? $statusConfig['offline'];
    @endphp

    {{-- AVAILABILITY TOGGLE CARD --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 sm:p-8 mb-6 animate-slide-up">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            
            <div class="flex items-center gap-5">
                <div class="relative flex-shrink-0">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-xl shadow-green-500/25">
                        <span class="text-3xl font-black text-white">{{ number_format($cleaner->rating ?? 0, 1) }}</span>
                    </div>
                    <div id="statusDot" class="absolute -bottom-1.5 -right-1.5 w-9 h-9 rounded-xl border-[3px] border-white dark:border-gray-800 shadow-lg {{ $s['bg'] }}"></div>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-heading tracking-tight">{{ Auth::user()->full_name }}</h2>
                    <p class="text-sm text-muted font-mono">{{ $cleaner->cleaner_id ?? 'No ID' }}</p>
                    <div class="flex items-center gap-1.5 text-sm text-muted mt-0.5">
                        <i class="fas fa-map-marker-alt text-red-400 text-xs"></i>
                        {{ $cleaner->city->name ?? 'No City' }}
                    </div>
                    <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-xs font-bold {{ $s['lightBg'] }} {{ $s['text'] }} border border-{{ $s['color'] }}-200 dark:border-{{ $s['color'] }}-500/20" id="statusBadge">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $s['dot'] }}"></span>
                        {{ $s['label'] }}
                    </span>
                </div>
            </div>

            <div class="text-center flex-shrink-0">
                <p class="text-sm font-bold text-heading mb-3">Availability</p>
                <button id="toggleBtn" onclick="toggleAvailability()"
                        class="relative inline-flex items-center h-16 w-[130px] rounded-full transition-all duration-300 shadow-xl {{ $s['bg'] }}">
                    <span id="toggleKnob" class="inline-flex items-center justify-center w-14 h-14 transform transition-all duration-300 bg-white rounded-full shadow-md"
                          style="transform: translateX({{ $currentStatus !== 'offline' ? '66px' : '4px' }})">
                        <i class="fas {{ $s['icon'] }} text-xl" id="toggleIcon" style="color: {{ $currentStatus !== 'offline' ? '#16a34a' : '#9ca3af' }}"></i>
                    </span>
                </button>
                <p id="toggleText" class="text-lg font-black mt-2 text-{{ $s['color'] }}-600 dark:text-{{ $s['color'] }}-400">
                    {{ strtoupper($currentStatus === 'offline' ? 'OFFLINE' : $s['label']) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
            @php
                $quickStatuses = [
                    ['status' => 'online', 'icon' => 'fa-circle', 'color' => 'green', 'label' => 'Online', 'desc' => 'Available for bookings'],
                    ['status' => 'online_busy', 'icon' => 'fa-clock', 'color' => 'yellow', 'label' => 'Busy', 'desc' => 'Currently on a job'],
                    ['status' => 'scheduled_only', 'icon' => 'fa-calendar-alt', 'color' => 'blue', 'label' => 'Scheduled', 'desc' => 'Future bookings only'],
                    ['status' => 'offline', 'icon' => 'fa-power-off', 'color' => 'gray', 'label' => 'Offline', 'desc' => 'Not receiving requests'],
                ];
            @endphp
            @foreach($quickStatuses as $qs)
            <button onclick="setStatus('{{ $qs['status'] }}')" 
                    class="p-4 rounded-2xl border-2 transition-all duration-300 text-center hover:shadow-lg status-card group"
                    data-status="{{ $qs['status'] }}">
                <div class="w-12 h-12 bg-{{ $qs['color'] }}-100 dark:bg-{{ $qs['color'] }}-500/10 rounded-xl flex items-center justify-center mx-auto mb-2.5 group-hover:scale-110 transition-transform">
                    <i class="fas {{ $qs['icon'] }} text-{{ $qs['color'] }}-500 text-xl"></i>
                </div>
                <p class="font-bold text-sm text-heading">{{ $qs['label'] }}</p>
                <p class="text-[10px] text-muted mt-0.5">{{ $qs['desc'] }}</p>
            </button>
            @endforeach
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
        @php
            $statsCards = [
                ['label' => "Today's Earnings", 'value' => 'TZS ' . number_format($todayEarnings), 'icon' => 'fa-money-bill-wave', 'color' => 'green'],
                ['label' => 'Completed Jobs', 'value' => $cleaner->total_completed_jobs ?? 0, 'icon' => 'fa-check-circle', 'color' => 'blue'],
                ['label' => 'Rating', 'value' => number_format($cleaner->rating ?? 0, 1), 'icon' => 'fa-star', 'color' => 'yellow'],
                ['label' => 'Pending Payout', 'value' => 'TZS ' . number_format($cleaner->pending_payout ?? 0), 'icon' => 'fa-wallet', 'color' => 'purple'],
            ];
        @endphp
        @foreach($statsCards as $stat)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 card-hover-lift group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-muted font-medium uppercase tracking-wider">{{ $stat['label'] }}</p>
                <div class="w-10 h-10 bg-{{ $stat['color'] }}-100 dark:bg-{{ $stat['color'] }}-500/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- PENDING REQUESTS (only status=pending, not expired) --}}
    @if($pendingRequests->count() > 0)
    <div class="mb-6 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-500/25">
                    <i class="fas fa-bell text-white"></i>
                </div>
                <h3 class="text-xl font-black text-heading">Pending Requests</h3>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white rounded-full text-xs font-bold animate-pulse shadow-lg shadow-red-500/25">
                {{ $pendingRequests->count() }} New
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5">
            @foreach($pendingRequests as $req)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 border-l-4 border-l-yellow-500 overflow-hidden card-hover-lift group">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 rounded-full text-[10px] font-bold border border-yellow-200">
                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span> NEW REQUEST
                        </span>
                        <span class="text-xs font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg">#{{ $req->booking_number }}</span>
                    </div>
                    <h4 class="font-bold text-heading text-lg mb-3">{{ $req->service->name ?? 'Service' }}</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-red-500 text-xs w-4"></i>
                            <span class="truncate">{{ Str::limit($req->service_address, 40) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user text-blue-500 text-xs w-4"></i>
                            <span>{{ $req->homeowner->user->full_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-sm">
                        <span class="font-bold text-green-600">TZS {{ number_format($req->hourly_rate ?? 0) }}/hr</span>
                        <span class="text-gray-300">|</span>
                        <span class="{{ $req->booking_type === 'instant' ? 'text-orange-600' : 'text-blue-600' }}">
                            <i class="fas {{ $req->booking_type === 'instant' ? 'fa-bolt' : 'fa-calendar' }} mr-1 text-xs"></i>
                            {{ $req->booking_type === 'instant' ? 'Instant' : 'Scheduled' }}
                        </span>
                        <span class="text-gray-300">|</span>
                        <span class="text-muted text-xs">{{ $req->pricing_model === 'fixed' ? $req->booked_hours . 'hrs' : 'PAYG' }}</span>
                    </div>
                    <a href="/cleaner/bookings/{{ $req->id }}/detail" 
                       class="flex items-center justify-center gap-2 w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] transition-all">
                        <i class="fas fa-eye"></i> View & Respond
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ACTIVE JOBS (accepted, arrived, in progress) --}}
    @if($activeJobs->count() > 0)
    <div class="mb-6 animate-slide-up">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                <i class="fas fa-spinner text-white animate-spin"></i>
            </div>
            <h3 class="text-xl font-black text-heading">Active Jobs</h3>
            <span class="bg-blue-100 dark:bg-blue-500/10 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">{{ $activeJobs->count() }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5">
            @foreach($activeJobs as $job)
            @php 
                $statusLabels = [
                    'cleaner_assigned' => ['label' => 'Assigned', 'color' => 'purple'],
                    'cleaner_accepted' => ['label' => 'Accepted', 'color' => 'blue'],
                    'cleaner_arrived' => ['label' => 'Arrived', 'color' => 'green'],
                    'in_progress' => ['label' => 'In Progress', 'color' => 'teal'],
                ];
                $sl = $statusLabels[$job->status] ?? ['label' => 'Active', 'color' => 'blue'];
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden card-hover-lift border-l-4 border-l-{{ $sl['color'] }}-500">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-{{ $sl['color'] }}-100 text-{{ $sl['color'] }}-700">
                            {{ $sl['label'] }}
                        </span>
                        <span class="text-xs font-mono text-muted">#{{ $job->booking_number }}</span>
                    </div>
                    <h4 class="font-bold text-heading text-lg mb-2">{{ $job->service->name ?? 'Service' }}</h4>
                    <div class="space-y-1.5 text-sm text-body mb-3">
                        <div class="flex items-center gap-2"><i class="fas fa-user text-blue-500 text-xs w-4"></i><span>{{ $job->homeowner->user->full_name ?? 'N/A' }}</span></div>
                        <div class="flex items-center gap-2"><i class="fas fa-map-marker-alt text-red-500 text-xs w-4"></i><span class="truncate">{{ Str::limit($job->service_address, 40) }}</span></div>
                    </div>
                    <div class="flex items-center gap-3 pt-3 border-t border-gray-100 text-sm">
                        <span class="font-bold text-green-600">TZS {{ number_format($job->hourly_rate ?? 0) }}/hr</span>
                        @if($job->distance_km)<span class="text-gray-300">|</span><span>{{ round($job->distance_km, 1) }} km</span>@endif
                    </div>
                    <a href="/cleaner/bookings/{{ $job->id }}/detail" 
                       class="flex items-center justify-center gap-2 w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] transition-all">
                        <i class="fas fa-external-link-alt"></i> View Details
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- COMPLETED JOBS --}}
    @if($completedJobs->count() > 0 && $activeJobs->count() == 0 && $pendingRequests->count() == 0)
    <div class="mb-6 animate-slide-up">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-check-circle text-white"></i>
            </div>
            <h3 class="text-xl font-black text-heading">Recent Completed Jobs</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase">Booking</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase">Service</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase">Date</th>
                            <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase">Earning</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($completedJobs as $job)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4"><span class="text-xs font-mono">#{{ $job->booking_number }}</span></td>
                            <td class="px-5 py-4"><span class="text-sm font-semibold">{{ $job->service->name ?? 'N/A' }}</span></td>
                            <td class="px-5 py-4"><span class="text-sm">{{ $job->completed_at?->format('M d, Y') }}</span></td>
                            <td class="px-5 py-4 text-right"><span class="text-sm font-bold text-green-600">TZS {{ number_format($job->cleaner_payout_amount) }}</span></td>
                            <td class="px-5 py-4 text-center">
                                @if($job->cleaner_rating_given)
                                <span class="text-yellow-500"><i class="fas fa-star text-xs"></i> {{ $job->cleaner_rating_given }}</span>
                                @else<span class="text-muted">-</span>@endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- EMPTY STATE --}}
    @if($pendingRequests->count() == 0 && $activeJobs->count() == 0 && $completedJobs->count() == 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 p-16 text-center animate-slide-up">
        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-broom text-gray-400 text-4xl"></i>
        </div>
        <h3 class="text-2xl font-black text-heading mb-3">Ready to Start Earning?</h3>
        <p class="text-muted max-w-md mx-auto mb-6">
            @if($currentStatus === 'online')
                You're <strong class="text-green-600">ONLINE</strong> and ready to receive booking requests!
            @else
                Turn <strong class="text-green-600">ONLINE</strong> to start receiving booking requests from homeowners.
            @endif
        </p>
        @if($currentStatus !== 'online')
        <button onclick="setStatus('online')" 
                class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-base shadow-xl hover:scale-105 transition-all">
            <i class="fas fa-power-off mr-2"></i> Go Online Now
        </button>
        @endif
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function cleanerDashboard() {
        return {
            currentStatus: '{{ $currentStatus }}',
            
            init() {
                this.updateUI(this.currentStatus);
            },

            updateUI(status) {
                this.currentStatus = status;
                const configs = {
                    'online': { bg: '#22c55e', lightBg: '#dcfce7', text: '#166534', dot: '#22c55e', icon: 'fa-check', iconColor: '#22c55e', label: 'ONLINE', labelColor: '#16a34a', knobX: '66px', badge: 'Online' },
                    'online_busy': { bg: '#eab308', lightBg: '#fef9c3', text: '#854d0e', dot: '#eab308', icon: 'fa-clock', iconColor: '#eab308', label: 'BUSY', labelColor: '#a16207', knobX: '66px', badge: 'Online Busy' },
                    'scheduled_only': { bg: '#3b82f6', lightBg: '#eff6ff', text: '#1e3a5f', dot: '#3b82f6', icon: 'fa-calendar-alt', iconColor: '#3b82f6', label: 'SCHEDULED', labelColor: '#1d4ed8', knobX: '66px', badge: 'Scheduled Only' },
                    'offline': { bg: '#d1d5db', lightBg: '#f3f4f6', text: '#4b5563', dot: '#9ca3af', icon: 'fa-times', iconColor: '#9ca3af', label: 'OFFLINE', labelColor: '#6b7280', knobX: '4px', badge: 'Offline' },
                };
                const c = configs[status] || configs['offline'];
                
                const btn = document.getElementById('toggleBtn');
                const knob = document.getElementById('toggleKnob');
                const icon = document.getElementById('toggleIcon');
                const text = document.getElementById('toggleText');
                const dot = document.getElementById('statusDot');
                const badge = document.getElementById('statusBadge');
                
                if (btn) btn.style.backgroundColor = c.bg;
                if (knob) knob.style.transform = `translateX(${c.knobX})`;
                if (icon) { icon.className = `fas ${c.icon} text-xl`; icon.style.color = c.iconColor; }
                if (text) { text.textContent = c.label; text.style.color = c.labelColor; }
                if (dot) { dot.style.backgroundColor = c.dot; dot.classList.toggle('animate-pulse', status === 'online'); }
                if (badge) { badge.textContent = c.badge; badge.style.backgroundColor = c.lightBg; badge.style.color = c.text; }

                document.querySelectorAll('.status-card').forEach(card => {
                    const isActive = card.dataset.status === status;
                    card.style.borderColor = isActive ? c.bg : '#e5e7eb';
                    card.style.backgroundColor = isActive ? c.lightBg : '';
                });
            },

            async toggleAvailability() {
                const newStatus = this.currentStatus === 'online' ? 'offline' : 'online';
                await this.setStatus(newStatus);
            },

            async setStatus(status) {
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
                        this.updateUI(status);
                        window.showToast(result.message, 'success');
                        if (status === 'offline') setTimeout(() => location.reload(), 1000);
                    } else {
                        window.showToast(result.message || 'Failed to update status', 'error');
                    }
                } catch (e) {
                    window.showToast('Failed to update status.', 'error');
                }
            }
        };
    }

    setInterval(() => location.reload(), 60000);
</script>

<style>
    @keyframes slide-up { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }
    .animate-slide-up { animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
</style>
@endpush