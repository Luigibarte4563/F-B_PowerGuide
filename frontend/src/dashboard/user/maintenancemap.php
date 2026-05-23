<?php

session_start();

require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();

$isGoogleUser =
    !empty($user['google_id']) ||
    ($user['auth_provider'] ?? '') === 'google';

$defaultPicture = "https://i.imgur.com/8Km9tLL.png";
$picture = $user['picture'] ?? $defaultPicture;

$current_user_id = $user['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerGuide Maintenance Map</title>

    <!-- CDN Deliveries (Tailwind, Montserrat, Leaflet Map) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body { font-family: 'Montserrat', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0D0E2A; }
        ::-webkit-scrollbar-thumb { background-color: #31324C; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background-color: #555; }
        * { scrollbar-width: thin; scrollbar-color: #31324C transparent; }
        
        .leaflet-popup-content-wrapper {
            background: #1A1B33;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .leaflet-popup-tip { background: #1A1B33; }
        .leaflet-container a.leaflet-popup-close-button { color: #fff; }
        
        /* Dark Theme Inversion for Map */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-container {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased">

    <!-- Mobile Menu Toggle -->
    <button id="menuToggle" class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg shadow-md hover:bg-opacity-80 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div id="overlay" class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden"></div>

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR NAV -->
        <nav id="sidebar" class="flex flex-col fixed lg:sticky top-0 h-screen w-[280px] lg:w-[340px] text-[#B5B5B5] text-center pt-8 px-5 border-r-2 border-white/10 bg-[#03041A] z-40 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <!-- Logo -->
            <div class="flex items-center gap-3 ml-4 mb-8">
                <div class="w-10 h-10 md:w-12 h-12 bg-gradient-to-br from-[#FFBB02] to-[#E39A00] rounded-xl flex items-center justify-center shadow-lg shadow-[#FFBB02]/10">
                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                    </svg>
                </div>
                <div class="flex flex-col justify-center items-start">
                    <span class="text-white font-bold text-lg md:text-xl tracking-tight leading-tight">
                        POWER<span class="text-[#FFBB02]">GUIDE</span>
                    </span>
                    <span class="text-white font-semibold text-[9px] md:text-[10px] tracking-widest opacity-60 leading-none mt-0.5">
                        SECURITY AND RELIABILITY
                    </span>
                </div>
            </div>

            <!-- Nav Links -->
            <div class="flex flex-col gap-1.5 text-left">
                <span class="text-[11px] font-bold tracking-widest text-white px-4 pt-2 mb-2 opacity-50">MAIN MENU</span>

                <a href="dashboard.php" class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="maintenancemap.php" class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 4L9 7" />
                    </svg>
                    <span>Maintenance Map</span>
                </a>

                <a href="findhubs.php" class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Find Hubs</span>
                </a>

                <span class="text-[11px] font-bold tracking-widest text-white px-4 pt-4 mb-2 opacity-50">COMMUNITY</span>

                <a href="settings.php" class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>
            </div>

            <!-- Profile Info Panel -->
            <div class="flex flex-row items-center justify-between gap-3 px-4 py-3 mb-8 mt-auto rounded-2xl bg-[#31324C]/20 border border-white/5 text-left">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="h-10 w-10 rounded-xl overflow-hidden border border-[#FFBB02]/30 flex-shrink-0 bg-[#31324C]">
                        <img src="<?= htmlspecialchars($picture) ?>" alt="User Avatar" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <span class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="text-[10px] font-medium text-[#B5B5B5] truncate"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                </div>
                <a href="<?= BACKEND_URL ?>/public/logout.php" class="p-2 text-[#B5B5B5] hover:text-[#CB3435] hover:bg-[#CB3435]/10 rounded-xl transition-all flex-shrink-0 group" title="Logout">
                    <svg class="w-5 h-5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
        </nav>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 overflow-y-auto bg-[#03041A]">

            <header class="mx-4 lg:mx-8 mt-14 lg:mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Interactive Maintenance Map</h1>
                    <span class="text-xs lg:text-sm text-[#B5B5B5] flex items-center gap-2 mt-1">
                        Grid status:
                        <span class="flex items-center gap-1.5 text-[#00BA00] font-medium" id="activeMaintenanceCounter">
                            Synchronizing live schedule...
                        </span>
                    </span>
                </div>

                <div class="flex items-center gap-4 self-end sm:self-auto">
                    <!-- Search Field -->
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="search" id="mapSearch" oninput="filterBarangay(this.value)" placeholder="Search barangay..." class="w-[240px] sm:w-[280px] h-11 pl-10 pr-4 rounded-xl bg-[#31324C]/40 border border-white/5 text-sm font-medium outline-none placeholder:text-white/40 focus:border-[#FFBB02] transition-colors focus:bg-[#03041A]">
                    </div>

                    <!-- Profile Avatar -->
                    <div id="profileBtn" class="h-10 w-10 rounded-xl border-2 border-[#FFBB02] bg-[#31324C] overflow-hidden flex items-center justify-center shadow-lg transition-all transform hover:scale-105">
                        <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar" class="h-full w-full object-cover">
                    </div>
                </div>
            </header>

            <section class="flex flex-col lg:flex-row justify-between min-h-0 py-2 px-4 lg:px-8 gap-6">

                <!-- LEFT SIDE: Leaflet Map -->
                <div class="flex-1 min-w-0">
                    <div class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#31324C]/20 flex flex-col relative h-[440px] lg:h-[550px]">
                        <div id="map" class="w-full h-full bg-[#0E0F26]"></div>

                        <!-- Legend -->
                        <div class="absolute bottom-4 left-4 border border-white/10 bg-[#1A1B33]/95 rounded-2xl z-[1000] p-3 shadow-xl backdrop-blur-md">
                            <div class="flex flex-col gap-1.5 min-w-[130px]">
                                <span class="font-bold text-[10px] tracking-widest text-white/40 block mb-0.5">MAP LEGEND</span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#e74c3c] mr-2 block shadow-sm"></span>
                                    Pending Maintenance
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#3498db] mr-2 block shadow-sm"></span>
                                    Ongoing Maintenance
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE: List Feed Panel -->
                <div class="w-full lg:w-[420px] lg:flex-shrink-0">
                    <div class="bg-[#31324C]/20 border border-white/5 p-5 rounded-2xl h-[440px] lg:h-[550px] flex flex-col relative overflow-hidden shadow-xl">

                        <!-- Cleaned Filter Header -->
                        <div class="flex flex-row border-b border-white/5 pb-3.5 justify-between items-center bg-transparent">
                            <span class="text-[12px] font-bold text-white">Maintenance Schedule</span>
                            <span class="text-[10px] text-[#B5B5B5] font-bold rounded-lg bg-[#31324C]/60 border border-white/10 px-2.5 py-1 tracking-wider uppercase">
                                STATUS: ACTIVE
                            </span>
                        </div>

                        <!-- Maintenance Data Feed -->
                        <div id="maintenanceFeed" class="flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-3.5 pt-3.5 pr-1">
                            <p class="text-xs text-white/40 text-center py-8">Loading schedule vectors...</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MAIN LEAFLET MAP IMPLEMENTATION ENGINE -->
    <script>
        const API_URL = 'http://localhost/crowdsourcedAPI/api/maintenance_map/get.php'; 
        
        let map;
        let layersGroup = L.layerGroup();
        let allMarkers = [];

        // Hardcoded authoritative spatial bounds for Dagupan Barangays
        const barangayData = {
            "Bonuan Gueset": { lat:16.0585, lng:120.3345 },
            "Bonuan Boquig": { lat:16.0600, lng:120.3200 },
            "Bonuan Binloc": { lat:16.0620, lng:120.3100 },
            "Lucao": { lat:16.0435, lng:120.3310 },
            "Tapuac": { lat:16.0460, lng:120.3450 },
            "Tambac": { lat:16.0520, lng:120.3400 },
            "Pantal": { lat:16.0468, lng:120.3330 },
            "Bacayao Norte": { lat:16.0300, lng:120.3200 },
            "Bacayao Sur": { lat:16.0250, lng:120.3250 },
            "Malued": { lat:16.0400, lng:120.3200 },
            "Mayombo": { lat:16.0480, lng:120.3100 },
            "Mangin": { lat:16.0550, lng:120.3500 },
            "Tebeng": { lat:16.0600, lng:120.3450 },
            "Pogo Chico": { lat:16.0510, lng:120.3600 },
            "Pogo Grande": { lat:16.0550, lng:120.3650 },
            "Herrero": { lat:16.0450, lng:120.3350 },
            "Poblacion Centro": { lat:16.0430, lng:120.3335 },
            "Poblacion Oeste": { lat:16.0410, lng:120.3300 },
            "Poblacion Este": { lat:16.0440, lng:120.3360 }
        };

        // Initialize Map bounded to Dagupan Area
        function initMap() {
            map = L.map('map', {
                zoomControl: true,
                minZoom: 12,
                maxZoom: 15,
                maxBounds: [
                    [15.95, 120.25],
                    [16.15, 120.45]
                ],
                maxBoundsViscosity: 1.0
            }).setView([16.0431, 120.3330], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            layersGroup.addTo(map);
        }

        async function fetchMaintenanceData() {
            try {
                const response = await fetch(API_URL);
                if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                
                // 1. Fetch as raw text first to defensively handle stringified JSON issues
                const rawText = await response.text();
                
                let result;
                try {
                    result = JSON.parse(rawText);
                } catch (e) {
                    throw new Error("API did not return valid JSON. Response starts with: " + rawText.substring(0, 50));
                }

                // 2. Handle double-encoded JSON (where the backend returns a JSON string instead of an object)
                if (typeof result === 'string') {
                    try {
                        result = JSON.parse(result);
                    } catch (e) {
                        console.warn("Could not parse inner stringified JSON");
                    }
                }

                console.log("🔍 DEBUG - RAW API RESPONSE:", result);

                // 3. Flexibly extract the array depending on backend structure
                let data = [];
                if (Array.isArray(result)) {
                    // Scenario A: API returns a flat array [...]
                    data = result;
                } else if (result && typeof result === 'object') {
                    // Scenario B: API returns { data: [...] } or { success: true, data: [...] }
                    if (Array.isArray(result.data)) {
                        data = result.data;
                    } else if (result.success === false) {
                        throw new Error(result.message || "API reported failure status.");
                    } else {
                        console.warn("⚠️ DEBUG - Object received but 'data' array is missing.", result);
                    }
                }

                console.log("✅ DEBUG - EXTRACTED DATA ARRAY:", data);
                
                layersGroup.clearLayers();
                allMarkers = [];
                const feedContainer = document.getElementById('maintenanceFeed');
                feedContainer.innerHTML = '';
                
                const now = new Date();
                let activeCount = 0;

                // 4. Safely iterate over verified data array
                data.forEach(item => {
                    if (!item || typeof item !== 'object') return; // Skip invalid items

                    const startTime = new Date(item.start_time);
                    const endTime = new Date(item.end_time);
                    let computedStatus = 'done';

                    if (now < startTime) {
                        computedStatus = 'pending';
                    } else if (now >= startTime && now <= endTime) {
                        computedStatus = 'ongoing';
                    }

                    const dbStatus = (item.status || '').toLowerCase();
                    if (computedStatus === 'done' || computedStatus === 'complete' || dbStatus === 'done' || dbStatus === 'complete') {
                        return; // Skip this event entirely
                    }

                    activeCount++;
                    
                    const timeOpts = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    const timeStr = `${startTime.toLocaleString('en-US', timeOpts)} - ${endTime.toLocaleString('en-US', timeOpts)}`;

                    const circleColor = computedStatus === 'pending' ? '#e74c3c' : '#3498db';
                    const badgeBg = computedStatus === 'pending' ? 'bg-[#e74c3c]' : 'bg-[#3498db]';

                    // Render to side feed
                    feedContainer.innerHTML += `
                        <div class="bg-[#1A1B33]/80 p-4 rounded-xl border border-white/5 border-l-4" style="border-left-color: ${circleColor}">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-sm text-white">${item.company || 'Utility Provider'}</span>
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-md text-white uppercase ${badgeBg}">
                                    ${computedStatus}
                                </span>
                            </div>
                            <div class="text-xs text-white/60 mb-2 font-medium">${timeStr}</div>
                            <div class="text-[11px] text-[#B5B5B5] mb-2 font-bold uppercase tracking-wider">Affected: ${item.barangay}</div>
                            <div class="text-xs text-white/80 line-clamp-2 leading-relaxed">${item.description || 'Routine system maintenance.'}</div>
                        </div>
                    `;

                    const affectedAreas = (item.barangay || '').split(',').map(b => b.trim());

                    // Render Map Markers
                    affectedAreas.forEach(bName => {
                        const coords = barangayData[bName]; // Match exactly with hardcoded dictionary
                        if (coords) {
                            const marker = L.circleMarker([coords.lat, coords.lng], {
                                radius: 14,
                                fillColor: circleColor,
                                color: circleColor,
                                weight: 2,
                                opacity: 0.8,
                                fillOpacity: 0.4
                            }).addTo(layersGroup)
                            .bindPopup(`
                                <div class="p-1 min-w-[200px]">
                                    <div class="font-bold text-lg text-white mb-2">${bName}</div>
                                    <div class="text-xs text-[#B5B5B5] mb-1"><b>Company:</b> <span class="text-white">${item.company || 'Utility Provider'}</span></div>
                                    <div class="text-xs text-[#B5B5B5] mb-1"><b>Status:</b> <span style="color:${circleColor}; font-weight:bold" class="uppercase">${computedStatus}</span></div>
                                    <div class="text-xs text-[#B5B5B5] mb-3"><b>Schedule:</b> <br><span class="text-white">${timeStr}</span></div>
                                    <div class="text-xs text-white/80 pt-3 border-t border-white/10 leading-relaxed">${item.description || 'System maintenance operation.'}</div>
                                </div>
                            `);
                            
                            allMarkers.push({ marker: marker, name: bName.toLowerCase() });
                        } else {
                            console.warn(`⚠️ DEBUG - Barangay '${bName}' not found in spatial bounds dictionary.`);
                        }
                    });
                });

                // Update Header UI Counter
                const counterElement = document.getElementById('activeMaintenanceCounter');
                if (activeCount === 0) {
                    counterElement.innerHTML = `<span class="text-[#B5B5B5]">No scheduled interventions</span>`;
                    feedContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full opacity-40 py-10">
                            <svg class="w-10 h-10 mb-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs font-semibold">Grid operates optimally</span>
                        </div>`;
                } else {
                    counterElement.innerHTML = `<span class="text-[#00BA00]">${activeCount} active operation(s)</span>`;
                }

            } catch (error) {
                console.error("Failed to fetch maintenance schedule:", error);
                document.getElementById('maintenanceFeed').innerHTML = `
                    <div class="text-xs text-[#e74c3c] bg-[#e74c3c]/10 border border-[#e74c3c]/20 p-3 rounded-lg text-center mt-4">
                        Failed to synchronize data: ${error.message}. Retrying...
                    </div>`;
            }
        }

        // Barangay client-side search filtering
        function filterBarangay(searchTerm) {
            const term = searchTerm.toLowerCase().trim();
            allMarkers.forEach(item => {
                if(term === '') {
                    item.marker.setStyle({ opacity: 0.8, fillOpacity: 0.4 });
                } else if(item.name.includes(term)) {
                    item.marker.setStyle({ opacity: 1, fillOpacity: 0.8 });
                    item.marker.bringToFront();
                } else {
                    item.marker.setStyle({ opacity: 0.1, fillOpacity: 0.05 });
                }
            });
        }

        // Initialize Map and Kickoff Lifecycle Polling
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            fetchMaintenanceData();
            
            // Auto refresh every 30 seconds
            setInterval(fetchMaintenanceData, 30000);
        });

        // Mobile Menu Script Toggle Logic
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const isClosed = sidebar.classList.contains('-translate-x-full');

            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });

        document.getElementById('overlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            this.classList.add('hidden');
        });
    </script>
</body>
</html>