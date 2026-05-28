@extends('layouts.homeowner')

@section('title', 'Track Service #' . $booking->booking_number)
@section('page_title', 'Service Tracking')
@section('page_subtitle', 'Booking #' . $booking->booking_number)

@section('content')
<div x-data="serviceTracker()" x-init="init()">
    @php 
        $currentStatus = $booking->status;
        $steps = ['cleaner_assigned' => 'Assigned', 'cleaner_accepted' => 'Accepted', 'cleaner_arrived' => 'Arrived', 'in_progress' => 'Started', 'completed' => 'Done'];
        $keys = array_keys($steps); 
        $currentIdx = array_search($currentStatus, $keys);
        if ($currentIdx === false) $currentIdx = 0;
    @endphp

    {{-- STATUS PROGRESS --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center">
            @foreach($steps as $key => $label)
                @php $i = array_search($key, $keys); @endphp
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300
                        {{ $i < $currentIdx ? 'bg-gradient-to-br from-green-400 to-emerald-600 text-white shadow-lg' : 
                           ($i === $currentIdx ? 'bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg' : 
                           'bg-gray-100 dark:bg-gray-700 text-muted') }}">
                        @if($i < $currentIdx) <i class="fas fa-check"></i> @elseif($i === $currentIdx && $currentStatus === 'in_progress') <i class="fas fa-spinner animate-spin"></i> @else {{ $i + 1 }} @endif
                    </div>
                    <span class="text-[10px] mt-1.5 font-semibold {{ $i <= $currentIdx ? 'text-heading' : 'text-muted' }}">{{ $label }}</span>
                </div>
                @if($i < count($steps) - 1)
                    <div class="flex-1 h-1.5 mx-1 rounded-full {{ $i < $currentIdx ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
        
        {{-- LEFT: MAIN CONTENT --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Booking Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 rounded-xl flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-heading text-lg">{{ $booking->service->name ?? 'Service' }}</h3>
                                <span class="text-xs font-mono text-muted">#{{ $booking->booking_number }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold
                            {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-700' : 
                               ($currentStatus === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $currentStatus)) }}
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    {{-- Address --}}
                    <p class="text-sm text-body mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-red-500"></i> {{ $booking->service_address }}
                    </p>
                    
                    {{-- Pricing Model Badge --}}
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold
                            {{ $booking->pricing_model === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            <i class="fas {{ $booking->pricing_model === 'fixed' ? 'fa-clock' : 'fa-stopwatch' }} mr-1.5"></i>
                            {{ $booking->pricing_model === 'fixed' ? 'Fixed Block (' . ($booking->booked_hours ?? '?') . ' hrs)' : 'Pay As You Go (30 min billing)' }}
                        </span>
                    </div>

                    {{-- Billing Details --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                            <p class="text-[10px] text-muted uppercase">Rate</p>
                            <p class="text-lg font-black text-heading">TZS {{ number_format($booking->hourly_rate ?? 0) }}/hr</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                            <p class="text-[10px] text-muted uppercase">Distance</p>
                            <p class="text-lg font-black text-blue-600">{{ round($booking->distance_km ?? 0, 1) }} km</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                            <p class="text-[10px] text-muted uppercase">ETA</p>
                            <p class="text-lg font-black text-purple-600">{{ round($booking->estimated_travel_time_minutes ?? 0) }} min</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                            <p class="text-[10px] text-muted uppercase">Est. Total</p>
                            <p class="text-lg font-black text-green-600">
                                TZS {{ number_format($booking->pricing_model === 'fixed' ? ($booking->booked_hours * $booking->hourly_rate) : ($booking->hourly_rate ?? 0)) }}
                            </p>
                        </div>
                    </div>

                    {{-- Completed: Show Final Billing --}}
                    @if($currentStatus === 'completed' && $booking->final_amount)
                    <div class="mt-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-500/5 dark:to-emerald-500/5 rounded-xl p-4 border border-green-100 dark:border-green-500/10">
                        <h4 class="font-bold text-heading text-sm mb-3"><i class="fas fa-receipt text-green-500 mr-2"></i>Final Billing</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted">Actual Hours</span>
                                <span class="font-bold text-heading">{{ $booking->actual_hours ?? '?' }} hrs</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted">Billed Hours</span>
                                <span class="font-bold text-heading">{{ $booking->billed_hours ?? '?' }} hrs</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted">Rate</span>
                                <span class="font-bold text-heading">TZS {{ number_format($booking->hourly_rate ?? 0) }}/hr</span>
                            </div>
                            <div class="border-t border-green-200 dark:border-green-500/10 pt-2 flex justify-between">
                                <span class="font-bold text-heading">Total Amount</span>
                                <span class="text-lg font-black text-green-600">TZS {{ number_format($booking->final_amount ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted">Commission ({{ $booking->commission_percentage ?? 15 }}%)</span>
                                <span class="text-red-500">- TZS {{ number_format($booking->commission_amount ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted">Cleaner Receives</span>
                                <span class="text-green-600 font-semibold">TZS {{ number_format($booking->cleaner_payout_amount ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cleaner Profile --}}
            @if($booking->cleaner)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 border-l-4 border-l-blue-500 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-heading text-lg"><i class="fas fa-user-check text-blue-500 mr-2"></i>Your Cleaner</h3>
                        <a href="/cleaner/{{ $booking->cleaner->id }}/profile" target="_blank" class="text-blue-600 text-xs font-bold hover:underline">
                            View Profile <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($booking->cleaner->user->full_name) }}&background=3b82f6&color=fff&size=56&bold=true" 
                             class="w-14 h-14 rounded-2xl ring-2 ring-blue-100 flex-shrink-0">
                        <div>
                            <h4 class="font-black text-heading">{{ $booking->cleaner->user->full_name }}</h4>
                            <div class="flex items-center gap-2 text-xs text-muted mt-0.5">
                                <span class="text-yellow-500"><i class="fas fa-star"></i> {{ number_format($booking->cleaner->rating, 1) }}</span>
                                <span>|</span>
                                <span>{{ $booking->cleaner->total_completed_jobs }} jobs</span>
                                <span>|</span>
                                <span class="text-green-600">{{ number_format($booking->cleaner->completion_rate, 0) }}% completion</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Verify & Start --}}
            @if(in_array($currentStatus, ['cleaner_accepted', 'cleaner_assigned', 'cleaner_arrived']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-yellow-400 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-shield-halved text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">Verify Cleaner Arrival</h3>
                    <p class="text-muted text-sm mt-1 mb-5">Enter the 6-digit code shown by the cleaner</p>
                    
                    <div class="flex justify-center gap-2 sm:gap-3 mb-5">
                        @for($i = 1; $i <= 6; $i++)
                        <input type="text" id="code{{ $i }}" maxlength="1" 
                               oninput="if(this.value) document.getElementById('code{{ $i < 6 ? $i+1 : $i }}')?.focus()" 
                               class="w-11 h-14 sm:w-12 sm:h-16 text-center text-xl font-black rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all">
                        @endfor
                    </div>
                    
                    <button onclick="verifyAndStart()" 
                            class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-sm shadow-xl hover:scale-105 transition-all">
                        <i class="fas fa-check-circle"></i> Verify & Start Service
                    </button>
                    <p id="verifyError" class="text-red-500 text-sm mt-3 hidden"></p>
                </div>
            </div>
            @endif

            {{-- In Progress Actions --}}
            @if($currentStatus === 'in_progress')
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-blue-400 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-broom text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">Service in Progress</h3>
                    <p class="text-muted text-sm mt-1 mb-5">Your cleaner is working. Mark complete when done.</p>
                    <button onclick="markComplete()" 
                            class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-sm shadow-xl hover:scale-105 transition-all">
                        <i class="fas fa-check-circle"></i> Mark as Complete
                    </button>
                </div>
            </div>
            @endif

            {{-- Rating --}}
            @if($currentStatus === 'completed' && !$booking->cleaner_rating_given)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b">
                    <h3 class="font-bold text-heading text-lg"><i class="fas fa-star text-yellow-500 mr-2"></i>Rate Your Cleaner</h3>
                </div>
                <div class="p-6">
                    <div class="flex justify-center gap-2 text-4xl mb-5" id="stars">
                        @for($i = 1; $i <= 5; $i++)
                        <button onclick="setRating({{ $i }})" class="star-btn text-gray-300 dark:text-gray-600 hover:text-yellow-400 transition-all hover:scale-110">
                            <i class="far fa-star"></i>
                        </button>
                        @endfor
                    </div>
                    <textarea id="reviewText" rows="3" 
                              class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm focus:border-yellow-500 outline-none mb-4"
                              placeholder="Share your experience... (optional)"></textarea>
                    <button onclick="submitReview()" 
                            class="w-full px-6 py-3.5 bg-gradient-to-r from-yellow-500 to-amber-600 text-white rounded-2xl font-bold text-sm shadow-lg hover:scale-[1.01] transition-all">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Review
                    </button>
                </div>
            </div>
            @endif

            {{-- Already Rated --}}
            @if($booking->cleaner_rating_given)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-heading">Review Submitted</h3>
                    <div class="flex justify-center gap-1 mt-2 text-yellow-500 text-2xl">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas{{ $i <= $booking->cleaner_rating_given ? '' : '-regular' }} fa-star"></i>
                        @endfor
                    </div>
                    <p class="text-muted text-sm mt-2">Thank you for your feedback!</p>
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-5">
            {{-- Cleaner Mini Card --}}
            @if($booking->cleaner)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden text-center">
                <div class="p-6">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($booking->cleaner->user->full_name) }}&background=6366f1&color=fff&size=72&bold=true" 
                         class="w-18 h-18 rounded-2xl ring-2 ring-indigo-100 shadow-lg mx-auto mb-4">
                    <h4 class="font-bold text-heading">{{ $booking->cleaner->user->full_name }}</h4>
                    <div class="flex items-center justify-center gap-1 mt-1 text-yellow-500">
                        <i class="fas fa-star text-sm"></i>
                        <span class="font-bold text-heading">{{ number_format($booking->cleaner->rating, 1) }}</span>
                    </div>
                    @if($booking->cleaner->user->phone)
                    <a href="tel:{{ $booking->cleaner->user->phone }}" 
                       class="flex items-center justify-center gap-2 w-full mt-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 rounded-xl font-bold text-sm hover:bg-green-100 transition-all">
                        <i class="fas fa-phone"></i> {{ $booking->cleaner->user->phone }}
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Quick Stats --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h4 class="font-bold text-heading"><i class="fas fa-chart-bar text-blue-500 mr-2"></i>Quick Stats</h4>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Service</span><span class="font-bold">{{ $booking->service->name ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Type</span><span class="font-bold capitalize">{{ $booking->booking_type }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Pricing</span><span class="font-bold capitalize">{{ $booking->pricing_model === 'payg' ? 'Pay As You Go' : 'Fixed Block' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Date</span><span class="font-bold">{{ $booking->created_at->format('M d, Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Time</span><span class="font-bold">{{ $booking->created_at->format('h:i A') }}</span></div>
                    @if($booking->grace_window_minutes)
                    <div class="flex justify-between"><span class="text-muted">Grace Window</span><span class="font-bold">{{ $booking->grace_window_minutes }} min</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BOOKING_ID = {{ $booking->id }};
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let rating = 0;

    function setRating(r) { 
        rating = r; 
        document.querySelectorAll('.star-btn').forEach((b, i) => {
            b.innerHTML = i < r ? '<i class="fas fa-star text-yellow-400"></i>' : '<i class="far fa-star text-gray-300 dark:text-gray-600"></i>';
        }); 
    }

    async function verifyAndStart() {
        let code = ''; 
        for (let i = 1; i <= 6; i++) code += document.getElementById('code' + i).value;
        if (code.length !== 6) { 
            document.getElementById('verifyError').textContent = 'Please enter the complete 6-digit code';
            document.getElementById('verifyError').classList.remove('hidden'); 
            return; 
        }
        try {
            const res = await fetch(`/homeowner/bookings/${BOOKING_ID}/verify-start`, {
                method:'POST', 
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body:JSON.stringify({verification_code: code})
            });
            const data = await res.json();
            if (data.success) { 
                window.showToast('Service started! Billing timer is now running.', 'success'); 
                setTimeout(() => location.reload(), 1500); 
            } else { 
                document.getElementById('verifyError').textContent = data.message || 'Invalid code';
                document.getElementById('verifyError').classList.remove('hidden'); 
            }
        } catch (e) { window.showToast('Error verifying code', 'error'); }
    }

    async function markComplete() {
        if (!confirm('Confirm the service is complete? The cleaner will be paid based on time worked.')) return;
        try {
            const res = await fetch(`/homeowner/bookings/${BOOKING_ID}/complete`, {
                method:'POST', 
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
            });
            const data = await res.json();
            if (data.success) { 
                window.showToast('Service completed! Final billing calculated.', 'success'); 
                setTimeout(() => location.reload(), 1500); 
            } else {
                window.showToast(data.message || 'Error completing service', 'error');
            }
        } catch (e) { window.showToast('Error completing service', 'error'); }
    }

    async function submitReview() {
        if (!rating) { window.showToast('Please select a rating', 'error'); return; }
        try {
            const res = await fetch(`/homeowner/bookings/${BOOKING_ID}/review`, {
                method:'POST', 
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body:JSON.stringify({rating, review_text: document.getElementById('reviewText').value})
            });
            const data = await res.json();
            if (data.success) { 
                window.showToast('Thank you for your review!', 'success'); 
                setTimeout(() => location.reload(), 1500); 
            } else {
                window.showToast(data.message || 'Error submitting review', 'error');
            }
        } catch (e) { window.showToast('Error submitting review', 'error'); }
    }
</script>
@endpush