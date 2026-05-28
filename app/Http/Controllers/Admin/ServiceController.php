<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * List all services with commission rate
     */
    public function index()
    {
        $services = Service::with('category')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();
            
        $categories = ServiceCategory::orderBy('sort_order')->get();
        $commissionRate = Setting::get('commission_rate', 15);
        
        return view('admin.services.index', compact('services', 'categories', 'commissionRate'));
    }

    /**
     * Store a new service (NO price - cleaners set their own)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string|max:1000',
            'estimated_duration_minutes' => 'required|integer|min:30|max:600',
            'is_active' => 'boolean',
        ]);

        Service::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'base_price' => null,
            'estimated_duration_minutes' => $request->estimated_duration_minutes,
            'is_active' => $request->is_active ?? true,
            'sort_order' => Service::max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully! Cleaners will set their own prices.',
        ]);
    }

    /**
     * Update a service (NO price update)
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string|max:1000',
            'estimated_duration_minutes' => 'required|integer|min:30|max:600',
            'is_active' => 'boolean',
        ]);

        $service->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'description' => $request->description,
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
        if ($service->bookings()->count() > 0) {
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
     * Update commission rate
     */
    public function updateCommission(Request $request)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        Setting::updateOrCreate(
            ['key' => 'commission_rate'],
            ['value' => $request->commission_rate]
        );

        return response()->json([
            'success' => true,
            'message' => 'Commission rate updated to ' . $request->commission_rate . '%',
        ]);
    }
}