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
        
        // Summary calculations
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
        
        // Top cleaners by commission
        $topCleaners = App\Models\Cleaner::with('user')
            ->withSum('commissions', 'expected_total_amount')
            ->withSum('commissions', 'actual_submitted_amount')
            ->orderByDesc('commissions_sum_expected_total_amount')
            ->limit(5)
            ->get();
        
        // Monthly breakdown
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
    @endphp

    <!-- ============================================ -->
    <!-- SUMMARY CARDS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">TZS {{ number_format($summary['total_expected'], 0) }}</p>
            <p class="text-blue-200 text-xs">Total Expected</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-emerald-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">TZS {{ number_format($summary['total_submitted'], 0) }}</p>
            <p class="text-green-200 text-xs">Total Collected</p>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-pink-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">TZS {{ number_format($summary['total_remaining'], 0) }}</p>
            <p class="text-red-200 text-xs">Outstanding</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">{{ round(($summary['total_submitted'] / max($summary['total_expected'], 1)) * 100) }}%</p>
            <p class="text-purple-200 text-xs">Collection Rate</p>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- STATUS BREAKDOWN -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[120px] text-center">
                <p class="text-2xl font-extrabold text-yellow-600">{{ $summary['pending_count'] }}</p>
                <p class="text-xs text-gray-500">Pending</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                    <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ ($summary['pending_count'] / max($commissions->count(), 1)) * 100 }}%"></div>
                </div>
            </div>
            <div class="flex-1 min-w-[120px] text-center">
                <p class="text-2xl font-extrabold text-blue-600">{{ $summary['partial_count'] }}</p>
                <p class="text-xs text-gray-500">Partial</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ ($summary['partial_count'] / max($commissions->count(), 1)) * 100 }}%"></div>
                </div>
            </div>
            <div class="flex-1 min-w-[120px] text-center">
                <p class="text-2xl font-extrabold text-green-600">{{ $summary['paid_count'] }}</p>
                <p class="text-xs text-gray-500">Fully Paid</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ ($summary['paid_count'] / max($commissions->count(), 1)) * 100 }}%"></div>
                </div>
            </div>
            <div class="flex-1 min-w-[120px] text-center">
                <p class="text-2xl font-extrabold text-purple-600">{{ $summary['overpaid_count'] }}</p>
                <p class="text-xs text-gray-500">Overpaid</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ ($summary['overpaid_count'] / max($commissions->count(), 1)) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- ============================================ -->
        <!-- TOP CLEANERS (Left Sidebar) -->
        <!-- ============================================ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top Cleaners
            </h3>
            <div class="space-y-3">
                @foreach($topCleaners as $index => $tc)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold
                            {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : ($index === 2 ? 'bg-orange-600' : 'bg-blue-500')) }}">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-gray-800 dark:text-white">{{ $tc->user->full_name }}</p>
                            <p class="text-xs text-gray-500">TZS {{ number_format($tc->commissions_sum_expected_total_amount ?? 0, 0) }}</p>
                        </div>
                    </div>
                    <span class="text-xs {{ ($tc->commissions_sum_actual_submitted_amount ?? 0) >= ($tc->commissions_sum_expected_total_amount ?? 0) ? 'text-green-600' : 'text-red-600' }} font-bold">
                        {{ round((($tc->commissions_sum_actual_submitted_amount ?? 0) / max(($tc->commissions_sum_expected_total_amount ?? 1), 1)) * 100) }}%
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- ============================================ -->
        <!-- MONTHLY BREAKDOWN (Right) -->
        <!-- ============================================ -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i> Monthly Breakdown
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Commissions</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Expected</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Collected</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Remaining</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($monthlyBreakdown as $m)
                        <tr>
                            <td class="py-2 font-medium text-gray-800 dark:text-white">{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('F Y') }}</td>
                            <td class="py-2 text-right">{{ $m->total }}</td>
                            <td class="py-2 text-right">TZS {{ number_format($m->expected, 0) }}</td>
                            <td class="py-2 text-right text-green-600">TZS {{ number_format($m->collected, 0) }}</td>
                            <td class="py-2 text-right text-red-600">TZS {{ number_format($m->remaining, 0) }}</td>
                            <td class="py-2 text-right font-bold">{{ $m->expected > 0 ? round(($m->collected / $m->expected) * 100) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- FILTERS -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="status" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Status</option>
                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="partially_paid" {{ $statusFilter === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                <option value="fully_paid" {{ $statusFilter === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                <option value="overpaid" {{ $statusFilter === 'overpaid' ? 'selected' : '' }}>Overpaid</option>
            </select>
            
            <select name="cleaner_id" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Cleaners</option>
                @foreach($allCleaners as $c)
                <option value="{{ $c->id }}" {{ $cleanerFilter == $c->id ? 'selected' : '' }}>{{ $c->user->full_name }}</option>
                @endforeach
            </select>
            
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <span class="text-gray-500">to</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            
            <select name="sort" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                <option value="highest" {{ $sort === 'highest' ? 'selected' : '' }}>Highest Amount</option>
                <option value="lowest" {{ $sort === 'lowest' ? 'selected' : '' }}>Lowest Amount</option>
            </select>
            
            <button type="submit" class="px-4 py-2.5 bg-blue-500 text-white rounded-xl font-bold text-sm hover:bg-blue-600 transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            
            @if($statusFilter || $cleanerFilter || $dateFrom || $dateTo || $sort !== 'newest')
            <a href="/admin/commissions" class="px-3 py-2.5 text-red-500 hover:text-red-700 text-sm">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
            @endif
            
            <div class="ml-auto flex space-x-2">
                <form method="POST" action="/admin/commissions/generate" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-purple-500 text-white rounded-xl font-bold text-sm hover:bg-purple-600 transition">
                        <i class="fas fa-sync-alt mr-1"></i> Generate from Bookings
                    </button>
                </form>
            </div>
        </form>
    </div>

    <!-- ============================================ -->
    <!-- COMMISSIONS TABLE -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cleaner</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Expected</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($commissions as $commission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $commission->id }}</td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-sm text-gray-800 dark:text-white">{{ $commission->cleaner->user->full_name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $commission->cleaner->cleaner_id ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $commission->booking->service->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-sm text-gray-800 dark:text-white">
                            TZS {{ number_format($commission->expected_total_amount, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-sm text-green-600">
                            TZS {{ number_format($commission->actual_submitted_amount, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-sm {{ $commission->remaining_unpaid_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                            TZS {{ number_format($commission->remaining_unpaid_amount, 0) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                @if($commission->payment_status === 'fully_paid') bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                                @elseif($commission->payment_status === 'partially_paid') bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                                @elseif($commission->payment_status === 'overpaid') bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                                @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 @endif">
                                {{ ucfirst(str_replace('_', ' ', $commission->payment_status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($commission->remaining_unpaid_amount > 0)
                            <button @click="openPaymentModal({{ $commission->id }}, {{ $commission->remaining_unpaid_amount }})"
                                    class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-bold text-xs transition">
                                <i class="fas fa-money-bill-wave mr-1"></i> Record
                            </button>
                            @else
                            <span class="text-green-600 text-xs font-bold"><i class="fas fa-check-circle mr-1"></i> Settled</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-gray-500">
                            <i class="fas fa-receipt text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <p class="text-lg font-bold">No Commission Records</p>
                            <p class="text-sm">Generate commissions from completed bookings</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($commissions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- PAYMENT MODAL -->
<div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="modalOpen = false" x-cloak>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md m-4 p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i> Record Payment
        </h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount Submitted (TZS)</label>
                <input type="number" x-model="paymentAmount" min="0" :max="maxAmount"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-lg font-bold"
                       placeholder="Enter amount">
                <p class="text-xs text-gray-500 mt-1">Remaining: TZS <span x-text="formatNumber(maxAmount)"></span></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                <textarea x-model="paymentNotes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                          placeholder="Any notes about this payment..."></textarea>
            </div>
            
            <button @click="recordPayment()" :disabled="!paymentAmount || submitting"
                    class="w-full px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold transition disabled:opacity-50">
                <span x-show="!submitting"><i class="fas fa-check mr-2"></i> Record Payment</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i> Processing...</span>
            </button>
            
            <button @click="modalOpen = false" class="w-full px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-600 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </button>
        </div>
    </div>
</div>

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
            
            openPaymentModal(id, remaining) {
                this.selectedCommissionId = id;
                this.maxAmount = remaining;
                this.paymentAmount = '';
                this.paymentNotes = '';
                this.modalOpen = true;
            },
            
            async recordPayment() {
                if (!this.paymentAmount || this.paymentAmount <= 0) return;
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
                    window.showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        this.modalOpen = false;
                        setTimeout(() => location.reload(), 1500);
                    }
                } catch (e) {
                    window.showToast('Failed to record payment', 'error');
                } finally {
                    this.submitting = false;
                }
            },
            
            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num || 0);
            }
        };
    }
</script>
@endpush
@endsection