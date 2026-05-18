<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register as Cleaner - SmartClean AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function() { if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark'); })();
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .dark .bg-white { background-color: #1f2937; }
        .dark .text-gray-800 { color: #f9fafb; }
        .dark .text-gray-700 { color: #e5e7eb; }
        .dark .text-gray-600 { color: #d1d5db; }
        .dark .text-gray-500 { color: #9ca3af; }
        .dark .border-gray-200 { border-color: #374151; }
        .dark .bg-gray-50 { background-color: #111827; }
        .dark .bg-gray-100 { background-color: #1f2937; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-slide-up { animation: slideUp 0.3s ease-out; }
        #locationMap { width: 100%; height: 300px; border-radius: 12px; background: #d1d5db; transition: border 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-2xl w-full">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                <i class="fas fa-broom text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white">Become a Cleaner</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Join SmartClean AI and start earning</p>
        </div>

        <!-- Form Container -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-slide-up">
            
            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold" id="dot1">1</div>
                    <div class="w-10 h-1 bg-blue-500" id="line1"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold" id="dot2">2</div>
                    <div class="w-10 h-1 bg-gray-300" id="line2"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold" id="dot3">3</div>
                </div>
            </div>

            <form id="cleanerForm" onsubmit="submitRegistration(event)">
                @csrf
                
                <!-- ============================================ -->
                <!-- STEP 1: Personal Information -->
                <!-- ============================================ -->
                <div id="step1" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-3">
                        <i class="fas fa-user-circle text-blue-500 mr-2"></i> Personal Information
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                            <input type="text" name="first_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                            <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phone *</label>
                            <input type="tel" name="phone" required placeholder="+255 7XX XXX XXX" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                            <select name="gender" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">NIDA Number</label>
                            <input type="text" name="national_id" placeholder="e.g., 19901234-12345-00001-23" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Password *</label>
                            <input type="password" name="password" required minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                    </div>
                    
                    <button type="button" onclick="goToStep(2)" class="w-full px-6 py-3.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-base transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>

                <!-- ============================================ -->
                <!-- STEP 2: Location (Auto-detected from Map) -->
                <!-- ============================================ -->
                <div id="step2" class="space-y-4 hidden">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-3">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Your Location
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-2">
                        Click on map or use button to set your location. Address will be auto-detected.
                    </p>

                    <!-- Map -->
                    <div>
                        <div id="locationMessage" class="mb-2 px-3 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-xs font-medium">
                            <i class="fas fa-info-circle mr-1"></i> <span id="locationText">Click on map to set your location</span>
                        </div>
                        <div id="locationMap"></div>
                        <div class="flex space-x-2 mt-3">
                            <button type="button" onclick="useMyLocation()" class="flex-1 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold text-xs transition">
                                <i class="fas fa-location-crosshairs mr-1"></i> Detect My Location
                            </button>
                            <button type="button" onclick="clearLocation()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" id="clearBtn" style="display:none;">
                                <i class="fas fa-times mr-1"></i> Clear
                            </button>
                        </div>
                    </div>

                    <!-- Auto-detected Address (Read-only) -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 space-y-3">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <i class="fas fa-map-pin text-red-500 mr-1"></i> Detected Address (from map)
                        </p>
                        
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Street/Road</label>
                                <input type="text" id="detectedStreet" readonly 
                                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed"
                                       placeholder="Will be detected from map...">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">District/Ward</label>
                                    <input type="text" id="detectedDistrict" readonly 
                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed"
                                           placeholder="Auto-detected...">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">City</label>
                                    <input type="text" id="detectedCity" readonly 
                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed"
                                           placeholder="Auto-detected...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Region</label>
                                <input type="text" id="detectedRegion" readonly 
                                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed"
                                       placeholder="Auto-detected...">
                            </div>
                        </div>
                        
                        <!-- House/Gate Number - ONLY EDITABLE FIELD -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                House/Gate/Plot Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="house_number" required 
                                   placeholder="e.g., Plot 123, Gate 5, House No. 45, Floor 3"
                                   class="w-full px-4 py-2.5 rounded-xl border-2 border-blue-300 dark:border-blue-500 bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 transition">
                            <p class="text-xs text-gray-400 mt-1">Only field you fill - helps cleaners find your exact location</p>
                        </div>
                    </div>

                    <!-- Hidden fields for submission -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" name="street" id="hiddenStreet">
                    <input type="hidden" name="ward" id="hiddenWard">
                    <input type="hidden" name="city_name" id="hiddenCity">
                    <input type="hidden" name="region" id="hiddenRegion">

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Service Radius (km)</label>
                        <input type="number" name="max_service_radius_km" value="30" min="5" max="100" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="goToStep(1)" class="px-5 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </button>
                        <button type="button" onclick="goToStep(3)" class="flex-1 px-5 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-sm">
                            Continue <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- STEP 3: Experience & Submit -->
                <!-- ============================================ -->
                <div id="step3" class="space-y-4 hidden">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-3">
                        <i class="fas fa-clipboard-check text-green-500 mr-2"></i> Experience & Submit
                    </h3>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Years of Experience</label>
                        <input type="number" name="years_experience" value="0" min="0" max="50" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Equipment</label>
                        <select name="has_equipment" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="yes">Yes, I have my own equipment</option>
                            <option value="partial">I have some equipment</option>
                            <option value="no">No, I need equipment provided</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">About Yourself</label>
                        <textarea name="bio" rows="3" placeholder="Tell us about your cleaning experience..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-3">
                        <label class="flex items-start space-x-2">
                            <input type="checkbox" name="terms" required class="mt-0.5 rounded">
                            <span class="text-xs text-yellow-800 dark:text-yellow-300">I agree to Terms of Service. My registration will be reviewed within 24-48 hours.</span>
                        </label>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3">
                        <p class="text-xs text-blue-800 dark:text-blue-300">
                            <i class="fas fa-info-circle mr-1"></i> After approval, add services and set prices from your dashboard.
                        </p>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="goToStep(2)" class="px-5 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </button>
                        <button type="submit" class="flex-1 px-5 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold text-sm transition shadow-lg">
                            <i class="fas fa-paper-plane mr-1"></i> Submit Application
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-4">
            Already have an account? <a href="/login" class="text-blue-600 dark:text-blue-400 font-medium">Sign in</a>
        </p>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&v=weekly"></script>
    <script>
        let map, marker, locationSet = false, currentStep = 1;

        // ============ STEP NAVIGATION ============
        function goToStep(step) {
            document.querySelectorAll('#step1, #step2, #step3').forEach(el => el.classList.add('hidden'));
            document.getElementById('step' + step).classList.remove('hidden');
            currentStep = step;
            
            document.getElementById('dot1').className = step >= 1 ? 'w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold' : 'w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold';
            document.getElementById('dot2').className = step >= 2 ? 'w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold' : 'w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold';
            document.getElementById('dot3').className = step >= 3 ? 'w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold' : 'w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold';
            document.getElementById('line1').className = step >= 2 ? 'w-10 h-1 bg-blue-500' : 'w-10 h-1 bg-gray-300';
            document.getElementById('line2').className = step >= 3 ? 'w-10 h-1 bg-blue-500' : 'w-10 h-1 bg-gray-300';
            
            if (step === 2) setTimeout(initMap, 300);
        }

        // ============ MAP FUNCTIONS ============
        function initMap() {
            const el = document.getElementById('locationMap');
            if (!el || map) return;
            
            map = new google.maps.Map(el, { 
                center: { lat: -6.7924, lng: 39.2083 }, zoom: 6,
                mapTypeControl: false, streetViewControl: false, fullscreenControl: false 
            });
            
            google.maps.event.addListener(map, 'click', function(e) { setLocation(e.latLng.lat(), e.latLng.lng()); });
            detectMyLocation();
        }

        function detectMyLocation() {
            updateStatus('Detecting your location...', 'blue');
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                        map.setZoom(17);
                        setLocation(pos.coords.latitude, pos.coords.longitude);
                    },
                    function() { updateStatus('Could not detect. Click on map to set.', 'yellow'); },
                    { enableHighAccuracy: true, timeout: 15000 }
                );
            }
        }

        function reverseGeocode(lat, lng) {
            updateStatus('Fetching address from Google Maps...', 'blue');
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    const comps = results[0].address_components;
                    let city = '', region = '', district = '', route = '', street_number = '';
                    
                    for (let c of comps) {
                        if (c.types.includes('locality')) city = c.long_name;
                        if (c.types.includes('administrative_area_level_1')) region = c.long_name;
                        if (c.types.includes('administrative_area_level_2')) district = c.long_name;
                        if (c.types.includes('route')) route = c.long_name;
                        if (c.types.includes('street_number')) street_number = c.long_name;
                    }
                    
                    const fullStreet = (street_number ? street_number + ' ' : '') + (route || '');
                    const ward = district || city || '';
                    const cityName = city || district || '';
                    const regionName = region || '';
                    
                    document.getElementById('detectedStreet').value = fullStreet;
                    document.getElementById('detectedDistrict').value = ward;
                    document.getElementById('detectedCity').value = cityName;
                    document.getElementById('detectedRegion').value = regionName;
                    
                    document.getElementById('hiddenStreet').value = fullStreet;
                    document.getElementById('hiddenWard').value = ward;
                    document.getElementById('hiddenCity').value = cityName;
                    document.getElementById('hiddenRegion').value = regionName;
                    
                    updateStatus('Location set! Add your house/gate number below.', 'green');
                } else {
                    updateStatus('Address not found. Try a different location.', 'yellow');
                }
            });
        }

        function setLocation(lat, lng) {
            if (marker) marker.setMap(null);
            marker = new google.maps.Marker({ 
                position: { lat, lng }, map, draggable: true, 
                animation: google.maps.Animation.DROP,
                icon: { url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' }
            });
            document.getElementById('latitude').value = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);
            locationSet = true;
            reverseGeocode(lat, lng);
            
            document.getElementById('clearBtn').style.display = 'inline-block';
            document.getElementById('locationMap').style.border = '3px solid #22c55e';
            
            google.maps.event.addListener(marker, 'dragend', function() {
                const p = marker.getPosition();
                document.getElementById('latitude').value = p.lat().toFixed(7);
                document.getElementById('longitude').value = p.lng().toFixed(7);
                reverseGeocode(p.lat(), p.lng());
            });
        }

        function updateStatus(msg, type) {
            const colors = { 
                blue: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300', 
                green: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300', 
                yellow: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300' 
            };
            document.getElementById('locationText').textContent = msg;
            document.getElementById('locationMessage').className = 'mb-2 px-3 py-2 rounded-lg ' + colors[type] + ' text-xs font-medium';
        }

        function useMyLocation() { if (!map) initMap(); detectMyLocation(); }

        function clearLocation() {
            if (marker) marker.setMap(null); marker = null; locationSet = false;
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            document.getElementById('detectedStreet').value = '';
            document.getElementById('detectedDistrict').value = '';
            document.getElementById('detectedCity').value = '';
            document.getElementById('detectedRegion').value = '';
            document.getElementById('hiddenStreet').value = '';
            document.getElementById('hiddenWard').value = '';
            document.getElementById('hiddenCity').value = '';
            document.getElementById('hiddenRegion').value = '';
            updateStatus('Click on map to set your location', 'blue');
            document.getElementById('locationMap').style.border = '3px solid #d1d5db';
            document.getElementById('clearBtn').style.display = 'none';
        }

        // ============ FORM SUBMISSION ============
        async function submitRegistration(event) {
            event.preventDefault();
            
            if (!document.getElementById('latitude').value) {
                alert('Please set your location on the map before submitting.');
                goToStep(2);
                return;
            }
            
            const form = document.getElementById('cleanerForm');
            const formData = new FormData(form);
            formData.append('user_type', 'cleaner');
            
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...';

            try {
                const res = await fetch('/register/cleaner/submit', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert('Registration submitted! Please wait for admin approval.');
                    window.location.href = '/login';
                } else {
                    alert(data.message || 'Registration failed.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Submit Application';
                }
            } catch (e) {
                alert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Submit Application';
            }
        }

        window.addEventListener('load', () => setTimeout(initMap, 500));
    </script>
</body>
</html>