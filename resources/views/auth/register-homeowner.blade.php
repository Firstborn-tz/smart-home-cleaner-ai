<!DOCTYPE html>
<html lang="en" class="scroll-smooth" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); }); document.documentElement.classList.toggle('dark', darkMode)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join as Homeowner — SmartClean AI</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse-ring { 0% { box-shadow:0 0 0 0 rgba(59,130,246,0.4); } 70% { box-shadow:0 0 0 20px rgba(59,130,246,0); } 100% { box-shadow:0 0 0 0 rgba(59,130,246,0); } }
        @keyframes gradientShift { 0% { background-position:0% 50%; } 50% { background-position:100% 50%; } 100% { background-position:0% 50%; } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-slide-up { animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1); }
        .pulse-ring { animation: pulse-ring 2.5s infinite; }
        .gradient-animate { background-size:200% 200%; animation: gradientShift 5s ease infinite; }
        .glass-card { background:rgba(255,255,255,0.85); backdrop-filter:blur(20px) saturate(180%); -webkit-backdrop-filter:blur(20px) saturate(180%); border:1px solid rgba(0,0,0,0.06); box-shadow:0 8px 40px rgba(0,0,0,0.08); }
        .dark .glass-card { background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.08); box-shadow:0 8px 40px rgba(0,0,0,0.4); }
        .input-premium { transition:all 0.3s cubic-bezier(0.16,1,0.3,1); background:rgba(255,255,255,0.6); }
        .dark .input-premium { background:rgba(30,41,59,0.7); }
        .input-premium:focus { transform:translateY(-1px); box-shadow:0 8px 25px -5px rgba(59,130,246,0.3); border-color:#3b82f6; }
        #homeownerMap { width:100%; height:280px; border-radius:16px; background:#e5e7eb; border:3px solid #d1d5db; transition:all 0.3s ease; }
        .dark #homeownerMap { background:#374151; border-color:#4b5563; }
        ::-webkit-scrollbar { width:6px; } ::-webkit-scrollbar-track { background:transparent; } ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }
        .dark body { background-color:#020617; }
    </style>
</head>
<body class="min-h-screen flex items-start justify-center p-4 sm:p-6 sm:py-10 relative overflow-y-auto overflow-x-hidden bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-gradient-to-br from-blue-300/30 to-purple-300/30 dark:from-blue-600/8 dark:to-purple-600/8 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-[400px] h-[400px] bg-gradient-to-tr from-pink-300/20 to-rose-300/20 dark:from-pink-600/8 dark:to-rose-600/8 rounded-full blur-3xl animate-float" style="animation-delay:3s;"></div>
    </div>

    <button @click="darkMode = !darkMode" class="fixed top-4 right-4 z-50 w-11 h-11 flex items-center justify-center rounded-2xl bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:scale-105 transition-all duration-300" title="Toggle theme">
        <i class="fas text-lg transition-all duration-500" :class="darkMode ? 'fa-sun text-yellow-400' : 'fa-moon text-gray-600'"></i>
    </button>

    <a href="/" class="fixed top-4 left-4 z-50 inline-flex items-center gap-2 px-4 py-2.5 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 rounded-2xl shadow-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300">
        <i class="fas fa-arrow-left text-xs"></i> Home
    </a>

    <div class="w-full max-w-lg relative z-10 animate-slide-up">
        <div class="text-center mb-6">
            <div class="relative inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25 mb-4 pulse-ring mx-auto"><i class="fas fa-home text-white text-2xl"></i></div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Join <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent gradient-animate">SmartClean</span> AI</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1.5">Find the best verified cleaners for your home</p>
        </div>

        <div class="glass-card rounded-3xl p-6 sm:p-7 shadow-2xl">
            <form id="homeownerForm" onsubmit="submitForm(event)">
                @csrf
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center"><i class="fas fa-user-circle text-blue-600 dark:text-blue-400 text-lg"></i></div>
                        <div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Your Details</h3><p class="text-xs text-gray-500 dark:text-gray-400">Basic account information</p></div>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">First Name <span class="text-red-500">*</span></label><input type="text" name="first_name" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder=""></div>
                            <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Last Name <span class="text-red-500">*</span></label><input type="text" name="last_name" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder=""></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email <span class="text-red-500">*</span></label><input type="email" name="email" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="your@email.com"></div>
                            <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Phone <span class="text-red-500">*</span></label><input type="tel" name="phone" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="+255 7XX XXX XXX"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password <span class="text-red-500">*</span></label><input type="password" name="password" required minlength="8" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="Min 8 characters"></div>
                            <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Confirm Password <span class="text-red-500">*</span></label><input type="password" name="password_confirmation" required minlength="8" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="Re-enter password"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900/40 dark:to-red-800/40 rounded-xl flex items-center justify-center"><i class="fas fa-map-marker-alt text-red-600 dark:text-red-400 text-lg"></i></div>
                        <div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Your Home Location</h3><p class="text-xs text-gray-500 dark:text-gray-400">So cleaners can find you</p></div>
                    </div>
                    <div id="homeownerMap" class="mb-3"></div>
                    <div class="flex items-center gap-2 mb-3">
                        <button type="button" onclick="useMyLocation()" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-green-500/20 hover:shadow-green-500/40 hover:scale-[1.02] transition-all duration-300"><i class="fas fa-location-crosshairs mr-1.5"></i> Detect My Location</button>
                        <div id="locationStatus" class="flex-1 text-xs text-center py-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border border-gray-100 dark:border-gray-700"><i class="fas fa-map-pin mr-1"></i> Set your location</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 space-y-3">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detected Address</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Street/Road</label><input type="text" id="detectedStreet" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                            <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">District/Ward</label><input type="text" id="detectedDistrict" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                            <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">City</label><input type="text" id="detectedCity" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                            <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Region</label><input type="text" id="detectedRegion" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3"><label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5"><i class="fas fa-home text-blue-500 mr-1"></i> House/Gate/Plot Number <span class="text-red-500">*</span></label><input type="text" name="house_number" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-blue-300 dark:border-blue-500 bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm font-medium focus:outline-none" placeholder="e.g., Plot 456, Gate 2, House 12"></div>
                    </div>
                    <input type="hidden" id="latitude" name="latitude"><input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" name="street" id="hiddenStreet"><input type="hidden" name="ward" id="hiddenWard"><input type="hidden" name="city_name" id="hiddenCity"><input type="hidden" name="region" id="hiddenRegion">
                </div>

                <button type="submit" id="submitBtn" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 hover:from-blue-600 hover:via-purple-600 hover:to-pink-600 text-white rounded-2xl font-bold text-base shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.01] transition-all duration-300"><i class="fas fa-check-circle mr-2"></i> Complete Registration</button>
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-5">Already have an account? <a href="/login" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold transition-colors">Sign in</a></p>
            </form>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&v=weekly"></script>
    <script>
        let map, marker, locationSet = false;
        function initMap() { const el = document.getElementById('homeownerMap'); if (!el || map) return; map = new google.maps.Map(el, { center: { lat: -6.7924, lng: 39.2083 }, zoom: 6, mapTypeControl: false, streetViewControl: false, fullscreenControl: false, styles: [{ featureType: "poi", stylers: [{ visibility: "off" }] }, { featureType: "transit", stylers: [{ visibility: "off" }] }] }); google.maps.event.addListener(map, 'click', function(e) { setLocation(e.latLng.lat(), e.latLng.lng()); }); detectMyLocation(); }
        function detectMyLocation() { updateLocationStatus('Detecting...', 'blue'); if (navigator.geolocation) { navigator.geolocation.getCurrentPosition(function(pos) { map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude }); map.setZoom(17); setLocation(pos.coords.latitude, pos.coords.longitude); }, function() { updateLocationStatus('Failed. Click map instead.', 'yellow'); }, { enableHighAccuracy: true, timeout: 15000 }); } }
        function reverseGeocode(lat, lng) { updateLocationStatus('Fetching address...', 'blue'); const geocoder = new google.maps.Geocoder(); geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, function(results, status) { if (status === 'OK' && results[0]) { const comps = results[0].address_components; let city = '', region = '', district = '', route = '', street_number = ''; for (let c of comps) { if (c.types.includes('locality')) city = c.long_name; if (c.types.includes('administrative_area_level_1')) region = c.long_name; if (c.types.includes('administrative_area_level_2')) district = c.long_name; if (c.types.includes('route')) route = c.long_name; if (c.types.includes('street_number')) street_number = c.long_name; } const fullStreet = (street_number ? street_number + ' ' : '') + (route || ''); document.getElementById('detectedStreet').value = fullStreet || 'Detected'; document.getElementById('detectedDistrict').value = district || city || ''; document.getElementById('detectedCity').value = city || district || ''; document.getElementById('detectedRegion').value = region || ''; document.getElementById('hiddenStreet').value = fullStreet; document.getElementById('hiddenWard').value = district || city || ''; document.getElementById('hiddenCity').value = city || district || region || ''; document.getElementById('hiddenRegion').value = region || ''; updateLocationStatus('✓ Location set!', 'green'); } }); }
        function setLocation(lat, lng) { if (marker) marker.setMap(null); marker = new google.maps.Marker({ position: { lat, lng }, map, draggable: true, animation: google.maps.Animation.DROP, icon: { url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' } }); document.getElementById('latitude').value = lat.toFixed(7); document.getElementById('longitude').value = lng.toFixed(7); locationSet = true; reverseGeocode(lat, lng); document.getElementById('homeownerMap').style.border = '3px solid #22c55e'; google.maps.event.addListener(marker, 'dragend', function() { const p = marker.getPosition(); document.getElementById('latitude').value = p.lat().toFixed(7); document.getElementById('longitude').value = p.lng().toFixed(7); reverseGeocode(p.lat(), p.lng()); }); }
        function updateLocationStatus(msg, type) { const el = document.getElementById('locationStatus'); const colors = { blue: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/20', green: 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-500/20', yellow: 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-500/10 border-yellow-200 dark:border-yellow-500/20' }; el.innerHTML = msg; el.className = 'flex-1 text-xs text-center py-2.5 rounded-xl font-medium border ' + colors[type]; }
        function useMyLocation() { if (!map) initMap(); else detectMyLocation(); }
        async function submitForm(event) { event.preventDefault(); if (!document.getElementById('latitude').value) { showFormMessage('Please set your home location on the map before submitting.', 'error'); return; } const form = event.target; const formData = new FormData(form); formData.append('user_type', 'homeowner'); const btn = document.getElementById('submitBtn'); const originalHTML = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Registering...'; try { const res = await fetch('/register/homeowner/submit', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: formData }); const data = await res.json(); if (data.success) { showFormMessage('Registration successful! Redirecting...', 'success'); btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Success!'; btn.classList.add('from-green-500', 'to-emerald-600'); setTimeout(() => window.location.href = data.redirect || '/homeowner/dashboard', 2000); } else { showFormMessage(data.message || 'Registration failed.', 'error'); btn.disabled = false; btn.innerHTML = originalHTML; } } catch (e) { showFormMessage('Network error.', 'error'); btn.disabled = false; btn.innerHTML = originalHTML; } }
        function showFormMessage(msg, type) { const existing = document.getElementById('formMessage'); if (existing) existing.remove(); const form = document.getElementById('homeownerForm'); const div = document.createElement('div'); div.id = 'formMessage'; if (type === 'success') { div.className = 'bg-green-50 dark:bg-green-500/10 border-2 border-green-300 dark:border-green-500/30 rounded-2xl p-5 text-center mb-5 animate-slide-up'; div.innerHTML = '<div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-check-circle text-green-500 text-2xl"></i></div><h3 class="text-base font-bold text-green-700 dark:text-green-300 mb-1">Success!</h3><p class="text-green-600 dark:text-green-400 text-sm">' + msg + '</p>'; } else { div.className = 'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl p-4 mb-5 animate-slide-up'; div.innerHTML = '<div class="flex items-start gap-3"><div class="w-8 h-8 bg-red-100 dark:bg-red-500/20 rounded-xl flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-circle text-red-500"></i></div><div><p class="font-bold text-red-700 dark:text-red-300 text-sm">Error</p><p class="text-red-600 dark:text-red-400 text-sm mt-1">' + msg + '</p></div></div>'; } form.insertBefore(div, form.firstChild); div.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        window.addEventListener('load', () => setTimeout(initMap, 500));
    </script>
</body>
</html>

