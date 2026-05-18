<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cleaner Details - SmartClean AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">

    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-8">
                <a href="/admin/dashboard" class="text-xl font-bold">🛡️ Admin Panel</a>
                <a href="/admin/cleaners" class="text-blue-600 font-bold">← Back to Cleaners</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-red-600 font-medium">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-8">
        
        <!-- Cleaner Profile Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($cleaner->user->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">{{ $cleaner->user->full_name }}</h1>
                        <p class="text-gray-500">{{ $cleaner->user->email }} | {{ $cleaner->user->phone }}</p>
                        <p class="text-sm text-gray-400">Cleaner ID: {{ $cleaner->cleaner_id }}</p>
                        <div class="flex items-center space-x-3 mt-2">
                            <span class="px-3 py-1 rounded-full text-sm
                                @if($cleaner->availability_status === 'online') bg-green-100 text-green-700
                                @elseif($cleaner->availability_status === 'online_busy') bg-yellow-100 text-yellow-700
                                @else bg-gray-100 text-gray-600 @endif">
                                {{ ucfirst(str_replace('_', ' ', $cleaner->availability_status)) }}
                            </span>
                            @if($cleaner->is_verified)
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Verified</span>
                            @else
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3 mt-4 md:mt-0">
                    @if(!$cleaner->is_verified)
                    <button onclick="approveCleaner({{ $cleaner->id }})"
                            class="px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600">
                        Approve Cleaner
                    </button>
                    @endif
                    <button onclick="suspendCleaner({{ $cleaner->id }})"
                            class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600">
                        Suspend
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-sm text-gray-500">Total Jobs</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total_bookings'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['completed_bookings'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-sm text-gray-500">Rating</p>
                <p class="text-3xl font-bold text-yellow-600">⭐ {{ number_format($stats['average_rating'], 1) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-sm text-gray-500">Completion Rate</p>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['completion_rate'], 1) }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-sm text-gray-500">Total Earnings</p>
                <p class="text-3xl font-bold text-green-600">TZS {{ number_format($stats['total_earnings']) }}</p>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-6">Recent Bookings</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($cleaner->bookings as $booking)
                        <tr>
                            <td class="px-4 py-3 font-mono text-sm">{{ $booking->booking_number }}</td>
                            <td class="px-4 py-3">{{ $booking->service->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs
                                    @if($booking->status === 'completed') bg-green-100 text-green-700
                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-blue-100 text-blue-700 @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">TZS {{ number_format($booking->cleaner_payout_amount) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $booking->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No bookings yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden"></div>

    <script>
        function showToast(msg, type) {
            const t = document.getElementById('toast');
            t.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-2xl shadow-2xl text-white font-medium ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            t.textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        async function approveCleaner(id) {
            if (!confirm('Approve this cleaner?')) return;
            try {
                const res = await fetch(`/admin/cleaners/${id}/approve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 1000);
            } catch (e) { showToast('Failed', 'error'); }
        }

        async function suspendCleaner(id) {
            const reason = prompt('Reason:');
            if (!reason) return;
            try {
                const res = await fetch(`/admin/cleaners/${id}/suspend`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason })
                });
                const data = await res.json();
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 1000);
            } catch (e) { showToast('Failed', 'error'); }
        }
    </script>
</body>
</html>