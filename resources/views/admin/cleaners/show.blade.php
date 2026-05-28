@extends('layouts.app')

@section('title', 'Cleaner Details')
@section('user_role', 'Administrator')
@section('page_title', 'Cleaner Details')
@section('page_subtitle', $cleaner->user->full_name . ' — ' . $cleaner->cleaner_id)

@section('content')
<div x-data="cleanerDetail()">
    @php
        $totalBookings = $cleaner->bookings->count();
        $completedBookings = $cleaner->bookings->where('status', 'completed')->count();
        $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100) : 0;
    @endphp

    {{-- ============================================ --}}
    {{-- BACK BUTTON --}}
    {{-- ============================================ --}}
    <a href="/admin/cleaners" class="inline-flex items-center gap-2 px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 text-body rounded-xl font-semibold text-sm hover:border-blue-300 hover:text-blue-600 dark:hover:border-blue-500 dark:hover:text-blue-400 transition-all duration-300 mb-6 group">
        <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i> 
        Back to Cleaners
    </a>

    {{-- ============================================ --}}
    {{-- CLEANER PROFILE HEADER --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                {{-- Left: Avatar + Info --}}
                <div class="flex items-start gap-5">
                    <div class="w-20 h-20 rounded-2xl bg-linear-to-br from-blue-400 via-purple-400 to-purple-600 flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-purple-500/25 flex-shrink-0">
                        {{ strtoupper(substr($cleaner->user->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-black text-heading tracking-tight">{{ $cleaner->user->full_name }}</h1>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                            <p class="text-sm text-muted">
                                <i class="fas fa-envelope mr-1 text-blue-400"></i> {{ $cleaner->user->email }}
                            </p>
                            <p class="text-sm text-muted">
                                <i class="fas fa-phone mr-1 text-green-400"></i> {{ $cleaner->user->phone }}
                            </p>
                        </div>
                        <span class="inline-flex items-center mt-2 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-mono text-muted">
                            <i class="fas fa-id-card mr-1.5 text-gray-400"></i> {{ $cleaner->cleaner_id }}
                        </span>
                        
                        {{-- Status Badges --}}
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            @php
                                $statusMap = [
                                    'online' => ['bg' => 'bg-green-100 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500 animate-pulse', 'border' => 'border-green-200 dark:border-green-500/20', 'label' => 'Online'],
                                    'online_busy' => ['bg' => 'bg-yellow-100 dark:bg-yellow-500/10', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-500/20', 'label' => 'Busy'],
                                    'scheduled_only' => ['bg' => 'bg-blue-100 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-500/20', 'label' => 'Scheduled Only'],
                                    'offline' => ['bg' => 'bg-gray-100 dark:bg-gray-500/10', 'text' => 'text-gray-600 dark:text-gray-400', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200 dark:border-gray-500/20', 'label' => 'Offline'],
                                ];
                                $s = $statusMap[$cleaner->availability_status] ?? $statusMap['offline'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $s['bg'] }} {{ $s['text'] }} border {{ $s['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $s['dot'] }}"></span>
                                {{ $s['label'] }}
                            </span>
                            
                            @if($cleaner->is_verified)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                                <i class="fas fa-shield-halved mr-1.5"></i> Verified
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-500/20">
                                <i class="fas fa-clock mr-1.5"></i> Pending Verification
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                    @if(!$cleaner->is_verified)
                    <button @click="approveCleaner({{ $cleaner->id }})"
                            class="px-5 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105 transition-all duration-300">
                        <i class="fas fa-check mr-1.5"></i> Approve Cleaner
                    </button>
                    @endif
                    <a href="/cleaner/{{ $cleaner->id }}/profile" target="_blank"
                       class="px-5 py-3 bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 rounded-xl font-bold text-sm hover:bg-purple-100 dark:hover:bg-purple-500/20 transition-all duration-300">
                        <i class="fas fa-external-link-alt mr-1.5"></i> Public Profile
                    </a>
                    <button @click="suspendCleaner({{ $cleaner->id }})"
                            class="px-5 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-105 transition-all duration-300">
                        <i class="fas fa-ban mr-1.5"></i> Suspend
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick Info Strip --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-gray-100 dark:bg-gray-700">
            <div class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 text-center">
                <p class="text-xs text-muted">Gender</p>
                <p class="text-sm font-bold text-heading">{{ ucfirst($cleaner->gender ?? 'N/A') }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 text-center">
                <p class="text-xs text-muted">City</p>
                <p class="text-sm font-bold text-heading">{{ $cleaner->city->name ?? 'N/A' }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 text-center">
                <p class="text-xs text-muted">Service Radius</p>
                <p class="text-sm font-bold text-heading">{{ $cleaner->max_service_radius_km ?? 30 }} km</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 text-center">
                <p class="text-xs text-muted">Member Since</p>
                <p class="text-sm font-bold text-heading">{{ $cleaner->created_at->format('M Y') }}</p>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATS CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 lg:gap-4 mb-6">
        {{-- Total Jobs --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-briefcase text-blue-600 dark:text-blue-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $stats['total_bookings'] }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Total Jobs</p>
        </div>

        {{-- Completed --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $stats['completed_bookings'] }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Completed</p>
        </div>

        {{-- Rating --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-yellow-100 to-amber-200 dark:from-yellow-900/40 dark:to-amber-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ number_format($stats['average_rating'], 1) }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Rating</p>
            <div class="flex items-center justify-center gap-0.5 mt-1 text-yellow-500">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-[9px] {{ $i <= round($stats['average_rating']) ? '' : 'text-gray-300 dark:text-gray-600' }}"></i>
                @endfor
            </div>
        </div>

        {{-- Completion Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line text-purple-600 dark:text-purple-400"></i>
            </div>
            <p class="text-2xl font-black text-heading stat-number">{{ $completionRate }}%</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Completion</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full {{ $completionRate >= 90 ? 'bg-green-500' : ($completionRate >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                     style="width: {{ $completionRate }}%"></div>
            </div>
        </div>

        {{-- Total Earnings --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 text-center card-hover-lift group">
            <div class="w-10 h-10 bg-linear-to-br from-emerald-100 to-teal-200 dark:from-emerald-900/40 dark:to-teal-800/40 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-coins text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <p class="text-xl font-black text-heading stat-number">TZS {{ number_format($stats['total_earnings']) }}</p>
            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Earnings</p>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- RECENT BOOKINGS TABLE --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fas fa-list-check text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-heading text-lg">Recent Bookings</h3>
                    <p class="text-xs text-muted">{{ $totalBookings }} total bookings</p>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Booking #</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Service</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Homeowner</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($cleaner->bookings as $booking)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <span class="text-sm font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg">
                                {{ $booking->booking_number }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm font-semibold text-heading">{{ $booking->service->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm text-body">{{ $booking->homeowner->user->full_name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $bookingStatusMap = [
                                    'completed' => ['bg' => 'bg-green-100 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-500/20'],
                                    'in_progress' => ['bg' => 'bg-blue-100 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-500/20'],
                                    'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-500/10', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-500/20'],
                                    'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-300', 'dot' => 'bg-red-500', 'border' => 'border-red-200 dark:border-red-500/20'],
                                    'accepted' => ['bg' => 'bg-indigo-100 dark:bg-indigo-500/10', 'text' => 'text-indigo-700 dark:text-indigo-300', 'dot' => 'bg-indigo-500', 'border' => 'border-indigo-200 dark:border-indigo-500/20'],
                                ];
                                $bs = $bookingStatusMap[$booking->status] ?? ['bg' => 'bg-gray-100 dark:bg-gray-500/10', 'text' => 'text-gray-600 dark:text-gray-400', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200 dark:border-gray-500/20'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $bs['bg'] }} {{ $bs['text'] }} border {{ $bs['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $bs['dot'] }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">TZS {{ number_format($booking->cleaner_payout_amount) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm text-muted">{{ $booking->created_at->format('M d, Y') }}</span>
                            <p class="text-[10px] text-muted">{{ $booking->created_at->format('h:i A') }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="w-16 h-16 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-calendar-xmark text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-heading mb-1">No Bookings Yet</h3>
                            <p class="text-sm text-muted">This cleaner hasn't completed any bookings</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-x-6" 
         x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-x-0" 
         x-transition:leave-end="opacity-0 translate-x-6" 
         class="fixed top-6 right-6 z-[9999] px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-sm font-semibold text-white"
         :class="toast.type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'"
         style="display: none;">
        <i class="fas text-lg" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function cleanerDetail() {
        return {
            toast: { show: false, message: '', type: 'success' },

            async approveCleaner(id) {
                if (!confirm('Approve this cleaner? They will be able to accept bookings.')) return;
                
                try {
                    const res = await fetch(`/admin/cleaners/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        this.showToast(data.message || 'Approval failed', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                }
            },

            async suspendCleaner(id) {
                const reason = prompt('Please enter the reason for suspension:');
                if (!reason || !reason.trim()) return;
                
                if (!confirm(`Suspend this cleaner?\n\nReason: ${reason}`)) return;
                
                try {
                    const res = await fetch(`/admin/cleaners/${id}/suspend`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reason })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => location.href = '/admin/cleaners', 1500);
                    } else {
                        this.showToast(data.message || 'Suspension failed', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                }
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 3500);
            }
        };
    }
</script>
@endpush
