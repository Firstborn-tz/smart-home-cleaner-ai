<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register as Homeowner - SmartClean AI</title>
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
        .dark .text-gray-500 { color: #9ca3af; }
        .dark .border-gray-200 { border-color: #374151; }
        .dark .bg-gray-50 { background-color: #111827; }
        .dark .bg-gray-100 { background-color: #1f2937; }
        #homeownerMap { width: 100%; height: 280px; border-radius: 12px; background: #d1d5db; transition: border 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-lg w-full">
        
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white">Join SmartClean AI</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Find the best cleaners for your home</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            <form id="homeownerForm" onsubmit="submitForm(event)">
                @csrf
                
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-user-circle text-blue-500 mr-2"></i> Your Details
                </h3>
                
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label><input type="text" name="first_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></div>
                        <div><label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label><input type="text" name="last_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label><input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></div>
                        <div><label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phone *</label><input type="tel" name="phone" required placeholder="+255 7XX XXX XXX" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Password *</label><input type="password" name="password" required minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></div>
                        <div><label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password *</label><input type="password" name="password_confirmation" required minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></div>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-3 mt-6">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Your Home Location
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Click on map or use button. Address auto-detected.</p>

                <div id="homeownerMap" class="mb-2"></div>
                
                <div class="flex space-x-2 mb-3">
                    <button type="button" onclick="useMyLocation()" class="flex-1 px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold text-xs transition">
                        <i class="fas fa-location-crosshairs mr-1"></i> Detect My Location
                    </button>
                    <span id="locationStatus" class="flex-1 text-xs text-center py-2 text-gray-500">Click map or use button</span>
                </div>

                <!-- Auto-detected Address (Read-only) -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 space-y-2 mb-3">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Detected Address (from map)</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Street/Road</label>
                            <input type="text" id="detectedStreet" readonly class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">District/Ward</label>
                            <input type="text" id="detectedDistrict" readonly class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">City</label>
                            <input type="text" id="detectedCity" readonly class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Region</label>
                            <input type="text" id="detectedRegion" readonly class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected...">
                        </div>
                    </div>
                    
                    <!-- House/Gate Number -->
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">House/Gate/Plot Number <span class="text-red-500">*</span></label>
                        <input type="text" name="house_number" required placeholder="e.g., Plot 456, Gate 2, House 12" class="w-full px-4 py-2.5 rounded-xl border-2 border-blue-300 dark:border-blue-500 bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm">
                    </div>
                </div>

                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <input type="hidden" name="street" id="hiddenStreet">
                <input type="hidden" name="ward" id="hiddenWard">
                <input type="hidden" name="city_name" id="hiddenCity">
                <input type="hidden" name="region" id="hiddenRegion">

                <button type="submit" class="w-full mt-4 px-6 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-xl font-bold text-base transition shadow-lg">
                    <i class="fas fa-check-circle mr-2"></i> Complete Registration
                </button>
                
                <p class="text-center text-xs text-gray-500 mt-4">Already have an account? <a href="/login" class="text-blue-600 font-medium">Sign in</a></p>
            </form>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&v=weekly"></script>
    <script>
        let map, marker, locationSet = false;

        function initMap() {
            const el = document.getElementById('homeownerMap');
            if (!el || map) return;
            map = new google.maps.Map(el, { center: { lat: -6.7924, lng: 39.2083 }, zoom: 6, mapTypeControl: false, streetViewControl: false, fullscreenControl: false });
            google.maps.event.addListener(map, 'click', function(e) { setLocation(e.latLng.lat(), e.latLng.lng()); });
            detectMyLocation();
        }

        function detectMyLocation() {
            document.getElementById('locationStatus').textContent = 'Detecting...';
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) { map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude }); map.setZoom(17); setLocation(pos.coords.latitude, pos.coords.longitude); },
                    function() { document.getElementById('locationStatus').textContent = 'Failed. Click map.'; },
                    { enableHighAccuracy: true, timeout: 15000 }
                );
            }
        }

        function reverseGeocode(lat, lng) {
            document.getElementById('locationStatus').textContent = 'Fetching address...';
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
                    document.getElementById('detectedStreet').value = fullStreet;
                    document.getElementById('detectedDistrict').value = district || city || '';
                    document.getElementById('detectedCity').value = city || district || '';
                    document.getElementById('detectedRegion').value = region || '';
                    document.getElementById('hiddenStreet').value = fullStreet;
                    document.getElementById('hiddenWard').value = district || city || '';
                    document.getElementById('hiddenCity').value = city || district || region || '';
                    document.getElementById('hiddenRegion').value = region || '';
                    document.getElementById('locationStatus').innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i> Location set!';
                    document.getElementById('locationStatus').className = 'flex-1 text-xs text-center py-2 text-green-600 font-medium';
                }
            });
        }

        function setLocation(lat, lng) {
            if (marker) marker.setMap(null);
            marker = new google.maps.Marker({ position: { lat, lng }, map, draggable: true, animation: google.maps.Animation.DROP, icon: { url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' } });
            document.getElementById('latitude').value = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);
            locationSet = true;
            reverseGeocode(lat, lng);
            document.getElementById('homeownerMap').style.border = '3px solid #22c55e';
            google.maps.event.addListener(marker, 'dragend', function() { const p = marker.getPosition(); document.getElementById('latitude').value = p.lat().toFixed(7); document.getElementById('longitude').value = p.lng().toFixed(7); reverseGeocode(p.lat(), p.lng()); });
        }

        function useMyLocation() { if (!map) initMap(); detectMyLocation(); }

        async function submitForm(event) {
            event.preventDefault();
            if (!document.getElementById('latitude').value) { alert('Please set your location on the map.'); return; }
            const form = event.target; const formData = new FormData(form); formData.append('user_type', 'homeowner');
            const btn = form.querySelector('button[type="submit"]'); btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Registering...';
            try {
                const res = await fetch('/register/homeowner/submit', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: formData });
                const data = await res.json();
                if (data.success) { window.location.href = data.redirect || '/homeowner/dashboard'; }
                else { alert(data.message || 'Registration failed'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Complete Registration'; }
            } catch (e) { alert('Error occurred.'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Complete Registration'; }
        }

        window.addEventListener('load', () => setTimeout(initMap, 500));
    </script>
</body>
</html>