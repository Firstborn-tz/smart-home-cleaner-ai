@extends('layouts.app')

@section('title', 'My Earnings')
@section('user_role', 'Cleaner')
@section('page_title', 'My Earnings')
@section('page_subtitle', 'Track your income and payouts')

@section('content')
<div x-data="earningsDashboard()" x-init="init()">
    
    @php
        $cleaner = Auth::user()->cleaner;
        
        // Earnings stats
        $totalEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->where('status', 'completed')
            ->sum('cleaner_payout_amount');
            
        $todayEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->whereDate('completed_at', today())
            ->where('status', 'completed')
            ->sum('cleaner_payout_amount');
            
        $weekEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'completed')
            ->sum('cleaner_payout_amount');
            
        $monthEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->where('status', 'completed')
            ->sum('cleaner_payout_amount');
            
        // Commission summary
        $pendingPayout = $cleaner->pending_payout ?? 0;
        $walletBalance = $cleaner->wallet_balance ?? 0;
        
        // Recent completed jobs
        $recentJobs = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->where('status', 'completed')
            ->latest()
            ->limit(20)
            ->get();
            
        // Monthly breakdown
        $monthlyBreakdown = App\Models\Booking::where('cleaner_id', $cleaner->id)
            ->where('status', 'completed')
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as month, COUNT(*) as jobs, SUM(cleaner_payout_amount) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
            
        // Commission records
        $commissions = App\Models\Commission::with(['booking.service'])
            ->where('cleaner_id', $cleaner->id)
            ->latest()
            ->limit(20)
            ->get();
    @endphp

    <!-- Earnings Overview Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today</p>
                    <p class="text-xl font-extrabold text-green-600 mt-1">TZS {{ number_format($todayEarnings) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-day text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">This Week</p>
                    <p class="text-xl font-extrabold text-blue-600 mt-1">TZS {{ number_format($weekEarnings) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-week text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">This Month</p>
                    <p class="text-xl font-extrabold text-purple-600 mt-1">TZS {{ number_format($monthEarnings) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Earnings</p>
                    <p class="text-xl font-extrabold text-indigo-600 mt-1">TZS {{ number_format($totalEarnings) }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-xl flex items-center justify-center">
                    <i class="fas fa-trophy text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout & Wallet -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Available for Payout</p>
                    <p class="text-3xl font-extrabold mt-1">TZS {{ number_format($pendingPayout) }}</p>
                    <p class="text-green-100 text-xs mt-2">Pending admin processing</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-hand-holding-usd text-white text-3xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Wallet Balance</p>
                    <p class="text-3xl font-extrabold mt-1">TZS {{ number_format($walletBalance) }}</p>
                    <p class="text-blue-100 text-xs mt-2">Withdrawable funds</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-wallet text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex space-x-2 mb-6 overflow-x-auto" x-data="{ tab: 'completed' }">
        <button @click="tab = 'completed'" :class="tab === 'completed' ? 'bg-blue-500 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                class="px-4 py-2 rounded-xl text-sm font-medium transition flex-shrink-0">
            <i class="fas fa-check-circle mr-1"></i> Completed Jobs
        </button>
        <button @click="tab = 'commissions'" :class="tab === 'commissions' ? 'bg-blue-500 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                class="px-4 py-2 rounded-xl text-sm font-medium transition flex-shrink-0">
            <i class="fas fa-money-bill-wave mr-1"></i> Commission Records
        </button>
        <button @click="tab = 'monthly'" :class="tab === 'monthly' ? 'bg-blue-500 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                class="px-4 py-2 rounded-xl text-sm font-medium transition flex-shrink-0">
            <i class="fas fa-chart-bar mr-1"></i> Monthly Breakdown
        </button>
    </div>

    <!-- Completed Jobs Table -->
    <div x-show="tab === 'completed'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden animate-fade-in">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Booking</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Service</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($recentJobs as $job)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <p class="font-mono text-sm font-medium text-gray-800 dark:text-white">#{{ $job->booking_number }}</p>
                            <p class="text-xs text-gray-500">{{ $job->homeowner->user->full_name ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 dark:text-white">{{ $job->service->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $job->service_address ? Str::limit($job->service_address, 30) : '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $job->completed_at ? $job->completed_at->format('M d, Y') : 'N/A' }}
                            <p class="text-xs text-gray-400">{{ $job->completed_at ? $job->completed_at->format('h:i A') : '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-green-600 dark:text-green-400">TZS {{ number_format($job->cleaner_payout_amount) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($job->cleaner_rating_given)
                                <span class="text-yellow-500">
                                    <i class="fas fa-star"></i> {{ number_format($job->cleaner_rating_given, 1) }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">No rating</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            No completed jobs yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Commission Records -->
    <div x-show="tab === 'commissions'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden animate-fade-in">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Service</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Expected</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Submitted</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Remaining</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($commissions as $commission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                            {{ $commission->booking->service->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">TZS {{ number_format($commission->expected_total_amount) }}</td>
                        <td class="px-6 py-4 text-green-600 dark:text-green-400 font-bold">TZS {{ number_format($commission->actual_submitted_amount) }}</td>
                        <td class="px-6 py-4 text-red-600 dark:text-red-400 font-bold">TZS {{ number_format($commission->remaining_unpaid_amount) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($commission->payment_status === 'fully_paid') bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                                @elseif($commission->payment_status === 'partially_paid') bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300
                                @elseif($commission->payment_status === 'overpaid') bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                                @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                                {{ ucfirst(str_replace('_', ' ', $commission->payment_status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-receipt text-3xl mb-2 block"></i>
                            No commission records yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div x-show="tab === 'monthly'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden animate-fade-in">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Month</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jobs</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total Earnings</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Average per Job</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($monthlyBreakdown as $month)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800 dark:text-white">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('F Y') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                {{ $month->jobs }} jobs
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-green-600 dark:text-green-400">TZS {{ number_format($month->total) }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            TZS {{ number_format($month->jobs > 0 ? $month->total / $month->jobs : 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-chart-line text-3xl mb-2 block"></i>
                            No earnings data yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function earningsDashboard() {
        return {
            init() {
                // Initialize any charts or data here
            }
        };
    }
</script>
@endpush