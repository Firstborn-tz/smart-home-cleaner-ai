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
        
        $activeCount = $services->where('is_active', true)->count();
        $inactiveCount = $services->where('is_active', false)->count();
        $categoryBreakdown = $services->groupBy('category_id');
    @endphp

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-list-check text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $services->count() }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Total Services</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $activeCount }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Active</p>
            @if($services->count() > 0)
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-green-500 h-full rounded-full" style="width: {{ round(($activeCount / $services->count()) * 100) }}%"></div>
            </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-red-100 to-rose-200 dark:from-red-900/40 dark:to-rose-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-pause-circle text-red-600 dark:text-red-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $inactiveCount }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Inactive</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 text-center card-hover-lift group">
            <div class="w-12 h-12 bg-linear-to-br from-purple-100 to-purple-200 dark:from-purple-900/40 dark:to-purple-800/40 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-layer-group text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
            <p class="text-3xl font-black text-heading stat-number">{{ $categories->count() }}</p>
            <p class="text-xs text-muted font-medium uppercase tracking-wider mt-1">Categories</p>
        </div>
    </div>

    {{-- HEADER ACTIONS --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-layer-group text-purple-500 text-sm"></i>
                <span class="text-sm font-semibold text-heading">{{ $categories->count() }} Categories</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-tag text-blue-500 text-sm"></i>
                <span class="text-sm font-semibold text-heading">Cleaner-Set Pricing</span>
            </div>
        </div>
        
        <button @click="openAddModal()" 
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 transition-all duration-300">
            <i class="fas fa-plus mr-2"></i> Add New Service
        </button>
    </div>

    {{-- SERVICES TABLE --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">#</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Service</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-muted uppercase tracking-wider">Category</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Duration</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        <td class="px-5 py-4">
                            <span class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-muted">
                                {{ $service->sort_order }}
                            </span>
                        </td>
                        
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-linear-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-broom text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-heading group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $service->name }}
                                    </p>
                                    <p class="text-xs text-muted line-clamp-1">{{ Str::limit($service->description, 60) }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-5 py-4">
                            @if($service->category)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-500/20">
                                <i class="fas fa-folder mr-1.5 text-[10px]"></i> {{ $service->category->name }}
                            </span>
                            @else
                            <span class="text-xs text-muted">N/A</span>
                            @endif
                        </td>
                        
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5 text-sm font-semibold text-heading">
                                <i class="fas fa-clock text-muted text-xs"></i>
                                {{ $service->estimated_duration_minutes }} <span class="text-xs text-muted font-normal">min</span>
                            </div>
                        </td>
                        
                        <td class="px-5 py-4 text-center">
                            <button @click="toggleStatus({{ $service->id }})" 
                                    class="relative inline-flex items-center h-7 w-[52px] rounded-full transition-all duration-300 focus:outline-none"
                                    :class="statuses[{{ $service->id }}] ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                                <span class="sr-only">Toggle status</span>
                                <span class="inline-flex items-center justify-center w-6 h-6 transform transition-all duration-300 bg-white rounded-full shadow-md"
                                      :class="statuses[{{ $service->id }}] ? 'translate-x-6' : 'translate-x-1'">
                                    <i class="fas text-[10px] transition-all duration-300"
                                       :class="statuses[{{ $service->id }}] ? 'fa-check text-green-500' : 'fa-times text-gray-400'"></i>
                                </span>
                            </button>
                        </td>
                        
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="openEditModal({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ $service->category_id }}', '{{ addslashes($service->description ?? '') }}', {{ $service->estimated_duration_minutes }})"
                                        class="w-9 h-9 flex items-center justify-center bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all duration-200"
                                        title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button @click="deleteService({{ $service->id }})"
                                        class="w-9 h-9 flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition-all duration-200"
                                        title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-20 text-center">
                            <div class="w-20 h-20 bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-broom text-gray-400 dark:text-gray-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-heading mb-2">No Services Yet</h3>
                            <p class="text-muted text-sm mb-4">Get started by adding your first cleaning service</p>
                            <button @click="openAddModal()" 
                                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 transition-all duration-300">
                                <i class="fas fa-plus mr-2"></i> Add First Service
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ADD/EDIT MODAL --}}
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
         @click.self="modalOpen = false" 
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg m-4 max-h-[90vh] overflow-y-auto animate-slide-up border border-gray-100 dark:border-gray-700"
             @click.stop>
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-5 border-b border-gray-100 dark:border-gray-700 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-400 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/25">
                        <i class="fas fa-broom text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-heading" x-text="modalTitle"></h3>
                        <p class="text-xs text-muted" x-text="editingId ? 'Update service details' : 'Create a new cleaning service'"></p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <form @submit.prevent="saveService()" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <i class="fas fa-tag text-blue-500 mr-1.5"></i> Service Name *
                        </label>
                        <input type="text" x-model="form.name" required 
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-heading text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                               placeholder="e.g., Deep Cleaning">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <i class="fas fa-folder text-purple-500 mr-1.5"></i> Category *
                        </label>
                        <div class="relative">
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-muted text-xs pointer-events-none"></i>
                            <select x-model="form.category_id" required 
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300 appearance-none">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <i class="fas fa-align-left text-green-500 mr-1.5"></i> Description
                        </label>
                        <textarea x-model="form.description" rows="3" 
                                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-body text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                                  placeholder="Describe what this service includes..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-heading mb-2">
                            <i class="fas fa-clock text-blue-400 mr-1.5"></i> Duration (min) *
                        </label>
                        <input type="number" x-model="form.estimated_duration_minutes" required min="30" max="600" 
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-heading text-sm font-bold focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none transition-all duration-300"
                               placeholder="120">
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button type="submit" 
                                class="flex-1 px-5 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.01] transition-all duration-300">
                            <i class="fas fa-save mr-2"></i> <span x-text="modalSubmitText"></span>
                        </button>
                        <button type="button" @click="modalOpen = false" 
                                class="px-5 py-3.5 border-2 border-gray-200 dark:border-gray-600 text-body rounded-xl font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-x-6" 
         x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-x-0" 
         x-transition:leave-end="opacity-0 translate-x-6" 
         class="fixed top-6 right-6 z-[9999] px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-sm font-semibold text-white"
         :class="toast.type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'"
         style="display: none;">
        <i class="fas text-lg" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function serviceManager() {
        return {
            modalOpen: false,
            modalTitle: 'Add Service',
            modalSubmitText: 'Save Service',
            editingId: null,
            statuses: {},
            toast: { show: false, message: '', type: 'success' },
            
            form: {
                name: '',
                category_id: '',
                description: '',
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
                this.modalSubmitText = 'Save Service';
                this.form = { 
                    name: '', 
                    category_id: '', 
                    description: '', 
                    estimated_duration_minutes: 120 
                };
                this.modalOpen = true;
            },
            
            openEditModal(id, name, catId, desc, duration) {
                this.editingId = id;
                this.modalTitle = 'Edit Service';
                this.modalSubmitText = 'Update Service';
                this.form = { 
                    name: name, 
                    category_id: catId, 
                    description: desc || '', 
                    estimated_duration_minutes: parseInt(duration) 
                };
                this.modalOpen = true;
            },
            
            async saveService() {
                if (!this.form.name || !this.form.category_id || !this.form.estimated_duration_minutes) {
                    this.showToast('Please fill all required fields', 'error');
                    return;
                }
                
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
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.modalOpen = false;
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        this.showToast(data.message || 'Failed to save', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                }
            },

            async toggleStatus(id) {
                try {
                    const res = await fetch(`/admin/services/${id}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        this.statuses[id] = data.is_active;
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || 'Failed to toggle', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error', 'error');
                }
            },

            async deleteService(id) {
                if (!confirm('Delete this service? If it has bookings, it will be deactivated instead.')) return;
                
                try {
                    const res = await fetch(`/admin/services/${id}/delete`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        this.showToast(data.message || 'Failed to delete', 'error');
                    }
                } catch (e) {
                    this.showToast('Network error. Please try again.', 'error');
                }
            },
            
            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 3500);
            }
        };
    }
</script>

<style>
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-slide-up {
        animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>
@endpush