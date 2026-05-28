@extends('layouts.app')

@section('title', 'My Earnings')
@section('user_role', 'Cleaner')
@section('page_title', 'My Earnings')
@section('page_subtitle', 'Track your income and payouts')

@section('content')
<div x-data="earningsDashboard()" x-init="init()">
    @php
        $cleaner = Auth::user()->cleaner;
        
        $totalEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)->where('status', 'completed')->sum('cleaner_payout_amount');
        $todayEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)->whereDate('completed_at', today())->where('status', 'completed')->sum('cleaner_payout_amount');
        $weekEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'completed')->sum('cleaner_payout_amount');
        $monthEarnings = App\Models\Booking::where('cleaner_id', $cleaner->id)->whereMonth('completed_at', now()->month)->whereYear('completed_at', now()->year)->where('status', 'completed')->sum('cleaner_payout_amount');
        
        $pendingPayout = $cleaner->pending_payout ?? 0;
        $walletBalance = $cleaner->wallet_balance ?? 0;
        
        $recentJobs = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)->where('status', 'completed')->latest()->limit(20)->get();
            
        $monthlyBreakdown = App\Models\Booking::where('cleaner_id', $cleaner->id)->where('status', 'completed')
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as month, COUNT(*) as jobs, SUM(cleaner_payout_amount) as total")
            ->groupBy('month')->orderBy('month', 'desc')->limit(12)->get();
            
        $commissions = App\Models\Commission::with(['booking.service'])
            ->where('cleaner_id', $cleaner->id)->latest()->limit(20)->get();
    @endphp

    {{-- ============================================ --}}
    {{-- EARNINGS OVERVIEW CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
        @php
            $earningCards = [
                ['label' => 'Today', 'value' => 'TZS ' . number_format($todayEarnings), 'icon' => 'fa-calendar-day', 'color' => 'green'],
                ['label' => 'This Week', 'value' => 'TZS ' . number_format($weekEarnings), 'icon' => 'fa-calendar-week', 'color' => 'blue'],
                ['label' => 'This Month', 'value' => 'TZS ' . number_format($monthEarnings), 'icon' => 'fa-calendar-alt', 'color' => 'purple'],
                ['label' => 'Total Earnings', 'value' => 'TZS ' . number_format($totalEarnings), 'icon' => 'fa-trophy', 'color' => 'indigo'],
            ];
        @endphp
        @foreach($earningCards as $card)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 sm:p-5 card-hover-lift group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] text-muted font-medium uppercase tracking-wider">{{ $card['label'] }}</p>
                <div class="w-9 h-9 bg-{{ $card['color'] }}-100 dark:bg-{{ $card['color'] }}-500/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas {{ $card['icon'] }} text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400 text-sm"></i>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-black text-heading stat-number">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ============================================ --}}
    {{-- PAYOUT & WALLET CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5 mb-6">
        {{-- Available for Payout --}}
        <div class="bg-linear-to-br from-green-400 to-emerald-600 rounded-2xl shadow-xl shadow-green-500/25 p-5 sm:p-6 text-white relative overflow-hidden card-hover-lift">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-bl-3xl -mr-6 -mt-6"></div>
            <div class="absolute bottom-0 right-0 w-24 h-24 bg-white/5 rounded-tl-3xl -mr-4 -mb-4"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-xs font-medium uppercase tracking-wider mb-1">Available for Payout</p>
                    <p class="text-3xl sm:text-4xl font-black tracking-tight">TZS {{ number_format($pendingPayout) }}</p>
                    <p class="text-green-100/70 text-xs mt-2 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i> Pending admin processing
                    </p>
                </div>
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur">
                    <i class="fas fa-hand-holding-usd text-white text-2xl sm:text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Wallet Balance --}}
        <div class="bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl shadow-blue-500/25 p-5 sm:p-6 text-white relative overflow-hidden card-hover-lift">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-bl-3xl -mr-6 -mt-6"></div>
            <div class="absolute bottom-0 right-0 w-24 h-24 bg-white/5 rounded-tl-3xl -mr-4 -mb-4"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-xs font-medium uppercase tracking-wider mb-1">Wallet Balance</p>
                    <p class="text-3xl sm:text-4xl font-black tracking-tight">TZS {{ number_format($walletBalance) }}</p>
                    <p class="text-blue-100/70 text-xs mt-2 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i> Withdrawable funds
                    </p>
                </div>
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur">
                    <i class="fas fa-wallet text-white text-2xl sm:text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TAB NAVIGATION --}}
    {{-- ============================================ --}}
    <div class="flex gap-1.5 mb-6 overflow-x-auto pb-1 scrollbar-hide" x-data="{ tab: 'completed' }">
        <button @click="tab = 'completed'" 
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300"
                :class="tab === 'completed' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/25' : 'bg-white dark:bg-gray-800 text-body border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'">
            <i class="fas fa-check-circle text-xs"></i> Completed Jobs
        </button>
        <button @click="tab = 'commissions'" 
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300"
                :class="tab === 'commissions' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/25' : 'bg-white dark:bg-gray-800 text-body border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'">
            <i class="fas fa-money-bill-wave text-xs"></i> Commission Records
        </button>
        <button @click="tab = 'monthly'" 
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300"
                :class="tab === 'monthly' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/25' : 'bg-white dark:bg-gray-800 text-body border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'">
            <i class="fas fa-chart-bar text-xs"></i> Monthly Breakdown
        </button>
    </div>

    {{-- ============================================ --}}
    {{-- COMPLETED JOBS TABLE --}}
    {{-- ============================================ --}}
    <div x-show="tab === 'completed'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Booking</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Service</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Date</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentJobs as $job)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        <td class="px-5 py-4">
                            <span class="text-xs font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg">#{{ $job->booking_number }}</span>
                            <p class="text-xs text-muted mt-1">{{ $job->homeowner->user->full_name ?? 'N/A' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $job->service->name ?? 'N/A' }}</p>
                            <p class="text-xs text-muted">{{ $job->service_address ? Str::limit($job->service_address, 30) : '' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-body">{{ $job->completed_at ? $job->completed_at->format('M d, Y') : 'N/A' }}</p>
                            <p class="text-xs text-muted">{{ $job->completed_at ? $job->completed_at->format('h:i A') : '' }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">TZS {{ number_format($job->cleaner_payout_amount) }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($job->cleaner_rating_given)
                            <div class="flex items-center justify-center gap-1 text-yellow-500">
                                <i class="fas fa-star text-xs"></i>
                                <span class="text-sm font-bold text-heading">{{ number_format($job->cleaner_rating_given, 1) }}</span>
                            </div>
                            @else
                            <span class="text-xs text-muted">No rating</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="w-16 h-16 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-inbox text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-heading mb-1">No Completed Jobs</h3>
                            <p class="text-sm text-muted">Earnings will appear here once jobs are completed</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- COMMISSION RECORDS --}}
    {{-- ============================================ --}}
    <div x-show="tab === 'commissions'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Service</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Expected</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Submitted</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Remaining</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($commissions as $commission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        <td class="px-5 py-4">
                            <span class="text-sm font-semibold text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $commission->booking->service->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-semibold text-heading">TZS {{ number_format($commission->expected_total_amount) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">TZS {{ number_format($commission->actual_submitted_amount) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold {{ $commission->remaining_unpaid_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                TZS {{ number_format($commission->remaining_unpaid_amount) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $commStatusMap = [
                                    'fully_paid' => ['bg' => 'bg-green-100 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-500/20'],
                                    'partially_paid' => ['bg' => 'bg-blue-100 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-500/20'],
                                    'overpaid' => ['bg' => 'bg-purple-100 dark:bg-purple-500/10', 'text' => 'text-purple-700 dark:text-purple-300', 'dot' => 'bg-purple-500', 'border' => 'border-purple-200 dark:border-purple-500/20'],
                                    'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-500/10', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-500/20'],
                                ];
                                $cs = $commStatusMap[$commission->payment_status] ?? $commStatusMap['pending'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $cs['bg'] }} {{ $cs['text'] }} border {{ $cs['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $cs['dot'] }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $commission->payment_status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="w-16 h-16 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-receipt text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-heading mb-1">No Commission Records</h3>
                            <p class="text-sm text-muted">Commission records will appear here once generated</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MONTHLY BREAKDOWN --}}
    {{-- ============================================ --}}
    <div x-show="tab === 'monthly'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Month</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Jobs</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Total Earnings</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Average per Job</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($monthlyBreakdown as $month)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <span class="text-sm font-bold text-heading">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('F Y') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20">
                                {{ $month->jobs }} jobs
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">TZS {{ number_format($month->total) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-semibold text-heading">
                                TZS {{ number_format($month->jobs > 0 ? $month->total / $month->jobs : 0) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-16 text-center">
                            <div class="w-16 h-16 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-chart-line text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-heading mb-1">No Earnings Data</h3>
                            <p class="text-sm text-muted">Monthly breakdown will appear here once you complete jobs</p>
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
            init() {}
        };
    }
</script>
@endpush
