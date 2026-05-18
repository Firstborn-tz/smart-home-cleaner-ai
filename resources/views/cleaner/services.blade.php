@extends('layouts.app')

@section('title', 'My Services & Pricing')
@section('user_role', 'Cleaner')
@section('page_title', 'My Services & Pricing')
@section('page_subtitle', 'Manage services you offer and set your prices')

@section('content')
<div x-data="servicesManager()">
    
    @php
        $cleaner = Auth::user()->cleaner;
        $allServices = \App\Models\Service::where('is_active', true)->get();
        
        $skills = $cleaner->service_skills ?? [];
        if (is_string($skills)) { $skills = json_decode($skills, true) ?? []; }
        
        $customPrices = $cleaner->custom_prices ?? [];
        if (is_string($customPrices)) { $customPrices = json_decode($customPrices, true) ?? []; }
    @endphp

    <!-- Info Banner -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl shadow-xl p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold"><i class="fas fa-tools mr-2"></i> Your Service Portfolio</h3>
                <p class="text-blue-100 mt-1">Select services and set your competitive prices</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-extrabold" x-text="selectedCount + '/' + allServices.length"></p>
                <p class="text-blue-100 text-sm">Services Selected</p>
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        @foreach($allServices as $service)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 transition-all duration-300 border-2"
             :class="isSelected({{ $service->id }}) ? 'border-green-500 shadow-xl' : 'border-gray-200 dark:border-gray-600 opacity-75'">
            
            <!-- Service Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold"
                         :class="isSelected({{ $service->id }}) ? 'bg-gradient-to-br from-green-400 to-emerald-500' : 'bg-gradient-to-br from-gray-300 to-gray-400'">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg text-gray-800 dark:text-white">{{ $service->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $service->estimated_duration_minutes }} min</p>
                    </div>
                </div>
                
                <!-- Toggle Switch -->
                <button @click="toggleService({{ $service->id }})" 
                        class="relative inline-flex items-center h-8 w-16 rounded-full transition-all duration-300 focus:outline-none"
                        :class="isSelected({{ $service->id }}) ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="inline-block w-7 h-7 transform transition-all duration-300 bg-white rounded-full shadow-md"
                          :class="isSelected({{ $service->id }}) ? 'translate-x-8' : 'translate-x-0.5'"></span>
                </button>
            </div>

            <!-- Description -->
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ Str::limit($service->description, 80) }}</p>

            <!-- Base Price Info -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Platform Base Price</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">TZS {{ number_format($service->base_price) }}</span>
                </div>
            </div>

            <!-- Custom Price Setting (Only when selected) -->
            <div x-show="isSelected({{ $service->id }})" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Your Price (TZS)</label>
                    <input type="number" 
                           x-model="prices[{{ $service->id }}]"
                           @input="updatePriceDisplay({{ $service->id }})"
                           min="{{ round($service->base_price * 0.7) }}"
                           max="{{ round($service->base_price * 1.5) }}"
                           step="1000"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <p class="text-xs text-gray-400 mt-1">
                        Range: TZS {{ number_format(round($service->base_price * 0.7)) }} - TZS {{ number_format(round($service->base_price * 1.5)) }}
                    </p>
                </div>

                <!-- Earnings Breakdown -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Your Earning (85%)</span>
                        <span class="font-bold text-green-600">TZS <span x-text="formatNumber(Math.round(prices[{{ $service->id }}] * 0.85))"></span></span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-600 dark:text-gray-400">Platform Fee (15%)</span>
                        <span class="text-gray-500">TZS <span x-text="formatNumber(Math.round(prices[{{ $service->id }}] * 0.15))"></span></span>
                    </div>
                </div>
            </div>

            <!-- Selected Indicator -->
            <div x-show="isSelected({{ $service->id }})" class="mt-3 flex items-center text-green-600 text-sm">
                <i class="fas fa-check-circle mr-1"></i> Service Active
            </div>
        </div>
        @endforeach
    </div>

    <!-- Save Button -->
    <div class="mt-8 text-center">
        <button @click="saveAllChanges()" 
                class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-lg hover:shadow-2xl transition-all transform hover:scale-105">
            <i class="fas fa-save mr-2"></i> Save All Changes
        </button>
        <p class="text-sm text-gray-500 mt-2">Changes are not saved until you click this button</p>
    </div>

    <!-- Earnings Estimate -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h4 class="font-bold text-lg text-gray-800 dark:text-white mb-4">
            <i class="fas fa-calculator text-purple-500 mr-2"></i> Potential Earnings Estimate
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
                <p class="text-sm text-gray-500">Per Job (Average)</p>
                <p class="text-2xl font-extrabold text-blue-600">TZS <span x-text="formatNumber(avgEarning)"></span></p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
                <p class="text-sm text-gray-500">Daily (5 Jobs)</p>
                <p class="text-2xl font-extrabold text-green-600">TZS <span x-text="formatNumber(avgEarning * 5)"></span></p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
                <p class="text-sm text-gray-500">Monthly (25 Days)</p>
                <p class="text-2xl font-extrabold text-purple-600">TZS <span x-text="formatNumber(avgEarning * 5 * 25)"></span></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function servicesManager() {
        return {
            // Initialize from PHP data
            selectedServices: {!! json_encode(array_values($skills)) !!},
            prices: {!! json_encode($customPrices + array_combine($allServices->pluck('id')->toArray(), $allServices->pluck('base_price')->toArray())) !!},
            allServices: {!! json_encode($allServices->pluck('id')->toArray()) !!},

            get selectedCount() {
                return this.selectedServices.length;
            },

            get avgEarning() {
                if (this.selectedServices.length === 0) return 0;
                let total = 0;
                this.selectedServices.forEach(id => {
                    total += (this.prices[id] || 50000) * 0.85;
                });
                return Math.round(total / this.selectedServices.length);
            },

            isSelected(serviceId) {
                return this.selectedServices.includes(serviceId);
            },

            toggleService(serviceId) {
                const index = this.selectedServices.indexOf(serviceId);
                if (index > -1) {
                    this.selectedServices.splice(index, 1);
                } else {
                    this.selectedServices.push(serviceId);
                    // Set default price if not already set
                    if (!this.prices[serviceId]) {
                        this.prices[serviceId] = {{ $service->base_price ?? 50000 }};
                    }
                }
            },

            updatePriceDisplay(serviceId) {
                // Auto-called by x-model, price is already updated
                // This forces reactivity for the earnings display
                this.prices = { ...this.prices };
            },

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num || 0);
            },

            async saveAllChanges() {
                try {
                    // Build prices object for selected services only
                    let selectedPrices = {};
                    this.selectedServices.forEach(id => {
                        selectedPrices[id] = parseInt(this.prices[id]) || 50000;
                    });

                    const res = await fetch('/cleaner/services/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            service_skills: this.selectedServices,
                            custom_prices: selectedPrices
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.showToast('Services and prices saved!', 'success');
                    } else {
                        window.showToast(data.message || 'Failed to save', 'error');
                    }
                } catch (e) {
                    console.error('Save error:', e);
                    window.showToast('Save failed. Please try again.', 'error');
                }
            }
        };
    }
</script>
@endpush
@endsection