@extends('layouts.cleaner')

@section('title', 'My Services & Pricing')
@section('page_title', 'My Services & Pricing')
@section('page_subtitle', 'Set your hourly rate for each service')

@section('content')
<div x-data="servicesManager()">
    @php
        $cleaner = Auth::user()->cleaner;
        $allServices = App\Models\Service::where('is_active', true)
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();
        $commissionRate = App\Models\Setting::get('commission_rate', 15);
        
        $skills = $cleaner->service_skills ?? [];
        if (is_string($skills)) { $skills = json_decode($skills, true) ?? []; }
        
        $customPrices = $cleaner->custom_prices ?? [];
        if (is_string($customPrices)) { $customPrices = json_decode($customPrices, true) ?? []; }
    @endphp

    {{-- INFO BANNER --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-green-500 via-emerald-500 to-teal-600 rounded-3xl shadow-xl shadow-green-500/25 p-6 sm:p-7 mb-6 text-white">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-clock text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold">Set Your Hourly Rates</h3>
                </div>
                <p class="text-green-100 text-sm mt-1">Set price per hour for each service. Homeowners will see your rate when booking.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-center bg-white/15 backdrop-blur rounded-2xl px-5 py-3 border border-white/20">
                    <p class="text-white/70 text-[10px] uppercase tracking-wider font-medium">Platform Fee</p>
                    <p class="text-2xl font-black">{{ $commissionRate }}%</p>
                    <p class="text-white/60 text-[10px]">You keep {{ 100 - $commissionRate }}%</p>
                </div>
                <div class="text-center bg-white/15 backdrop-blur rounded-2xl px-5 py-3 border border-white/20">
                    <p class="text-3xl font-black" x-text="selectedCount"></p>
                    <p class="text-white/60 text-[10px]">Services Active</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SERVICES GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5 mb-8">
        
        @foreach($allServices as $service)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 border-2"
             :class="isSelected({{ $service->id }}) ? 'border-green-500 shadow-green-500/10' : 'border-gray-100 dark:border-gray-700 opacity-70 hover:opacity-90'">
            
            {{-- Card Header --}}
            <div class="p-5 border-b border-gray-100 dark:border-gray-700"
                 :class="isSelected({{ $service->id }}) ? 'bg-gradient-to-r from-green-50 to-transparent dark:from-green-500/5 dark:to-transparent' : ''">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white flex-shrink-0 transition-all duration-300 shadow-md"
                             :class="isSelected({{ $service->id }}) ? 'bg-gradient-to-br from-green-400 to-emerald-600 shadow-green-500/25' : 'bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700'">
                            <i class="fas fa-broom"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-heading text-sm truncate">{{ $service->name }}</h4>
                            <div class="flex items-center gap-2 text-xs text-muted">
                                <span>{{ $service->category->name ?? 'Service' }}</span>
                                <span>&middot;</span>
                                <span><i class="fas fa-clock mr-1"></i>{{ $service->estimated_duration_minutes }} min est.</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Toggle Switch --}}
                    <button @click="toggleService({{ $service->id }})" 
                            class="relative inline-flex items-center h-7 w-[52px] rounded-full transition-all duration-300 focus:outline-none flex-shrink-0"
                            :class="isSelected({{ $service->id }}) ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="sr-only">Toggle service</span>
                        <span class="inline-flex items-center justify-center w-6 h-6 transform transition-all duration-300 bg-white rounded-full shadow-md"
                              :class="isSelected({{ $service->id }}) ? 'translate-x-6' : 'translate-x-1'">
                            <i class="fas text-[10px] transition-all duration-300"
                               :class="isSelected({{ $service->id }}) ? 'fa-check text-green-500' : 'fa-times text-gray-400'"></i>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-5">
                @if($service->description)
                <p class="text-xs text-muted mb-4 leading-relaxed">{{ Str::limit($service->description, 80) }}</p>
                @endif

                {{-- Settings when selected --}}
                <div x-show="isSelected({{ $service->id }})" x-transition class="space-y-4">
                    
                    {{-- Hourly Rate --}}
                    <div>
                        <label class="block text-xs font-bold text-heading mb-2">
                            <i class="fas fa-coins text-yellow-500 mr-1.5"></i> Price Per Hour (TZS) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted text-sm font-bold">TZS</span>
                            <input type="number" 
                                   x-model="prices[{{ $service->id }}]" 
                                   @input="updatePriceDisplay({{ $service->id }})"
                                   min="1000" step="500"
                                   class="w-full pl-14 pr-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-heading text-lg font-bold focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none transition-all"
                                   placeholder="e.g., 15000">
                        </div>
                        <p class="text-xs text-muted mt-1.5 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i> This is your hourly rate. Set competitively to attract bookings.
                        </p>
                    </div>

                    {{-- Pricing Models Available --}}
                    <div class="bg-blue-50 dark:bg-blue-500/5 rounded-xl p-4 border border-blue-100 dark:border-blue-500/10">
                        <p class="text-xs font-bold text-heading mb-2">
                            <i class="fas fa-tag text-blue-500 mr-1.5"></i> Pricing Models Available to Homeowners
                        </p>
                        <div class="space-y-2 text-xs text-muted">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                                <span><strong class="text-heading">Fixed Block:</strong> Homeowner selects hours. Price = hours &times; your hourly rate. Minimum charge applies.</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                                <span><strong class="text-heading">Pay As You Go:</strong> Homeowner pays per 30 minutes of actual work. Billed in half-hour blocks.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Earnings Breakdown --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2.5">
                        <p class="text-xs font-bold text-heading mb-1 flex items-center gap-1.5">
                            <i class="fas fa-calculator text-purple-500"></i> Per Hour Breakdown
                        </p>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted">Your Rate</span>
                            <span class="font-bold text-heading">TZS <span x-text="formatNumber(prices[{{ $service->id }}] || 0)"></span>/hr</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted">Commission ({{ $commissionRate }}%)</span>
                            <span class="font-bold text-red-500">- TZS <span x-text="formatNumber(Math.round((prices[{{ $service->id }}] || 0) * {{ $commissionRate / 100 }}))"></span>/hr</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-2.5 flex justify-between text-sm">
                            <span class="font-bold text-heading">You Earn</span>
                            <span class="font-black text-green-600 text-lg">TZS <span x-text="formatNumber(Math.round((prices[{{ $service->id }}] || 0) * {{ 1 - ($commissionRate / 100) }}))"></span>/hr</span>
                        </div>
                    </div>

                    {{-- Example Earnings --}}
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-500/5 dark:to-emerald-500/5 rounded-xl p-3 border border-green-100 dark:border-green-500/10">
                        <p class="text-xs text-green-700 dark:text-green-300 font-medium">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Example:</strong> 3-hour job = 
                            <strong>TZS <span x-text="formatNumber((prices[{{ $service->id }}] || 0) * 3)"></span></strong> total &rarr; 
                            You earn <strong>TZS <span x-text="formatNumber(Math.round((prices[{{ $service->id }}] || 0) * 3 * {{ 1 - ($commissionRate / 100) }}))"></span></strong>
                        </p>
                    </div>

                    {{-- Active Badge --}}
                    <div class="flex items-center justify-center gap-2 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-500/10 rounded-xl py-2.5 border border-green-100 dark:border-green-500/10">
                        <i class="fas fa-check-circle"></i>
                        <span class="text-sm font-bold">Active — Visible to Customers</span>
                    </div>
                </div>

                {{-- Inactive Badge --}}
                <div x-show="!isSelected({{ $service->id }})" class="flex items-center justify-center gap-2 text-muted bg-gray-50 dark:bg-gray-700/50 rounded-xl py-2.5">
                    <i class="fas fa-circle text-[6px]"></i>
                    <span class="text-sm">Service Inactive</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- SAVE BUTTON --}}
    <div class="text-center mb-8">
        <button @click="saveAllChanges()" 
                class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-base shadow-xl shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105 transition-all duration-300">
            <i class="fas fa-save mr-2"></i> Save All Changes
        </button>
        <p class="text-sm text-muted mt-2 flex items-center justify-center gap-1">
            <i class="fas fa-info-circle text-xs"></i> Changes apply immediately after saving
        </p>
    </div>

    {{-- EARNINGS ESTIMATE --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calculator text-purple-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-heading text-lg">Potential Earnings Estimate</h4>
                    <p class="text-xs text-muted">Based on your active services and hourly rates (after {{ $commissionRate }}% commission)</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-5 text-center">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-hand-holding-usd text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <p class="text-xs text-muted font-medium uppercase tracking-wider mb-1">Per Hour (Avg)</p>
                    <p class="text-2xl font-black text-blue-600 stat-number">TZS <span x-text="formatNumber(avgRate)"></span></p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl p-5 text-center">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-500/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-calendar-day text-green-600 dark:text-green-400"></i>
                    </div>
                    <p class="text-xs text-muted font-medium uppercase tracking-wider mb-1">Per 3-Hour Job</p>
                    <p class="text-2xl font-black text-green-600 stat-number">TZS <span x-text="formatNumber(avgRate * 3)"></span></p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl p-5 text-center">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <p class="text-xs text-muted font-medium uppercase tracking-wider mb-1">Per 5 Jobs Daily</p>
                    <p class="text-2xl font-black text-purple-600 stat-number">TZS <span x-text="formatNumber(avgRate * 3 * 5)"></span></p>
                </div>
            </div>
            
            <div x-show="selectedCount === 0" class="text-center py-8 mt-4">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-tools text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-bold text-heading">No Services Active</h4>
                <p class="text-sm text-muted mt-1">Toggle services ON and set your hourly rate</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function servicesManager() {
        return {
            selectedServices: {!! json_encode(array_values($skills)) !!},
            prices: (function() {
                let p = {!! json_encode($customPrices) !!};
                @foreach($allServices as $service)
                if (!p[{{ $service->id }}]) p[{{ $service->id }}] = 0;
                @endforeach
                return p;
            })(),
            allServices: {!! json_encode($allServices->pluck('id')->toArray()) !!},
            commissionRate: {{ $commissionRate }},

            get selectedCount() { return this.selectedServices.length; },

            get avgRate() {
                if (this.selectedServices.length === 0) return 0;
                let total = 0;
                this.selectedServices.forEach(id => { total += (parseInt(this.prices[id]) || 0); });
                return Math.round(total / this.selectedServices.length);
            },

            isSelected(serviceId) { return this.selectedServices.includes(serviceId); },

            toggleService(serviceId) {
                const index = this.selectedServices.indexOf(serviceId);
                if (index > -1) { this.selectedServices.splice(index, 1); } 
                else { this.selectedServices.push(serviceId); }
                this.prices = { ...this.prices };
            },

            updatePriceDisplay(serviceId) { this.prices = { ...this.prices }; },

            formatNumber(num) { return new Intl.NumberFormat('en-US').format(num || 0); },

            async saveAllChanges() {
                let selectedPrices = {};
                this.selectedServices.forEach(id => { selectedPrices[id] = parseInt(this.prices[id]) || 0; });
                try {
                    const res = await fetch('/cleaner/services/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ service_skills: this.selectedServices, custom_prices: selectedPrices })
                    });
                    const data = await res.json();
                    window.showToast(data.message || (data.success ? 'Saved!' : 'Failed'), data.success ? 'success' : 'error');
                } catch (e) { window.showToast('Save failed. Please try again.', 'error'); }
            }
        };
    }
</script>
@endpush