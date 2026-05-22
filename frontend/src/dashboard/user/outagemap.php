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

// Inject the logged-in user's absolute ID securely into client-side execution boundaries
$current_user_id = $user['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerGuide Outage Map</title>

    <!-- CDN Deliveries (Tailwind, Montserrat, Leaflet Map) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0D0E2A;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #31324C;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: #555;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: #31324C transparent;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
        }

        .leaflet-popup-content-wrapper {
            background: #1A1B33;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .leaflet-popup-tip {
            background: #1A1B33;
        }

        .leaflet-container a.leaflet-popup-close-button {
            color: #fff;
        }

        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-container {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased">

    <!-- Pass backend system variables safely down to the JavaScript layer -->
    <script>
        const CURRENT_USER_ID = <?= json_encode($current_user_id) ?>;
    </script>

    <!-- Mobile Menu Toggle -->
    <button id="menuToggle"
        class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg shadow-md hover:bg-opacity-80 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden"></div>

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR NAV -->
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

                <!-- CHANGED: Dashboard is now the unhighlighted link -->
                <a href="dashboard.php"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- CHANGED: Outage Map is now statically highlighted -->
                <a href="outagemap.php"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 4L9 7" />
                    </svg>
                    <span>Outage Map</span>
                </a>

                <a href="findhubs.html"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Find Hubs</span>
                </a>

                <span
                    class="text-[11px] font-bold tracking-widest text-white px-4 pt-4 mb-2 opacity-50">COMMUNITY</span>

                <a href="reports.html"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Reports</span>
                </a>

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
            <div
                class="flex flex-col text-left mt-auto mb-3 mx-2 p-5 rounded-2xl bg-[#31324C]/30 border border-white/5">
                <span class="text-[#FEBB02] text-xs font-bold tracking-wider mb-1">PRO TIP</span>
                <span class="text-white/50 text-xs font-normal leading-relaxed">Lower screen brightness to 40% to save
                    roughly 15 minutes of device runtime.</span>
            </div>

            <!-- Profile Info Panel (Repositioned to bottom of sidebar) -->
            <div
                class="flex flex-row items-center justify-between gap-3 px-4 py-3 mb-8 rounded-2xl bg-[#31324C]/20 border border-white/5 text-left">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="h-10 w-10 rounded-xl overflow-hidden border border-[#FFBB02]/30 flex-shrink-0 bg-[#31324C]">
                        <img src="<?= htmlspecialchars($picture) ?>" alt="User Avatar"
                            class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <span
                            class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['name']) ?></span>
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
        <main class="flex-1 overflow-y-auto bg-[#03041A]">

            <!-- HEADER BAR MATCHING THE COMPACT DASHBOARD ARCHITECTURE STYLE -->
            <header
                class="mx-4 lg:mx-8 mt-14 lg:mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Interactive Outage Map</h1>
                    <span class="text-xs lg:text-sm text-[#B5B5B5] flex items-center gap-2 mt-1">
                        Grid status:
                        <span class="flex items-center gap-1.5 text-[#00BA00] font-medium" id="activeOutageCounter">
                            Synchronizing live grid loops...
                        </span>
                    </span>
                </div>

                <div class="flex items-center gap-4 self-end sm:self-auto">
                    <!-- Submit Incident Button Layout Trigger -->
                    <button onclick="openPopup()"
                        class="cursor-pointer px-5 py-2.5 bg-[#FFBB02] text-black rounded-xl hover:bg-[#D99A00] transition-all transform hover:scale-105 active:scale-95 font-bold text-xs md:text-sm shadow-md shadow-[#FFBB02]/10">
                        + Report Outage
                    </button>

                    <!-- Search Node Field Layer Component -->
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="search" id="mapSearch" oninput="filterBarangay(this.value)"
                            placeholder="Search barangay..."
                            class="w-[240px] sm:w-[280px] h-11 pl-10 pr-4 rounded-xl bg-[#31324C]/40 border border-white/5 text-sm font-medium outline-none placeholder:text-white/40 focus:border-[#FFBB02] transition-colors focus:bg-[#03041A]">
                    </div>

                    <!-- Layout Connected Profile Icon Button -->
                    <div id="profileBtn"
                        class="h-10 w-10 rounded-xl border-2 border-[#FFBB02] bg-[#31324C] overflow-hidden flex items-center justify-center shadow-lg transition-all transform hover:scale-105">
                        <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar" class="h-full w-full object-cover">
                    </div>
                </div>
            </header>

            <!-- CORE DATA HUD LAYOUT PANELS -->
            <section class="flex flex-col lg:flex-row justify-between min-h-0 py-2 px-4 lg:px-8 gap-6">

                <!-- LEFT SIDE: Leaflet Interactive Map View Canvas Container -->
                <div class="flex-1 min-w-0">
                    <div
                        class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#31324C]/20 flex flex-col relative h-[440px] lg:h-[550px]">
                        <div id="map" class="w-full h-full bg-[#0E0F26]"></div>

                        <!-- Floating Map Legend Panel -->
                        <div
                            class="absolute bottom-4 left-4 border border-white/10 bg-[#1A1B33]/95 rounded-2xl z-[1000] p-3 shadow-xl backdrop-blur-md">
                            <div class="flex flex-col gap-1.5 min-w-[130px]">
                                <span class="font-bold text-[10px] tracking-widest text-white/40 block mb-0.5">MAP
                                    LEGEND</span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#FF2E1F] mr-2 block shadow-sm"></span>
                                    Confirmed Outage
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#FFBB02] mr-2 block shadow-sm"></span>
                                    Partial/Surge
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#34FB34] mr-2 block shadow-sm"></span>
                                    Stable/Grid
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE: Detailed City Statistics List Feed Panel Component -->
                <div class="w-full lg:w-[420px] lg:flex-shrink-0">
                    <div
                        class="bg-[#31324C]/20 border border-white/5 p-5 rounded-2xl h-[440px] lg:h-[550px] flex flex-col relative overflow-hidden shadow-xl">

                        <!-- Panel Subheader with Feed Filtering Controls -->
                        <div
                            class="flex flex-row border-b border-white/5 pb-3.5 justify-between items-center bg-transparent">
                            <div class="flex gap-2">
                                <button onclick="toggleFilterMode('all')" id="filterBtnAll"
                                    class="text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all">
                                    All Grid
                                </button>
                                <button onclick="toggleFilterMode('mine')" id="filterBtnMine"
                                    class="text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#31324C]/40 text-[#B5B5B5] hover:text-white transition-all">
                                    My Reports
                                </button>
                            </div>
                            <span
                                class="text-[10px] text-[#B5B5B5] font-bold rounded-lg bg-[#31324C]/60 border border-white/10 px-2.5 py-1 tracking-wider uppercase">
                                SORT BY: ALERT LEVEL
                            </span>
                        </div>

                        <!-- Dynamic Scrollable Analytics Feed Deck Container -->
                        <div id="recentReports"
                            class="flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-3.5 pt-3.5 pr-1">
                            <p class="text-xs text-white/40 text-center py-8">Initializing live updates synchronization
                                vectors...</p>
                        </div>

                        <!-- PAGINATION CONTAINER INTERFACE COMPONENT -->
                        <div id="pagination"
                            class="flex justify-center items-center gap-2 pt-3 border-t border-white/5 bg-transparent">
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Hidden element states tracking contexts -->
    <div id="formOverlay" class="hidden">
        <span id="batteryLevel"></span>
        <span id="batteryCharging"></span>
        <span id="batteryStatus"></span>
    </div>

    <!-- WIDE OVERLAY MODAL FEATURING INTUITION LOCATION-PICKING MAP CANVAS -->
    <div id="popup"
        class="fixed inset-0 bg-black/80 flex justify-center items-center z-50 p-4 opacity-0 invisible transition-all duration-300 ease-out"
        onclick="closePopup()">
        <div id="popupBox"
            class="relative w-full max-w-[1000px] bg-[#1A1B33] border border-white/10 rounded-[24px] overflow-hidden shadow-2xl transition-all"
            onclick="event.stopPropagation()">

            <button type="button" onclick="closePopup()"
                class="absolute top-5 h-8 w-8 rounded-full bg-[#03041A] border border-white/10 flex items-center justify-center right-5 text-white/60 text-xl font-light hover:text-[#FFBB02] transition-colors z-50">&times;</button>

            <!-- Dual-Panel Flex Layout Grid System -->
            <div class="flex flex-col md:flex-row h-[90vh] md:h-[620px]">

                <!-- LEFT SIDEBAR MAP: Click target to define precise marker coordinate vectors -->
                <div
                    class="w-full md:w-[45%] h-[240px] md:h-full relative bg-[#03041A] border-b md:border-b-0 md:border-r border-white/10">
                    <div id="modalMap" class="w-full h-full"></div>
                    <div
                        class="absolute top-4 left-4 z-[1000] pointer-events-none bg-[#03041A]/95 backdrop-blur-md px-3 py-2 rounded-xl border border-white/10">
                        <span class="text-[10px] font-black tracking-wider text-[#FFBB02] uppercase block">Geographic
                            Pinpoint</span>
                        <p class="text-[11px] text-white/70 font-medium mt-0.5">Click or drag the map node position
                            inside this viewport panel.</p>
                    </div>
                </div>

                <!-- RIGHT PANEL: Input form structures -->
                <form id="outageForm" class="flex-1 flex flex-col justify-between overflow-y-auto p-6 md:p-8"
                    onsubmit="handleFormSubmit(event)">
                    <input type="hidden" id="report_id" value="">
                    <!-- Coordinates Hidden Trackers -->
                    <input type="hidden" id="formLatitude" value="">
                    <input type="hidden" id="formLongitude" value="">

                    <div>
                        <div class="mb-4">
                            <h3 id="formTitle" class="text-2xl font-black text-white tracking-tight">Report an Outage
                            </h3>
                            <p class="text-xs text-[#B5B5B5] mt-0.5">Define your location via the picker view layout on
                                the left, then fill in details below.</p>
                        </div>

                        <!-- USE MY CURRENT LOCATION GPS TRIGGER BUTTON -->
                        <div class="mb-4">
                            <button type="button" onclick="useCurrentLocation()"
                                class="w-full h-11 px-4 bg-[#31324C]/60 hover:bg-[#31324C] border border-white/10 rounded-xl text-xs font-bold text-white flex items-center justify-center gap-2 transition-all active:scale-95">
                                <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Use My Current Location
                            </button>
                        </div>

                        <!-- OUTAGE TYPE / SEVERITY (Acts as status in create/update payloads) -->
                        <span
                            class="text-[#B5B5B5] font-bold text-[11px] tracking-widest mb-1.5 block uppercase opacity-60">OUTAGE
                            TYPE / SEVERITY</span>
                        <div class="relative w-full mb-3">
                            <select id="formSeverity"
                                class="w-full h-11 px-3 bg-[#03041A] border border-white/10 rounded-xl text-sm outline-none focus:border-[#FFBB02] transition-colors text-white cursor-pointer">
                                <option value="Critical">Critical (Total Blackout)</option>
                                <option value="Warning">Warning (Partial Outage)</option>
                                <option value="Stable">Stable / Checking</option>
                            </select>
                        </div>

                        <!-- CATEGORY CLASSIFICATION -->
                        <span
                            class="text-[#B5B5B5] font-bold text-[11px] tracking-widest mb-1.5 block uppercase opacity-60">CATEGORY
                            CLASSIFICATION</span>
                        <div class="relative w-full mb-3">
                            <select id="formCategory"
                                class="w-full h-11 px-3 bg-[#03041A] border border-white/10 rounded-xl text-sm outline-none focus:border-[#FFBB02] transition-colors text-white cursor-pointer">
                                <option value="Grid Power outage">Grid Power Outage</option>
                                <option value="Line Fluctuation">Line Fluctuation</option>
                                <option value="Transformer Failure">Transformer Failure</option>
                            </select>
                        </div>

                        <!-- LOCATION NAME -->
                        <span
                            class="text-[#B5B5B5] font-bold text-[11px] tracking-widest mb-1.5 block uppercase opacity-60">LOCATION
                            NAME</span>
                        <input id="formLocation" required
                            class="w-full px-4 h-11 bg-[#03041A] border border-white/10 rounded-xl text-sm outline-none focus:border-[#FFBB02] transition-colors mb-3"
                            type="text" placeholder="e.g., Barangay Name, Street, Local Landmark">

                        <!-- OBSERVATIONS -->
                        <span
                            class="text-[#B5B5B5] font-bold text-[11px] tracking-widest mb-1.5 block uppercase opacity-60">OBSERVATIONS</span>
                        <textarea id="formDescription"
                            class="w-full h-20 border border-white/10 p-3 rounded-xl bg-[#03041A] focus:border-[#FFBB02] outline-none text-sm resize-none"
                            placeholder="Describe environmental context parameters..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full h-12 mt-6 rounded-xl bg-[#FFBB02] hover:bg-[#D99A00] text-black font-extrabold text-sm transition-colors shadow-md shrink-0">
                        Submit Community Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Battery Warning Low Overlay Component Toast window -->
    <div id="battery-warning"
        class="fixed inset-0 bg-black/70 flex justify-center items-center z-50 p-4 opacity-0 invisible pointer-events-none transition-all duration-300">
        <div id="batteryBox"
            class="bg-[#1A1B33] border-2 border-red-600 rounded-3xl p-8 max-w-sm w-full flex flex-col items-center gap-4 text-center scale-95 translate-y-4 opacity-0 transition-all duration-300 ease-out">
            <span class="text-red-500 text-5xl">⚠️</span>
            <h2 class="text-white text-xl font-bold">Battery Low!</h2>
            <p class="text-[#B5B5B5] text-sm leading-relaxed">Please charge your device as soon as possible to avoid
                losing power telemetry infrastructure connection tracks.</p>
            <button onclick="closeBatteryWarning()"
                class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-sm shadow-md">
                Got it
            </button>
        </div>
    </div>

    <!-- ================= JAVASCRIPT SYSTEM ENGINE ================= -->
    <script>
        /* ================= MAP COMPONENT LAYERS INITIALIZATION ================= */
        const map = L.map('map', { zoomControl: false }).setView([16.04, 120.33], 12);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let layerGroup = L.layerGroup().addTo(map);

        // Pagination & Global Cache Layer Data States
        let allCachedReports = [];
        let filteredReports = [];
        let currentFilterMode = 'all';
        let currentPage = 1;
        const perPage = 3; // Number of items loaded concurrently inside the scroll feed viewport block

        /* ================= SAFE TEXT HELPER (XSS PROTECTION) ================= */
        function escapeHTML(str) {
            return String(str ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        /* ================= MODAL MAP (PICKER ENVIRONMENT) INITIALIZATION ================= */
        let modalMap;
        let modalSelectionMarker;

        function initModalMap() {
            if (modalMap) return;

            modalMap = L.map('modalMap', { zoomControl: false }).setView([16.04, 120.33], 13);
            L.control.zoom({ position: 'bottomright' }).addTo(modalMap);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(modalMap);

            modalMap.on('click', function (e) {
                setModalCoordinates(e.latlng.lat, e.latlng.lng);
            });
        }

        function setModalCoordinates(lat, lng) {
            document.getElementById("formLatitude").value = Number(lat).toFixed(4);
            document.getElementById("formLongitude").value = Number(lng).toFixed(4);

            if (modalSelectionMarker) {
                modalSelectionMarker.setLatLng([lat, lng]);
            } else {
                modalSelectionMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);
                modalSelectionMarker.on('dragend', function (event) {
                    const marker = event.target;
                    const position = marker.getLatLng();
                    document.getElementById("formLatitude").value = position.lat.toFixed(4);
                    document.getElementById("formLongitude").value = position.lng.toFixed(4);
                });
            }
            modalMap.panTo([lat, lng]);
        }

        /* ================= GEOLOCATION UTILITIES ================= */
        function useCurrentLocation() {
            if (!navigator.geolocation) {
                alert("Geolocation tracking is not supported by your current browser environment.");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    setModalCoordinates(lat, lng);
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    alert("Unable to fetch location data tracks. Verify device diagnostic permissions.");
                },
                { enableHighAccuracy: true }
            );
        }

        /* ================= MODAL WORKFLOW SYSTEM MODALS ================= */
        function openPopup(editMode = false) {
            const popup = document.getElementById("popup");
            popup.classList.remove("invisible", "opacity-0");

            setTimeout(() => {
                initModalMap();
                modalMap.invalidateSize();

                if (!editMode) {
                    document.getElementById("outageForm").reset();
                    document.getElementById("report_id").value = "";
                    document.getElementById("formTitle").innerText = "Report an Outage";
                    setModalCoordinates(16.043, 120.333);
                }
            }, 50);
        }

        function closePopup() {
            const popup = document.getElementById("popup");
            popup.classList.add("invisible", "opacity-0");
        }

        function closeForm() {
            closePopup();
        }

        /* ================= BUSINESS LOGIC CRUD API FETCH ACTIONS ================= */
        const API_BASE = "http://localhost/crowdsourcedapi/api/outage_report";

        async function createReport(payload) {
            const res = await fetch(`${API_BASE}/create.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "include",
                body: JSON.stringify(payload)
            });
            return await res.json();
        }

        async function updateReport() {
            const payload = {
                id: document.getElementById("report_id").value,
                location_name: document.getElementById("formLocation").value,
                category: document.getElementById("formCategory").value,
                severity: document.getElementById("formSeverity").value,
                status: document.getElementById("formSeverity").value, // Sync severity with status
                description: document.getElementById("formDescription").value,
                latitude: document.getElementById("formLatitude").value,
                longitude: document.getElementById("formLongitude").value
            };

            try {
                const res = await fetch(`${API_BASE}/update.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                alert(result.message || "Updated");

                if (result.success) {
                    closeForm();
                    initGridSynchronization();
                }
            } catch (err) {
                console.error(err);
                alert("Update failed");
            }
        }

        async function deleteReport(id) {
            if (!confirm("Delete this report?")) return;
            try {
                const res = await fetch(`${API_BASE}/delete.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({ id })
                });
                const result = await res.json();
                alert(result.message || "Done");

                if (result.success) {
                    initGridSynchronization();
                }
            } catch (err) {
                console.error(err);
                alert("Delete failed");
            }
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            const id = document.getElementById("report_id").value;

            if (id) {
                await updateReport();
                return;
            }

            const payload = {
                location_name: document.getElementById("formLocation").value,
                category: document.getElementById("formCategory").value,
                severity: document.getElementById("formSeverity").value,
                description: document.getElementById("formDescription").value,
                status: document.getElementById("formSeverity").value,
                latitude: document.getElementById("formLatitude").value,
                longitude: document.getElementById("formLongitude").value
            };

            try {
                const result = await createReport(payload);
                alert(result.message || "Operation Completed successfully");
                closePopup();
                initGridSynchronization();
            } catch (err) {
                console.error(err);
                alert("API Submission encountered an error.");
            }
        }

        /* ================= FILTERING LOGIC MANAGEMENT TABS ================= */
        function toggleFilterMode(mode) {
            currentFilterMode = mode;
            const btnAll = document.getElementById("filterBtnAll");
            const btnMine = document.getElementById("filterBtnMine");

            if (mode === 'mine') {
                btnMine.className = "text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all";
                btnAll.className = "text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#31324C]/40 text-[#B5B5B5] hover:text-white transition-all";
            } else {
                btnAll.className = "text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all";
                btnMine.className = "text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#31324C]/40 text-[#B5B5B5] hover:text-white transition-all";
            }
            currentPage = 1;
            // Reset keyword filter search input box UI field during view state swaps
            document.getElementById("mapSearch").value = "";
            initGridSynchronization();
        }

        /* ================= DATA RECONCILIATION & FEED RENDER PIPELINES ================= */
        async function initGridSynchronization() {
            try {
                const mapRes = await fetch(`${API_BASE}/get.php`, { credentials: "include" });
                const mapResult = await mapRes.json();
                const publicReports = mapResult.data || [];
                renderMapMarkers(publicReports);

                // Side statistics list respects the toggle filter path structures
                const targetUrl = (currentFilterMode === 'mine') ? `${API_BASE}/get_my_report.php` : `${API_BASE}/get.php`;
                const feedRes = await fetch(targetUrl, { credentials: "include" });
                const feedResult = await feedRes.json();

                allCachedReports = feedResult.data || [];

                // Re-apply keyword search context filters if any exist inside input field layout boxes
                const currentKeyword = document.getElementById("mapSearch").value;
                if (currentKeyword) {
                    filterBarangay(currentKeyword);
                } else {
                    filteredReports = [...allCachedReports];
                    renderStatisticsFeed();
                    renderPaginationControls();
                }
            } catch (e) {
                console.error("Data syncing failed:", e);
            }
        }

        function renderMapMarkers(reports) {
            layerGroup.clearLayers();
            let activeAlertsCount = 0;

            reports.forEach(r => {
                if (!r.latitude || !r.longitude) return;
                activeAlertsCount++;

                let markerColor = "#34FB34";
                if (r.severity?.toLowerCase() === 'critical' || r.status?.toLowerCase() === 'critical') markerColor = "#FF2E1F";
                if (r.severity?.toLowerCase() === 'warning' || r.status?.toLowerCase() === 'warning') markerColor = "#FFBB02";

                L.circleMarker([r.latitude, r.longitude], {
                    radius: 9, fillColor: markerColor, color: "#fff", weight: 2, opacity: 1, fillOpacity: 0.9
                })
                    .bindPopup(`
                    <div class="text-white text-xs">
                        <strong class="text-sm block border-b border-white/10 pb-1 mb-1">${escapeHTML(r.location_name)}</strong>
                        <p class="mb-1 text-white/80">${escapeHTML(r.description || 'No notes noted.')}</p>
                        <span class="inline-block px-2 py-0.5 rounded font-bold text-[10px]" style="background:${markerColor}; color:#000;">${escapeHTML(r.severity || r.status || 'REPORTED')}</span>
                    </div>
                `)
                    .addTo(layerGroup);
            });

            document.getElementById("activeOutageCounter").innerHTML = `
                <svg class="w-4 h-4 inline-block mr-1 fill-current text-[#00BA00]" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Active Incidents: ${activeAlertsCount} Locations Reporting
            `;
        }

        function filterBarangay(keyword) {
            filteredReports = allCachedReports.filter(r =>
                r.location_name.toLowerCase().includes(keyword.toLowerCase()) ||
                (r.description && r.description.toLowerCase().includes(keyword.toLowerCase()))
            );
            currentPage = 1;
            renderStatisticsFeed();
            renderPaginationControls();
        }

        /* ================= HIGHLY-OPTIMIZED FRAGMENT STREAM FEED ================= */
        function renderStatisticsFeed() {
            const feedContainer = document.getElementById("recentReports");
            feedContainer.innerHTML = "";

            if (!filteredReports.length) {
                feedContainer.innerHTML = `<p class="text-xs text-white/40 text-center py-12">No matching records found in this context view.</p>`;
                return;
            }

            const start = (currentPage - 1) * perPage;
            const pageData = filteredReports.slice(start, start + perPage);

            const fragment = document.createDocumentFragment();

            pageData.forEach((r) => {
                const status = (r.severity || r.status || "stable").toUpperCase();
                let badgeStyle = "bg-[#22FF221A]/[10%] text-[#34FB34]";
                let textLabel = "STABLE";
                let dynamicMetricField = "UPTIME";
                let dynamicMetricValue = "100 %";
                let secondaryMetricField = "GRID LOAD";
                let secondaryMetricValue = "Optimal";

                if (status.includes("CRITICAL") || status.includes("OUTAGE")) {
                    badgeStyle = "bg-[#FF3C2F1A]/[10%] text-[#FF2E1F]";
                    textLabel = "CRITICAL";
                    dynamicMetricField = "AFFECTED";
                    dynamicMetricValue = "Total Blackout";
                    secondaryMetricField = "ETR";
                    secondaryMetricValue = "Pending";
                } else if (status.includes("WARN") || status.includes("FLUCTUATION")) {
                    badgeStyle = "bg-[#FFBB021A]/[10%] text-[#FFBB02]";
                    textLabel = "WARNING";
                    dynamicMetricField = "AFFECTED";
                    dynamicMetricValue = "Partial Phase";
                    secondaryMetricField = "STATUS";
                    secondaryMetricValue = "Fluctuation";
                }

                const isAuthor = (CURRENT_USER_ID && r.user_id && String(r.user_id) === String(CURRENT_USER_ID)) || (currentFilterMode === 'mine');

                const card = document.createElement("div");
                card.className = "card-hover flex flex-col p-4 border border-white/5 rounded-2xl bg-[#1C1D30]/30 transition-all hover:border-white/10";

                card.innerHTML = `
                    <div class="flex flex-col">
                        <span class="font-bold flex justify-between items-center text-white text-base md:text-lg">
                            <span class="truncate max-w-[240px]">${escapeHTML(r.location_name)}</span>
                            <span class="flex ${badgeStyle} px-2 py-1 text-[10px] items-center rounded-lg font-black tracking-wider shadow-sm shrink-0">
                                ${escapeHTML(textLabel)}
                            </span>
                        </span>
                        <span class="font-medium text-xs text-[#B5B5B5] mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            ${escapeHTML(r.category || 'Power Grid Terminal Node')}
                        </span>
                    </div>
                    <div class="flex flex-row justify-between mt-3.5 gap-3">
                        <div class="border border-white/5 rounded-xl bg-[#31324C]/40 flex flex-col flex-1 p-2.5">
                            <span class="text-[10px] text-[#B5B5B5] font-bold tracking-wide uppercase opacity-50">${escapeHTML(dynamicMetricField)}</span>
                            <span class="text-sm text-white font-extrabold mt-0.5">${escapeHTML(dynamicMetricValue)}</span>
                        </div>
                        <div class="border border-white/5 rounded-xl bg-[#31324C]/40 flex flex-col flex-1 p-2.5">
                            <span class="text-[10px] text-[#B5B5B5] font-bold tracking-wide uppercase opacity-50">${escapeHTML(secondaryMetricField)}</span>
                            <span class="text-sm text-[#FFBB02] font-extrabold mt-0.5">${escapeHTML(secondaryMetricValue)}</span>
                        </div>
                    </div>
                `;

                if (isAuthor) {
                    const controls = document.createElement("div");
                    controls.className = "flex gap-4 justify-end pt-2 mt-3 border-t border-white/5 text-xs";

                    const editBtn = document.createElement("button");
                    editBtn.className = "text-[#FFBB02] hover:underline font-semibold flex items-center gap-1";
                    editBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg> Edit`;
                    editBtn.onclick = () => editReportByObject(r);

                    const deleteBtn = document.createElement("button");
                    deleteBtn.className = "text-red-400 hover:underline font-semibold flex items-center gap-1";
                    deleteBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Delete`;
                    deleteBtn.onclick = () => deleteReport(r.id);

                    controls.appendChild(editBtn);
                    controls.appendChild(deleteBtn);
                    card.appendChild(controls);
                }

                fragment.appendChild(card);
            });

            feedContainer.appendChild(fragment);
        }

        /* ================= PAGINATION CONTROL INTERFACE ENGINE ================= */
        function renderPaginationControls() {
            const paginationContainer = document.getElementById("pagination");
            paginationContainer.innerHTML = "";

            const totalPages = Math.ceil(filteredReports.length / perPage);
            if (totalPages <= 1) return;

            const fragment = document.createDocumentFragment();

            // Prev Button
            const prevBtn = document.createElement("button");
            prevBtn.textContent = "Prev";
            prevBtn.className = `px-3 py-1.5 text-xs font-bold rounded-lg border border-white/10 transition-all ${currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-transparent text-white/40' : 'bg-[#31324C]/40 text-white hover:bg-[#31324C]'}`;
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderStatisticsFeed();
                    renderPaginationControls();
                }
            };
            fragment.appendChild(prevBtn);

            // Page Numeric Keys Loop
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement("button");
                pageBtn.textContent = i;
                pageBtn.className = `px-3 py-1.5 text-xs font-black rounded-lg transition-all border ${i === currentPage ? 'bg-[#FFBB02] text-black border-[#FFBB02]' : 'bg-[#31324C]/20 text-[#B5B5B5] border-white/5 hover:text-white'}`;
                pageBtn.onclick = () => {
                    currentPage = i;
                    renderStatisticsFeed();
                    renderPaginationControls();
                };
                fragment.appendChild(pageBtn);
            }

            // Next Button
            const nextBtn = document.createElement("button");
            nextBtn.textContent = "Next";
            nextBtn.className = `px-3 py-1.5 text-xs font-bold rounded-lg border border-white/10 transition-all ${currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-transparent text-white/40' : 'bg-[#31324C]/40 text-white hover:bg-[#31324C]'}`;
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderStatisticsFeed();
                    renderPaginationControls();
                }
            };
            fragment.appendChild(nextBtn);

            paginationContainer.appendChild(fragment);
        }

        function editReportByObject(r) {
            if (!r) return;

            document.getElementById("report_id").value = r.id;
            document.getElementById("formLocation").value = r.location_name;
            document.getElementById("formCategory").value = r.category;
            document.getElementById("formSeverity").value = r.severity || r.status;
            document.getElementById("formDescription").value = r.description || "";

            document.getElementById("formTitle").innerText = "Update Grid Report Config";
            openPopup(true);

            setTimeout(() => {
                if (r.latitude && r.longitude) {
                    setModalCoordinates(parseFloat(r.latitude), parseFloat(r.longitude));
                }
            }, 100);
        }

        /* ================= HARDWARE LEVEL TELEMETRY MONITORING ================= */
        function batteryDetection() {
            if (!navigator.getBattery) return;
            navigator.getBattery().then(battery => {
                function update() {
                    const levelSpan = document.getElementById("batteryLevel");
                    const chargingSpan = document.getElementById("batteryCharging");
                    const statusBox = document.getElementById("batteryStatus");

                    if (levelSpan) levelSpan.innerText = Math.round(battery.level * 100) + "%";
                    if (chargingSpan) chargingSpan.innerText = battery.charging ? "Yes" : "No";

                    if (battery.level <= 0.20 && !battery.charging) {
                        const toast = document.getElementById("battery-warning");
                        if (toast) {
                            toast.classList.remove("invisible", "opacity-0");
                            document.getElementById("batteryBox").classList.remove("scale-95", "opacity-0");
                        }
                        if (statusBox) {
                            statusBox.innerText = "Low Battery";
                            statusBox.style.background = "#e74c3c";
                        }
                    } else if (battery.charging) {
                        if (statusBox) {
                            statusBox.innerText = "Charging";
                            statusBox.style.background = "#2ecc71";
                        }
                    } else {
                        if (statusBox) {
                            statusBox.innerText = "Normal";
                            statusBox.style.background = "#f39c12";
                        }
                    }
                }
                update();
                battery.addEventListener("levelchange", update);
                battery.addEventListener("chargingchange", update);
            });
        }

        function closeBatteryWarning() {
            document.getElementById("battery-warning").classList.add("invisible", "opacity-0");
        }

        /* ================= INITIALIZATION RENDERING RUNTIME CONTROLS ================= */
        document.addEventListener("DOMContentLoaded", () => {
            initGridSynchronization();
            batteryDetection();
            setInterval(initGridSynchronization, 10000);
        });

        /* ================= SIDEBAR VIEW MOBILE CONTROLS ================= */
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        if (menuToggle && sidebar && overlay) {
            menuToggle.addEventListener('click', () => {
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