@extends('layouts.app')

@section('title', 'Cleaner Registration Requests')
@section('user_role', 'Administrator')
@section('page_title', 'Registration Requests')
@section('page_subtitle', 'Review and manage cleaner applications')

@section('content')
<div x-data="cleanerRequests()">
    
   @php
    // Get ALL unverified cleaners as pending
    $pendingCleaners = App\Models\Cleaner::with(['user', 'city'])
        ->where('is_verified', false)
        ->latest()
        ->get();
        
    $approvedCleaners = App\Models\Cleaner::with(['user', 'city'])
        ->where('is_verified', true)
        ->latest()->limit(30)->get();
        
    $rejectedCleaners = App\Models\Cleaner::with(['user'])
        ->where('registration_status', 'rejected')
        ->latest()->limit(20)->get();
        
    $tab = request('tab', 'pending');
   @endphp
    <!-- Stats Bar -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-yellow-600">{{ $pendingCleaners->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-green-600">{{ $approvedToday }}</p>
            <p class="text-xs text-gray-500 mt-1">Approved Today</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-blue-600">{{ App\Models\Cleaner::count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Cleaners</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex space-x-2 mb-6 overflow-x-auto" x-data="{ tab: 'pending' }">
        <button @click="tab = 'pending'" :class="tab === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                class="px-4 py-2 rounded-xl text-sm font-bold transition flex-shrink-0">
            <i class="fas fa-clock mr-1"></i> Pending ({{ $pendingCleaners->count() }})
        </button>
        <button @click="tab = 'approved'" :class="tab === 'approved' ? 'bg-green-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                class="px-4 py-2 rounded-xl text-sm font-bold transition flex-shrink-0">
            <i class="fas fa-check mr-1"></i> Approved
        </button>
        <button @click="tab = 'rejected'" :class="tab === 'rejected' ? 'bg-red-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                class="px-4 py-2 rounded-xl text-sm font-bold transition flex-shrink-0">
            <i class="fas fa-times mr-1"></i> Rejected
        </button>
    </div>

    <!-- PENDING REQUESTS -->
    <div x-show="tab === 'pending'">
        @if($pendingCleaners->count() > 0)
        <div class="space-y-4">
            @foreach($pendingCleaners as $cleaner)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-yellow-500">
                
                <!-- Cleaner Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($cleaner->user->full_name) }}&background=f59e0b&color=fff&bold=true&size=56" 
                             class="w-14 h-14 rounded-xl flex-shrink-0">
                        <div>
                            <h3 class="font-extrabold text-lg text-gray-800 dark:text-white">{{ $cleaner->user->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $cleaner->user->email }}</p>
                            <p class="text-sm text-gray-500">{{ $cleaner->user->phone }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 rounded-full text-xs font-bold">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </span>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Gender</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ ucfirst($cleaner->gender ?? 'N/A') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Date of Birth</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ $cleaner->date_of_birth ? \Carbon\Carbon::parse($cleaner->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">National ID</p>
                        <p class="font-bold text-gray-800 dark:text-white text-xs">{{ $cleaner->national_id ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">City</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ $cleaner->city->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Street Address</p>
                        <p class="font-bold text-gray-800 dark:text-white text-xs">{{ $cleaner->street ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Ward/District</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ $cleaner->ward ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Region</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ $cleaner->region ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Applied</p>
                        <p class="font-bold text-gray-800 dark:text-white">{{ $cleaner->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <!-- Map & Location -->
                @if($cleaner->current_latitude && $cleaner->current_longitude)
                <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-3 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Registered Location
                        </p>
                        <a href="https://www.google.com/maps?q={{ $cleaner->current_latitude }},{{ $cleaner->current_longitude }}" 
                           target="_blank" class="text-blue-600 text-xs hover:underline">
                            <i class="fas fa-external-link-alt mr-1"></i> View on Google Maps
                        </a>
                    </div>
                    <div id="map-{{ $cleaner->id }}" class="w-full h-48 rounded-lg bg-gray-200 dark:bg-gray-600"></div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-street-view mr-1"></i> {{ $cleaner->street ?? '' }}, {{ $cleaner->ward ?? '' }}, {{ $cleaner->city->name ?? '' }}
                    </p>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button onclick="showDetailModal({{ $cleaner->id }})" 
                            class="flex-1 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-sm transition">
                        <i class="fas fa-eye mr-1"></i> View Full Details
                    </button>
                    <button onclick="approveCleaner({{ $cleaner->id }})" 
                            class="flex-1 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold text-sm transition">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                    <button onclick="rejectCleaner({{ $cleaner->id }})" 
                            class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-sm transition">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16 text-gray-500">
            <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
            <p class="text-lg font-bold">No Pending Requests</p>
            <p class="text-sm">All cleaner applications have been processed</p>
        </div>
        @endif
    </div>

    <!-- APPROVED TAB -->
    <div x-show="tab === 'approved'" x-data="{ approved: [] }" x-init="fetchApproved()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cleaner</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jobs</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach(App\Models\Cleaner::with('user','city')->where('registration_status','approved')->latest()->limit(30)->get() as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($c->user->full_name) }}&background=22c55e&color=fff&size=32" class="w-8 h-8 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white">{{ $c->user->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $c->cleaner_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $c->city->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3"><span class="text-green-600 font-bold text-xs">Approved</span></td>
                            <td class="px-4 py-3">{{ number_format($c->rating, 1) }}</td>
                            <td class="px-4 py-3">{{ $c->total_completed_jobs }}</td>
                            <td class="px-4 py-3">
                                <a href="/admin/cleaners/{{ $c->id }}" class="text-blue-600 text-xs hover:underline">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- REJECTED TAB -->
    <div x-show="tab === 'rejected'">
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
                    @foreach(App\Models\Cleaner::with('user')->where('registration_status','rejected')->latest()->limit(30)->get() as $c)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $c->user->full_name }}</td>
                        <td class="px-4 py-3 text-sm text-red-600">{{ $c->registration_notes ?? 'No reason' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $c->updated_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DETAIL MODAL -->
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" onclick="closeDetailModal(event)">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white dark:bg-gray-800 p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between rounded-t-2xl">
            <h3 class="font-bold text-lg text-gray-800 dark:text-white">Cleaner Details</h3>
            <button onclick="closeDetailModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>
        <div id="detailContent" class="p-6">
            <p class="text-center text-gray-500">Loading...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Load maps for pending cleaners
    function initMaps() {
        @foreach($pendingCleaners as $cleaner)
        @if($cleaner->current_latitude && $cleaner->current_longitude)
        (function() {
            const mapEl = document.getElementById('map-{{ $cleaner->id }}');
            if (!mapEl) return;
            const pos = { lat: {{ $cleaner->current_latitude }}, lng: {{ $cleaner->current_longitude }} };
            const map = new google.maps.Map(mapEl, { center: pos, zoom: 15, mapTypeControl: false, streetViewControl: false });
            new google.maps.Marker({ position: pos, map: map, title: "{{ $cleaner->user->full_name }}",
                icon: { url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' }
            });
        })();
        @endif
        @endforeach
    }

    async function showDetailModal(cleanerId) {
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModal').classList.add('flex');
        document.getElementById('detailContent').innerHTML = '<p class="text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Loading...</p>';
        
        try {
            const res = await fetch(`/admin/cleaners/${cleanerId}/details`);
            const data = await res.json();
            if (data.success) {
                const c = data.cleaner;
                document.getElementById('detailContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.user.full_name)}&background=3b82f6&color=fff&size=64" class="w-16 h-16 rounded-xl">
                            <div>
                                <h3 class="font-extrabold text-xl">${c.user.full_name}</h3>
                                <p class="text-gray-500">${c.user.email}</p>
                                <p class="text-gray-500">${c.user.phone}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"><p class="text-xs text-gray-500">Gender</p><p class="font-bold">${c.gender || 'N/A'}</p></div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"><p class="text-xs text-gray-500">DOB</p><p class="font-bold">${c.date_of_birth || 'N/A'}</p></div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"><p class="text-xs text-gray-500">NIDA</p><p class="font-bold text-xs">${c.national_id || 'N/A'}</p></div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"><p class="text-xs text-gray-500">City</p><p class="font-bold">${c.city?.name || 'N/A'}</p></div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 col-span-2"><p class="text-xs text-gray-500">Address</p><p class="font-bold">${c.street || ''}, ${c.ward || ''}, ${c.region || ''}</p></div>
                        </div>
                        ${c.current_latitude ? `
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-3">
                            <p class="text-sm font-bold mb-2"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i>Location</p>
                            <div id="detailMap" class="w-full h-48 rounded-lg bg-gray-200"></div>
                        </div>` : ''}
                    </div>
                `;
                
                // Init detail map
                if (c.current_latitude) {
                    setTimeout(() => {
                        const mapEl = document.getElementById('detailMap');
                        if (!mapEl) return;
                        const pos = { lat: parseFloat(c.current_latitude), lng: parseFloat(c.current_longitude) };
                        const map = new google.maps.Map(mapEl, { center: pos, zoom: 16 });
                        new google.maps.Marker({ position: pos, map: map });
                    }, 300);
                }
            }
        } catch (e) {
            document.getElementById('detailContent').innerHTML = '<p class="text-red-500">Failed to load details</p>';
        }
    }

    function closeDetailModal(event) {
        if (event && event.target !== document.getElementById('detailModal')) return;
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModal').classList.remove('flex');
    }

    async function approveCleaner(id) {
        if (!confirm('Approve this cleaner? They will be notified and can start receiving bookings.')) return;
        try {
            const res = await fetch(`/admin/cleaners/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            const data = await res.json();
            window.showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) { window.showToast('Failed', 'error'); }
    }

    async function rejectCleaner(id) {
        const reason = prompt('Reason for rejection (will be shown to the cleaner):');
        if (!reason) return;
        try {
            const res = await fetch(`/admin/cleaners/${id}/reject`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ reason })
            });
            const data = await res.json();
            window.showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (e) { window.showToast('Failed', 'error'); }
    }

    // Load maps on page load
    window.addEventListener('load', () => setTimeout(initMaps, 500));
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&v=weekly"></script>
@endpush
@endsection