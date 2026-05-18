@extends('layouts.app')

@section('title', 'Region Management')
@section('user_role', 'Administrator')
@section('page_title', 'Region Management')
@section('page_subtitle', 'Control where services are available')

@section('content')
<div>
    @php
        $regions = App\Models\Region::orderBy('name')->get();
        $stats = [
            'total' => $regions->count(),
            'active' => $regions->where('is_active', true)->count(),
            'allow_registration' => $regions->where('allow_registration', true)->count(),
            'blocked' => $regions->where('allow_registration', false)->count(),
        ];
    @endphp

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-blue-600">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500">Total Regions</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-green-600">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-500">Active</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-purple-600">{{ $stats['allow_registration'] }}</p>
            <p class="text-xs text-gray-500">Open for Registration</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-red-600">{{ $stats['blocked'] }}</p>
            <p class="text-xs text-gray-500">Blocked</p>
        </div>
    </div>

    <!-- Regions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Region</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @foreach($regions as $region)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $region->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $region->code }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $region->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $region->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $region->allow_registration ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $region->allow_registration ? 'Open' : 'Blocked' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="toggleRegistration({{ $region->id }})" 
                                class="px-3 py-1.5 text-xs rounded-lg font-bold transition
                                       {{ $region->allow_registration ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                            {{ $region->allow_registration ? 'Block' : 'Allow' }}
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    async function toggleRegistration(id) {
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
            window.showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1000);
        } catch (e) {
            window.showToast('Failed to update', 'error');
        }
    }
</script>
@endpush
@endsection