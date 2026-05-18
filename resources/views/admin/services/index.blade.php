@extends('layouts.app')

@section('title', 'Service Management')
@section('user_role', 'Administrator')
@section('page_title', 'Service Management')
@section('page_subtitle', 'Add, edit, and manage cleaning services')

@section('content')
<div x-data="serviceManager()">
    
    @php
        $services = App\Models\Service::with('category')->orderBy('category_id')->orderBy('sort_order')->get();
        $categories = App\Models\ServiceCategory::orderBy('sort_order')->get();
    @endphp

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-blue-600">{{ $services->count() }}</p>
            <p class="text-xs text-gray-500">Total Services</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-green-600">{{ $services->where('is_active', true)->count() }}</p>
            <p class="text-xs text-gray-500">Active</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <p class="text-2xl font-extrabold text-red-600">{{ $services->where('is_active', false)->count() }}</p>
            <p class="text-xs text-gray-500">Inactive</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 text-center">
            <button @click="openAddModal()" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-sm transition">
                <i class="fas fa-plus mr-1"></i> Add Service
            </button>
        </div>
    </div>

    <!-- Services Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Price (TZS)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Duration</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $service->sort_order }}</td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-gray-800 dark:text-white">{{ $service->name }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($service->description, 50) }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $service->category->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-green-600">TZS {{ number_format($service->base_price) }}</span>
                            
                        </td>
                        <td class="px-4 py-3 text-center text-sm">{{ $service->estimated_duration_minutes }} min</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="toggleStatus({{ $service->id }})" 
                                    class="relative inline-flex items-center h-6 w-12 rounded-full transition-colors duration-200"
                                    :class="statuses[{{ $service->id }}] ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block w-5 h-5 transform transition-transform duration-200 bg-white rounded-full shadow-md"
                                      :class="statuses[{{ $service->id }}] ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                <button @click="openEditModal({{ $service->id }}, '{{ $service->name }}', '{{ $service->category_id }}', '{{ $service->description }}', {{ $service->base_price }}, {{ $service->instant_booking_premium }}, {{ $service->estimated_duration_minutes }})"
                                        class="p-2 bg-blue-100 dark:bg-blue-900 text-blue-600 rounded-lg hover:bg-blue-200 transition">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="deleteService({{ $service->id }})"
                                        class="p-2 bg-red-100 dark:bg-red-900 text-red-600 rounded-lg hover:bg-red-200 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-16 text-center text-gray-500">No services found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD/EDIT MODAL -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="modalOpen = false" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg m-4 p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4" x-text="modalTitle"></h3>
            
            <form @submit.prevent="saveService()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Name *</label>
                    <input type="text" x-model="form.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                    <select x-model="form.category_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea x-model="form.description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base Price (TZS) *</label>
                        <input type="number" x-model="form.base_price" required min="0" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (minutes) *</label>
                    <input type="number" x-model="form.estimated_duration_minutes" required min="30" max="600" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold transition">
                        <i class="fas fa-save mr-1"></i> <span x-text="modalSubmitText"></span>
                    </button>
                    <button type="button" @click="modalOpen = false" class="px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function serviceManager() {
        return {
            modalOpen: false,
            modalTitle: 'Add Service',
            modalSubmitText: 'Save',
            editingId: null,
            statuses: {},
            
            form: {
                name: '',
                category_id: '',
                description: '',
                base_price: 50000,
                instant_booking_premium: 0,
                estimated_duration_minutes: 120,
            },
            
            init() {
                @foreach($services as $s)
                this.statuses[{{ $s->id }}] = {{ $s->is_active ? 'true' : 'false' }};
                @endforeach
            },
            
            openAddModal() {
                this.editingId = null;
                this.modalTitle = 'Add New Service';
                this.modalSubmitText = 'Save';
                this.form = { name: '', category_id: '', description: '', base_price: 50000, instant_booking_premium: 0, estimated_duration_minutes: 120 };
                this.modalOpen = true;
            },
            
            openEditModal(id, name, catId, desc, price, instant, duration) {
                this.editingId = id;
                this.modalTitle = 'Edit Service';
                this.modalSubmitText = 'Update';
                this.form = { 
                    name: name, 
                    category_id: catId, 
                    description: desc || '', 
                    base_price: price, 
                    instant_booking_premium: instant || 0, 
                    estimated_duration_minutes: duration 
                };
                this.modalOpen = true;
            },
            
            async saveService() {
                const url = this.editingId 
                    ? `/admin/services/${this.editingId}/update`
                    : '/admin/services/store';
                
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });
                    const data = await res.json();
                    window.showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) { this.modalOpen = false; setTimeout(() => location.reload(), 1000); }
                } catch (e) {
                    window.showToast('Failed to save', 'error');
                }
            }
        };
    }

    async function toggleStatus(id) {
        try {
            const res = await fetch(`/admin/services/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            window.showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } catch (e) {
            window.showToast('Failed', 'error');
        }
    }

    async function deleteService(id) {
        if (!confirm('Delete this service? If it has bookings, it will be deactivated instead.')) return;
        try {
            const res = await fetch(`/admin/services/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            window.showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1000);
        } catch (e) {
            window.showToast('Failed', 'error');
        }
    }
</script>
@endpush
@endsection
