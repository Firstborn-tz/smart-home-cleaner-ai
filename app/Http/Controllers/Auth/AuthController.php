<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ============================================
    // LOGIN / LOGOUT
    // ============================================
    
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Check if cleaner is approved
            if ($user->user_type === 'cleaner') {
                $cleaner = $user->cleaner;
                if (!$cleaner || $cleaner->registration_status !== 'approved') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    $status = $cleaner->registration_status ?? 'pending';
                    
                    if ($status === 'rejected') {
                        $message = 'Your registration has been REJECTED. Reason: ' . ($cleaner->registration_notes ?? 'No reason provided.') . ' You may reapply with updated information.';
                    } else {
                        $message = 'Your registration is still under review. You will be notified once approved. Please check back later.';
                    }
                    
                    return back()->withErrors([
                        'email' => $message,
                    ])->onlyInput('email');
                }
            }
            
            return match($user->user_type) {
                'admin', 'super_admin' => redirect()->intended(route('admin.dashboard')),
                'cleaner' => redirect()->intended(route('cleaner.dashboard')),
                'homeowner' => redirect()->intended(route('homeowner.dashboard')),
                default => redirect('/'),
            };
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }
    
    /**
     * Redirect cleaner based on registration status
     */
    private function redirectCleaner($user)
    {
        $cleaner = $user->cleaner;
        
        // If not approved or no profile, show registration status
        if (!$cleaner || $cleaner->registration_status !== 'approved') {
            return redirect()->route('cleaner.registration-status');
        }
        
        return redirect()->intended(route('cleaner.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ============================================
    // REGISTRATION PAGES
    // ============================================
    
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function showCleanerRegistration()
    {
        return view('auth.register-cleaner');
    }

    public function showHomeownerRegistration()
    {
        return view('auth.register-homeowner');
    }

    // ============================================
    // GENERIC REGISTRATION
    // ============================================
    
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required|in:homeowner,cleaner',
        ]);

        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ($request->user_type === 'cleaner') {
            $user->cleaner()->create([
                'cleaner_id' => 'CLN-' . strtoupper(Str::random(8)),
                'city_id' => 1,
                'availability_status' => 'offline',
                'is_verified' => false,
                'registration_status' => 'pending',
            ]);
            $user->assignRole('cleaner');
            
            Auth::login($user);
            return redirect()->route('cleaner.registration-status');
        } else {
            $user->homeowner()->create([
                'homeowner_id' => 'HMO-' . strtoupper(Str::random(8)),
            ]);
            $user->assignRole('homeowner');
            
            Auth::login($user);
            return redirect()->route('homeowner.dashboard');
        }
    }

    // ============================================
    // CLEANER REGISTRATION (from multi-step form)
    // ============================================
    
    public function registerCleaner(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:8|confirmed',
            'city_name' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string',
            'max_service_radius_km' => 'nullable|integer',
            'house_number' => 'nullable|string|max:100',
        ], [
            'email.unique' => 'This email is already registered. Please login or use a different email.',
            'phone.unique' => 'This phone number is already registered. Please login or use a different phone number.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        // Check region
        $regionName = $request->region ?? $request->city_name;
        if ($regionName && !Region::isRegionAllowed($regionName)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, services are not yet available in ' . $regionName . '.',
                'region_blocked' => true,
            ], 422);
        }

        // Find or create city
        $city = City::where('name', $request->city_name)->first();
        if (!$city) {
            $code = strtoupper(substr($request->city_name, 0, 3));
            if (City::where('code', $code)->exists()) {
                $code = $code . rand(10, 99);
            }
            $city = City::create([
                'name' => $request->city_name,
                'region' => $regionName ?? $request->city_name,
                'code' => $code,
                'latitude' => $request->latitude ?? -6.7924,
                'longitude' => $request->longitude ?? 39.2083,
                'is_active' => true,
                'sort_order' => 99,
            ]);
        }
        $cityId = $city->id;

        $fullStreet = $request->street ?? '';
        if ($request->house_number) {
            $fullStreet = $request->house_number . ', ' . $fullStreet;
        }

        // Create user
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'cleaner',
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        // Create cleaner - PENDING APPROVAL
        $cleaner = $user->cleaner()->create([
            'cleaner_id' => 'CLN-' . strtoupper(Str::random(8)),
            'city_id' => $cityId,
            'availability_status' => 'offline',
            'is_verified' => false,
            'registration_status' => 'pending',
            'street' => $fullStreet,
            'ward' => $request->ward,
            'region' => $regionName ?? $request->city_name,
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'max_service_radius_km' => $request->max_service_radius_km ?? 30,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'national_id' => $request->national_id,
        ]);

        $user->assignRole('cleaner');

        // Create in-app notification for cleaner
        \App\Models\Notification::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'type' => 'registration_submitted',
            'title' => 'Registration Submitted',
            'body' => 'Your cleaner application has been received. We will review it within 24-48 hours. You will be notified once approved.',
            'icon' => 'fa-paper-plane',
            'priority' => 1,
            'channel' => 'in-app',
        ]);

        // Notify admins
        $this->notifyAdminsNewRegistration($cleaner);

        // Log the cleaner in so they can see status
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration submitted successfully! You cannot login until an admin approves your application. You will be notified once approved.',
            'redirect' => '/',
        ]);
    }

    // ============================================
    // HOMEOWNER REGISTRATION (Auto-accepted)
    // ============================================
    
    public function registerHomeowner(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:8|confirmed',
            'city_name' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'house_number' => 'nullable|string|max:100',
        ], [
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        // Check region availability
        $regionName = $request->region ?? $request->city_name;
        if ($regionName && !Region::isRegionAllowed($regionName)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, SmartClean AI is not yet available in ' . $regionName . '.',
                'region_blocked' => true,
            ], 422);
        }

        // Find or create city
        $cityId = null;
        if ($request->city_name) {
            $city = City::where('name', $request->city_name)->first();
            if (!$city) {
                $code = strtoupper(substr($request->city_name, 0, 3));
                if (City::where('code', $code)->exists()) { $code = $code . rand(10, 99); }
                $city = City::create([
                    'name' => $request->city_name,
                    'region' => $regionName ?? $request->city_name,
                    'code' => $code,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'is_active' => true,
                    'sort_order' => 99,
                ]);
            }
            $cityId = $city->id;
        }

        $fullStreet = $request->street ?? '';
        if ($request->house_number) {
            $fullStreet = $request->house_number . ', ' . $fullStreet;
        }

        // Create user
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'homeowner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create homeowner - DIRECTLY ACTIVE
        $user->homeowner()->create([
            'homeowner_id' => 'HMO-' . strtoupper(Str::random(8)),
            'city_id' => $cityId,
            'street' => $fullStreet,
            'ward' => $request->ward,
            'district' => $request->ward,
            'region' => $regionName ?? $request->city_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'full_address' => $fullStreet,
        ]);

        $user->assignRole('homeowner');
        Auth::login($user);

        // Create welcome notification
        \App\Models\Notification::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'type' => 'welcome',
            'title' => 'Welcome to SmartClean AI!',
            'body' => 'Your account has been created. You can now book cleaning services instantly.',
            'icon' => 'fa-home',
            'priority' => 1,
            'channel' => 'in-app',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Welcome to SmartClean AI.',
            'redirect' => '/homeowner/bookings/create',
        ]);
    }

    // ============================================
    // NOTIFICATIONS
    // ============================================
    
    private function notifyAdminsNewRegistration($cleaner)
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin'])->get();
        
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $admin->id,
                'type' => 'cleaner_registration',
                'title' => 'New Cleaner Registration',
                'body' => $cleaner->user->full_name . ' from ' . ($cleaner->city->name ?? $cleaner->region ?? 'Unknown') . ' has applied. Review their application.',
                'icon' => 'fa-user-plus',
                'priority' => 1,
                'channel' => 'in-app',
            ]);
        }
    }
}