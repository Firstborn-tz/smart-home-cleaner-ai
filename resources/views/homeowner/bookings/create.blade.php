@extends('layouts.app')

@section('title', 'Book a Cleaner')
@section('page_title', 'Book a Cleaner')
@section('page_subtitle', 'Select service, pricing, and choose your cleaner')

@section('content')
<div x-data="bookingWizard()" x-init="init()">
    @php
        $homeowner = Auth::user()->homeowner;
        $savedLat = $homeowner->latitude ?? -6.7924;
        $savedLng = $homeowner->longitude ?? 39.2083;
        $savedAddress = $homeowner->street ?? '';
        $savedDistrict = $homeowner->ward ?? '';
        $savedCityId = $homeowner->city_id ?? '';
        $allServices = \App\Models\Service::where('is_active', true)->orderBy('sort_order')->get();
    @endphp

    {{-- STEP INDICATOR --}}
    <div class="flex items-center justify-center mb-8">
        <template x-for="(label, i) in ['Service', 'Pricing', 'AI Match']" :key="i">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300"
                     :class="step > i ? 'bg-gradient-to-br from-green-400 to-emerald-600 text-white shadow-lg' : 
                            (step === i + 1 ? 'bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg' : 
                            'bg-gray-100 dark:bg-gray-700 text-muted')">
                    <span x-show="step > i"><i class="fas fa-check"></i></span>
                    <span x-show="step <= i" x-text="i + 1"></span>
                </div>
                <span class="ml-2 text-sm hidden sm:block font-semibold" 
                      :class="step > i ? 'text-green-600' : (step === i + 1 ? 'text-heading' : 'text-muted')" 
                      x-text="label"></span>
                <div x-show="i < 2" class="w-8 sm:w-16 h-1.5 mx-2 rounded-full transition-all duration-300" 
                     :class="step > i + 1 ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
            </div>
        </template>
    </div>

    {{-- STEP 1: SELECT SERVICE --}}
    <div x-show="step === 1" x-transition>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tools text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading text-lg">Select Cleaning Service</h3>
                        <p class="text-xs text-muted">Cleaners set their own prices per service</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @foreach($allServices as $service)
                    <div @click="selectService({{ $service->id }}, '{{ addslashes($service->name) }}')"
                         class="cursor-pointer rounded-2xl p-5 border-2 transition-all duration-300 group"
                         :class="form.service_id === {{ $service->id }} ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 shadow-lg' : 'border-gray-100 dark:border-gray-700 hover:border-blue-300'">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white transition-all duration-300"
                                 :class="form.service_id === {{ $service->id }} ? 'bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg' : 'bg-gradient-to-br from-gray-300 to-gray-400'">
                                <i class="fas fa-broom"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-heading text-sm">{{ $service->name }}</h3>
                                <span class="text-xs text-muted"><i class="fas fa-clock mr-1"></i> Est. {{ $service->estimated_duration_minutes }} min</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button @click="step = 2" :disabled="!form.service_id"
                        class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-base shadow-lg hover:scale-[1.01] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    Continue <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- STEP 2: PRICING MODEL --}}
    <div x-show="step === 2" x-transition style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tag text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-heading text-lg">Choose Pricing & Location</h3>
                        <p class="text-xs text-muted">Service: <span x-text="form.service_name" class="font-semibold"></span></p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                {{-- Pricing Cards --}}
                <div class="grid grid-cols-2 gap-4">
                    <div @click="form.pricing_model = 'fixed'"
                         class="cursor-pointer rounded-2xl p-5 border-2 transition-all duration-300 text-center"
                         :class="form.pricing_model === 'fixed' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 shadow-lg' : 'border-gray-100 dark:border-gray-700 hover:border-blue-300'">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-clock text-blue-500 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-heading">Fixed Block</h4>
                        <p class="text-xs text-muted mt-1">Select hours, pay fixed price. Extra time added at same rate.</p>
                    </div>
                    <div @click="form.pricing_model = 'payg'"
                         class="cursor-pointer rounded-2xl p-5 border-2 transition-all duration-300 text-center"
                         :class="form.pricing_model === 'payg' ? 'border-green-500 bg-green-50 dark:bg-green-500/10 shadow-lg' : 'border-gray-100 dark:border-gray-700 hover:border-green-300'">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-500/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-stopwatch text-green-500 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-heading">Pay As You Go</h4>
                        <p class="text-xs text-muted mt-1">Billed per 30 min. Minimum 30 min. Pay only for time used.</p>
                    </div>
                </div>

                {{-- Hours Selection --}}
                <div x-show="form.pricing_model === 'fixed'" x-transition>
                    <label class="block text-sm font-semibold text-heading mb-3">How many hours do you need?</label>
                    <div class="grid grid-cols-4 gap-3">
                        <template x-for="h in [1, 2, 3, 4, 5, 6, 7, 8]" :key="h">
                            <div @click="form.booked_hours = h"
                                 class="cursor-pointer rounded-xl py-3 text-center border-2 transition-all duration-200 font-bold"
                                 :class="form.booked_hours === h ? 'border-blue-500 bg-blue-50 text-blue-600' : 'border-gray-200 text-muted hover:border-blue-300'">
                                <span x-text="h + ' hr'"></span><span x-show="h > 1">s</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-muted mt-2">Minimum charge applies. Extra time: same hourly rate.</p>
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-sm font-semibold text-heading mb-2">Service Address</label>
                    <input type="text" x-model="form.address" 
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm focus:border-blue-500 outline-none"
                           placeholder="Enter your full address">
                </div>

                {{-- Booking Type --}}
                <div>
                    <label class="block text-sm font-semibold text-heading mb-2">When do you need service?</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div @click="form.booking_type = 'instant'"
                             class="cursor-pointer rounded-2xl p-4 border-2 transition-all text-center"
                             :class="form.booking_type === 'instant' ? 'border-orange-500 bg-orange-50' : 'border-gray-100 hover:border-orange-300'">
                            <i class="fas fa-bolt text-orange-500 text-xl mb-1"></i>
                            <h4 class="font-bold text-heading text-sm">Instant</h4>
                            <p class="text-xs text-muted">2 min response time</p>
                        </div>
                        <div @click="form.booking_type = 'scheduled'"
                             class="cursor-pointer rounded-2xl p-4 border-2 transition-all text-center"
                             :class="form.booking_type === 'scheduled' ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-blue-300'">
                            <i class="fas fa-calendar text-blue-500 text-xl mb-1"></i>
                            <h4 class="font-bold text-heading text-sm">Schedule</h4>
                            <p class="text-xs text-muted">30 min response time</p>
                        </div>
                    </div>
                </div>

                {{-- DateTime Picker --}}
                <div x-show="form.booking_type === 'scheduled'" x-transition>
                    <label class="block text-sm font-semibold text-heading mb-2">Select Date & Time</label>
                    <input type="datetime-local" x-model="form.scheduled_at" 
                           :min="new Date().toISOString().slice(0, 16)"
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:bg-gray-700 text-body text-sm">
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button @click="step = 1" class="px-6 py-3.5 border-2 border-gray-200 text-body rounded-2xl font-semibold text-sm hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left mr-1.5"></i> Back
                    </button>
                    <button @click="getRecommendations()" 
                            :disabled="!form.address || !form.pricing_model || (form.pricing_model === 'fixed' && !form.booked_hours)"
                            class="flex-1 px-6 py-3.5 bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-2xl font-bold text-sm shadow-lg hover:scale-[1.01] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading"><i class="fas fa-brain mr-2"></i> Get AI Recommendations</span>
                        <span x-show="loading"><i class="fas fa-spinner fa-spin mr-2"></i> AI is analyzing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 3: AI RECOMMENDATIONS (TOP 5) --}}
    <div x-show="step === 3" x-transition style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-500/5 dark:to-blue-500/5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-robot text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-heading text-lg">Top 5 AI-Recommended Cleaners</h3>
                            <p class="text-xs text-muted">Ranked by quality score. Select one to send a request.</p>
                        </div>
                    </div>
                    <span class="text-xs bg-white dark:bg-gray-700 px-3 py-1 rounded-full font-semibold shadow-sm" x-text="activeRequests + '/3 requests used'"></span>
                </div>
            </div>
            <div class="p-6">
                {{-- Loading --}}
                <div x-show="loading" class="text-center py-16">
                    <div class="w-20 h-20 bg-purple-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-spinner fa-spin text-purple-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-heading">AI is Analyzing</h3>
                    <p class="text-muted text-sm mt-1">Finding the best cleaners near you...</p>
                </div>

                {{-- Recommendations List --}}
                <div x-show="!loading && recommendations.length > 0" class="space-y-4">
                    <template x-for="(rec, index) in recommendations" :key="rec.cleaner_id">
                        <div class="rounded-2xl p-5 border-2 transition-all duration-300"
                             :class="sentCleanerIds.includes(rec.cleaner_id) ? 'border-green-400 bg-green-50 dark:bg-green-500/5' : 'border-gray-100 dark:border-gray-700 hover:border-blue-300 hover:shadow-md'">
                            
                            {{-- Rank & Name --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-lg"
                                         :class="index === 0 ? 'bg-gradient-to-br from-yellow-400 to-amber-600' : 
                                                index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500' : 
                                                index === 2 ? 'bg-gradient-to-br from-orange-400 to-orange-600' : 
                                                'bg-gradient-to-br from-blue-400 to-blue-600'">
                                        <span x-text="index + 1"></span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-heading text-base" x-text="rec.cleaner_name"></h3>
                                        <div class="flex items-center gap-2 text-xs mt-0.5">
                                            <span class="text-yellow-500 font-semibold"><i class="fas fa-star"></i> <span x-text="rec.rating"></span></span>
                                            <span class="text-gray-300">|</span>
                                            <span class="text-muted"><span x-text="rec.completed_jobs"></span> jobs</span>
                                            <span class="text-gray-300">|</span>
                                            <span class="text-green-600 font-semibold"><span x-text="rec.completion_rate"></span>%</span>
                                        </div>
                                    </div>
                                </div>
                                {{-- AI Score --}}
                                <div class="text-center">
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center border-[3px] shadow-md"
                                         :class="rec.ai_score >= 80 ? 'border-green-500 text-green-600 bg-green-50' : 
                                                (rec.ai_score >= 60 ? 'border-blue-500 text-blue-600 bg-blue-50' : 
                                                'border-yellow-500 text-yellow-600 bg-yellow-50')">
                                        <span class="text-xl font-black" x-text="rec.ai_score"></span>
                                    </div>
                                    <p class="text-[10px] text-muted font-medium mt-1">AI Score</p>
                                </div>
                            </div>

                            {{-- Pricing Highlight --}}
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-500/5 dark:to-indigo-500/5 rounded-xl p-4 mb-3 border border-blue-100 dark:border-blue-500/10">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-muted font-medium uppercase tracking-wider">
                                            <span x-text="form.pricing_model === 'fixed' ? 'Fixed Price (' + form.booked_hours + ' hrs)' : 'Hourly Rate'"></span>
                                        </p>
                                        <p class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                            TZS <span x-text="form.pricing_model === 'fixed' ? formatNumber(rec.total_price) : formatNumber(rec.hourly_rate) + '/hr'"></span>
                                        </p>
                                    </div>
                                    <div class="text-right text-xs text-muted">
                                        <p x-show="form.pricing_model === 'fixed'">Extra: TZS <span x-text="formatNumber(rec.hourly_rate) + '/hr'"></span></p>
                                        <p x-show="form.pricing_model === 'payg'">Billed per 30 min</p>
                                        <p>Min: 0.5 hr</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Stats Grid --}}
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                    <p class="text-[10px] text-muted uppercase tracking-wider"><i class="fas fa-road mr-1"></i>Distance</p>
                                    <p class="text-sm font-bold text-blue-600" x-text="rec.distance_km + ' km'"></p>
                                </div>
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                    <p class="text-[10px] text-muted uppercase tracking-wider"><i class="fas fa-clock mr-1"></i>ETA</p>
                                    <p class="text-sm font-bold text-purple-600" x-text="rec.eta_minutes + ' min'"></p>
                                </div>
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                    <p class="text-[10px] text-muted uppercase tracking-wider"><i class="fas fa-calendar mr-1"></i>Exp.</p>
                                    <p class="text-sm font-bold text-orange-600" x-text="rec.experience_days + ' days'"></p>
                                </div>
                            </div>

                            {{-- Send Request Button --}}
                            <button @click="sendRequest(rec.cleaner_id, rec.hourly_rate, rec.total_price, rec.distance_km, rec.eta_minutes, rec.ai_score)"
                                    :disabled="submitting || activeRequests >= 3 || sentCleanerIds.includes(rec.cleaner_id)"
                                    class="w-full py-3 rounded-xl font-bold text-sm transition-all duration-300 disabled:opacity-60"
                                    :class="sentCleanerIds.includes(rec.cleaner_id) ? 'bg-green-100 text-green-700 border border-green-300' : 
                                            'bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:shadow-lg hover:scale-[1.01]'">
                                <span x-show="!sentCleanerIds.includes(rec.cleaner_id) && !submitting">
                                    <i class="fas fa-paper-plane mr-2"></i> Send Request to This Cleaner
                                </span>
                                <span x-show="submitting && !sentCleanerIds.includes(rec.cleaner_id)">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Sending Request...
                                </span>
                                <span x-show="sentCleanerIds.includes(rec.cleaner_id)">
                                    <i class="fas fa-check-circle mr-2"></i> Request Sent — Waiting for Response
                                </span>
                            </button>
                        </div>
                    </template>

                    {{-- Back Button --}}
                    <button @click="step = 2" class="w-full mt-4 px-6 py-3.5 border-2 border-gray-200 dark:border-gray-600 text-body rounded-2xl font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-arrow-left mr-1.5"></i> Back to Pricing
                    </button>
                </div>

                {{-- No Results --}}
                <div x-show="!loading && recommendations.length === 0" class="text-center py-16">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-heading mb-2">No Cleaners Available</h3>
                    <p class="text-muted text-sm mb-4">No cleaners offer this service with pricing in your area.</p>
                    <button @click="step = 2" class="px-6 py-3 bg-blue-500 text-white rounded-xl font-bold hover:bg-blue-600 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Adjust Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SUCCESS MODAL --}}
    <div x-show="showSuccessModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md m-4 p-8 text-center" @click.stop>
            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-green-500/25">
                <i class="fas fa-check-circle text-white text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-heading mb-2">Request Sent!</h3>
            <p class="text-muted text-sm mb-6">Your request has been sent to <strong x-text="successCleanerName"></strong>. They have <strong x-text="successTimeout"></strong> to respond.</p>
            <div class="flex gap-3">
                <button @click="showSuccessModal = false" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl font-bold text-sm">Close</button>
                <a href="/homeowner/dashboard" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm text-center">View Dashboard</a>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div x-show="toast.show" x-transition
         class="fixed top-6 right-6 z-[9999] px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-sm font-semibold text-white"
         :class="toast.type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'"
         style="display: none;">
        <i class="fas" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bookingWizard() {
        return {
            step: 1, loading: false, submitting: false,
            recommendations: [], selectedCleaner: null,
            activeRequests: {{ $activeRequests ?? 0 }},
            sentCleanerIds: [],
            showSuccessModal: false,
            successCleanerName: '',
            successTimeout: '',
            toast: { show: false, message: '', type: 'success' },
            
            form: {
                service_id: '', service_name: '', service_duration: 0,
                pricing_model: '', booked_hours: null,
                city_id: '{{ $savedCityId }}', booking_type: 'instant', scheduled_at: '',
                address: '{{ $savedAddress }}', district: '{{ $savedDistrict }}',
                street: '{{ $savedAddress }}',
                latitude: {{ $savedLat }}, longitude: {{ $savedLng }},
                special_instructions: '',
            },

            init() {
                this.fetchActiveRequests();
            },

            async fetchActiveRequests() {
                try {
                    const res = await fetch('/homeowner/requests/active');
                    const data = await res.json();
                    this.activeRequests = data.count || 0;
                } catch (e) {}
            },

            selectService(id, name) {
                this.form.service_id = id;
                this.form.service_name = name;
            },

            async getRecommendations() {
                this.loading = true; this.step = 3; this.sentCleanerIds = [];
                try {
                    const payload = {
                        service_id: this.form.service_id,
                        pricing_model: this.form.pricing_model,
                        booked_hours: this.form.booked_hours,
                        city_id: this.form.city_id || null,
                        latitude: this.form.latitude,
                        longitude: this.form.longitude,
                        booking_type: this.form.booking_type
                    };
                    
                    const res = await fetch('/homeowner/recommendations', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    this.recommendations = data.recommendations || [];
                    if (this.recommendations.length === 0) {
                        this.showToast('No cleaners available. Try a different service or pricing model.', 'error');
                    }
                } catch (e) {
                    this.showToast('Failed to get recommendations. Please try again.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async sendRequest(cleanerId, hourlyRate, totalPrice, distanceKm, etaMin, aiScore) {
                if (this.activeRequests >= 3) {
                    this.showToast('Maximum 3 active requests. Cancel one first.', 'error');
                    return;
                }
                if (this.sentCleanerIds.includes(cleanerId)) return;

                this.submitting = true;
                try {
                    const payload = {
                        service_id: this.form.service_id,
                        pricing_model: this.form.pricing_model,
                        booked_hours: this.form.booked_hours,
                        city_id: this.form.city_id || null,
                        booking_type: this.form.booking_type,
                        scheduled_at: this.form.scheduled_at || null,
                        latitude: this.form.latitude,
                        longitude: this.form.longitude,
                        address: this.form.address,
                        district: this.form.district,
                        ward: this.form.district,
                        street: this.form.street,
                        special_instructions: this.form.special_instructions || '',
                        cleaner_id: cleanerId,
                        hourly_rate: hourlyRate,
                        distance_km: distanceKm,
                        eta_minutes: etaMin,
                        ai_score: aiScore,
                        ai_rank: 1,
                    };

                    const res = await fetch('/homeowner/bookings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await res.json();
                    
                    if (data.success) {
                        this.sentCleanerIds.push(cleanerId);
                        this.activeRequests++;
                        
                        // Find cleaner name for success modal
                        const rec = this.recommendations.find(r => r.cleaner_id === cleanerId);
                        this.successCleanerName = rec ? rec.cleaner_name : 'Cleaner';
                        this.successTimeout = this.form.booking_type === 'instant' ? '2 minutes' : '30 minutes';
                        this.showSuccessModal = true;
                        
                        this.fetchActiveRequests();
                    } else {
                        this.showToast(data.message || 'Failed to send request', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                } finally {
                    this.submitting = false;
                }
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 4000);
            },

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num || 0);
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