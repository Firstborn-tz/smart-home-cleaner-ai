<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * List all services
     */
    public function index()
    {
        $services = Service::with('category')->orderBy('category_id')->orderBy('sort_order')->get();
        $categories = ServiceCategory::orderBy('sort_order')->get();
        
        return view('admin.services.index', compact('services', 'categories'));
    }

    /**
     * Store a new service
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'instant_booking_premium' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'required|integer|min:30|max:600',
            'is_active' => 'boolean',
        ]);

        Service::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'instant_booking_premium' => $request->instant_booking_premium ?? 0,
            'estimated_duration_minutes' => $request->estimated_duration_minutes,
            'is_active' => $request->is_active ?? true,
            'sort_order' => Service::max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully!',
        ]);
    }

    /**
     * Update a service
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'instant_booking_premium' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'required|integer|min:30|max:600',
            'is_active' => 'boolean',
        ]);

        $service->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'instant_booking_premium' => $request->instant_booking_premium ?? 0,
            'estimated_duration_minutes' => $request->estimated_duration_minutes,
            'is_active' => $request->is_active ?? $service->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully!',
        ]);
    }

    /**
     * Delete a service
     */
    public function destroy(Service $service)
    {
        // Check if service has bookings
        if ($service->bookings()->count() > 0) {
            // Soft delete instead
            $service->update(['is_active' => false]);
            return response()->json([
                'success' => true,
                'message' => 'Service has bookings. It has been deactivated instead.',
            ]);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully!',
        ]);
    }

    /**
     * Toggle service active status
     */
    public function toggleStatus(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $service->is_active,
            'message' => $service->is_active ? 'Service activated!' : 'Service deactivated!',
        ]);
    }

    /**
     * Update sort order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:services,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $order) {
            Service::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated!']);
    }
}