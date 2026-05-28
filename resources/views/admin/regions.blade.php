@extends('layouts.app')

@section('title', 'Region Management')
@section('user_role', 'Administrator')
@section('page_title', 'Region Management')
@section('page_subtitle', 'Control where services are available')

@section('content')
<div x-data="regionManager()">
    @php
        $regions = App\Models\Region::orderBy('name')->get();
        $stats = [
            'total' => $regions->count(),
            'active' => $regions->where('is_active', true)->count(),
            'allow_registration' => $regions->where('allow_registration', true)->count(),
            'blocked' => $regions->where('allow_registration', false)->count(),
        ];
        
        $activePercent = $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100) : 0;
        $openPercent = $stats['total'] > 0 ? round(($stats['allow_registration'] / $stats['total']) * 100) : 0;
    @endphp

    {{-- ============================================ --}}
    {{-- STATS CARDS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
        {{-- Total Regions --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-globe-africa text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $stats['total'] }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Total Regions</p>
        </div>

        {{-- Active Regions --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 relative group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></span>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $stats['active'] }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Active</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-green-500 h-full rounded-full" style="width: {{ $activePercent }}%"></div>
            </div>
        </div>

        {{-- Open for Registration --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-door-open text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $stats['allow_registration'] }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Open for Registration</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-purple-500 h-full rounded-full" style="width: {{ $openPercent }}%"></div>
            </div>
        </div>

        {{-- Blocked --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-red-100 to-rose-200 dark:from-red-900/40 dark:to-rose-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-lock text-red-600 dark:text-red-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $stats['blocked'] }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Blocked</p>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- QUICK SUMMARY BAR --}}
    {{-- ============================================ --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <span class="text-sm font-semibold text-heading">{{ $stats['active'] }} Active Regions</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-door-open text-purple-500 text-sm"></i>
                <span class="text-sm font-semibold text-heading">{{ $stats['allow_registration'] }} Open for Registration</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-lock text-red-500 text-sm"></i>
                <span class="text-sm font-semibold text-heading">{{ $stats['blocked'] }} Blocked</span>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- REGIONS TABLE --}}
    {{-- ============================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Region</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Code</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Registration</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($regions as $region)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        {{-- Region Name --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-md
                                    {{ $region->is_active ? 'bg-linear-to-br from-blue-400 to-purple-500' : 'bg-linear-to-br from-gray-400 to-gray-600' }}">
                                    {{ strtoupper(substr($region->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $region->name }}
                                    </p>
                                    @if($region->description)
                                    <p class="text-xs text-muted">{{ Str::limit($region->description, 50) }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        {{-- Code --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-mono font-bold text-heading">
                                {{ $region->code }}
                            </span>
                        </td>
                        
                        {{-- Status Badge --}}
                        <td class="px-5 py-4 text-center">
                            @if($region->is_active)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/20">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span> Inactive
                            </span>
                            @endif
                        </td>
                        
                        {{-- Registration Status --}}
                        <td class="px-5 py-4 text-center">
                            @if($region->allow_registration)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20">
                                <i class="fas fa-door-open mr-1.5 text-[10px]"></i> Open
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-500/20">
                                <i class="fas fa-lock mr-1.5 text-[10px]"></i> Blocked
                            </span>
                            @endif
                        </td>
                        
                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <button @click="toggleRegistration({{ $region->id }})" 
                                    class="inline-flex items-center px-4 py-2.5 rounded-xl font-bold text-xs transition-all duration-300 shadow-md hover:scale-105
                                           {{ $region->allow_registration ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-red-500/25 hover:shadow-red-500/40' : 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-green-500/25 hover:shadow-green-500/40' }}">
                                <i class="fas {{ $region->allow_registration ? 'fa-lock' : 'fa-door-open' }} mr-1.5"></i>
                                {{ $region->allow_registration ? 'Block' : 'Allow' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-20 text-center">
                            <div class="w-20 h-20 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-globe text-gray-400 dark:text-gray-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-heading mb-2">No Regions Found</h3>
                            <p class="text-muted text-sm">No regions have been added to the system yet</p>
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
    function regionManager() {
        return {
            toast: { show: false, message: '', type: 'success' },

            async toggleRegistration(id) {
                try {
                    const res = await fetch(`/admin/regions/${id}/toggle-registration`, {
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
                        this.showToast(data.message || 'Failed to update', 'error');
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
