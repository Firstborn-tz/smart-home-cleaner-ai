@extends('layouts.app')

@section('title', 'Registration Requests')
@section('user_role', 'Administrator')
@section('page_title', 'Registration Requests')
@section('page_subtitle', 'Review and manage cleaner applications')

@section('content')
<div>
    @php
        $sort = request('sort', 'newest');
        $cityFilter = request('city', '');
        $genderFilter = request('gender', '');
        $tab = request('tab', 'pending');
        
        // Pending query with filters
        $pendingQuery = App\Models\Cleaner::with(['user', 'city'])
            ->where('is_verified', false);
        
        if ($cityFilter) $pendingQuery->where('city_id', $cityFilter);
        if ($genderFilter) $pendingQuery->where('gender', $genderFilter);
        
        switch($sort) {
            case 'oldest': $pendingQuery->oldest(); break;
            case 'name_asc': $pendingQuery->join('users', 'cleaners.user_id', '=', 'users.id')->orderBy('users.first_name', 'asc')->select('cleaners.*'); break;
            case 'name_desc': $pendingQuery->join('users', 'cleaners.user_id', '=', 'users.id')->orderBy('users.first_name', 'desc')->select('cleaners.*'); break;
            default: $pendingQuery->latest();
        }
        
        $pendingCleaners = $pendingQuery->get();
        $approvedCleaners = App\Models\Cleaner::with(['user', 'city'])->where('is_verified', true)->latest()->limit(30)->get();
        $rejectedCleaners = App\Models\Cleaner::with(['user'])->where('registration_status', 'rejected')->latest()->limit(20)->get();
        
        $allCities = App\Models\City::where('is_active', true)->orderBy('name')->get();
    @endphp

    <!-- ============================================ -->
    <!-- STATS CARDS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <a href="?tab=pending" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 text-center border-b-4 {{ $tab === 'pending' ? 'border-yellow-500' : 'border-transparent' }} hover:shadow-xl transition">
            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-clock text-yellow-600 text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ $pendingCleaners->count() }}</p>
            <p class="text-xs text-gray-500">Pending</p>
        </a>
        <a href="?tab=approved" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 text-center border-b-4 {{ $tab === 'approved' ? 'border-green-500' : 'border-transparent' }} hover:shadow-xl transition">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ $approvedCleaners->count() }}</p>
            <p class="text-xs text-gray-500">Approved</p>
        </a>
        <a href="?tab=rejected" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 text-center border-b-4 {{ $tab === 'rejected' ? 'border-red-500' : 'border-transparent' }} hover:shadow-xl transition">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-times-circle text-red-600 text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ $rejectedCleaners->count() }}</p>
            <p class="text-xs text-gray-500">Rejected</p>
        </a>
        <a href="/admin/cleaners" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 text-center border-b-4 border-transparent hover:shadow-xl transition">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-users text-blue-600 text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ App\Models\Cleaner::count() }}</p>
            <p class="text-xs text-gray-500">All Cleaners</p>
        </a>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 text-center">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-chart-pie text-purple-600 text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 dark:text-white">{{ round(($approvedCleaners->count() / max(App\Models\Cleaner::count(), 1)) * 100) }}%</p>
            <p class="text-xs text-gray-500">Approval Rate</p>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- FILTERS -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <select name="sort" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest First</option>
                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
            </select>
            
            <select name="city" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Cities</option>
                @foreach($allCities as $city)
                <option value="{{ $city->id }}" {{ $cityFilter == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            
            <select name="gender" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">All Genders</option>
                <option value="male" {{ $genderFilter === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ $genderFilter === 'female' ? 'selected' : '' }}>Female</option>
            </select>
            
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-xl font-bold text-sm hover:bg-blue-600 transition">
                <i class="fas fa-filter mr-1"></i> Apply
            </button>
            
            @if($sort !== 'newest' || $cityFilter || $genderFilter)
            <a href="?tab={{ $tab }}" class="px-3 py-2 text-red-500 hover:text-red-700 text-sm">
                <i class="fas fa-times mr-1"></i> Clear Filters
            </a>
            @endif
        </form>
    </div>

    <!-- ============================================ -->
    <!-- PENDING TAB -->
    <!-- ============================================ -->
    @if($tab === 'pending')
        @if($pendingCleaners->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($pendingCleaners as $cleaner)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-yellow-500 hover:shadow-xl transition">
                
                <!-- Cleaner Header -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($cleaner->user->full_name) }}&background=f59e0b&color=fff&size=48&bold=true" 
                             class="w-12 h-12 rounded-xl flex-shrink-0">
                        <div>
                            <h3 class="font-extrabold text-gray-800 dark:text-white">{{ $cleaner->user->full_name }}</h3>
                            <p class="text-xs text-gray-500">{{ $cleaner->user->email }}</p>
                            <p class="text-xs text-gray-500">{{ $cleaner->user->phone }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 rounded-full text-xs font-bold">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </span>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-3 gap-2 mb-3 text-xs">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2 text-center">
                        <p class="text-gray-500">Gender</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ ucfirst($cleaner->gender ?? 'N/A') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2 text-center">
                        <p class="text-gray-500">City</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ $cleaner->city->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2 text-center">
                        <p class="text-gray-500">NIDA</p>
                        <p class="font-bold text-gray-800 dark:text-white text-xs">{{ $cleaner->national_id ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Location -->
                @if($cleaner->street || $cleaner->ward)
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2 mb-3 text-xs text-gray-700 dark:text-gray-300">
                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                    {{ $cleaner->street ?? '' }}, {{ $cleaner->ward ?? '' }}, {{ $cleaner->city->name ?? '' }}, {{ $cleaner->region ?? '' }}
                </div>
                @endif

                <!-- Applied Time -->
                <p class="text-xs text-gray-400 mb-3">
                    <i class="fas fa-calendar mr-1"></i> Applied {{ $cleaner->created_at->diffForHumans() }}
                </p>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button onclick="showDetailModal({{ $cleaner->id }})" 
                            class="flex-1 px-3 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-xs transition">
                        <i class="fas fa-eye mr-1"></i> Details
                    </button>
                    <form method="POST" action="/admin/cleaners/{{ $cleaner->id }}/approve" class="flex-1" onsubmit="return confirm('Approve {{ $cleaner->user->full_name }}?')">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold text-xs transition">
                            <i class="fas fa-check mr-1"></i> Approve
                        </button>
                    </form>
                    <button onclick="rejectCleaner({{ $cleaner->id }})" 
                            class="flex-1 px-3 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-xs transition">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300">No Pending Requests</h3>
            <p class="text-gray-500 text-sm mt-1">All cleaner applications have been processed</p>
        </div>
        @endif
    @endif

    <!-- ============================================ -->
    <!-- APPROVED TAB -->
    <!-- ============================================ -->
    @if($tab === 'approved')
        @if($approvedCleaners->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cleaner</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jobs</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($approvedCleaners as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($c->user->full_name) }}&background=22c55e&color=fff&size=28" class="w-7 h-7 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white">{{ $c->user->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $c->cleaner_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $c->city->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ number_format($c->rating, 1) }}</td>
                            <td class="px-4 py-3">{{ $c->total_completed_jobs }}</td>
                            <td class="px-4 py-3"><span class="text-green-600 font-bold text-xs"><i class="fas fa-circle text-xs mr-1"></i> Active</span></td>
                            <td class="px-4 py-3">
                                <a href="/admin/cleaners/{{ $c->id }}" class="text-blue-600 text-xs hover:underline">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="text-center py-16 text-gray-500">No approved cleaners yet</div>
        @endif
    @endif

    <!-- ============================================ -->
    <!-- REJECTED TAB -->
    <!-- ============================================ -->
    @if($tab === 'rejected')
        @if($rejectedCleaners->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cleaner</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($rejectedCleaners as $c)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $c->user->full_name }}</td>
                        <td class="px-4 py-3 text-sm text-red-600">{{ $c->registration_notes ?? 'No reason given' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $c->updated_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-16 text-gray-500">No rejected cleaners</div>
        @endif
    @endif
</div>

<!-- DETAIL MODAL -->
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" onclick="closeModal(event)">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto m-4" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white dark:bg-gray-800 p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between rounded-t-2xl">
            <h3 class="font-bold text-lg text-gray-800 dark:text-white">Cleaner Details</h3>
            <button onclick="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>
        <div id="detailContent" class="p-5">
            <p class="text-center text-gray-500">Loading...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    async function showDetailModal(cleanerId) {
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('detailContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i><p class="text-gray-500 text-sm mt-2">Loading...</p></div>';
        
        try {
            const res = await fetch(`/admin/cleaners/${cleanerId}/details`);
            const data = await res.json();
            if (data.success) {
                const c = data.cleaner;
                document.getElementById('detailContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.user.full_name)}&background=3b82f6&color=fff&size=56" class="w-14 h-14 rounded-xl">
                            <div>
                                <h3 class="font-extrabold text-lg text-gray-800 dark:text-white">${c.user.full_name}</h3>
                                <p class="text-sm text-gray-500">${c.user.email}</p>
                                <p class="text-sm text-gray-500">${c.user.phone}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500">Gender</p>
                                <p class="font-bold text-gray-800 dark:text-white">${c.gender || 'N/A'}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500">DOB</p>
                                <p class="font-bold text-gray-800 dark:text-white">${c.date_of_birth || 'N/A'}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500">NIDA</p>
                                <p class="font-bold text-gray-800 dark:text-white text-xs">${c.national_id || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-sm">
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="font-bold text-gray-800 dark:text-white">${c.street || ''}, ${c.ward || ''}, ${c.city?.name || 'N/A'}, ${c.region || ''}</p>
                        </div>
                        <div class="flex space-x-2">
                            <form method="POST" action="/admin/cleaners/${c.id}/approve" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold text-sm">Approve</button>
                            </form>
                            <button onclick="closeModal(); rejectCleaner(${c.id})" class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-sm">Reject</button>
                        </div>
                    </div>
                `;
            }
        } catch (e) {
            document.getElementById('detailContent').innerHTML = '<p class="text-red-500 text-center">Failed to load details</p>';
        }
    }

    function closeModal(event) {
        if (event && event.target !== document.getElementById('detailModal')) return;
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function rejectCleaner(id) {
        const reason = prompt('Reason for rejection:');
        if (!reason) return;
        try {
            const res = await fetch(`/admin/cleaners/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason })
            });
            const data = await res.json();
            window.showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) {
            window.showToast('Failed to reject', 'error');
        }
    }
</script>
@endpush
@endsection