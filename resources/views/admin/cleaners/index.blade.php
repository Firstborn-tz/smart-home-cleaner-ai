@extends('layouts.app')

@section('title', 'All Cleaners')
@section('user_role', 'Administrator')
@section('page_title', 'All Cleaners')
@section('page_subtitle', 'Manage all registered cleaners')

@section('content')
<div>
    @php
        $search = request('search', '');
        $status = request('status', '');
        $city = request('city', '');
        $verified = request('verified', '');
        $sort = request('sort', 'rating');
        
        $query = App\Models\Cleaner::with(['user', 'city'])
            ->when($search, function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('cleaner_id', 'like', "%{$search}%");
            })
            ->when($status, fn($q) => $q->where('availability_status', $status))
            ->when($city, fn($q) => $q->where('city_id', $city))
            ->when($verified !== '', fn($q) => $q->where('is_verified', $verified === 'yes'));
        
        switch($sort) {
            case 'name': $query->join('users', 'cleaners.user_id', '=', 'users.id')->orderBy('users.first_name')->select('cleaners.*'); break;
            case 'jobs': $query->orderByDesc('total_completed_jobs'); break;
            case 'earnings': $query->orderByDesc('total_earnings'); break;
            case 'newest': $query->latest(); break;
            default: $query->orderByDesc('rating');
        }
        
        $cleaners = $query->paginate(15)->appends(request()->query());
        
        $allCities = App\Models\City::where('is_active', true)->orderBy('name')->get();
        
        // Quick stats
        $totalCleaners = App\Models\Cleaner::count();
        $onlineNow = App\Models\Cleaner::where('availability_status', 'online')->count();
        $verifiedCount = App\Models\Cleaner::where('is_verified', true)->count();
        $pendingCount = App\Models\Cleaner::where('is_verified', false)->count();
        $avgRating = App\Models\Cleaner::avg('rating') ?? 0;
    @endphp

    <!-- ============================================ -->
    <!-- STATS ROW -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-blue-600">{{ $totalCleaners }}</p>
            <p class="text-xs text-gray-500">Total</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-green-600">{{ $onlineNow }}</p>
            <p class="text-xs text-gray-500">Online Now</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-purple-600">{{ $verifiedCount }}</p>
            <p class="text-xs text-gray-500">Verified</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-yellow-600">{{ $pendingCount }}</p>
            <p class="text-xs text-gray-500">Unverified</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-orange-600">{{ number_format($avgRating, 1) }}</p>
            <p class="text-xs text-gray-500">Avg Rating</p>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SEARCH & FILTERS -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email, phone, ID..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            </div>
            
            <select name="status" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Status</option>
                <option value="online" {{ $status === 'online' ? 'selected' : '' }}>Online</option>
                <option value="online_busy" {{ $status === 'online_busy' ? 'selected' : '' }}>Busy</option>
                <option value="offline" {{ $status === 'offline' ? 'selected' : '' }}>Offline</option>
                <option value="scheduled_only" {{ $status === 'scheduled_only' ? 'selected' : '' }}>Scheduled Only</option>
            </select>
            
            <select name="city" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Cities</option>
                @foreach($allCities as $c)
                <option value="{{ $c->id }}" {{ $city == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            
            <select name="verified" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Verification</option>
                <option value="yes" {{ $verified === 'yes' ? 'selected' : '' }}>Verified</option>
                <option value="no" {{ $verified === 'no' ? 'selected' : '' }}>Unverified</option>
            </select>
            
            <select name="sort" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Top Rated</option>
                <option value="jobs" {{ $sort === 'jobs' ? 'selected' : '' }}>Most Jobs</option>
                <option value="earnings" {{ $sort === 'earnings' ? 'selected' : '' }}>Highest Earnings</option>
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name A-Z</option>
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
            </select>
            
            <button type="submit" class="px-4 py-2.5 bg-blue-500 text-white rounded-xl font-bold text-sm hover:bg-blue-600 transition">
                <i class="fas fa-filter mr-1"></i> Apply
            </button>
            
            @if($search || $status || $city || $verified !== '' || $sort !== 'rating')
            <a href="/admin/cleaners" class="px-3 py-2.5 text-red-500 hover:text-red-700 text-sm">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
            @endif
        </form>
    </div>

    <!-- ============================================ -->
    <!-- CLEANERS TABLE -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cleaner</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rating</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jobs</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Earnings</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Verified</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($cleaners as $cleaner)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <!-- Cleaner Info -->
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($cleaner->user->full_name) }}&background=3b82f6&color=fff&size=36&bold=true" 
                                     class="w-9 h-9 rounded-lg flex-shrink-0">
                                <div>
                                    <p class="font-bold text-sm text-gray-800 dark:text-white">{{ $cleaner->user->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $cleaner->user->email }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $cleaner->cleaner_id }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- City -->
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $cleaner->city->name ?? 'N/A' }}
                        </td>
                        
                        <!-- Status Badge -->
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                @if($cleaner->availability_status === 'online') bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                                @elseif($cleaner->availability_status === 'online_busy') bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300
                                @elseif($cleaner->availability_status === 'scheduled_only') bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                                @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                    @if($cleaner->availability_status === 'online') bg-green-500
                                    @elseif($cleaner->availability_status === 'online_busy') bg-yellow-500
                                    @else bg-gray-400 @endif"></span>
                                {{ ucfirst(str_replace('_', ' ', $cleaner->availability_status)) }}
                            </span>
                        </td>
                        
                        <!-- Rating -->
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                <span class="font-bold text-sm text-gray-800 dark:text-white">{{ number_format($cleaner->rating, 1) }}</span>
                            </div>
                        </td>
                        
                        <!-- Jobs -->
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-sm text-gray-800 dark:text-white">{{ $cleaner->total_completed_jobs }}</span>
                            <p class="text-xs text-gray-500">{{ number_format($cleaner->completion_rate, 0) }}% completion</p>
                        </td>
                        
                        <!-- Earnings -->
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-sm text-green-600 dark:text-green-400">TZS {{ number_format($cleaner->total_earnings, 0) }}</span>
                            <p class="text-xs text-gray-500">Payout: TZS {{ number_format($cleaner->pending_payout, 0) }}</p>
                        </td>
                        
                        <!-- Verified -->
                        <td class="px-4 py-3 text-center">
                            @if($cleaner->is_verified)
                            <span class="inline-flex items-center text-green-600 text-xs font-bold">
                                <i class="fas fa-check-circle mr-1"></i> Verified
                            </span>
                            @else
                            <span class="inline-flex items-center text-yellow-600 text-xs font-bold">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @endif
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                <a href="/admin/cleaners/{{ $cleaner->id }}" 
                                   class="p-2 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 transition" 
                                   title="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="/cleaner/{{ $cleaner->id }}/profile" target="_blank"
                                   class="p-2 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg hover:bg-purple-200 transition"
                                   title="Public Profile">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                                @if(!$cleaner->is_verified)
                                <form method="POST" action="/admin/cleaners/{{ $cleaner->id }}/approve" class="inline" onsubmit="return confirm('Approve this cleaner?')">
                                    @csrf
                                    <button type="submit" class="p-2 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-200 transition" title="Approve">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                </form>
                                @endif
                                <button onclick="toggleStatus({{ $cleaner->id }}, '{{ $cleaner->availability_status }}')"
                                        class="p-2 bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400 rounded-lg hover:bg-yellow-200 transition"
                                        title="Toggle Status">
                                    <i class="fas fa-power-off text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-gray-500">
                            <i class="fas fa-search text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <p class="text-lg font-bold">No Cleaners Found</p>
                            <p class="text-sm">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($cleaners->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">
            {{ $cleaners->links() }}
        </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- BOTTOM STATS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4">
            <p class="text-xs text-gray-500">Total Earnings (All Cleaners)</p>
            <p class="text-xl font-extrabold text-green-600">TZS {{ number_format(App\Models\Cleaner::sum('total_earnings'), 0) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4">
            <p class="text-xs text-gray-500">Total Jobs Completed</p>
            <p class="text-xl font-extrabold text-blue-600">{{ App\Models\Cleaner::sum('total_completed_jobs') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4">
            <p class="text-xs text-gray-500">Avg Completion Rate</p>
            <p class="text-xl font-extrabold text-purple-600">{{ number_format(App\Models\Cleaner::avg('completion_rate') ?? 0, 1) }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4">
            <p class="text-xs text-gray-500">Total Pending Payouts</p>
            <p class="text-xl font-extrabold text-orange-600">TZS {{ number_format(App\Models\Cleaner::sum('pending_payout'), 0) }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    async function toggleStatus(cleanerId, currentStatus) {
        const newStatus = currentStatus === 'online' ? 'offline' : 'online';
        try {
            const res = await fetch(`/admin/cleaners/${cleanerId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });
            const data = await res.json();
            window.showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1000);
        } catch (e) {
            window.showToast('Failed to update status', 'error');
        }
    }
</script>
@endpush
@endsection