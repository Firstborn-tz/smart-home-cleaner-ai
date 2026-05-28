@extends('layouts.app')

@section('title', 'My Jobs')
@section('user_role', 'Cleaner')
@section('page_title', 'My Jobs')
@section('page_subtitle', 'View and manage your bookings')

@section('content')
<div>
    @php $cleaner = Auth::user()->cleaner; @endphp

    {{-- ============================================ --}}
    {{-- TAB NAVIGATION --}}
    {{-- ============================================ --}}
    <div class="flex gap-1.5 mb-6 overflow-x-auto pb-1 scrollbar-hide">
        @php
            $tabs = [
                'pending' => ['label' => 'Pending', 'icon' => 'fa-clock', 'color' => 'yellow'],
                'cleaner_assigned' => ['label' => 'Assigned', 'icon' => 'fa-user-check', 'color' => 'blue'],
                'cleaner_accepted' => ['label' => 'Accepted', 'icon' => 'fa-handshake', 'color' => 'purple'],
                'in_progress' => ['label' => 'In Progress', 'icon' => 'fa-spinner', 'color' => 'orange'],
                'completed' => ['label' => 'Completed', 'icon' => 'fa-check-circle', 'color' => 'green'],
            ];
            $activeTab = request('tab', 'pending');
        @endphp
        
        @foreach($tabs as $key => $tab)
            <a href="?tab={{ $key }}" 
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-300
                      {{ $activeTab === $key 
                          ? 'bg-gradient-to-r from-' . $tab['color'] . '-500 to-' . $tab['color'] . '-600 text-white shadow-lg shadow-' . $tab['color'] . '-500/25' 
                          : 'bg-white dark:bg-gray-800 text-body hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600' }}">
                <i class="fas {{ $tab['icon'] }} text-xs"></i>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ============================================ --}}
    {{-- BOOKINGS --}}
    {{-- ============================================ --}}
    @php
        $bookings = App\Models\Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->when($activeTab === 'pending', fn($q) => $q->whereIn('status', ['pending', 'cleaner_assigned']))
            ->when(!in_array($activeTab, ['pending']), fn($q) => $q->where('status', $activeTab))
            ->latest()->get();
    @endphp

    @if($bookings->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
        @foreach($bookings as $booking)
        @php
            $borderColors = [
                'instant' => 'border-l-red-500',
                'scheduled' => 'border-l-blue-500',
                'completed' => 'border-l-green-500',
                'in_progress' => 'border-l-orange-500',
                'cleaner_accepted' => 'border-l-purple-500',
                'cleaner_assigned' => 'border-l-blue-500',
                'pending' => 'border-l-yellow-500',
            ];
            $borderColor = $borderColors[$booking->status] ?? ($booking->booking_type === 'instant' ? 'border-l-red-500' : 'border-l-yellow-500');
        @endphp
        
        <a href="/cleaner/bookings/{{ $booking->id }}/detail" 
           class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 border-l-4 {{ $borderColor }} p-5 card-hover-lift group block">
            
            {{-- Header --}}
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-mono text-muted bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-lg">
                    #{{ $booking->booking_number }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold
                    @if($booking->booking_type === 'instant') 
                        bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20
                    @else 
                        bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/20 
                    @endif">
                    <i class="fas {{ $booking->booking_type === 'instant' ? 'fa-bolt' : 'fa-calendar' }} mr-1 text-[9px]"></i>
                    {{ $booking->booking_type === 'instant' ? 'Instant' : 'Scheduled' }}
                </span>
            </div>

            {{-- Service Name --}}
            <h3 class="font-bold text-heading text-lg mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                {{ $booking->service->name ?? 'Service' }}
            </h3>

            {{-- Status Badge --}}
            <div class="mb-3">
                @php
                    $statusMap = [
                        'completed' => ['bg' => 'bg-green-100 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-500/20'],
                        'in_progress' => ['bg' => 'bg-orange-100 dark:bg-orange-500/10', 'text' => 'text-orange-700 dark:text-orange-300', 'dot' => 'bg-orange-500 animate-pulse', 'border' => 'border-orange-200 dark:border-orange-500/20'],
                        'cleaner_accepted' => ['bg' => 'bg-purple-100 dark:bg-purple-500/10', 'text' => 'text-purple-700 dark:text-purple-300', 'dot' => 'bg-purple-500', 'border' => 'border-purple-200 dark:border-purple-500/20'],
                        'cleaner_assigned' => ['bg' => 'bg-blue-100 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-500/20'],
                        'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-500/10', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-500/20'],
                    ];
                    $s = $statusMap[$booking->status] ?? $statusMap['pending'];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $s['bg'] }} {{ $s['text'] }} border {{ $s['border'] }}">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $s['dot'] }}"></span>
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </span>
            </div>

            {{-- Details --}}
            <div class="space-y-2.5 pt-3 border-t border-gray-100 dark:border-gray-700">
                {{-- Address --}}
                <div class="flex items-center gap-2 text-sm text-body">
                    <div class="w-8 h-8 bg-red-50 dark:bg-red-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-map-marker-alt text-red-500 text-xs"></i>
                    </div>
                    <span class="truncate">{{ Str::limit($booking->service_address, 35) }}</span>
                </div>

                {{-- Distance --}}
                <div class="flex items-center gap-2 text-sm text-body">
                    <div class="w-8 h-8 bg-blue-50 dark:bg-blue-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-road text-blue-500 text-xs"></i>
                    </div>
                    <span>{{ $booking->distance_km ? round($booking->distance_km, 1) . ' km away' : 'Distance N/A' }}</span>
                </div>

                {{-- Payout --}}
                <div class="flex items-center gap-2 text-sm">
                    <div class="w-8 h-8 bg-green-50 dark:bg-green-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-green-500 text-xs"></i>
                    </div>
                    <span class="font-bold text-green-600 dark:text-green-400">TZS {{ number_format($booking->cleaner_payout_amount) }}</span>
                </div>

                {{-- Time --}}
                <div class="flex items-center gap-2 text-xs text-muted">
                    <div class="w-8 h-8 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-gray-400 text-xs"></i>
                    </div>
                    <span>{{ $booking->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-16 text-center">
        <div class="w-20 h-20 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-inbox text-gray-400 dark:text-gray-500 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-heading mb-2">No {{ ucfirst(str_replace('_', ' ', $activeTab)) }} Jobs</h3>
        <p class="text-muted text-sm max-w-sm mx-auto">
            @if($activeTab === 'pending')
                Make sure your status is set to <strong class="text-green-600 dark:text-green-400">ONLINE</strong> to receive booking requests
            @else
                No bookings found in this category. Check other tabs or wait for new assignments.
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
