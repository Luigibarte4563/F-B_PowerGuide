<?php
session_start();

// Ensure these paths match your actual backend structure
require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();
$picture = $user['picture'] ?? "https://i.imgur.com/8Km9tLL.png";
$current_user_id = $user['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Map | PowerGuide</title>

    <!-- CDN Deliveries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #02030A;
            color: #E2E8F0;
        }

        /* Modernized Scrollbar matching exact dimensions */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #1E293B;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #334155;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: #1E293B transparent;
        }

        /* Leaflet Dark Theme Integration */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-control-attribution {
            filter: none;
        }

        /* Glassmorphic Popups matching the aesthetic */
        .leaflet-popup-content-wrapper {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(8px);
            color: #F8FAFC !important;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            padding: 4px;
        }

        .leaflet-popup-tip {
            background: rgba(15, 23, 42, 0.95) !important;
        }

        .leaflet-container a.leaflet-popup-close-button {
            color: #94A3B8 !important;
            padding: 8px;
            transition: color 0.2s;
        }

        .leaflet-container a.leaflet-popup-close-button:hover {
            color: #FFF !important;
        }

        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="antialiased text-white h-screen overflow-hidden flex">

    <!-- Mobile Menu Toggle -->
    <button id="menuToggle"
        class="fixed top-4 left-4 z-50 lg:hidden bg-[#0F172A] border border-white/10 p-2 rounded-lg shadow-xl hover:bg-[#1E293B] transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    <div id="overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity"></div>

    <!-- SIDEBAR -->
    <nav id="sidebar" class="flex flex-col fixed lg:sticky top-0 h-screen w-[280px] lg:w-[340px]
                text-[#B5B5B5] text-center pt-8 px-5
                border-r-2 border-white/10 bg-[#03041A] z-40
                -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

        <!-- Logo -->
        <div class="flex items-center gap-3 ml-4 mb-8">
            <div
                class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-[#FFBB02] to-[#E39A00] rounded-xl flex items-center justify-center shadow-lg shadow-[#FFBB02]/10">
                <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
            </div>
            <div class="flex flex-col justify-center items-start">
                <span class="text-white font-bold text-lg md:text-xl tracking-tight leading-tight">
                    POWER<span class="text-[#FFBB02]">GUIDE</span>
                </span>
                <span
                    class="text-white font-semibold text-[9px] md:text-[10px] tracking-widest opacity-60 leading-none mt-0.5">
                    SECURITY AND RELIABILITY
                </span>
            </div>
        </div>

        <!-- Nav Links -->
        <div class="flex flex-col gap-1.5 text-left">
            <span class="text-[11px] font-bold tracking-widest text-white px-4 pt-2 mb-2 opacity-50">MAIN
                MENU</span>

            <a href="dashboard.php"
                class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="outagemap.php"
                class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 4L9 7" />
                </svg>
                <span>Outage Map</span>
            </a>

            <a href="findhubs.php"
                class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Find Hubs</span>
            </a>

            <!-- Added Maintenance Map -->
            <a href="maintenancemap.php"
                class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black text-black transition-colors" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Maintenance Map</span>
            </a>

            <span class="text-[11px] font-bold tracking-widest text-white px-4 pt-4 mb-2 opacity-50">COMMUNITY</span>

            <a href="settings.html"
                class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Settings</span>
            </a>
        </div>

        <!-- Pro Tip -->
        <div class="flex flex-col text-left mt-auto mb-3 mx-2 p-5 rounded-2xl bg-[#31324C]/30 border border-white/5">
            <span class="text-[#FEBB02] text-xs font-bold tracking-wider mb-1">PRO TIP</span>
            <span class="text-white/50 text-xs font-normal leading-relaxed">Lower screen brightness to 40% to save
                roughly 15 minutes of device runtime.</span>
        </div>

        <!-- Profile Info Panel -->
        <div
            class="flex flex-row items-center justify-between gap-3 px-4 py-3 mb-8 rounded-2xl bg-[#31324C]/20 border border-white/5 text-left">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-xl overflow-hidden border border-[#FFBB02]/30 flex-shrink-0 bg-[#31324C]">
                    <img src="<?= htmlspecialchars($picture) ?>" alt="User Avatar" class="h-full w-full object-cover">
                </div>
                <div class="min-w-0 flex flex-col">
                    <span class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['name']) ?></span>
                    <span
                        class="text-[10px] font-medium text-[#B5B5B5] truncate"><?= htmlspecialchars($user['email']) ?></span>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="<?= BACKEND_URL ?>/public/logout.php"
                class="p-2 text-[#B5B5B5] hover:text-[#CB3435] hover:bg-[#CB3435]/10 rounded-xl transition-all flex-shrink-0 group"
                title="Logout">
                <svg class="w-5 h-5 transform group-hover:translate-x-0.5 transition-transform" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto bg-[#02030A]">

        <!-- HEADER -->
        <header
            class="mx-4 lg:mx-8 mt-14 lg:mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <!-- Title and Status -->
            <div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-white">Scheduled Maintenance</h1>
            </div>

            <!-- Actions and Search -->
            <div class="flex items-center gap-4 self-end sm:self-auto w-full sm:w-auto">
                <div class="relative w-full sm:w-auto">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="search" id="mapSearch" oninput="handleSearch(this.value)"
                        placeholder="Search barangay or provider..."
                        class="w-full sm:w-[280px] h-11 pl-10 pr-4 rounded-xl bg-[#31324C]/40 border border-white/5 text-sm font-medium outline-none placeholder:text-white/40 focus:border-[#FFBB02] transition-colors focus:bg-[#03041A] text-white">
                </div>
            </div>
        </header>

        <!-- DASHBOARD GRID -->
        <section class="flex flex-col lg:flex-row justify-between min-h-0 py-2 px-4 lg:px-8 gap-6 pb-8">

            <!-- LEFT: Map Panel -->
            <div class="flex-1 min-w-0">
                <div
                    class="rounded-2xl border border-white/5 overflow-hidden shadow-2xl bg-[#0F172A] flex flex-col relative h-[440px] lg:h-[550px]">
                    <div id="map" class="w-full h-full bg-[#050711]"></div>
                    <div
                        class="absolute bottom-4 left-4 border border-white/10 bg-[#0F172A]/90 backdrop-blur-md rounded-2xl z-[1000] p-3 shadow-xl">
                        <div class="flex flex-col gap-2 min-w-[140px]">
                            <span class="font-bold text-[10px] tracking-widest text-[#64748B] block">MAP LEGEND</span>
                            <span class="font-semibold text-xs flex items-center text-[#E2E8F0]">
                                <span
                                    class="w-2.5 h-2.5 rounded-full bg-[#e74c3c] mr-2.5 shadow-[0_0_8px_rgba(231,76,60,0.6)]"></span>
                                Pending (Future)
                            </span>
                            <span class="font-semibold text-xs flex items-center text-[#E2E8F0]">
                                <span
                                    class="w-2.5 h-2.5 rounded-full bg-[#3498db] mr-2.5 shadow-[0_0_8px_rgba(52,152,219,0.6)]"></span>
                                Ongoing (Active)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: List Feed Panel with Pagination -->
            <div class="w-full lg:w-[420px] lg:flex-shrink-0">
                <div
                    class="bg-[#0F172A] border border-white/5 p-5 rounded-2xl h-[440px] lg:h-[550px] flex flex-col relative shadow-2xl">

                    <div class="flex flex-row border-b border-white/10 pb-4 justify-between items-center shrink-0">
                        <span class="text-[13px] font-bold text-white tracking-wide">Maintenance Schedule</span>
                        <span
                            class="text-[10px] text-[#34D399] font-bold rounded-lg bg-[#34D399]/10 border border-[#34D399]/20 px-2.5 py-1 tracking-wider uppercase">
                            LIVE FEED
                        </span>
                    </div>

                    <!-- Feed Container -->
                    <div id="maintenanceFeed"
                        class="flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-3.5 pt-4 pr-2">
                        <!-- Loading State -->
                        <div class="flex flex-col items-center justify-center h-full opacity-60 py-10">
                            <svg class="animate-spin w-8 h-8 text-[#FFBB02] mb-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-xs font-semibold text-[#94A3B8]">Compiling schedule vectors...</span>
                        </div>
                    </div>

                    <!-- Pagination Controls -->
                    <div id="paginationControls"
                        class="flex justify-between items-center mt-2 pt-3 border-t border-white/10 hidden">
                        <!-- Injected by JS -->
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- JS ENGINE -->
    <script>
        /* --- 1. BARANGAY SPATIAL DATA --- */
        const barangayData = {
            "Bonuan Gueset": { lat: 16.0585, lng: 120.3345 }, "Bonuan Boquig": { lat: 16.0600, lng: 120.3200 },
            "Bonuan Binloc": { lat: 16.0620, lng: 120.3100 }, "Lucao": { lat: 16.0435, lng: 120.3310 },
            "Tapuac": { lat: 16.0460, lng: 120.3450 }, "Tambac": { lat: 16.0520, lng: 120.3400 },
            "Pantal": { lat: 16.0468, lng: 120.3330 }, "Bacayao Norte": { lat: 16.0300, lng: 120.3200 },
            "Bacayao Sur": { lat: 16.0250, lng: 120.3250 }, "Malued": { lat: 16.0400, lng: 120.3200 },
            "Mayombo": { lat: 16.0480, lng: 120.3100 }, "Mangin": { lat: 16.0550, lng: 120.3500 },
            "Tebeng": { lat: 16.0600, lng: 120.3450 }, "Pogo Chico": { lat: 16.0510, lng: 120.3600 },
            "Pogo Grande": { lat: 16.0550, lng: 120.3650 }, "Herrero": { lat: 16.0450, lng: 120.3350 },
            "Poblacion Centro": { lat: 16.0430, lng: 120.3335 }, "Poblacion Oeste": { lat: 16.0410, lng: 120.3300 },
            "Poblacion Este": { lat: 16.0440, lng: 120.3360 }
        };

        /* --- 2. LEAFLET MAP INITIALIZATION --- */
        const map = L.map('map', {
            zoomControl: false,
            minZoom: 13,
            maxZoom: 16,
            maxBounds: [[15.95, 120.25], [16.15, 120.45]],
            maxBoundsViscosity: 1.0
        }).setView([16.0431, 120.3330], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.control.zoom({ position: 'bottomright' }).addTo(map);
        const markerLayerGroup = L.layerGroup().addTo(map);

        /* --- 3. STATE MANAGEMENT & PAGINATION VARIABLES --- */
        let activeMaintenanceData = [];
        let filteredFeedData = []; // Array used for slicing pagination
        let currentPage = 1;

        const API_ENDPOINT = 'http://localhost/crowdsourcedAPI/api/maintenance_map/get.php';

        /* --- HELPER: TIME PARSING --- */
        function format12Hour(timeStr) {
            if (!timeStr) return '';
            const [h, m] = timeStr.split(':');
            const d = new Date();
            d.setHours(parseInt(h, 10), parseInt(m, 10));
            return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        }

        /* --- 4. API FETCH & STATUS ENGINE --- */
        async function fetchMaintenanceData() {
            try {
                const response = await fetch(API_ENDPOINT, { credentials: "include" });
                if (!response.ok) throw new Error("HTTP Status " + response.status);

                const json = await response.json();
                const rawArray = Array.isArray(json) ? json : (json.data || []);

                const now = new Date();
                activeMaintenanceData = [];

                rawArray.forEach(item => {
                    const dtBase = item.maintenance_date;
                    const startTime = new Date(`${dtBase}T${item.start_time}`);
                    const endTime = new Date(`${dtBase}T${item.end_time}`);

                    let status = 'done';
                    if (now < startTime) status = 'pending';
                    else if (now >= startTime && now <= endTime) status = 'ongoing';

                    // Omit 'done' entirely
                    if (status !== 'done') {
                        item._computedStatus = status;
                        item._startParsed = startTime;
                        activeMaintenanceData.push(item);
                    }
                });

                // Sort ascending
                activeMaintenanceData.sort((a, b) => a._startParsed - b._startParsed);

                // Re-apply existing filter (preserves pagination if bounds allow)
                applyFiltersAndRender(document.getElementById('mapSearch').value);

                document.getElementById("syncStatus").innerHTML = `
    <span class="w-1.5 h-1.5 rounded-full bg-[#34D399] animate-pulse"></span>
    System Sync Active
`;

                /* REMOVE GREEN BORDER + BACKGROUND */
                document.getElementById('syncStatus').className =
                    "flex items-center gap-1.5 text-[#34D399] font-medium";
            } catch (error) {
                console.error("Fetch Error:", error);
                document.getElementById('syncStatus').innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span> Connection Lost`;
                document.getElementById('syncStatus').className = "flex items-center gap-1.5 text-[#EF4444] font-semibold bg-[#EF4444]/10 px-2 py-0.5 rounded-md border border-[#EF4444]/20";
            }
        }

        /* --- 5. RENDER LOGIC (MAP & FILTERING) --- */
        function applyFiltersAndRender(searchQuery = '') {
            markerLayerGroup.clearLayers();
            const query = searchQuery.toLowerCase().trim();
            filteredFeedData = []; // Clear array for pagination

            activeMaintenanceData.forEach(item => {
                let bList = Array.isArray(item.affected_barangays) ? item.affected_barangays : (item.affected_barangays || "").split(',').map(s => s.trim());
                const searchableText = `${item.company_name || ''} ${item.description || ''} ${bList.join(' ')}`.toLowerCase();

                if (query && !searchableText.includes(query)) return;

                // Store matched item for pagination
                filteredFeedData.push({ item, bList });

                const status = item._computedStatus;
                const hexColor = status === 'pending' ? '#e74c3c' : '#3498db';
                const statusLabel = status.toUpperCase();
                const dispDate = item._startParsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const timeRange = `${format12Hour(item.start_time)} — ${format12Hour(item.end_time)}`;
                const provider = item.company_name || 'Utility Provider';

                // ALL matched items draw their map markers
                bList.forEach(bName => {
                    const geo = barangayData[bName];
                    if (geo) {
                        L.circle([geo.lat, geo.lng], {
                            radius: item.radius || 2000,
                            color: hexColor, fillColor: hexColor, fillOpacity: 0.25, weight: 2
                        }).addTo(markerLayerGroup).bindPopup(`
                            <div class="min-w-[220px]">
                                <div class="font-bold text-lg border-b border-white/10 pb-2 mb-2 tracking-tight">${bName}</div>
                                <div class="space-y-1.5">
                                    <div class="text-[11px] text-[#94A3B8] flex justify-between"><span class="uppercase tracking-wider font-semibold">Provider:</span><span class="text-white font-medium">${provider}</span></div>
                                    <div class="text-[11px] text-[#94A3B8] flex justify-between"><span class="uppercase tracking-wider font-semibold">Status:</span><span class="font-bold tracking-wide" style="color: ${hexColor}">${statusLabel}</span></div>
                                    <div class="text-[11px] text-[#94A3B8] flex justify-between"><span class="uppercase tracking-wider font-semibold">Date:</span><span class="text-white font-medium">${dispDate}</span></div>
                                    <div class="text-[11px] text-[#94A3B8] flex justify-between"><span class="uppercase tracking-wider font-semibold">Time:</span><span class="text-white font-medium">${timeRange}</span></div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-white/10 text-[10px] text-[#CBD5E1] leading-relaxed">
                                    ${item.description || 'System maintenance in progress.'}
                                </div>
                            </div>
                        `);
                    }
                });
            });

            // Adjust currentPage if out of bounds after filtering/refresh
            const itemsPerPage = window.innerWidth < 768 ? 3 : 5;
            const totalPages = Math.max(1, Math.ceil(filteredFeedData.length / itemsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;

            renderPaginatedFeed();
        }

        /* --- 6. PAGINATION RENDER ENGINE --- */
        function renderPaginatedFeed() {
            const feedContainer = document.getElementById('maintenanceFeed');
            feedContainer.innerHTML = '';

            if (filteredFeedData.length === 0) {
                feedContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full opacity-60 py-10 text-center">
                        <svg class="w-10 h-10 mb-3 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-semibold text-[#E2E8F0]">No active schedules found</span>
                    </div>`;
                document.getElementById('paginationControls').style.display = 'none';
                return;
            }

            document.getElementById('paginationControls').style.display = 'flex';

            const itemsPerPage = window.innerWidth < 768 ? 3 : 5;
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const paginatedSlice = filteredFeedData.slice(startIndex, endIndex);

            // Render DOM Cards
            paginatedSlice.forEach(data => {
                const { item, bList } = data;
                const status = item._computedStatus;
                const hexColor = status === 'pending' ? '#e74c3c' : '#3498db';
                const statusLabel = status.toUpperCase();
                const dispDate = item._startParsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const timeRange = `${format12Hour(item.start_time)} — ${format12Hour(item.end_time)}`;
                const provider = item.company_name || 'Utility Provider';

                feedContainer.innerHTML += `
                    <div class="hover-lift bg-[#1E293B]/40 border border-white/5 rounded-2xl p-4 flex flex-col relative overflow-hidden group shrink-0">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5" style="background-color: ${hexColor}"></div>
                        <div class="flex justify-between items-start pl-2 mb-3">
                            <div>
                                <h4 class="font-bold text-white text-[13px] tracking-tight">${provider}</h4>
                                <div class="text-[10px] text-[#94A3B8] font-medium mt-1 tracking-wide uppercase flex flex-col gap-0.5">
                                    <span>🗓️ ${dispDate}</span>
                                    <span>⏱️ ${timeRange}</span>
                                </div>
                            </div>
                            <span class="text-[9px] font-black tracking-widest px-2.5 py-1 rounded-md shadow-sm border" 
                                  style="color: ${hexColor}; background: ${hexColor}1A; border-color: ${hexColor}40;">
                                ${statusLabel}
                            </span>
                        </div>
                        <div class="pl-2 mb-2">
                            <span class="text-[9px] text-[#475569] font-bold tracking-widest uppercase block mb-1">AFFECTED ZONES</span>
                            <p class="text-[11px] font-semibold text-[#E2E8F0] leading-relaxed truncate">${bList.join(', ') || 'N/A'}</p>
                        </div>
                    </div>
                `;
            });

            updatePaginationControls(filteredFeedData.length, itemsPerPage);
        }

        /* --- 7. PAGINATION CONTROLS CONTROLLER --- */
        function updatePaginationControls(totalItems, itemsPerPage) {
            const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
            const container = document.getElementById('paginationControls');

            const prevDisabled = currentPage === 1;
            const nextDisabled = currentPage === totalPages;

            const btnBase = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all border shadow-md";
            const btnActive = "bg-[#1E293B] text-white hover:bg-[#FFBB02] hover:text-black border-white/10 hover:border-[#FFBB02]";
            const btnInactive = "opacity-40 cursor-not-allowed text-[#64748B] border-[#64748B]/30 bg-transparent";

            container.innerHTML = `
                <button onclick="goToPreviousPage()" ${prevDisabled ? 'disabled' : ''} class="${btnBase} ${prevDisabled ? btnInactive : btnActive}">Previous</button>
                <span class="text-[11px] text-[#94A3B8] font-bold tracking-widest uppercase">Page ${currentPage} of ${totalPages}</span>
                <button onclick="goToNextPage()" ${nextDisabled ? 'disabled' : ''} class="${btnBase} ${nextDisabled ? btnInactive : btnActive}">Next</button>
            `;
        }

        function goToPreviousPage() {
            if (currentPage > 1) {
                currentPage--;
                renderPaginatedFeed();
            }
        }

        function goToNextPage() {
            const itemsPerPage = window.innerWidth < 768 ? 3 : 5;
            const totalPages = Math.ceil(filteredFeedData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderPaginatedFeed();
            }
        }

        /* --- 8. EVENTS & HANDLERS --- */
        function handleSearch(val) {
            currentPage = 1; // Reset pagination on new search
            applyFiltersAndRender(val);
        }

        // Adjust items per page seamlessly when resizing between Mobile/Desktop views
        window.addEventListener('resize', () => {
            if (filteredFeedData.length > 0) {
                const itemsPerPage = window.innerWidth < 768 ? 3 : 5;
                const totalPages = Math.max(1, Math.ceil(filteredFeedData.length / itemsPerPage));
                if (currentPage > totalPages) currentPage = totalPages;
                renderPaginatedFeed();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            fetchMaintenanceData();
            setInterval(fetchMaintenanceData, 30000); // 30 sec auto-refresh
        });

        /* --- MOBILE MENU --- */
        const menuBtn = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        if (menuBtn && sidebar && overlay) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>
</body>

</html>