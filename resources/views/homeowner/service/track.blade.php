@extends('layouts.app')

@section('title', 'Track Service')
@section('user_role', 'Homeowner')
@section('page_title', 'Service Tracking')
@section('page_subtitle', '#' . $booking->booking_number)

@section('content')
<div class="space-y-6">
    @php $currentStatus = $booking->status; @endphp

    <!-- Status Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            @php $steps = ['cleaner_assigned'=>'Assigned','cleaner_accepted'=>'Accepted','in_progress'=>'Started','completed'=>'Done']; @endphp
            @php $keys = array_keys($steps); $currentIdx = array_search($currentStatus, $keys); @endphp
            @foreach($steps as $key => $label)
                @php $i = array_search($key, $keys); @endphp
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $i < $currentIdx ? 'bg-green-500 text-white' : ($i === $currentIdx ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-500') }}">
                        @if($i < $currentIdx) <i class="fas fa-check"></i> @else {{ $i + 1 }} @endif
                    </div>
                    <span class="text-xs mt-1 {{ $i <= $currentIdx ? 'text-gray-800 dark:text-white font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                </div>
                @if($i < count($steps) - 1)
                    <div class="flex-1 h-1 mx-1 rounded {{ $i < $currentIdx ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Booking Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ $booking->service->name ?? 'Service' }}</h3>
        @if($booking->cleaner)
        <div class="flex items-center space-x-4 mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">{{ strtoupper(substr($booking->cleaner->user->first_name ?? 'C', 0, 1)) }}</div>
            <div>
                <p class="font-bold text-gray-800 dark:text-white">{{ $booking->cleaner->user->full_name }}</p>
                <p class="text-yellow-500">⭐ {{ number_format($booking->cleaner->rating, 1) }}</p>
                <a href="tel:{{ $booking->cleaner->user->phone }}" class="text-blue-600 text-sm"><i class="fas fa-phone mr-1"></i> {{ $booking->cleaner->user->phone }}</a>
            </div>
        </div>
        @endif
    </div>

    <!-- Verify & Start -->
    @if($currentStatus === 'cleaner_accepted')
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-2 border-yellow-400 text-center">
        <i class="fas fa-shield-alt text-yellow-500 text-4xl mb-3"></i>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Verify Cleaner Arrival</h3>
        <p class="text-gray-500 mt-2 mb-4">Enter the 6-digit code from the cleaner</p>
        <div class="flex justify-center space-x-2 mb-4">
            @for($i = 1; $i <= 6; $i++)
            <input type="text" id="code{{ $i }}" maxlength="1" oninput="if(this.value) document.getElementById('code{{ $i < 6 ? $i+1 : $i }}')?.focus()" 
                   class="w-10 h-14 text-center text-xl font-bold rounded-lg border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            @endfor
        </div>
        <button onclick="verifyAndStart()" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold transition">
            <i class="fas fa-check-circle mr-2"></i> Verify & Start Service
        </button>
        <p id="verifyError" class="text-red-500 text-sm mt-2 hidden"></p>
    </div>
    @endif

    <!-- In Progress -->
    @if($currentStatus === 'in_progress')
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center border-2 border-blue-400">
        <i class="fas fa-broom text-blue-500 text-4xl mb-3"></i>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Service in Progress</h3>
        <button onclick="markComplete()" class="mt-4 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold transition">
            <i class="fas fa-check-circle mr-2"></i> Mark as Complete
        </button>
    </div>
    @endif

    <!-- Rating -->
    @if($currentStatus === 'completed' && !$booking->cleaner_rating_given)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Rate Your Cleaner</h3>
        <div class="flex space-x-2 text-4xl mb-4" id="stars">
            @for($i = 1; $i <= 5; $i++)
            <button onclick="setRating({{ $i }})" class="star-btn text-gray-300 hover:text-yellow-400"><i class="far fa-star"></i></button>
            @endfor
        </div>
        <textarea id="reviewText" rows="2" placeholder="Share your experience..." class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white mb-4"></textarea>
        <button onclick="submitReview()" class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl font-bold transition">
            <i class="fas fa-paper-plane mr-2"></i> Submit Review
        </button>
    </div>
    @endif
</div>

@push('scripts')
<script>
    const BOOKING_ID = {{ $booking->id }}, CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let rating = 0;

    function setRating(r) { rating = r; document.querySelectorAll('.star-btn').forEach((b,i) => b.innerHTML = i < r ? '<i class="fas fa-star text-yellow-400"></i>' : '<i class="far fa-star text-gray-300"></i>'); }

    async function verifyAndStart() {
        let code = ''; for(let i=1;i<=6;i++) code += document.getElementById('code'+i).value;
        if(code.length !== 6) { document.getElementById('verifyError').classList.remove('hidden'); return; }
        try {
            const res = await fetch(`/homeowner/bookings/${BOOKING_ID}/verify-start`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:JSON.stringify({verification_code:code}) });
            const data = await res.json();
            if(data.success) { window.showToast('Service started!','success'); setTimeout(()=>location.reload(),1500); }
            else { document.getElementById('verifyError').textContent = data.message; document.getElementById('verifyError').classList.remove('hidden'); }
        } catch(e) {}
    }

    async function markComplete() {
        if(!confirm('Confirm?')) return;
        try {
            const res = await fetch(`/homeowner/bookings/${BOOKING_ID}/complete`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'} });
            const data = await res.json();
            if(data.success) { window.showToast('Service completed!','success'); setTimeout(()=>location.reload(),1500); }
        } catch(e) {}
    }

    async function submitReview() {
        if(!rating) { window.showToast('Select a rating','error'); return; }
        try {
            const res = await fetch(`/homeowner/bookings/${BOOKING_ID}/review`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:JSON.stringify({rating, review_text:document.getElementById('reviewText').value}) });
            const data = await res.json();
            if(data.success) { window.showToast('Thank you!','success'); setTimeout(()=>location.reload(),1500); }
        } catch(e) {}
    }
</script>
@endpush
@endsection