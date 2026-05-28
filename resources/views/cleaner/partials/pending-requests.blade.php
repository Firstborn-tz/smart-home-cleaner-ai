<div x-data="pendingRequests()" x-init="fetchRequests()" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900/40 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-orange-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-heading">Pending Requests</h3>
                    <p class="text-xs text-muted">Respond within <span class="font-semibold text-orange-500">2 minutes</span> for instant bookings</p>
                </div>
            </div>
            <span class="bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 px-3 py-1 rounded-full text-sm font-bold" x-text="requests.length + ' pending'"></span>
        </div>
    </div>

    <div class="p-4">
        {{-- Loading --}}
        <div x-show="loading" class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-orange-500 text-2xl"></i>
        </div>

        {{-- Empty --}}
        <div x-show="!loading && requests.length === 0" class="text-center py-8">
            <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
            <p class="text-muted text-sm">No pending requests</p>
        </div>

        {{-- Request Cards --}}
        <div x-show="!loading && requests.length > 0" class="space-y-3">
            <template x-for="req in requests" :key="req.id">
                <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 hover:border-orange-300 dark:hover:border-orange-500/30 transition-all"
                     :class="req.time_left_seconds < 30 ? 'border-red-300 dark:border-red-500/30 bg-red-50 dark:bg-red-500/5' : ''">
                    
                    {{-- Timer Bar --}}
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mb-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-1000"
                             :class="req.time_left_seconds < 30 ? 'bg-red-500' : req.time_left_seconds < 60 ? 'bg-orange-500' : 'bg-green-500'"
                             :style="'width: ' + Math.min(100, (req.time_left_seconds / (req.timeout_seconds || 120)) * 100) + '%'"></div>
                    </div>

                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-bold text-heading text-sm" x-text="req.service_name"></h4>
                            <p class="text-xs text-muted" x-text="'Homeowner: ' + req.homeowner_name"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-black text-red-500" x-show="req.time_left_seconds > 0">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                <span x-text="Math.ceil(req.time_left_seconds / 60) + 'm ' + (req.time_left_seconds % 60) + 's'"></span>
                            </span>
                            <span class="text-sm font-black text-red-500" x-show="req.time_left_seconds <= 0">
                                <i class="fas fa-exclamation-circle mr-1"></i> Expired
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                            <span class="text-muted">Address:</span>
                            <span class="font-semibold text-heading" x-text="req.address || 'N/A'"></span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                            <span class="text-muted">Distance:</span>
                            <span class="font-semibold text-blue-600" x-text="req.distance_km ? req.distance_km + ' km' : 'N/A'"></span>
                        </div>
                    </div>

                    {{-- Pricing --}}
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-500/5 dark:to-emerald-500/5 rounded-lg p-3 mb-3 border border-green-100 dark:border-green-500/10">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted">
                                <span x-text="req.pricing_model === 'fixed' ? 'Fixed Block' : 'Pay As You Go'"></span>
                            </span>
                            <span class="text-lg font-black text-green-600">
                                TZS <span x-text="req.pricing_model === 'fixed' ? formatNumber(req.hourly_rate * (req.booked_hours || 1)) : formatNumber(req.hourly_rate) + '/hr'"></span>
                            </span>
                        </div>
                        <p x-show="req.pricing_model === 'fixed'" class="text-xs text-muted mt-1">
                            <span x-text="req.booked_hours + ' hour(s)'"></span> • Extra: TZS <span x-text="formatNumber(req.hourly_rate) + '/hr'"></span>
                        </p>
                        <p x-show="req.pricing_model === 'payg'" class="text-xs text-muted mt-1">Billed per 30 min</p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-2">
                        <button @click="acceptRequest(req.id)"
                                :disabled="req.time_left_seconds <= 0"
                                class="flex-1 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-sm hover:scale-[1.01] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-1.5"></i> Accept
                        </button>
                        <button @click="declineRequest(req.id)"
                                class="flex-1 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-bold text-sm hover:scale-[1.01] transition-all">
                            <i class="fas fa-times mr-1.5"></i> Decline
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function pendingRequests() {
        return {
            requests: [],
            loading: true,
            interval: null,

            async fetchRequests() {
                try {
                    const res = await fetch('/cleaner/requests/pending');
                    const data = await res.json();
                    this.requests = data.requests || [];
                } catch (e) {
                    console.error('Error fetching requests:', e);
                } finally {
                    this.loading = false;
                }
            },

            async acceptRequest(id) {
                try {
                    const res = await fetch(`/cleaner/bookings/${id}/accept`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.requests = this.requests.filter(r => r.id !== id);
                        window.showToast?.('Booking accepted!', 'success');
                    } else {
                        window.showToast?.(data.message || 'Failed to accept', 'error');
                    }
                } catch (e) {
                    window.showToast?.('Network error', 'error');
                }
            },

            async declineRequest(id) {
                const reason = prompt('Reason for declining (optional):');
                try {
                    const res = await fetch(`/cleaner/bookings/${id}/decline`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reason: reason || 'Declined' })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.requests = this.requests.filter(r => r.id !== id);
                        window.showToast?.('Request declined', 'success');
                    } else {
                        window.showToast?.(data.message || 'Failed to decline', 'error');
                    }
                } catch (e) {
                    window.showToast?.('Network error', 'error');
                }
            },

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num || 0);
            },

            init() {
                this.fetchRequests();
                // Refresh every 10 seconds
                this.interval = setInterval(() => this.fetchRequests(), 10000);
            },

            destroy() {
                clearInterval(this.interval);
            }
        };
    }
</script>