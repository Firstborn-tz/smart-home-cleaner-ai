@extends('layouts.app')

@section('title', 'Commission Management')
@section('user_role', 'Administrator')
@section('page_title', 'Commission Management')
@section('page_subtitle', 'Track and record cleaner payments')

@section('content')
<div x-data="commissionManager()">
    @php
        $statusFilter = request('status', '');
        $cleanerFilter = request('cleaner_id', '');
        $dateFrom = request('date_from', '');
        $dateTo = request('date_to', '');
        $sort = request('sort', 'newest');
        
        $query = App\Models\Commission::with(['booking.service', 'cleaner.user', 'recordedBy'])
            ->when($statusFilter, fn($q) => $q->where('payment_status', $statusFilter))
            ->when($cleanerFilter, fn($q) => $q->where('cleaner_id', $cleanerFilter))
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
        
        switch($sort) {
            case 'oldest': $query->oldest(); break;
            case 'highest': $query->orderByDesc('expected_total_amount'); break;
            case 'lowest': $query->orderBy('expected_total_amount'); break;
            default: $query->latest();
        }
        
        $commissions = $query->paginate(20)->appends(request()->query());
        
        $summary = [
            'total_expected' => App\Models\Commission::sum('expected_total_amount'),
            'total_submitted' => App\Models\Commission::sum('actual_submitted_amount'),
            'total_remaining' => App\Models\Commission::sum('remaining_unpaid_amount'),
            'total_overpayment' => App\Models\Commission::sum('overpayment_amount'),
            'pending_count' => App\Models\Commission::where('payment_status', 'pending')->count(),
            'partial_count' => App\Models\Commission::where('payment_status', 'partially_paid')->count(),
            'paid_count' => App\Models\Commission::where('payment_status', 'fully_paid')->count(),
            'overpaid_count' => App\Models\Commission::where('payment_status', 'overpaid')->count(),
        ];
        
        $topCleaners = App\Models\Cleaner::with('user')
            ->withSum('commissions', 'expected_total_amount')
            ->withSum('commissions', 'actual_submitted_amount')
            ->orderByDesc('commissions_sum_expected_total_amount')
            ->limit(5)
            ->get();
        
        $monthlyBreakdown = App\Models\Commission::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, 
            COUNT(*) as total, 
            SUM(expected_total_amount) as expected, 
            SUM(actual_submitted_amount) as collected,
            SUM(remaining_unpaid_amount) as remaining")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();
            
        $allCleaners = App\Models\Cleaner::with('user')->orderBy('user_id')->get();
        $collectionRate = $summary['total_expected'] > 0 ? round(($summary['total_submitted'] / $summary['total_expected']) * 100) : 0;
    @endphp

    {{-- ============================================ --}}
    {{-- SUMMARY CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
        {{-- Total Expected --}}
        <div class="bg-linear-to-br from-blue-500 to-blue-700 rounded-2xl shadow-lg shadow-blue-500/20 p-5 text-white relative overflow-hidden card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">TZS {{ number_format($summary['total_expected'], 0) }}</p>
                <p class="text-blue-200 text-xs font-medium mt-1 uppercase tracking-wider">Total Expected</p>
            </div>
        </div>

        {{-- Total Collected --}}
        <div class="bg-linear-to-br from-green-500 to-emerald-700 rounded-2xl shadow-lg shadow-green-500/20 p-5 text-white relative overflow-hidden card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">TZS {{ number_format($summary['total_submitted'], 0) }}</p>
                <p class="text-green-200 text-xs font-medium mt-1 uppercase tracking-wider">Total Collected</p>
            </div>
        </div>

        {{-- Outstanding --}}
        <div class="bg-linear-to-br from-red-500 to-rose-700 rounded-2xl shadow-lg shadow-red-500/20 p-5 text-white relative overflow-hidden card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-exclamation-circle text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">TZS {{ number_format($summary['total_remaining'], 0) }}</p>
                <p class="text-red-200 text-xs font-medium mt-1 uppercase tracking-wider">Outstanding</p>
            </div>
        </div>

        {{-- Collection Rate --}}
        <div class="bg-linear-to-br from-purple-500 to-purple-700 rounded-2xl shadow-lg shadow-purple-500/20 p-5 text-white relative overflow-hidden card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-chart-pie text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">{{ $collectionRate }}%</p>
                <p class="text-purple-200 text-xs font-medium mt-1 uppercase tracking-wider">Collection Rate</p>
                <div class="mt-2 w-full bg-white/20 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-white h-full rounded-full transition-all duration-700" style="width: {{ $collectionRate }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATUS BREAKDOWN --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-linear-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <i class="fas fa-list-check text-white"></i>
            </div>
            <h3 class="font-bold text-heading text-lg">Payment Status Breakdown</h3>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $statusCards = [
                    ['label' => 'Pending', 'count' => $summary['pending_count'], 'color' => 'yellow', 'icon' => 'fa-clock'],
                    ['label' => 'Partially Paid', 'count' => $summary['partial_count'], 'color' => 'blue', 'icon' => 'fa-hourglass-half'],
                    ['label' => 'Fully Paid', 'count' => $summary['paid_count'], 'color' => 'green', 'icon' => 'fa-check-double'],
                    ['label' => 'Overpaid', 'count' => $summary['overpaid_count'], 'color' => 'purple', 'icon' => 'fa-arrow-trend-up'],
                ];
                $maxCount = max(max($summary['pending_count'], $summary['partial_count']), max($summary['paid_count'], $summary['overpaid_count']), 1);
            @endphp
            
            @foreach($statusCards as $card)
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 text-center group hover:bg-{{ $card['color'] }}-50 dark:hover:bg-{{ $card['color'] }}-500/10 transition-all">
                <div class="w-10 h-10 bg-{{ $card['color'] }}-100 dark:bg-{{ $card['color'] }}-500/10 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas {{ $card['icon'] }} text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400"></i>
                </div>
                <p class="text-2xl font-black text-heading stat-number">{{ $card['count'] }}</p>
                <p class="text-[11px] text-muted font-medium uppercase tracking-wider mb-2">{{ $card['label'] }}</p>
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-{{ $card['color'] }}-500 h-full rounded-full transition-all duration-700" 
                         style="width: {{ ($card['count'] / $maxCount) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TOP CLEANERS & MONTHLY BREAKDOWN --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        
        {{-- Top Cleaners --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-yellow-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-500/25">
                        <i class="fas fa-trophy text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading">Top Cleaners</h3>
                        <p class="text-xs text-muted">By expected commission</p>
                    </div>
                </div>
            </div>
            
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($topCleaners as $index => $tc)
                @php 
                    $cleanerExpected = $tc->commissions_sum_expected_total_amount ?? 0;
                    $cleanerCollected = $tc->commissions_sum_actual_submitted_amount ?? 0;
                    $cleanerRate = $cleanerExpected > 0 ? round(($cleanerCollected / $cleanerExpected) * 100) : 0;
                @endphp
                <div class="px-5 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-md flex-shrink-0
                            @if($index === 0) bg-linear-to-br from-yellow-400 to-yellow-600
                            @elseif($index === 1) bg-linear-to-br from-gray-300 to-gray-500
                            @elseif($index === 2) bg-linear-to-br from-orange-400 to-orange-600
                            @else bg-linear-to-br from-blue-400 to-blue-600 @endif">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $tc->user->full_name }}
                            </p>
                            <p class="text-xs text-muted">TZS {{ number_format($cleanerExpected, 0) }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold
                        {{ $cleanerRate >= 100 ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20' : 
                           ($cleanerRate >= 50 ? 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-500/20' : 
                           'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20') }}">
                        {{ $cleanerRate }}%
                    </span>
                </div>
                @empty
                <div class="px-5 py-12 text-center">
                    <i class="fas fa-users text-gray-300 dark:text-gray-600 text-3xl mb-2"></i>
                    <p class="text-muted text-sm">No commission data yet</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Monthly Breakdown --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading">Monthly Breakdown</h3>
                        <p class="text-xs text-muted">Last 6 months</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-5 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Month</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-muted uppercase tracking-wider">Count</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-muted uppercase tracking-wider">Expected</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-muted uppercase tracking-wider">Collected</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-muted uppercase tracking-wider">Remaining</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-muted uppercase tracking-wider">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($monthlyBreakdown as $m)
                        @php $monthlyRate = $m->expected > 0 ? round(($m->collected / $m->expected) * 100) : 0; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-5 py-3.5">
                                <span class="text-sm font-semibold text-heading">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('F Y') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $m->total }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold text-heading">TZS {{ number_format($m->expected, 0) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">TZS {{ number_format($m->collected, 0) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold {{ $m->remaining > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    TZS {{ number_format($m->remaining, 0) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-12 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full {{ $monthlyRate >= 90 ? 'bg-green-500' : ($monthlyRate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                             style="width: {{ $monthlyRate }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold {{ $monthlyRate >= 90 ? 'text-green-600 dark:text-green-400' : ($monthlyRate >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                        {{ $monthlyRate }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <i class="fas fa-chart-bar text-gray-300 dark:text-gray-600 text-3xl mb-2"></i>
                                <p class="text-muted text-sm">No monthly data available</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILTERS --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            {{-- Status --}}
            <div class="relative">
                <i class="fas fa-flag absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="status" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Status</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partially_paid" {{ $statusFilter === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="fully_paid" {{ $statusFilter === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="overpaid" {{ $statusFilter === 'overpaid' ? 'selected' : '' }}>Overpaid</option>
                </select>
            </div>
            
            {{-- Cleaner --}}
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="cleaner_id" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="">All Cleaners</option>
                    @foreach($allCleaners as $c)
                    <option value="{{ $c->id }}" {{ $cleanerFilter == $c->id ? 'selected' : '' }}>{{ $c->user->full_name }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- Date Range --}}
            <div class="flex items-center gap-2">
                <div class="relative">
                    <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" 
                           class="pl-9 pr-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                </div>
                <span class="text-muted text-sm font-medium">to</span>
                <div class="relative">
                    <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                    <input type="date" name="date_to" value="{{ $dateTo }}" 
                           class="pl-9 pr-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300">
                </div>
            </div>
            
            {{-- Sort --}}
            <div class="relative">
                <i class="fas fa-sort-amount-down absolute left-3 top-1/2 -translate-y-1/2 text-muted text-sm"></i>
                <select name="sort" class="pl-9 pr-8 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="highest" {{ $sort === 'highest' ? 'selected' : '' }}>Highest Amount</option>
                    <option value="lowest" {{ $sort === 'lowest' ? 'selected' : '' }}>Lowest Amount</option>
                </select>
            </div>
            
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 transition-all duration-300">
                <i class="fas fa-filter mr-1.5"></i> Apply Filters
            </button>
            
            @if($statusFilter || $cleanerFilter || $dateFrom || $dateTo || $sort !== 'newest')
            <a href="/admin/commissions" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-300">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
            
            <div class="ml-auto">
                <form method="POST" action="/admin/commissions/generate" class="inline">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 hover:scale-105 transition-all duration-300">
                        <i class="fas fa-sync-alt mr-1.5"></i> Generate from Bookings
                    </button>
                </form>
            </div>
        </form>
    </div>

    {{-- ============================================ --}}
    {{-- COMMISSIONS TABLE --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Commission</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Cleaner</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Service</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Expected</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Submitted</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Remaining</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($commissions as $commission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        {{-- Commission ID --}}
                        <td class="px-5 py-4">
                            <span class="text-xs font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg">
                                #{{ $commission->id }}
                            </span>
                        </td>
                        
                        {{-- Cleaner --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($commission->cleaner->user->full_name ?? 'NA') }}&background=3b82f6&color=fff&size=32&bold=true" 
                                     class="w-8 h-8 rounded-lg flex-shrink-0">
                                <div>
                                    <p class="font-bold text-sm text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $commission->cleaner->user->full_name ?? 'N/A' }}
                                    </p>
                                    <p class="text-[10px] text-muted font-mono">{{ $commission->cleaner->cleaner_id ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        
                        {{-- Service --}}
                        <td class="px-5 py-4">
                            <span class="text-sm text-body">{{ $commission->booking->service->name ?? 'N/A' }}</span>
                        </td>
                        
                        {{-- Expected --}}
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-heading">TZS {{ number_format($commission->expected_total_amount, 0) }}</span>
                        </td>
                        
                        {{-- Submitted --}}
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                TZS {{ number_format($commission->actual_submitted_amount, 0) }}
                            </span>
                        </td>
                        
                        {{-- Remaining --}}
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold {{ $commission->remaining_unpaid_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                TZS {{ number_format($commission->remaining_unpaid_amount, 0) }}
                            </span>
                        </td>
                        
                        {{-- Status --}}
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
                        
                        {{-- Action --}}
                        <td class="px-5 py-4 text-right">
                            @if($commission->remaining_unpaid_amount > 0)
                            <button @click="openPaymentModal({{ $commission->id }}, {{ $commission->remaining_unpaid_amount }})"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 hover:scale-105 transition-all duration-300">
                                <i class="fas fa-money-bill-wave mr-1.5"></i> Record Payment
                            </button>
                            @else
                            <span class="inline-flex items-center px-3 py-1.5 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 rounded-xl text-xs font-semibold border border-green-200 dark:border-green-500/20">
                                <i class="fas fa-check-circle mr-1"></i> Settled
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-20 text-center">
                            <div class="w-20 h-20 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-receipt text-gray-400 dark:text-gray-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-heading mb-2">No Commission Records</h3>
                            <p class="text-muted text-sm">Generate commissions from completed bookings</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($commissions->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- PAYMENT MODAL --}}
    {{-- ============================================ --}}
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
         @click.self="modalOpen = false" 
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md m-4 overflow-hidden animate-slide-up border border-gray-100 dark:border-gray-700"
             @click.stop>
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-transparent dark:from-green-500/5 dark:to-transparent">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/25">
                        <i class="fas fa-money-bill-wave text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-heading">Record Payment</h3>
                        <p class="text-xs text-muted">Commission #<span x-text="selectedCommissionId"></span></p>
                    </div>
                </div>
            </div>
            
            {{-- Modal Body --}}
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-heading mb-2">
                        <i class="fas fa-coins text-yellow-500 mr-1.5"></i> Amount Submitted (TZS)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted font-bold">TZS</span>
                        <input type="number" x-model="paymentAmount" min="0" :max="maxAmount"
                               class="w-full pl-14 pr-4 py-3.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-heading text-lg font-bold focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-800 outline-none transition-all duration-300"
                               placeholder="0">
                    </div>
                    <p class="text-xs text-muted mt-2 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i> 
                        Remaining balance: <span class="font-bold text-red-600 dark:text-red-400">TZS <span x-text="formatNumber(maxAmount)"></span></span>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-heading mb-2">
                        <i class="fas fa-pen text-blue-500 mr-1.5"></i> Notes (Optional)
                    </label>
                    <textarea x-model="paymentNotes" rows="3" 
                              class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                              placeholder="Any notes about this payment..."></textarea>
                </div>
                
                <button @click="recordPayment()" :disabled="!paymentAmount || submitting"
                        class="w-full px-6 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-[1.01] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                    <span x-show="!submitting"><i class="fas fa-check mr-2"></i> Record Payment</span>
                    <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i> Processing...</span>
                </button>
                
                <button @click="modalOpen = false" 
                        class="w-full px-6 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-body font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300">
                    Cancel
                </button>
            </div>
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
    function commissionManager() {
        return {
            modalOpen: false,
            selectedCommissionId: null,
            maxAmount: 0,
            paymentAmount: '',
            paymentNotes: '',
            submitting: false,
            toast: { show: false, message: '', type: 'success' },
            
            openPaymentModal(id, remaining) {
                this.selectedCommissionId = id;
                this.maxAmount = remaining;
                this.paymentAmount = '';
                this.paymentNotes = '';
                this.modalOpen = true;
            },
            
            async recordPayment() {
                if (!this.paymentAmount || this.paymentAmount <= 0) {
                    this.showToast('Please enter a valid amount', 'error');
                    return;
                }
                
                this.submitting = true;
                
                try {
                    const res = await fetch(`/admin/commissions/${this.selectedCommissionId}/record-payment`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            amount: parseFloat(this.paymentAmount),
                            notes: this.paymentNotes
                        })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.modalOpen = false;
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.showToast(data.message || 'Payment recording failed', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                } finally {
                    this.submitting = false;
                }
            },
            
            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 3500);
            },
            
            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num || 0);
            }
        };
    }
</script>

<style>
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-slide-up {
        animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>
@endpush
