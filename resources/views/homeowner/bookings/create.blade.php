@extends('layouts.app')

@section('title', 'Book a Cleaner')
@section('user_role', 'Homeowner')
@section('page_title', 'Book a Cleaner')
@section('page_subtitle', 'Select service and get AI-matched cleaners')

@section('content')
<div x-data="bookingWizard()" x-init="init()">
    
    @php
        $homeowner = Auth::user()->homeowner;
        $savedLat = $homeowner->latitude ?? -6.7924;
        $savedLng = $homeowner->longitude ?? 39.2083;
        $savedAddress = $homeowner->street ?? '';
        $savedDistrict = $homeowner->ward ?? '';
        $savedCityId = $homeowner->city_id ?? '';
        $allServices = \App\Models\Service::where('is_active', true)->orderBy('base_price')->get();
        $allCities = \App\Models\City::where('is_active', true)->get();
    @endphp

    <!-- Steps -->
    <div class="flex items-center justify-center mb-8">
        <template x-for="(label, i) in ['Service', 'Location', 'AI Match', 'Confirm']" :key="i">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                     :class="step > i ? 'bg-green-500 text-white' : (step === i + 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-500')">
                    <span x-show="step > i"><i class="fas fa-check"></i></span>
                    <span x-show="step <= i" x-text="i + 1"></span>
                </div>
                <span class="ml-2 text-sm hidden sm:block text-gray-600 dark:text-gray-300" x-text="label"></span>
                <div x-show="i < 3" class="w-8 sm:w-16 h-1 mx-2 rounded" :class="step > i + 1 ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
            </div>
        </template>
    </div>

    <!-- STEP 1: Select Service -->
    <div x-show="step === 1" class="animate-slide-up">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
                <i class="fas fa-tools text-blue-500 mr-2"></i> Select Cleaning Service
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Choose a service - price affects which cleaners AI recommends</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach($allServices as $service)
                <div @click="selectService({{ $service->id }}, '{{ $service->name }}', {{ $service->base_price }}, {{ $service->estimated_duration_minutes }})"
                     class="cursor-pointer rounded-2xl p-6 border-2 transition-all hover:shadow-lg"
                     :class="form.service_id === {{ $service->id }} ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-lg' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300'">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
                                 :class="form.service_id === {{ $service->id }} ? 'bg-blue-500' : 'bg-gray-400'">
                                <i class="fas fa-broom"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">{{ $service->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $service->estimated_duration_minutes }} min</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-baseline justify-between mt-3">
                        <div>
                            <p class="text-2xl font-extrabold text-blue-600">TZS {{ number_format($service->base_price) }}</p>
                            <p class="text-xs text-gray-500">Base price</p>
                        </div>
                        @if($service->instant_booking_premium > 0)
                        <div class="text-right">
                            <p class="text-sm text-orange-600">+TZS {{ number_format($service->instant_booking_premium) }}</p>
                            <p class="text-xs text-orange-400">urgent fee</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <button @click="step = 2" :disabled="!form.service_id"
                    class="w-full px-6 py-4 bg-blue-500 hover:bg-blue-600 text-white rounded-2xl font-bold text-lg disabled:opacity-50 transition">
                Continue <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    <!-- STEP 2: Location (Auto-filled from registration) -->
    <div x-show="step === 2" class="animate-slide-up">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Service Location
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your registered address is pre-filled. Adjust if needed.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Street Address *</label>
                    <input type="text" x-model="form.address" placeholder="Your street address"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">District/Ward</label>
                        <input type="text" x-model="form.district" placeholder="e.g., Kinondoni"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Street Name</label>
                        <input type="text" x-model="form.street" placeholder="e.g., Mwai Kibaki Road"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Booking Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div @click="form.booking_type = 'instant'"
                             class="cursor-pointer rounded-2xl p-4 border-2 transition-all text-center"
                             :class="form.booking_type === 'instant' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-200 dark:border-gray-600'">
                            <i class="fas fa-bolt text-orange-500 text-2xl mb-2"></i>
                            <h4 class="font-bold text-gray-800 dark:text-white">Instant</h4>
                            <p class="text-xs text-gray-500">Get a cleaner now</p>
                        </div>
                        <div @click="form.booking_type = 'scheduled'"
                             class="cursor-pointer rounded-2xl p-4 border-2 transition-all text-center"
                             :class="form.booking_type === 'scheduled' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600'">
                            <i class="fas fa-calendar text-blue-500 text-2xl mb-2"></i>
                            <h4 class="font-bold text-gray-800 dark:text-white">Schedule</h4>
                            <p class="text-xs text-gray-500">Book for later</p>
                        </div>
                    </div>
                </div>

                <div x-show="form.booking_type === 'scheduled'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date & Time</label>
                    <input type="datetime-local" x-model="form.scheduled_at" 
                           :min="new Date().toISOString().slice(0, 16)"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Special Instructions (Optional)</label>
                    <textarea x-model="form.special_instructions" rows="2" placeholder="e.g., Gate code, parking info..."
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <div class="flex space-x-4">
                    <button @click="step = 1" class="px-6 py-4 border-2 border-gray-200 dark:border-gray-600 rounded-2xl font-medium text-gray-600 dark:text-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </button>
                    <button @click="getRecommendations()" :disabled="!form.address"
                            class="flex-1 px-6 py-4 bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl disabled:opacity-50 transition">
                        <span x-show="!loading"><i class="fas fa-brain mr-2"></i> Get AI Recommendations</span>
                        <span x-show="loading"><i class="fas fa-spinner fa-spin mr-2"></i> AI is analyzing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 3: AI Recommendations with Price Comparison -->
    <div x-show="step === 3" class="animate-slide-up">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-robot text-purple-500 mr-2"></i> AI Cleaner Recommendations
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cleaners ranked by AI score considering rating, distance, price, and experience</p>
            </div>

            <!-- Loading -->
            <div x-show="loading" class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-purple-500 text-4xl mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">AI is matching you with the best cleaners...</p>
                <p class="text-xs text-gray-400 mt-1">Analyzing 24 features including service price, distance, and ratings</p>
            </div>

            <!-- Recommendations -->
            <div x-show="!loading && recommendations.length > 0" class="space-y-4">
                <template x-for="(rec, index) in recommendations" :key="rec.cleaner_id">
                    <div @click="selectCleaner(rec)" 
                         class="cursor-pointer rounded-2xl p-6 border-2 transition-all hover:shadow-xl"
                         :class="selectedCleaner?.cleaner_id === rec.cleaner_id ? 'border-green-500 bg-green-50 dark:bg-green-900/20 shadow-lg' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300'">
                        
                        <!-- Rank & Name -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg"
                                     :class="index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-blue-500'">
                                    <span x-text="index + 1"></span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-white" x-text="rec.cleaner_name"></h3>
                                    <div class="flex items-center space-x-2 text-sm">
                                        <span class="text-yellow-500"><i class="fas fa-star"></i> <span x-text="rec.rating"></span></span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-gray-500" x-text="rec.completed_jobs + ' jobs'"></span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-green-600" x-text="rec.completion_rate + '%'"></span>
                                    </div>
                                </div>
                            </div>
                            <!-- AI Score -->
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                                     :class="rec.ai_score >= 80 ? 'border-green-500 text-green-600' : (rec.ai_score >= 60 ? 'border-blue-500 text-blue-600' : 'border-yellow-500 text-yellow-600')">
                                    <span class="text-lg font-extrabold" x-text="rec.ai_score"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">AI Score</p>
                            </div>
                        </div>

                        <!-- Comparison Grid -->
                        <div class="grid grid-cols-4 gap-3 mb-3">
                            <div class="text-center bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-xs text-gray-500"><i class="fas fa-road"></i> Distance</p>
                                <p class="font-bold text-blue-600" x-text="rec.distance_km + ' km'"></p>
                            </div>
                            <div class="text-center bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-xs text-gray-500"><i class="fas fa-clock"></i> ETA</p>
                                <p class="font-bold text-green-600" x-text="rec.eta_minutes + ' min'"></p>
                            </div>
                            <div class="text-center bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-xs text-gray-500"><i class="fas fa-tag"></i> Price</p>
                                <p class="font-bold text-purple-600" x-text="'TZS ' + formatNumber(rec.service_price || form.service_price)"></p>
                            </div>
                            <div class="text-center bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-xs text-gray-500"><i class="fas fa-chart-line"></i> Exp.</p>
                                <p class="font-bold text-orange-600" x-text="rec.experience_days + ' days'"></p>
                            </div>
                        </div>

                        <!-- Price Comparison Bar -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Platform Base</span>
                                <span class="text-gray-500">This Cleaner</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-bold text-gray-600" x-text="'TZS ' + formatNumber(form.service_price)"></span>
                                <div class="flex-1 h-2 bg-gray-200 rounded-full relative">
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-red-400 rounded-full" 
                                         :style="'width: ' + Math.min(100, ((rec.service_price || form.service_price) / form.service_price) * 100) + '%'"></div>
                                </div>
                                <span class="text-sm font-bold text-purple-600" x-text="'TZS ' + formatNumber(rec.service_price || form.service_price)"></span>
                            </div>
                        </div>

                        <!-- Selected Badge -->
                        <div x-show="selectedCleaner?.cleaner_id === rec.cleaner_id" class="mt-3 text-center">
                            <span class="text-green-600 dark:text-green-400 font-bold">
                                <i class="fas fa-check-circle mr-1"></i> Selected - Click Confirm to book
                            </span>
                        </div>
                    </div>
                </template>

                <!-- Confirm Button -->
                <div class="pt-4">
                    <button @click="confirmBooking()" :disabled="!selectedCleaner || submitting"
                            class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl disabled:opacity-50 transition">
                        <span x-show="!submitting">
                            <i class="fas fa-check-circle mr-2"></i> 
                            Confirm Booking with <span x-text="selectedCleaner?.cleaner_name || '...'"></span>
                            - TZS <span x-text="formatNumber(selectedCleaner?.service_price || form.service_price)"></span>
                        </span>
                        <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i> Processing...</span>
                    </button>
                    <button @click="step = 2" class="w-full mt-2 px-6 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-2xl font-medium text-gray-600 dark:text-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Location
                    </button>
                </div>
            </div>

            <!-- No results -->
            <div x-show="!loading && recommendations.length === 0" class="text-center py-12">
                <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300">No Cleaners Available</h3>
                <p class="text-gray-500 mt-2">Try a different time or expand your search area</p>
                <button @click="step = 2" class="mt-4 px-6 py-2 bg-blue-500 text-white rounded-xl">Adjust Location</button>
            </div>
        </div>
    </div>

    <!-- STEP 4: Confirmation -->
    <div x-show="step === 4" class="animate-slide-up">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
            <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Booking Confirmed!</h3>
            <p class="text-gray-500 mt-1" x-text="'Booking #' + bookingNumber"></p>
            
            <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-6 mt-6 text-left space-y-3">
                <div class="flex justify-between"><span class="text-gray-500">Service</span><span class="font-bold" x-text="form.service_name"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Cleaner</span><span class="font-bold text-blue-600" x-text="selectedCleaner?.cleaner_name"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Price</span><span class="font-bold text-green-600" x-text="'TZS ' + formatNumber(selectedCleaner?.service_price || form.service_price)"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">ETA</span><span class="font-bold" x-text="selectedCleaner?.eta_minutes + ' minutes'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Distance</span><span class="font-bold" x-text="selectedCleaner?.distance_km + ' km'"></span></div>
            </div>
            
            <a href="/homeowner/dashboard" class="inline-block mt-6 px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold transition">
                <i class="fas fa-home mr-2"></i> Go to Dashboard
            </a>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function bookingWizard() {
        return {
            step: 1,
            loading: false,
            submitting: false,
            recommendations: [],
            selectedCleaner: null,
            bookingNumber: '',
            
            form: {
                service_id: '',
                service_name: '',
                service_price: 0,
                service_duration: 0,
                city_id: '{{ $savedCityId }}',
                booking_type: 'instant',
                scheduled_at: '',
                address: '{{ $savedAddress }}',
                district: '{{ $savedDistrict }}',
                ward: '{{ $savedDistrict }}',
                street: '{{ $savedAddress }}',
                latitude: {{ $savedLat }},
                longitude: {{ $savedLng }},
                special_instructions: '',
            },

            init() {
                // Location is pre-filled from homeowner's registration data
            },

            selectService(id, name, price, duration) {
                this.form.service_id = id;
                this.form.service_name = name;
                this.form.service_price = price;
                this.form.service_duration = duration;
            },

           async getRecommendations() {
    this.loading = true;
    this.step = 3;
    this.selectedCleaner = null;

    try {
        const res = await fetch('/homeowner/recommendations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                service_id: this.form.service_id,
                city_id: this.form.city_id || null,
                latitude: this.form.latitude,
                longitude: this.form.longitude,
                booking_type: this.form.booking_type,
            })
        });
        
        const data = await res.json();
        this.recommendations = data.recommendations || [];
    } catch (e) {
        console.error('Error:', e);
    } finally {
        this.loading = false;
    }
},

            selectCleaner(rec) {
                this.selectedCleaner = rec;
            },

            async confirmBooking() {
                if (!this.selectedCleaner) return;
                this.submitting = true;

                try {
                    const payload = {
                        ...this.form,
                        cleaner_id: this.selectedCleaner.cleaner_id,
                        distance_km: this.selectedCleaner.distance_km,
                        eta_minutes: this.selectedCleaner.eta_minutes,
                        ai_score: this.selectedCleaner.ai_score,
                        ai_rank: this.recommendations.indexOf(this.selectedCleaner) + 1,
                        cleaner_price: this.selectedCleaner.service_price || this.form.service_price,
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
                        this.bookingNumber = data.booking.booking_number;
                        this.step = 4;
                    } else {
                        alert(data.message || 'Booking failed');
                    }
                } catch (e) {
                    alert('An error occurred. Please try again.');
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