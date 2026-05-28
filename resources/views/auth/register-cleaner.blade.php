<!DOCTYPE html>
<html lang="en" class="scroll-smooth" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); }); document.documentElement.classList.toggle('dark', darkMode)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Become a Cleaner — SmartClean AI</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse-ring { 0% { box-shadow:0 0 0 0 rgba(34,197,94,0.4); } 70% { box-shadow:0 0 0 20px rgba(34,197,94,0); } 100% { box-shadow:0 0 0 0 rgba(34,197,94,0); } }
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
        #locationMap { width:100%; height:280px; border-radius:16px; background:#e5e7eb; border:3px solid #d1d5db; transition:all 0.3s ease; }
        .dark #locationMap { background:#374151; border-color:#4b5563; }
        .step-active { background:linear-gradient(135deg,#3b82f6,#8b5cf6); color:white; box-shadow:0 4px 15px rgba(59,130,246,0.3); }
        .step-inactive { background:#e5e7eb; color:#9ca3af; }
        .dark .step-inactive { background:#374151; color:#6b7280; }
        .line-active { background:linear-gradient(90deg,#3b82f6,#8b5cf6); }
        .line-inactive { background:#e5e7eb; }
        .dark .line-inactive { background:#374151; }
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }
        .dark body { background-color:#020617; }
    </style>
</head>
<body class="min-h-screen flex items-start justify-center p-4 sm:p-6 sm:py-10 relative overflow-y-auto overflow-x-hidden bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-gradient-to-br from-green-300/30 to-emerald-300/30 dark:from-green-600/8 dark:to-emerald-600/8 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-[400px] h-[400px] bg-gradient-to-tr from-blue-300/20 to-purple-300/20 dark:from-blue-600/8 dark:to-purple-600/8 rounded-full blur-3xl animate-float" style="animation-delay:3s;"></div>
    </div>

    <button @click="darkMode = !darkMode" class="fixed top-4 right-4 z-50 w-11 h-11 flex items-center justify-center rounded-2xl bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:scale-105 transition-all duration-300" title="Toggle theme">
        <i class="fas text-lg transition-all duration-500" :class="darkMode ? 'fa-sun text-yellow-400' : 'fa-moon text-gray-600'"></i>
    </button>

    <a href="/" class="fixed top-4 left-4 z-50 inline-flex items-center gap-2 px-4 py-2.5 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 rounded-2xl shadow-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300">
        <i class="fas fa-arrow-left text-xs"></i> Home
    </a>

    <div class="w-full max-w-2xl relative z-10 animate-slide-up">
        <div class="text-center mb-6">
            <div class="relative inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-600 rounded-2xl shadow-xl shadow-green-500/25 mb-4 pulse-ring mx-auto"><i class="fas fa-broom text-white text-2xl"></i></div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Become a <span class="bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Cleaner</span></h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1.5">Join SmartClean AI and start earning on your schedule</p>
        </div>

        <div class="glass-card rounded-3xl p-5 sm:p-7 shadow-2xl">
            <div class="flex items-center justify-center mb-7" id="stepIndicator">
                <div class="flex items-center gap-0">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300 step-active" id="stepDot1">1</div>
                    <div class="w-12 h-1 transition-all duration-300 line-active" id="stepLine1"></div>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300 step-inactive" id="stepDot2">2</div>
                    <div class="w-12 h-1 transition-all duration-300 line-inactive" id="stepLine2"></div>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300 step-inactive" id="stepDot3">3</div>
                </div>
            </div>

            <div id="formMessageContainer"></div>
            
            <form id="cleanerForm" class="space-y-0">
                @csrf
                
                {{-- STEP 1 --}}
                <div id="step1" class="space-y-4 animate-slide-up">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/40 rounded-xl flex items-center justify-center"><i class="fas fa-user-circle text-blue-600 dark:text-blue-400 text-lg"></i></div>
                        <div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Personal Information</h3><p class="text-xs text-gray-500 dark:text-gray-400">Tell us about yourself</p></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">First Name <span class="text-red-500">*</span></label><input type="text" name="first_name" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder=""></div>
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Last Name <span class="text-red-500">*</span></label><input type="text" name="last_name" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder=""></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email <span class="text-red-500">*</span></label><input type="email" name="email" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="your@email.com"></div>
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Phone <span class="text-red-500">*</span></label><input type="tel" name="phone" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="+255 7XX XXX XXX"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gender</label><select name="gender" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700/60 dark:text-white text-sm font-medium focus:outline-none"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option></select></div>
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Date of Birth</label><input type="date" name="date_of_birth" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none"></div>
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">NIDA Number</label><input type="text" name="national_id" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-xs font-medium focus:outline-none placeholder:text-gray-400" placeholder="Optional"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password <span class="text-red-500">*</span></label><input type="password" name="password" required minlength="8" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="Min 8 characters"></div>
                        <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Confirm Password <span class="text-red-500">*</span></label><input type="password" name="password_confirmation" required minlength="8" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="Re-enter password"></div>
                    </div>
                    <button type="button" onclick="goToStep(2)" class="w-full px-6 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-base shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.01] transition-all duration-300">Continue <i class="fas fa-arrow-right ml-2"></i></button>
                </div>

                {{-- STEP 2: LOCATION (preserved full functionality) --}}
                <div id="step2" class="space-y-4 hidden">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900/40 dark:to-red-800/40 rounded-xl flex items-center justify-center"><i class="fas fa-map-marker-alt text-red-600 dark:text-red-400 text-lg"></i></div>
                        <div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Your Location</h3><p class="text-xs text-gray-500 dark:text-gray-400">Set your service area</p></div>
                    </div>
                    <div id="locationMessage" class="mb-3 px-4 py-3 rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-500/20"><i class="fas fa-info-circle mr-1.5"></i> <span id="locationText">Click on the map to set your location</span></div>
                    <div id="locationMap"></div>
                    <div class="flex gap-2 mt-3">
                        <button type="button" onclick="useMyLocation()" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-green-500/20 hover:shadow-green-500/40 hover:scale-[1.02] transition-all duration-300"><i class="fas fa-location-crosshairs mr-1.5"></i> Detect My Location</button>
                        <button type="button" onclick="clearLocation()" class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300" id="clearBtn" style="display:none;"><i class="fas fa-times mr-1.5"></i> Clear</button>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 space-y-3">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detected Address</p>
                        <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Street/Road</label><input type="text" id="detectedStreet" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Will be detected automatically..."></div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">District/Ward</label><input type="text" id="detectedDistrict" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                            <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">City</label><input type="text" id="detectedCity" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                        </div>
                        <div><label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Region</label><input type="text" id="detectedRegion" readonly class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs cursor-not-allowed" placeholder="Auto-detected..."></div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3"><label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5"><i class="fas fa-home text-blue-500 mr-1"></i> House/Gate/Plot Number <span class="text-red-500">*</span></label><input type="text" name="house_number" required class="input-premium w-full px-4 py-3 rounded-xl border-2 border-blue-300 dark:border-blue-500 bg-white dark:bg-gray-700 text-gray-800 dark:text-white text-sm font-medium focus:outline-none" placeholder="e.g., Plot 123, Gate 5"></div>
                    </div>
                    <input type="hidden" id="latitude" name="latitude"><input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" name="street" id="hiddenStreet"><input type="hidden" name="ward" id="hiddenWard"><input type="hidden" name="city_name" id="hiddenCity"><input type="hidden" name="region" id="hiddenRegion">
                    <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><i class="fas fa-circle-notch text-purple-500 mr-1"></i> Max Service Radius (km)</label><input type="number" name="max_service_radius_km" value="30" min="5" max="100" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none"></div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="goToStep(1)" class="px-5 py-3.5 border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-2xl font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300"><i class="fas fa-arrow-left mr-1.5"></i> Back</button>
                        <button type="button" onclick="goToStep(3)" class="flex-1 px-5 py-3.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.01] transition-all duration-300">Continue <i class="fas fa-arrow-right ml-1.5"></i></button>
                    </div>
                </div>

                {{-- STEP 3: EQUIPMENT & SUBMIT --}}
                <div id="step3" class="space-y-4 hidden">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-xl flex items-center justify-center"><i class="fas fa-clipboard-check text-green-600 dark:text-green-400 text-lg"></i></div>
                        <div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Equipment & Submit</h3><p class="text-xs text-gray-500 dark:text-gray-400">Final step — almost there!</p></div>
                    </div>
                    <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><i class="fas fa-toolbox text-orange-500 mr-1"></i> Equipment</label><select name="has_equipment" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700/60 dark:text-white text-sm font-medium focus:outline-none"><option value="yes">Yes, I have all equipment</option><option value="partial">I have some equipment</option><option value="no">No, I need equipment provided</option></select></div>
                    <div><label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5"><i class="fas fa-pen text-purple-500 mr-1"></i> About Yourself</label><textarea name="bio" rows="3" class="input-premium w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:text-white text-sm font-medium focus:outline-none placeholder:text-gray-400" placeholder="Tell us about your cleaning experience..."></textarea></div>
                    <div class="bg-yellow-50 dark:bg-yellow-500/10 rounded-2xl p-4 border border-yellow-200 dark:border-yellow-500/20"><label class="flex items-start gap-2.5 cursor-pointer"><input type="checkbox" name="terms" required class="mt-0.5 w-4 h-4 rounded-lg border-2 border-yellow-300 dark:border-yellow-600 text-yellow-600 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-0"><span class="text-xs text-yellow-800 dark:text-yellow-300 font-medium">I agree to the <a href="#" class="underline font-bold">Terms of Service</a>. Applications are reviewed within 24-48 hours.</span></label></div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="goToStep(2)" class="px-5 py-3.5 border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-2xl font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300"><i class="fas fa-arrow-left mr-1.5"></i> Back</button>
                        <button type="button" onclick="submitRegistration()" id="submitBtn" class="flex-1 px-5 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-2xl font-bold text-sm shadow-xl shadow-green-500/25 hover:shadow-green-500/40 hover:scale-[1.01] transition-all duration-300"><i class="fas fa-paper-plane mr-1.5"></i> Submit Application</button>
                    </div>
                </div>
            </form>
        </div>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-5">Already have an account? <a href="/login" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold transition-colors">Sign in</a></p>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&v=weekly"></script>
    <script>
        let map, marker, locationSet = false, currentStep = 1;
        function updateSteps(step) {
            document.querySelectorAll('#step1, #step2, #step3').forEach(el => el.classList.add('hidden'));
            document.getElementById('step' + step).classList.remove('hidden');
            for (let i = 1; i <= 3; i++) {
                const dot = document.getElementById('stepDot' + i);
                dot.className = step >= i ? 'w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300 step-active' : 'w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-all duration-300 step-inactive';
            }
            document.getElementById('stepLine1').className = 'w-12 h-1 transition-all duration-300 ' + (step >= 2 ? 'line-active' : 'line-inactive');
            document.getElementById('stepLine2').className = 'w-12 h-1 transition-all duration-300 ' + (step >= 3 ? 'line-active' : 'line-inactive');
        }
        function goToStep(step) { currentStep = step; updateSteps(step); if (step === 2) setTimeout(initMap, 300); document.querySelector('.glass-card').scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        function initMap() { const el = document.getElementById('locationMap'); if (!el || map) return; map = new google.maps.Map(el, { center: { lat: -6.7924, lng: 39.2083 }, zoom: 6, mapTypeControl: false, streetViewControl: false, fullscreenControl: false, styles: [{ featureType: "poi", stylers: [{ visibility: "off" }] }, { featureType: "transit", stylers: [{ visibility: "off" }] }] }); google.maps.event.addListener(map, 'click', function(e) { setLocation(e.latLng.lat(), e.latLng.lng()); }); detectMyLocation(); }
        function detectMyLocation() { updateStatus('Detecting your location...', 'blue'); if (navigator.geolocation) { navigator.geolocation.getCurrentPosition(function(pos) { map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude }); map.setZoom(17); setLocation(pos.coords.latitude, pos.coords.longitude); }, function() { updateStatus('Could not detect. Click on map to set location.', 'yellow'); }, { enableHighAccuracy: true, timeout: 15000 }); } }
        function reverseGeocode(lat, lng) { updateStatus('Fetching address...', 'blue'); new google.maps.Geocoder().geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, function(results, status) { if (status === 'OK' && results[0]) { const comps = results[0].address_components; let city = '', region = '', district = '', route = '', street_number = ''; for (let c of comps) { if (c.types.includes('locality')) city = c.long_name; if (c.types.includes('administrative_area_level_1')) region = c.long_name; if (c.types.includes('administrative_area_level_2')) district = c.long_name; if (c.types.includes('route')) route = c.long_name; if (c.types.includes('street_number')) street_number = c.long_name; } const fullStreet = (street_number ? street_number + ' ' : '') + (route || ''); document.getElementById('detectedStreet').value = fullStreet || 'Detected'; document.getElementById('detectedDistrict').value = district || city || ''; document.getElementById('detectedCity').value = city || district || ''; document.getElementById('detectedRegion').value = region || ''; document.getElementById('hiddenStreet').value = fullStreet; document.getElementById('hiddenWard').value = district || city || ''; document.getElementById('hiddenCity').value = city || district || region || ''; document.getElementById('hiddenRegion').value = region || ''; updateStatus('✓ Location set successfully!', 'green'); } }); }
        function setLocation(lat, lng) { if (marker) marker.setMap(null); marker = new google.maps.Marker({ position: { lat, lng }, map, draggable: true, animation: google.maps.Animation.DROP, icon: { url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' } }); document.getElementById('latitude').value = lat.toFixed(7); document.getElementById('longitude').value = lng.toFixed(7); locationSet = true; reverseGeocode(lat, lng); document.getElementById('clearBtn').style.display = 'inline-flex'; document.getElementById('locationMap').style.border = '3px solid #22c55e'; google.maps.event.addListener(marker, 'dragend', function() { const p = marker.getPosition(); document.getElementById('latitude').value = p.lat().toFixed(7); document.getElementById('longitude').value = p.lng().toFixed(7); reverseGeocode(p.lat(), p.lng()); }); }
        function updateStatus(msg, type) { const colors = { blue: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-500/20', green: 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 border-green-200 dark:border-green-500/20', yellow: 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-500/20' }; document.getElementById('locationText').textContent = msg; document.getElementById('locationMessage').className = 'mb-3 px-4 py-3 rounded-2xl ' + colors[type] + ' text-xs font-medium border'; }
        function useMyLocation() { if (!map) initMap(); else detectMyLocation(); }
        function clearLocation() { if (marker) marker.setMap(null); marker = null; locationSet = false; ['latitude','longitude','detectedStreet','detectedDistrict','detectedCity','detectedRegion','hiddenStreet','hiddenWard','hiddenCity','hiddenRegion'].forEach(id => { var el = document.getElementById(id); if (el) el.value = ''; }); updateStatus('Click on the map to set your location', 'blue'); document.getElementById('locationMap').style.border = '3px solid #d1d5db'; document.getElementById('clearBtn').style.display = 'none'; }
        async function submitRegistration() {
            if (!document.getElementById('latitude').value) { showFormMessage('Please set your location on the map before submitting.', 'error'); goToStep(2); return; }
            var houseNumber = document.querySelector('[name="house_number"]'); if (!houseNumber || !houseNumber.value.trim()) { showFormMessage('Please enter your house/gate/plot number.', 'error'); return; }
            var formData = new FormData(); formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            ['first_name','last_name','email','phone','password','password_confirmation','gender','date_of_birth','national_id'].forEach(n => formData.append(n, document.querySelector('[name="' + n + '"]').value));
            formData.append('city_name', document.getElementById('hiddenCity').value || document.getElementById('detectedCity').value || '');
            formData.append('region', document.getElementById('hiddenRegion').value || document.getElementById('detectedRegion').value || '');
            formData.append('street', document.getElementById('hiddenStreet').value || document.getElementById('detectedStreet').value || '');
            formData.append('ward', document.getElementById('hiddenWard').value || document.getElementById('detectedDistrict').value || '');
            formData.append('house_number', houseNumber.value); formData.append('latitude', document.getElementById('latitude').value); formData.append('longitude', document.getElementById('longitude').value);
            formData.append('max_service_radius_km', document.querySelector('[name="max_service_radius_km"]').value || '30');
            formData.append('has_equipment', document.querySelector('[name="has_equipment"]').value || 'yes');
            formData.append('bio', document.querySelector('[name="bio"]').value || ''); formData.append('terms', document.querySelector('[name="terms"]').checked ? '1' : '0'); formData.append('user_type', 'cleaner');
            var btn = document.getElementById('submitBtn'); var originalText = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Submitting...';
            try { var res = await fetch('/register/cleaner/submit', { method: 'POST', headers: { 'Accept': 'application/json' }, body: formData }); var data = await res.json();
                if (data.success) { showFormMessage(data.message || 'Registration submitted successfully!', 'success'); btn.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Submitted!'; btn.classList.add('bg-green-600', 'from-green-600', 'to-green-600'); setTimeout(function() { window.location.href = '/'; }, 2500); }
                else { showFormMessage(data.message || 'Registration failed.', 'error'); btn.disabled = false; btn.innerHTML = originalText; }
            } catch (e) { showFormMessage('Network error.', 'error'); btn.disabled = false; btn.innerHTML = originalText; }
        }
        function showFormMessage(msg, type) { var existing = document.getElementById('formMessage'); if (existing) existing.remove(); var container = document.getElementById('formMessageContainer'); var div = document.createElement('div'); div.id = 'formMessage';
            if (type === 'success') { div.className = 'bg-green-50 dark:bg-green-500/10 border-2 border-green-300 dark:border-green-500/30 rounded-2xl p-6 text-center mb-5 animate-slide-up'; div.innerHTML = '<div class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/40 dark:to-emerald-800/40 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-check-circle text-green-500 text-3xl"></i></div><h3 class="text-lg font-bold text-green-700 dark:text-green-300 mb-2">Application Submitted!</h3><p class="text-green-600 dark:text-green-400 text-sm">' + msg + '</p>'; }
            else { div.className = 'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl p-4 mb-5 animate-slide-up'; div.innerHTML = '<div class="flex items-start gap-3"><div class="w-8 h-8 bg-red-100 dark:bg-red-500/20 rounded-xl flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-circle text-red-500"></i></div><div><p class="font-bold text-red-700 dark:text-red-300 text-sm">Error</p><p class="text-red-600 dark:text-red-400 text-sm mt-1">' + msg + '</p></div></div>'; }
            container.appendChild(div); div.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        window.addEventListener('load', () => setTimeout(initMap, 500));
    </script>
</body>
</html>

