@extends('layouts.app')

@section('title', 'City Management')
@section('user_role', 'Administrator')
@section('page_title', 'City Management')
@section('page_subtitle', 'Monitor activity across all cities')

@section('content')
<div x-data="cityManager()">
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
        
        $topCity = $cities->first();
        $mostCleanersCity = $cities->sortByDesc('cleaners_count')->first();
    @endphp

    
    {{-- STATS CARDS --}}
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
        {{-- Total Cities --}}
        <div class="bg-linear-to-br from-blue-500 to-blue-700 rounded-2xl shadow-lg shadow-blue-500/20 p-5 text-white relative overflow-hidden group card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-city text-white text-lg"></i>
                    </div>
                    <span class="text-blue-200 text-xs font-medium bg-white/10 px-3 py-1 rounded-full">
                        {{ $activeCities }} Active
                    </span>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">{{ $cities->count() }}</p>
                <p class="text-blue-200 text-xs font-medium mt-1 uppercase tracking-wider">Total Cities</p>
            </div>
        </div>

        {{-- Total Cleaners --}}
        <div class="bg-linear-to-br from-green-500 to-emerald-700 rounded-2xl shadow-lg shadow-green-500/20 p-5 text-white relative overflow-hidden group card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">{{ $totalCleaners }}</p>
                <p class="text-green-200 text-xs font-medium mt-1 uppercase tracking-wider">Total Cleaners</p>
            </div>
        </div>

        {{-- Total Bookings --}}
        <div class="bg-linear-to-br from-purple-500 to-purple-700 rounded-2xl shadow-lg shadow-purple-500/20 p-5 text-white relative overflow-hidden group card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-calendar-check text-white text-lg"></i>
                    </div>
                    <span class="text-purple-200 text-xs font-medium bg-white/10 px-3 py-1 rounded-full">
                        This Month
                    </span>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">{{ $totalBookings }}</p>
                <p class="text-purple-200 text-xs font-medium mt-1 uppercase tracking-wider">Total Bookings</p>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-linear-to-br from-orange-500 to-rose-600 rounded-2xl shadow-lg shadow-orange-500/20 p-5 text-white relative overflow-hidden group card-hover-lift">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-3xl -mr-4 -mt-4"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-chart-line text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-3xl font-black tracking-tight stat-number">TZS {{ number_format($totalRevenue, 0) }}</p>
                <p class="text-orange-200 text-xs font-medium mt-1 uppercase tracking-wider">Total Revenue</p>
            </div>
        </div>
    </div>

    
    {{-- TOP & MOST CLEANERS CITY --}}
   
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        {{-- Most Active City --}}
        @if($topCity)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 card-hover-lift">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-500/25">
                        <i class="fas fa-trophy text-white"></i>
                    </div>
                    <h3 class="font-bold text-heading text-lg">Most Active City</h3>
                </div>
                <span class="px-3 py-1.5 bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900 rounded-full text-xs font-bold shadow-sm">
                    <i class="fas fa-crown mr-1"></i> #1
                </span>
            </div>
            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                <div class="w-16 h-16 bg-linear-to-br from-blue-400 to-purple-500 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">
                    {{ strtoupper(substr($topCity->code, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h4 class="text-xl font-bold text-heading">{{ $topCity->name }}</h4>
                    <p class="text-sm text-muted">{{ $topCity->region }}</p>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                            <i class="fas fa-users text-xs"></i> {{ $topCity->cleaners_count }} cleaners
                        </span>
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-green-600 dark:text-green-400">
                            <i class="fas fa-calendar-check text-xs"></i> {{ $topCity->bookings_count }} bookings
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Most Cleaners City --}}
        @if($mostCleanersCity)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 card-hover-lift">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/25">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <h3 class="font-bold text-heading text-lg">Most Cleaners</h3>
                </div>
                <span class="px-3 py-1.5 bg-gradient-to-r from-green-400 to-green-500 text-green-900 rounded-full text-xs font-bold shadow-sm">
                    {{ $mostCleanersCity->cleaners_count }} Cleaners
                </span>
            </div>
            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl">
                <div class="w-16 h-16 bg-linear-to-br from-green-400 to-emerald-500 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">
                    {{ strtoupper(substr($mostCleanersCity->code, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h4 class="text-xl font-bold text-heading">{{ $mostCleanersCity->name }}</h4>
                    <p class="text-sm text-muted">{{ $mostCleanersCity->region }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        @php $avgRating = App\Models\Cleaner::where('city_id', $mostCleanersCity->id)->avg('rating') ?? 0; @endphp
                        <div class="flex items-center gap-1 text-yellow-500 text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-300 dark:text-gray-600' }} text-xs"></i>
                            @endfor
                        </div>
                        <span class="text-sm font-semibold text-heading">{{ number_format($avgRating, 1) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    
    {{-- CITIES GRID --}}
   
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading">
                <i class="fas fa-globe-africa text-blue-500 mr-2"></i> All Cities
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted">{{ $cities->count() }} cities total</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            @foreach($cities as $city)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border {{ $city->is_active ? 'border-gray-100 dark:border-gray-700' : 'border-red-300 dark:border-red-700' }} p-5 card-hover-lift group">
                
                {{-- City Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-lg
                            {{ $city->is_active ? 'bg-linear-to-br from-blue-400 to-purple-500' : 'bg-linear-to-br from-gray-400 to-gray-600' }}">
                            {{ strtoupper(substr($city->code, 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-heading text-sm">{{ $city->name }}</h4>
                            <p class="text-xs text-muted">{{ $city->region }}</p>
                        </div>
                    </div>
                    
                    {{-- Toggle Switch --}}
                    <button @click="toggleCityStatus({{ $city->id }})" 
                            class="relative inline-flex items-center h-7 w-[52px] rounded-full transition-all duration-300 focus:outline-none"
                            :class="statuses[{{ $city->id }}] ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="sr-only">Toggle city status</span>
                        <span class="inline-flex items-center justify-center w-6 h-6 transform transition-all duration-300 bg-white rounded-full shadow-md"
                              :class="statuses[{{ $city->id }}] ? 'translate-x-6' : 'translate-x-1'">
                            <i class="fas text-[10px] transition-all duration-300"
                               :class="statuses[{{ $city->id }}] ? 'fa-check text-green-500' : 'fa-times text-gray-400'"></i>
                        </span>
                    </button>
                </div>

                {{-- Stats Mini Grid --}}
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 transition-all">
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400 stat-number">{{ $city->cleaners_count }}</p>
                        <p class="text-[11px] text-muted font-medium">Cleaners</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-green-50 dark:group-hover:bg-green-900/20 transition-all">
                        <p class="text-lg font-bold text-green-600 dark:text-green-400 stat-number">{{ $city->bookings_count }}</p>
                        <p class="text-[11px] text-muted font-medium">Bookings</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-purple-50 dark:group-hover:bg-purple-900/20 transition-all">
                        <p class="text-sm font-bold text-purple-600 dark:text-purple-400 stat-number">TZS {{ number_format($city->bookings_sum_total_amount ?? 0, 0) }}</p>
                        <p class="text-[11px] text-muted font-medium">Revenue</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center group-hover:bg-orange-50 dark:group-hover:bg-orange-900/20 transition-all">
                        <p class="text-sm font-bold text-orange-600 dark:text-orange-400 stat-number">TZS {{ number_format($city->bookings_sum_commission_amount ?? 0, 0) }}</p>
                        <p class="text-[11px] text-muted font-medium">Commission</p>
                    </div>
                </div>

                {{-- Online Cleaners Progress Bar --}}
                @php
                    $onlineInCity = App\Models\Cleaner::where('city_id', $city->id)->where('availability_status', 'online')->count();
                    $cityTotalCleaners = max($city->cleaners_count, 1);
                    $onlinePercent = round(($onlineInCity / $cityTotalCleaners) * 100);
                @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-muted font-medium">Online Cleaners</span>
                        <span class="font-bold {{ $onlinePercent > 50 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                            {{ $onlineInCity }}/{{ $city->cleaners_count }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $onlinePercent > 50 ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gradient-to-r from-orange-400 to-yellow-500' }}" 
                             style="width: {{ $onlinePercent }}%"></div>
                    </div>
                </div>

                {{-- View Details Button --}}
                <a href="/admin/cities/{{ $city->id }}" 
                   class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-semibold hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all duration-300 group/btn">
                    <i class="fas fa-chart-bar text-xs"></i>
                    View Details
                    <i class="fas fa-arrow-right text-xs opacity-0 group-hover/btn:opacity-100 group-hover/btn:translate-x-1 transition-all"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CLEANER DISTRIBUTION CHART --}}
    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-linear-to-br from-red-400 to-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/25">
                <i class="fas fa-map text-white"></i>
            </div>
            <div>
                <h3 class="font-bold text-heading text-lg">Cleaner Distribution by City</h3>
                <p class="text-xs text-muted">Top 10 cities by cleaner count</p>
            </div>
        </div>
        
        <div class="space-y-4">
            @foreach($cities->take(10) as $index => $city)
            @php $maxCleaners = $cities->max('cleaners_count') ?: 1; @endphp
            <div class="group">
                <div class="flex justify-between text-sm mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-muted">
                            {{ $index + 1 }}
                        </span>
                        <span class="font-semibold text-heading">{{ $city->name }}</span>
                    </div>
                    <span class="text-sm font-bold {{ $city->cleaners_count > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-muted' }}">
                        {{ $city->cleaners_count }} <span class="text-xs font-normal text-muted">cleaners</span>
                    </span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 via-blue-500 to-purple-500 transition-all duration-700 group-hover:from-blue-500 group-hover:to-purple-600"
                         style="width: {{ ($city->cleaners_count / $maxCleaners) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($cities->count() > 10)
        <div class="text-center mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-sm text-muted">
                <i class="fas fa-ellipsis-h mr-1"></i> 
                And {{ $cities->count() - 10 }} more cities
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function cityManager() {
        return {
            statuses: {
                @foreach($cities as $city)
                {{ $city->id }}: {{ $city->is_active ? 'true' : 'false' }},
                @endforeach
            },

            async toggleCityStatus(cityId) {
                const button = event.currentTarget;
                
                // Add loading state
                button.classList.add('opacity-50', 'pointer-events-none');
                
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
                        // Update local state
                        this.statuses[cityId] = data.is_active;
                        
                        // Show success toast
                        this.showNotification(data.message, 'success');
                        
                        // Reload after brief delay
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        this.showNotification(data.message || 'Failed to update status', 'error');
                        button.classList.remove('opacity-50', 'pointer-events-none');
                    }
                } catch (e) {
                    this.showNotification('Network error. Please try again.', 'error');
                    button.classList.remove('opacity-50', 'pointer-events-none');
                }
            },

            showNotification(message, type = 'success') {
                // Create toast element
                const toast = document.createElement('div');
                const bgColor = type === 'success' 
                    ? 'bg-gradient-to-r from-green-500 to-emerald-600' 
                    : 'bg-gradient-to-r from-red-500 to-rose-600';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                
                toast.className = `fixed top-6 right-6 z-[9999] ${bgColor} text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 animate-slide-up text-sm font-semibold`;
                toast.innerHTML = `
                    <i class="fas ${icon} text-lg"></i>
                    <span>${message}</span>
                `;
                
                document.body.appendChild(toast);
                
                // Auto remove after 3 seconds
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    toast.style.transition = 'all 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        };
    }
</script>

<style>
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
</style>
@endpush
