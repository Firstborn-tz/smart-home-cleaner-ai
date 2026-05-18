@extends('layouts.app')

@section('title', 'City Management')
@section('user_role', 'Administrator')
@section('page_title', 'City Management')
@section('page_subtitle', 'Monitor activity across all cities')

@section('content')
<div>
    @php
        $cities = App\Models\City::withCount(['cleaners', 'bookings'])
            ->withSum(['bookings' => function($q) {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }], 'total_amount')
            ->withSum(['bookings' => function($q) {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }], 'commission_amount')
            ->orderByDesc('bookings_count')
            ->get();
            
        $totalCleaners = App\Models\Cleaner::count();
        $totalBookings = App\Models\Booking::count();
        $activeCities = $cities->where('is_active', true)->count();
        $totalRevenue = App\Models\Booking::whereIn('status', ['completed', 'in_progress'])->sum('total_amount');
        
        // Top city by bookings
        $topCity = $cities->first();
        
        // City with most cleaners
        $mostCleanersCity = $cities->sortByDesc('cleaners_count')->first();
    @endphp

    <!-- ============================================ -->
    <!-- HERO STATS -->
    <!-- ============================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-city text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">{{ $cities->count() }}</p>
            <p class="text-blue-200 text-xs">{{ $activeCities }} Active Cities</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-emerald-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">{{ $totalCleaners }}</p>
            <p class="text-green-200 text-xs">Total Cleaners</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">{{ $totalBookings }}</p>
            <p class="text-purple-200 text-xs">Total Bookings</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl shadow-lg p-4 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold">TZS {{ number_format($totalRevenue, 0) }}</p>
            <p class="text-orange-200 text-xs">Total Revenue</p>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TOP & BOTTOM CITIES -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Most Active City -->
        @if($topCity)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i> Most Active City
                </h3>
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs font-bold">#1</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl font-extrabold">
                    {{ substr($topCity->code, 0, 2) }}
                </div>
                <div>
                    <h4 class="text-xl font-extrabold text-gray-800 dark:text-white">{{ $topCity->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $topCity->region }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="text-sm"><i class="fas fa-users text-blue-500 mr-1"></i> {{ $topCity->cleaners_count }} cleaners</span>
                        <span class="text-sm"><i class="fas fa-calendar-check text-green-500 mr-1"></i> {{ $topCity->bookings_count }} bookings</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Most Cleaners City -->
        @if($mostCleanersCity)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-users text-green-500 mr-2"></i> Most Cleaners
                </h3>
                <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full text-xs font-bold">{{ $mostCleanersCity->cleaners_count }} Cleaners</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-2xl font-extrabold">
                    {{ substr($mostCleanersCity->code, 0, 2) }}
                </div>
                <div>
                    <h4 class="text-xl font-extrabold text-gray-800 dark:text-white">{{ $mostCleanersCity->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $mostCleanersCity->region }}</p>
                    <p class="text-sm text-gray-500 mt-1">Avg Rating: {{ number_format(App\Models\Cleaner::where('city_id', $mostCleanersCity->id)->avg('rating') ?? 0, 1) }} ⭐</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- CITIES GRID -->
    <!-- ============================================ -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($cities as $city)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 hover:shadow-xl transition-all border-2 {{ $city->is_active ? 'border-transparent' : 'border-red-300 dark:border-red-700' }}">
            
            <!-- City Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg
                        {{ $city->is_active ? 'bg-gradient-to-br from-blue-400 to-purple-500' : 'bg-gradient-to-br from-gray-400 to-gray-600' }}">
                        {{ substr($city->code, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-800 dark:text-white">{{ $city->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $city->region }}</p>
                    </div>
                </div>
                <button onclick="toggleCityStatus({{ $city->id }})" 
                        class="relative inline-flex items-center h-7 w-14 rounded-full transition-colors duration-200"
                        :class="statuses[{{ $city->id }}] !== false ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="inline-block w-6 h-6 transform transition-transform duration-200 bg-white rounded-full shadow-md"
                          :class="statuses[{{ $city->id }}] !== false ? 'translate-x-7' : 'translate-x-1'"></span>
                </button>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-2 mb-3">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <p class="text-lg font-extrabold text-blue-600">{{ $city->cleaners_count }}</p>
                    <p class="text-xs text-gray-500">Cleaners</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <p class="text-lg font-extrabold text-green-600">{{ $city->bookings_count }}</p>
                    <p class="text-xs text-gray-500">Bookings</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <p class="text-lg font-extrabold text-purple-600">TZS {{ number_format($city->bookings_sum_total_amount ?? 0, 0) }}</p>
                    <p class="text-xs text-gray-500">Revenue</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <p class="text-lg font-extrabold text-orange-600">TZS {{ number_format($city->bookings_sum_commission_amount ?? 0, 0) }}</p>
                    <p class="text-xs text-gray-500">Commission</p>
                </div>
            </div>

            <!-- Online Cleaners Bar -->
            @php
                $onlineInCity = App\Models\Cleaner::where('city_id', $city->id)->where('availability_status', 'online')->count();
                $cityTotalCleaners = max($city->cleaners_count, 1);
                $onlinePercent = ($onlineInCity / $cityTotalCleaners) * 100;
            @endphp
            <div class="mb-3">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-500">Online Cleaners</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ $onlineInCity }}/{{ $city->cleaners_count }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $onlinePercent }}%"></div>
                </div>
            </div>

            <!-- Action -->
            <a href="/admin/cities/{{ $city->id }}" 
               class="block text-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-bold hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                <i class="fas fa-chart-bar mr-1"></i> View Details
            </a>
        </div>
        @endforeach
    </div>

    <!-- ============================================ -->
    <!-- CITY DISTRIBUTION MAP -->
    <!-- ============================================ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 mt-6">
        <h3 class="font-bold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-map text-red-500 mr-2"></i> Cleaner Distribution by City
        </h3>
        <div class="space-y-3">
            @foreach($cities->take(10) as $city)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $city->name }}</span>
                    <span class="text-gray-500">{{ $city->cleaners_count }} cleaners</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                    @php $maxCleaners = $cities->max('cleaners_count') ?: 1; @endphp
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full" 
                         style="width: {{ ($city->cleaners_count / $maxCleaners) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize statuses for all cities
    let statuses = {};
    @foreach($cities as $city)
    statuses[{{ $city->id }}] = {{ $city->is_active ? 'true' : 'false' }};
    @endforeach

    async function toggleCityStatus(cityId) {
        try {
            const res = await fetch(`/admin/cities/${cityId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                statuses[cityId] = data.is_active;
                window.showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (e) {
            window.showToast('Failed to toggle status', 'error');
        }
    }
</script>
@endpush
@endsection