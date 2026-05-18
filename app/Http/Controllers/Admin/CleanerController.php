<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cleaner;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\SMSHelper;

class CleanerController extends Controller
{
    /**
     * List all cleaners with filters
     */
    public function index(Request $request)
    {
        $query = Cleaner::with(['user', 'city'])
            ->when($request->status, function ($q, $status) {
                return $q->where('availability_status', $status);
            })
            ->when($request->city_id, function ($q, $cityId) {
                return $q->where('city_id', $cityId);
            })
            ->when($request->verified, function ($q, $verified) {
                return $q->where('is_verified', $verified === 'yes');
            })
            ->when($request->search, function ($q, $search) {
                return $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $cleaners = $query->latest()->paginate(15);
        $cities = City::where('is_active', true)->get();

        $stats = [
            'total' => Cleaner::count(),
            'online' => Cleaner::where('availability_status', 'online')->count(),
            'verified' => Cleaner::where('is_verified', true)->count(),
            'pending' => Cleaner::where('is_verified', false)->count(),
        ];

        return view('admin.cleaners.index', compact('cleaners', 'cities', 'stats'));
    }

    /**
     * Show single cleaner details
     */
    public function show(Cleaner $cleaner)
    {
        $cleaner->load(['user', 'city', 'bookings' => function ($q) {
            $q->latest()->limit(20);
        }, 'commissions', 'reviews']);

        $stats = [
            'total_bookings' => $cleaner->bookings()->count(),
            'completed_bookings' => $cleaner->total_completed_jobs,
            'cancellation_rate' => $cleaner->cancellation_rate,
            'total_earnings' => $cleaner->total_earnings,
            'average_rating' => $cleaner->rating,
            'completion_rate' => $cleaner->completion_rate,
        ];

        return view('admin.cleaners.show', compact('cleaner', 'stats'));
    }

    /**
     * APPROVE CLEANER - Step 5
     * Changes status to approved, sends notification + SMS
     */
    public function approve(Request $request, Cleaner $cleaner)
    {
        $cleaner->update([
            'is_verified' => true,
            'verified_at' => now(),
            'registration_status' => 'approved',
            'availability_status' => 'online',
        ]);

        $cleaner->user->update(['status' => 'active']);

        // CREATE IN-APP NOTIFICATION FOR CLEANER
        \App\Models\Notification::create([
            'user_id' => $cleaner->user_id,
            'type' => 'registration_approved',
            'title' => 'Registration Approved!',
            'body' => 'Congratulations! Your cleaner registration has been approved. You can now start receiving bookings. Welcome to SmartClean AI!',
            'icon' => 'fa-check-circle',
            'priority' => 2,
            'channel' => 'in-app',
        ]);

        // Send approval SMS
        try {
            SMSHelper::sendApprovalNotification($cleaner->user->phone, $cleaner->user->first_name);
        } catch (\Exception $e) {
            \Log::error('Approval SMS failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true, 
            'message' => 'Cleaner approved! Notification sent.',
        ]);
    }

    /**
     * REJECT CLEANER - Step 5
     * Changes status to rejected, sends notification + SMS with reason
     */
    public function reject(Request $request, Cleaner $cleaner)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $cleaner->update([
            'registration_status' => 'rejected',
            'is_verified' => false,
            'registration_notes' => $request->reason,
            'availability_status' => 'offline',
        ]);

        // CREATE IN-APP NOTIFICATION FOR CLEANER
        \App\Models\Notification::create([
            'user_id' => $cleaner->user_id,
            'type' => 'registration_rejected',
            'title' => 'Registration Not Approved',
            'body' => "Your application was not approved. Reason: {$request->reason}. You may reapply with updated information.",
            'icon' => 'fa-times-circle',
            'priority' => 2,
            'channel' => 'in-app',
        ]);

        // Send rejection SMS
        try {
            SMSHelper::sendRejectionNotification(
                $cleaner->user->phone, 
                $cleaner->user->first_name, 
                $request->reason
            );
        } catch (\Exception $e) {
            \Log::error('Rejection SMS failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true, 
            'message' => 'Cleaner rejected. Notification sent.',
        ]);
    }

    /**
     * Suspend a cleaner
     */
    public function suspend(Request $request, Cleaner $cleaner)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $cleaner->update([
            'availability_status' => 'offline',
            'location_sharing_enabled' => false,
        ]);

        $cleaner->user->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => 'Cleaner suspended successfully',
        ]);
    }

    /**
     * Update cleaner status manually
     */
    public function updateStatus(Request $request, Cleaner $cleaner)
    {
        $request->validate([
            'status' => 'required|in:online,offline,online_busy,scheduled_only',
        ]);

        $cleaner->update(['availability_status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    /**
     * Get cleaner details as JSON for modal
     */
    public function details(Cleaner $cleaner)
    {
        $cleaner->load(['user', 'city']);
        
        return response()->json([
            'success' => true,
            'cleaner' => [
                'id' => $cleaner->id,
                'cleaner_id' => $cleaner->cleaner_id,
                'gender' => $cleaner->gender,
                'date_of_birth' => $cleaner->date_of_birth ? $cleaner->date_of_birth->format('Y-m-d') : null,
                'national_id' => $cleaner->national_id,
                'street' => $cleaner->street,
                'ward' => $cleaner->ward,
                'region' => $cleaner->region,
                'current_latitude' => $cleaner->current_latitude,
                'current_longitude' => $cleaner->current_longitude,
                'registration_status' => $cleaner->registration_status,
                'created_at' => $cleaner->created_at->format('M d, Y'),
                'user' => [
                    'full_name' => $cleaner->user->full_name,
                    'email' => $cleaner->user->email,
                    'phone' => $cleaner->user->phone,
                ],
                'city' => [
                    'name' => $cleaner->city->name ?? 'N/A',
                    'region' => $cleaner->city->region ?? 'N/A',
                ],
            ]
        ]);
    }
}