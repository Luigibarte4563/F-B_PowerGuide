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
    <title>PowerGuide - Find Stations & Hubs</title>

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
            border-radius: 12px;
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

        <!-- ================= SIDEBAR NAV ================= -->
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
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] text-black group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Find Hubs</span>
                </a>

                <!-- Added Maintenance Map -->
                <a href="maintenancemap.php"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Maintenance Map</span>
                </a>

                <span
                    class="text-[11px] font-bold tracking-widest text-white px-4 pt-4 mb-2 opacity-50">COMMUNITY</span>

                <a href="settings.php"
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

            <!-- Profile Info Panel -->
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

        <!-- ================= MAIN CONTENT AREA ================= -->
        <main class="flex-1 overflow-y-auto bg-[#03041A]">

            <!-- HEADER BAR MATCHING THE COMPACT DASHBOARD ARCHITECTURE STYLE -->
            <header
                class="mx-4 lg:mx-8 mt-14 lg:mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Interactive Power Stations</h1>
                </div>

                <div class="flex items-center gap-4 self-end sm:self-auto">
                    <!-- Register Station Button Layout Trigger -->
                    <button onclick="openPopup()"
                        class="cursor-pointer px-5 py-2.5 bg-[#FFBB02] text-black rounded-xl hover:bg-[#D99A00] transition-all transform hover:scale-105 active:scale-95 font-bold text-xs md:text-sm shadow-md shadow-[#FFBB02]/10">
                        + Register Station
                    </button>

                    <!-- Search Node Field Layer Component -->
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" x1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="search" id="mapSearch" oninput="filterStations(this.value)"
                            placeholder="Search stations..."
                            class="w-[240px] sm:w-[280px] h-11 pl-10 pr-4 rounded-xl bg-[#31324C]/40 border border-white/5 text-sm font-medium outline-none placeholder:text-white/40 focus:border-[#FFBB02] transition-colors focus:bg-[#03041A]">
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
                            <div class="flex flex-col gap-1.5 min-w-[140px]">
                                <span
                                    class="font-bold text-[10px] tracking-widest text-white/40 block mb-0.5">AVAILABILITY
                                    LEGEND</span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#34FB34] mr-2 block shadow-sm"></span>
                                    Operational
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBB02] mr-2 block shadow-sm"></span>
                                    Maintenance
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF2E1F] mr-2 block shadow-sm"></span>
                                    Offline / Defect
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#00E5FF] mr-2 block shadow-sm"></span>
                                    Planned / Project
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE: Detailed Station List Feed Panel Component -->
                <div class="w-full lg:w-[420px] lg:flex-shrink-0">
                    <div
                        class="bg-[#31324C]/20 border border-white/5 p-5 rounded-2xl h-[440px] lg:h-[550px] flex flex-col relative overflow-hidden shadow-xl">

                        <!-- Panel Subheader with Feed Filtering Controls -->
                        <div
                            class="flex flex-row border-b border-white/5 pb-3.5 justify-between items-center bg-transparent">
                            <div class="flex gap-2">
                                <button onclick="toggleFilterMode('all')" id="filterBtnAll"
                                    class="text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all">
                                    All Stations
                                </button>
                                <button onclick="toggleFilterMode('mine')" id="filterBtnMine"
                                    class="text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#31324C]/40 text-[#B5B5B5] hover:text-white transition-all">
                                    My Stations
                                </button>
                            </div>
                            <span
                                class="text-[10px] text-[#B5B5B5] font-bold rounded-lg bg-[#31324C]/60 border border-white/10 px-2.5 py-1 tracking-wider uppercase">
                                SORT BY: NEWEST
                            </span>
                        </div>

                        <!-- Dynamic Scrollable Station Feed Deck Container -->
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
        class="fixed inset-0 bg-[#03041A]/80 backdrop-blur-sm flex justify-center items-center z-[2000] p-4 opacity-0 invisible transition-all duration-300 ease-out"
        onclick="closePopup()">
        <div id="popupBox"
            class="relative w-full max-w-[1000px] bg-gradient-to-b from-[#1E203C] to-[#141527] border border-white/10 rounded-[24px] overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] transition-all transform scale-95"
            onclick="event.stopPropagation()">

            <!-- Close Button -->
            <button type="button" onclick="closePopup()"
                class="absolute top-5 right-5 h-9 w-9 rounded-xl bg-[#03041A]/60 backdrop-blur-md border border-white/10 flex items-center justify-center text-white/70 text-lg hover:text-[#FFBB02] hover:border-[#FFBB02]/30 hover:bg-[#03041A] transition-all duration-200 z-50 shadow-lg">
                &times;
            </button>

            <!-- Dual-Panel Layout -->
            <div class="flex flex-col md:flex-row h-[90vh] md:h-[640px]">

                <!-- LEFT SIDEBAR MAP -->
                <div
                    class="w-full md:w-[45%] h-[280px] md:h-full relative bg-[#03041A] border-b md:border-b-0 md:border-r border-white/10">
                    <div id="modalMap" class="w-full h-full bg-[#050724]"></div>

                    <!-- Floating Info Badge -->
                    <div
                        class="absolute bottom-4 left-4 right-4 z-[1000] pointer-events-none bg-[#03041A]/90 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10 shadow-xl">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FFBB02] animate-pulse"></span>
                            <span class="text-[10px] font-bold tracking-wider text-[#FFBB02] uppercase">Geographic
                                Pinpoint</span>
                        </div>
                        <p class="text-[11px] text-white/60 font-normal mt-0.5 leading-relaxed">Click or drag the map
                            node inside this viewport to match the power station bounds.</p>
                    </div>
                </div>

                <!-- RIGHT PANEL: Input Form -->
                <form id="stationForm"
                    class="flex-1 flex flex-col justify-between overflow-y-auto p-6 md:p-8 bg-transparent">
                    <input type="hidden" id="station_id" value="">
                    <input type="hidden" id="latitude" value="">
                    <input type="hidden" id="longitude" value="">

                    <div class="space-y-4">
                        <!-- Header -->
                        <div>
                            <h3 id="formTitle" class="text-2xl font-bold text-white tracking-tight">Register Power
                                Station</h3>
                            <p class="text-xs text-white/50 mt-1">Specify grid parameters and coordinates for
                                crowdsourced infrastructure logs.</p>
                        </div>

                        <!-- GPS Trigger Button -->
                        <div>
                            <button type="button" onclick="useLocation()"
                                class="w-full h-11 px-4 bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 rounded-xl text-xs font-semibold text-white/90 flex items-center justify-center gap-2.5 transition-all active:scale-[0.98] hover:border-white/20 shadow-sm">
                                <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Pinpoint with My Current GPS Location
                            </button>
                        </div>

                        <!-- Station Name Field -->
                        <div>
                            <label
                                class="text-white/40 font-semibold text-[10px] tracking-wider mb-1 block uppercase">Station
                                Name</label>
                            <input id="station_name" required
                                class="w-full px-4 h-11 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white outline-none placeholder-white/20 focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all focus:bg-[#141527]"
                                type="text" placeholder="e.g., Calasiao Transmission Substation">
                        </div>

                        <!-- Station Type & Status -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-white/40 font-semibold text-[10px] tracking-wider mb-1 block uppercase">Station
                                    Type</label>
                                <div class="relative">
                                    <select id="station_type" required
                                        class="w-full h-11 pl-3 pr-8 bg-[#1E203C] border border-white/10 rounded-xl text-sm text-white/90 outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all cursor-pointer appearance-none focus:bg-[#141527]">
                                        <option value="Substation">Grid Substation</option>
                                        <option value="Solar Plant">Solar Energy Plant</option>
                                        <option value="Wind Farm">Wind Generation Farm</option>
                                        <option value="Hydroelectric">Hydroelectric Facility</option>
                                        <option value="Thermal Plant">Thermal Power Plant</option>
                                        <option value="Geothermal">Geothermal Well</option>
                                        <option value="Battery Storage">Battery Storage Station</option>
                                        <option value="Charging Station">EV Charging Hub</option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-white/30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-white/40 font-semibold text-[10px] tracking-wider mb-1 block uppercase">Availability
                                    Status</label>
                                <div class="relative">
                                    <select id="availability_status" required
                                        class="w-full h-11 pl-3 pr-8 bg-[#1E203C] border border-white/10 rounded-xl text-sm text-white/90 outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all cursor-pointer appearance-none focus:bg-[#141527]">
                                        <option value="Operational">Operational</option>
                                        <option value="Under Maintenance">Under Maintenance</option>
                                        <option value="Offline">Offline</option>
                                        <option value="Planned">Planned / Proposed</option>
                                        <option value="Decommissioned">Decommissioned</option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-white/30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Geographic Location Name -->
                        <div>
                            <label
                                class="text-white/40 font-semibold text-[10px] tracking-wider mb-1 block uppercase">Geographic
                                Location Name</label>
                            <input id="location_name" required
                                class="w-full px-4 h-11 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white outline-none placeholder-white/20 focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all focus:bg-[#141527]"
                                type="text" placeholder="Street, Barangay, City or Coordinates">
                        </div>

                        <!-- Description notes -->
                        <div>
                            <label
                                class="text-white/40 font-semibold text-[10px] tracking-wider mb-1 block uppercase">Operating
                                Notes / Description</label>
                            <textarea id="description"
                                class="w-full h-16 border border-white/10 p-3 rounded-xl bg-white/[0.03] text-white placeholder-white/20 focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 outline-none text-sm resize-none transition-all focus:bg-[#141527]"
                                placeholder="Describe operating configurations, transformers, or current structural logs..."></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                        class="w-full h-12 mt-6 rounded-xl bg-gradient-to-r from-[#FFBB02] to-[#E5A800] hover:from-[#FFC422] hover:to-[#F5B400] text-[#03041A] font-bold text-sm tracking-wide transition-all shadow-[0_4px_20px_rgba(255,187,2,0.15)] hover:shadow-[0_4px_25px_rgba(255,187,2,0.3)] transform hover:-translate-y-0.5 active:translate-y-0 shrink-0">
                        Submit Station Node
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= JAVASCRIPT SYSTEM ENGINE ================= -->
    <script>
        /* ================= LEAFLET MAP ================= */
        /* ================= LEAFLET MAP ================= */
const map = L.map('map', { zoomControl: false }).setView([16.04, 120.33], 12);
L.control.zoom({ position: 'bottomright' }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let layerGroup = L.layerGroup().addTo(map);

let allCachedReports  = [];
let filteredReports   = [];
let currentFilterMode = 'all';
let currentPage       = 1;
const perPage         = 3;

/* ================= DOM HELPERS ================= */
function getEl(id) { return document.getElementById(id); }

function setValue(id, value) {
    const el = getEl(id);
    if (el) el.value = value ?? "";
}

/**
 * Force-sets a <select> by iterating options (case-insensitive).
 * el.value = x silently fails when value casing doesn't match an option.
 */
function setSelectValue(id, value) {
    const el = getEl(id);
    if (!el) return;

    if (value === null || value === undefined || String(value).trim() === "") {
        el.selectedIndex = 0;
        return;
    }

    const normalized = String(value).toLowerCase().trim();
    let matched = false;

    for (let i = 0; i < el.options.length; i++) {
        if (el.options[i].value.toLowerCase().trim() === normalized) {
            el.selectedIndex = i;
            matched = true;
            break;
        }
    }

    if (!matched) {
        console.warn(`setSelectValue: no option matching "${value}" in #${id}`);
        el.selectedIndex = 0;
    }
}

function setText(id, value) {
    const el = getEl(id);
    if (el) el.textContent = value ?? "";
}

function setHTML(id, value) {
    const el = getEl(id);
    if (el) el.innerHTML = value ?? "";
}

function escapeHTML(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

/* ================= STATUS HELPERS ================= */
function getStatusColor(status) {
    switch (String(status || "").toLowerCase().trim()) {
        case "available":   return "#34FB34";
        case "busy":        return "#FFBB02";
        case "offline":     return "#FF2E1F";
        case "maintenance": return "#00E5FF";
        default:            return "#34FB34";
    }
}

function getStatusBadgeStyle(status) {
    switch (String(status || "").toLowerCase().trim()) {
        case "available":   return "bg-green-500/10 text-[#34FB34] border border-green-500/20";
        case "busy":        return "bg-yellow-500/10 text-[#FFBB02] border border-yellow-500/20";
        case "offline":     return "bg-red-500/10 text-[#FF2E1F] border border-red-500/20";
        case "maintenance": return "bg-blue-500/10 text-[#00E5FF] border border-blue-500/20";
        default:            return "bg-green-500/10 text-[#34FB34] border border-green-500/20";
    }
}

/* ================= ALERT HELPERS ================= */
function showAlert(title, message, type = "info") {
    alert((title ? title.toUpperCase() + ": " : "") + message);
}
function showConfirm(message) { return Promise.resolve(confirm(message)); }

/* ================= MODAL MAP STATE ================= */
// Two separate Leaflet map instances — one for create, one for edit.
// They must never share state or interfere with each other.
let createMap;
let createMarker;

let editMap;
let editMarker;

/* ================= MODAL MAP: CREATE ================= */
function initCreateMap(lat = 16.043, lng = 120.333) {
    if (!createMap) {
        createMap = L.map('createModalMap', { zoomControl: false }).setView([lat, lng], 13);
        L.control.zoom({ position: 'bottomright' }).addTo(createMap);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(createMap);

        createMap.on('click', function (e) {
            syncCreateCoords(e.latlng.lat, e.latlng.lng, false);
        });
    }

    createMap.setView([lat, lng], 13);
    createMap.invalidateSize();

    if (createMarker) {
        createMarker.setLatLng([lat, lng]);
    } else {
        createMarker = L.marker([lat, lng], { draggable: true }).addTo(createMap);
        createMarker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            syncCreateCoords(pos.lat, pos.lng, false);
        });
    }

    setValue("create_latitude",  lat.toFixed(8));
    setValue("create_longitude", lng.toFixed(8));
}

/**
 * Updates the create form's lat/lng inputs and optionally reverse-geocodes.
 * Called on map click and marker dragend.
 */
function syncCreateCoords(lat, lng, skipGeocode = false) {
    setValue("create_latitude",  Number(lat).toFixed(8));
    setValue("create_longitude", Number(lng).toFixed(8));

    if (createMarker) {
        createMarker.setLatLng([lat, lng]);
    }
    if (createMap) createMap.panTo([lat, lng]);

    if (!skipGeocode) {
        const locationInput = getEl("create_location_name");
        if (locationInput) {
            locationInput.value = "Fetching address...";
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(r => r.json())
                .then(data => {
                    if (locationInput)
                        locationInput.value = data.display_name || `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
                })
                .catch(() => {
                    if (locationInput)
                        locationInput.value = `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
                });
        }
    }
}

/* ================= MODAL MAP: EDIT ================= */
/**
 * initEditMap — always called with the station's saved coordinates.
 * Creates the edit map once; subsequent calls reposition it.
 * Marker drag and map click BOTH immediately update the hidden lat/lng inputs.
 */
function initEditMap(lat, lng) {
    if (!editMap) {
        editMap = L.map('editModalMap', { zoomControl: false }).setView([lat, lng], 15);
        L.control.zoom({ position: 'bottomright' }).addTo(editMap);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(editMap);

        // ✅ Map click repositions marker AND syncs inputs immediately
        editMap.on('click', function (e) {
            syncEditCoords(e.latlng.lat, e.latlng.lng);
        });
    } else {
        editMap.setView([lat, lng], 15);
    }

    editMap.invalidateSize();

    if (editMarker) {
        editMarker.setLatLng([lat, lng]);
    } else {
        editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);

        // ✅ Marker drag syncs inputs immediately — no stale coordinates on submit
        editMarker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            syncEditCoords(pos.lat, pos.lng);
        });
    }

    // Sync inputs to current position
    syncEditCoords(lat, lng);
}

/**
 * Single source of truth for edit modal coordinate state.
 * Always called instead of writing lat/lng inputs directly.
 */
function syncEditCoords(lat, lng) {
    setValue("edit_latitude",  Number(lat).toFixed(8));
    setValue("edit_longitude", Number(lng).toFixed(8));

    if (editMarker) editMarker.setLatLng([lat, lng]);
    if (editMap)    editMap.panTo([lat, lng]);
}

/* ================= GPS LOCATION (CREATE MODAL) ================= */
function useCreateLocation() {
    if (!navigator.geolocation) {
        showAlert("System Error", "Geolocation not supported.", "error");
        return;
    }
    const locationInput = getEl("create_location_name");
    if (locationInput) locationInput.value = "Acquiring GPS position...";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            syncCreateCoords(lat, lng, false);
            if (createMap) {
                createMap.invalidateSize();
                createMap.setView([lat, lng], 16);
            }
        },
        (error) => {
            const locationInput = getEl("create_location_name");
            if (locationInput && locationInput.value.includes("GPS")) locationInput.value = "";
            let msg = "Unable to fetch location.";
            switch (error.code) {
                case error.PERMISSION_DENIED:    msg = "Permission denied."; break;
                case error.POSITION_UNAVAILABLE: msg = "Location unavailable."; break;
                case error.TIMEOUT:              msg = "Request timed out."; break;
            }
            showAlert("GPS Error", msg, "error");
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

/* ================= MODAL HTML BUILDER ================= */
/**
 * Builds both modals (create + edit) and injects them into #modalContainer or body.
 * All <select> elements have a blank first option — no "selected" attribute anywhere.
 * This prevents form.reset() from snapping selects back to a hardcoded default.
 */
function buildModalHTML() {
    const target = getEl("modalContainer") || document.body;

    // Shared select option HTML — defined once, reused in both modals
    const stationTypeOptions = `
        <option value="">— Select Type —</option>
        <option value="power_station">Power Station</option>
        <option value="solar_station">Solar Station</option>
        <option value="charging_station">Charging Station</option>
        <option value="generator_station">Generator Station</option>`;

    const accessTypeOptions = `
        <option value="">— Select Access —</option>
        <option value="free">Free</option>
        <option value="paid">Paid</option>`;

    // ✅ CRITICAL: blank first option, NO "selected" attribute on any option
    const statusOptions = `
        <option value="">— Select Status —</option>
        <option value="available">Available</option>
        <option value="busy">Busy</option>
        <option value="offline">Offline</option>
        <option value="maintenance">Maintenance</option>`;

    const chargingTypeOptions = `
        <option value="">— Select Charging —</option>
        <option value="AC Level 1">AC Level 1</option>
        <option value="AC Level 2">AC Level 2</option>
        <option value="DC Fast Charge">DC Fast Charge</option>
        <option value="Solar Direct">Solar Direct</option>
        <option value="Generator">Generator</option>
        <option value="Standard Outlet">Standard Outlet</option>`;

    target.insertAdjacentHTML("beforeend", `

    <!-- ==================== CREATE MODAL ==================== -->
    <div id="createPopup"
         class="invisible opacity-0 fixed inset-0 z-[9999] flex items-center justify-center bg-black/70 backdrop-blur-sm transition-all duration-200 p-4">
        <div class="relative w-full max-w-2xl max-h-[95vh] overflow-y-auto rounded-2xl bg-[#13142A] border border-white/10 shadow-2xl">

            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-[#13142A] border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#FFBB02]/10 border border-[#FFBB02]/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#FFBB02]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <span class="text-white font-bold text-base tracking-tight">Register Power Station</span>
                </div>
                <button onclick="closeCreatePopup()"
                        class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="createForm" class="px-6 py-5 space-y-5">

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Station Name <span class="text-red-400">*</span></label>
                    <input id="create_station_name" type="text" placeholder="e.g. Urdaneta Solar Hub"
                           class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all" required>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Location Name <span class="text-red-400">*</span></label>
                    <div class="flex gap-2">
                        <input id="create_location_name" type="text" placeholder="Address or area name"
                               class="flex-1 bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all" required>
                        <button type="button" onclick="useCreateLocation()"
                                class="shrink-0 px-3 py-2 rounded-xl bg-[#FFBB02]/10 border border-[#FFBB02]/20 text-[#FFBB02] hover:bg-[#FFBB02]/20 transition-all" title="Use GPS">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Pin Location <span class="text-red-400">*</span></label>
                    <div id="createModalMap" class="w-full h-52 rounded-xl overflow-hidden border border-white/10"></div>
                    <p class="text-[10px] text-white/30">Click the map or drag the pin to set coordinates.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Latitude</label>
                        <input id="create_latitude" type="text" placeholder="16.04000000" readonly
                               class="w-full bg-[#1C1D30]/60 border border-white/5 rounded-xl px-4 py-3 text-white/60 text-sm cursor-not-allowed">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Longitude</label>
                        <input id="create_longitude" type="text" placeholder="120.33000000" readonly
                               class="w-full bg-[#1C1D30]/60 border border-white/5 rounded-xl px-4 py-3 text-white/60 text-sm cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Station Type <span class="text-red-400">*</span></label>
                        <select id="create_station_type"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${stationTypeOptions}
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Access Type</label>
                        <select id="create_access_type"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${accessTypeOptions}
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Availability Status <span class="text-red-400">*</span></label>
                        <select id="create_availability_status"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${statusOptions}
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Charging Type</label>
                        <select id="create_charging_type"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${chargingTypeOptions}
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Operating Hours</label>
                    <input id="create_operating_hours" type="text" placeholder="e.g. 24/7 or Mon–Fri 8AM–6PM"
                           class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Description</label>
                    <textarea id="create_description" rows="3" placeholder="Brief description of this station..."
                              class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCreatePopup()"
                            class="flex-1 py-3 rounded-xl border border-white/10 text-white/60 hover:text-white hover:border-white/20 text-sm font-semibold transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 rounded-xl bg-[#FFBB02] hover:bg-[#FFBB02]/90 text-black font-bold text-sm transition-all">
                        Submit Station Node
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== EDIT MODAL ==================== -->
    <div id="editPopup"
         class="invisible opacity-0 fixed inset-0 z-[9999] flex items-center justify-center bg-black/70 backdrop-blur-sm transition-all duration-200 p-4">
        <div class="relative w-full max-w-2xl max-h-[95vh] overflow-y-auto rounded-2xl bg-[#13142A] border border-white/10 shadow-2xl">

            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-[#13142A] border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#FFBB02]/10 border border-[#FFBB02]/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#FFBB02]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <span class="text-white font-bold text-base tracking-tight">Update Station Parameters</span>
                </div>
                <button onclick="closeEditPopup()"
                        class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editForm" class="px-6 py-5 space-y-5">

                <!-- Hidden ID — the single source of truth for which record to update -->
                <input type="hidden" id="edit_id">

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Station Name <span class="text-red-400">*</span></label>
                    <input id="edit_station_name" type="text"
                           class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all" required>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Location Name <span class="text-red-400">*</span></label>
                    <input id="edit_location_name" type="text"
                           class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all" required>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Reposition on Map</label>
                    <div id="editModalMap" class="w-full h-52 rounded-xl overflow-hidden border border-white/10"></div>
                    <p class="text-[10px] text-white/30">Drag the pin or click to update coordinates.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Latitude</label>
                        <input id="edit_latitude" type="text" readonly
                               class="w-full bg-[#1C1D30]/60 border border-white/5 rounded-xl px-4 py-3 text-white/60 text-sm cursor-not-allowed">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Longitude</label>
                        <input id="edit_longitude" type="text" readonly
                               class="w-full bg-[#1C1D30]/60 border border-white/5 rounded-xl px-4 py-3 text-white/60 text-sm cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Station Type <span class="text-red-400">*</span></label>
                        <select id="edit_station_type"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${stationTypeOptions}
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Access Type</label>
                        <select id="edit_access_type"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${accessTypeOptions}
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Availability Status <span class="text-red-400">*</span></label>
                        <select id="edit_availability_status"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${statusOptions}
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Charging Type</label>
                        <select id="edit_charging_type"
                                class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FFBB02]/50 transition-all appearance-none">
                            ${chargingTypeOptions}
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Operating Hours</label>
                    <input id="edit_operating_hours" type="text" placeholder="e.g. 24/7 or Mon–Fri 8AM–6PM"
                           class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#B5B5B5] uppercase tracking-widest">Description</label>
                    <textarea id="edit_description" rows="3"
                              class="w-full bg-[#1C1D30] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-[#FFBB02]/50 transition-all resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditPopup()"
                            class="flex-1 py-3 rounded-xl border border-white/10 text-white/60 hover:text-white hover:border-white/20 text-sm font-semibold transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 rounded-xl bg-[#FFBB02] hover:bg-[#FFBB02]/90 text-black font-bold text-sm transition-all">
                        Update Station Node
                    </button>
                </div>
            </form>
        </div>
    </div>
    `);
}

/* ================= POPUP CONTROLS: CREATE ================= */
function openCreatePopup() {
    const popup = getEl("createPopup");
    if (popup) popup.classList.remove("invisible", "opacity-0");

    // Reset the create form fully — safe because this is always a new record
    const form = getEl("createForm");
    if (form) form.reset();

    // Reset all selects to blank placeholder explicitly
    ["create_station_type", "create_access_type", "create_availability_status", "create_charging_type"].forEach(id => {
        const el = getEl(id);
        if (el) el.selectedIndex = 0;
    });

    // Init map after DOM is visible so Leaflet can read container dimensions
    setTimeout(() => {
        initCreateMap(16.043, 120.333);
    }, 50);
}

function closeCreatePopup() {
    const popup = getEl("createPopup");
    if (popup) popup.classList.add("invisible", "opacity-0");
}

/* ================= POPUP CONTROLS: EDIT ================= */
/**
 * openEdit — the reference entry point for editing a station.
 * Follows the exact pattern: populate fields → init map → open popup.
 * NO form.reset() is called here — that would wipe the values we just set.
 */
function openEdit(s) {
    if (!s || !s.id) {
        showAlert("Error", "Invalid station data.");
        return;
    }

    // ── Step 1: Populate all fields from the station object ──────────────────
    // Hidden ID — single source of truth, never changes until form submits
    setValue("edit_id", s.id);

    // Text inputs — use ?? "" so null/undefined become empty string, not "null"
    setValue("edit_station_name",    s.station_name    ?? "");
    setValue("edit_location_name",   s.location_name   ?? "");
    setValue("edit_operating_hours", s.operating_hours ?? "");
    setValue("edit_description",     s.description     ?? "");

    // ✅ Selects — setSelectValue iterates options for exact match.
    //    This is the fix for availability_status always being "available":
    //    el.value = "offline" silently fails if the browser hasn't matched it;
    //    selectedIndex assignment on a confirmed match never fails.
    setSelectValue("edit_station_type",        s.station_type);
    setSelectValue("edit_access_type",         s.access_type);
    setSelectValue("edit_availability_status", s.availability_status);
    setSelectValue("edit_charging_type",       s.charging_type);

    // ── Step 2: Show the popup ────────────────────────────────────────────────
    const popup = getEl("editPopup");
    if (popup) popup.classList.remove("invisible", "opacity-0");

    // ── Step 3: Init edit map after popup is visible ──────────────────────────
    // Delay ensures the #editModalMap container has rendered dimensions before
    // Leaflet tries to measure it. Coordinates come from the station object.
    setTimeout(() => {
        const lat = parseFloat(s.latitude);
        const lng = parseFloat(s.longitude);
        const safeLat = isNaN(lat) ? 16.043 : lat;
        const safeLng = isNaN(lng) ? 120.333 : lng;
        initEditMap(safeLat, safeLng);
    }, 50);
}

function closeEditPopup() {
    const popup = getEl("editPopup");
    if (popup) popup.classList.add("invisible", "opacity-0");
    // Wipe ID so a stale edit can't accidentally re-submit
    setValue("edit_id", "");
}

// Legacy alias — keeps any existing onclick="openPopup()" calls working
function openPopup(editMode = false) {
    if (editMode) return; // edit path now uses openEdit()
    openCreatePopup();
}
function closePopup() { closeCreatePopup(); }

/* ================= API HELPER ================= */
const API_BASE = "http://localhost/crowdsourcedapi/api/power_station";

async function api(url, options = {}) {
    try {
        const res = await fetch(url, {
            method:      options.method || "GET",
            headers:     { "Content-Type": "application/json" },
            credentials: "include",
            body:        options.body || null
        });

        if (!res.ok) {
            const errText = await res.text();
            console.error(`API ${res.status} from ${url}:`, errText);
            return { success: false, message: `Server error ${res.status}: ${errText}` };
        }

        return await res.json();
    } catch (err) {
        console.error("API Error:", err);
        return { success: false, message: "Network error occurred" };
    }
}

/* ================= LOAD FUNCTIONS ================= */
async function loadUserLocation() {
    try {
        const result = await api(`${API_BASE}/get_near_location.php`);
        if (!result.success) return;
        const lat = parseFloat(result.data.latitude);
        const lng = parseFloat(result.data.longitude);
        if (!isNaN(lat) && !isNaN(lng)) {
            map.setView([lat, lng], 14);
            const userLocIcon = L.divIcon({
                className: 'custom-user-location-marker',
                html: `<div class="relative flex items-center justify-center w-8 h-8 rounded-full border-2 border-white shadow-xl bg-[#007AFF]">
                    <svg class="w-4 h-4 text-white animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span class="absolute inset-0 rounded-full bg-[#007AFF] opacity-30 animate-ping"></span>
                </div>`,
                iconSize: [32, 32], iconAnchor: [16, 16]
            });
            L.marker([lat, lng], { icon: userLocIcon }).addTo(map)
                .bindPopup("<strong class='text-xs text-white block text-center'>Registered Home Location</strong>");
        }
    } catch (err) {
        console.error("loadUserLocation error:", err);
    }
}

async function loadStations() {
    try {
        const endpoint = currentFilterMode === 'mine'
            ? `${API_BASE}/get_my_posts.php`
            : `${API_BASE}/get.php`;
        const result = await api(endpoint);

        if (!result.success) {
            showAlert("Sync Issue", result.message || "Failed to load stations", "error");
            return;
        }

        allCachedReports = result.data || [];
        const keyword = getEl("mapSearch")?.value?.trim() || "";

        if (keyword) {
            filterStations(keyword);
        } else {
            filteredReports = [...allCachedReports];
            renderMapMarkers(filteredReports);
            renderStatisticsFeed();
            renderPaginationControls();
        }
    } catch (err) {
        console.error("loadStations error:", err);
        showAlert("Network Error", "Failed to load stations.", "error");
    }
}

function toggleFilterMode(mode) {
    currentFilterMode = mode;
    currentPage = 1;
    const btnAll  = getEl("filterBtnAll");
    const btnMine = getEl("filterBtnMine");

    if (mode === 'mine') {
        if (btnMine) btnMine.className = "text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all";
        if (btnAll)  btnAll.className  = "text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#31324C]/40 text-[#B5B5B5] hover:text-white transition-all";
    } else {
        if (btnAll)  btnAll.className  = "text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all";
        if (btnMine) btnMine.className = "text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#31324C]/40 text-[#B5B5B5] hover:text-white transition-all";
    }
    setValue("mapSearch", "");
    loadStations();
}

/* ================= MAP RENDERING ================= */
function renderMapMarkers(stations) {
    layerGroup.clearLayers();
    let liveCount = 0;

    stations.forEach(s => {
        const lat = parseFloat(s.latitude);
        const lng = parseFloat(s.longitude);
        if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;
        liveCount++;

        const markerColor = getStatusColor(s.availability_status);
        const pulseIcon = L.divIcon({
            className: 'custom-station-pulse-marker',
            html: `<div class="relative flex items-center justify-center w-6 h-6 rounded-full border-2 border-white shadow-md" style="background-color:${markerColor}">
                <svg class="w-3.5 h-3.5 text-[#03041A]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                <span class="absolute -top-1 -right-1 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color:${markerColor}"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" style="background-color:${markerColor}"></span>
                </span>
            </div>`,
            iconSize: [24, 24], iconAnchor: [12, 12]
        });

        const popupContent = `
            <div class="text-white text-xs p-1">
                <strong class="text-sm block border-b border-white/10 pb-1 mb-1 text-[#FFBB02]">${escapeHTML(s.station_name)}</strong>
                <p class="mb-1 text-white/50 uppercase tracking-widest text-[9px] font-bold">${escapeHTML(s.station_type)}</p>
                <p class="mb-2 text-white/80 text-[11px] leading-relaxed">${escapeHTML(s.description || 'No description available.')}</p>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/5">
                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold"
                          style="background:${markerColor}20;color:${markerColor};border:1px solid ${markerColor}40">
                        ${escapeHTML(s.availability_status)}
                    </span>
                    <span class="text-[10px] text-white/40 truncate max-w-[120px]">${escapeHTML(s.location_name)}</span>
                </div>
            </div>`;

        L.marker([lat, lng], { icon: pulseIcon }).bindPopup(popupContent).addTo(layerGroup);
    });

    setHTML("activeOutageCounter", `
        <svg class="w-4 h-4 inline-block mr-1 fill-current text-[#00BA00]" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Active Nodes: ${liveCount}`);
}

function filterStations(keyword) {
    const term = (keyword || "").toLowerCase().trim();
    filteredReports = allCachedReports.filter(r =>
        String(r.station_name        || "").toLowerCase().includes(term) ||
        String(r.location_name       || "").toLowerCase().includes(term) ||
        String(r.station_type        || "").toLowerCase().includes(term) ||
        String(r.availability_status || "").toLowerCase().includes(term)
    );
    currentPage = 1;
    renderStatisticsFeed();
    renderPaginationControls();
}

function renderStatisticsFeed() {
    const feedContainer = getEl("recentReports");
    if (!feedContainer) return;
    feedContainer.innerHTML = "";

    if (!filteredReports.length) {
        feedContainer.innerHTML = `<p class="text-xs text-white/40 text-center py-12">No registered stations found.</p>`;
        return;
    }

    const start    = (currentPage - 1) * perPage;
    const pageData = filteredReports.slice(start, start + perPage);
    const fragment = document.createDocumentFragment();

    pageData.forEach((s) => {
        const badgeStyle = getStatusBadgeStyle(s.availability_status);
        const card = document.createElement("div");
        card.className = "card-hover flex flex-col p-4 border border-white/5 rounded-2xl bg-[#1C1D30]/30 transition-all hover:border-white/10";

        card.innerHTML = `
            ${s.image
                ? `<img src="${escapeHTML(s.image)}" class="w-full h-40 object-cover rounded-xl mb-3" alt="${escapeHTML(s.station_name)}">`
                : '<div class="w-full h-40 bg-[#31324C]/60 rounded-xl mb-3 flex items-center justify-center text-white/30 text-xs">No Image</div>'
            }
            <div class="flex justify-between items-start gap-2">
                <span class="font-bold text-white text-sm tracking-tight leading-tight truncate max-w-[210px]">${escapeHTML(s.station_name)}</span>
                <span class="text-[9px] font-black tracking-wider uppercase px-2 py-0.5 rounded-md ${badgeStyle}">${escapeHTML(s.availability_status)}</span>
            </div>
            <span class="font-medium text-[11px] text-[#B5B5B5] mt-1.5 flex items-center gap-1">
                <svg class="w-3 h-3 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                ${escapeHTML(s.location_name)}
            </span>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <div class="border border-white/5 rounded-xl bg-[#31324C]/40 flex flex-col p-1.5 text-center">
                    <span class="text-[8px] text-[#B5B5B5] font-bold tracking-wider uppercase opacity-40">Type</span>
                    <span class="text-[11px] text-white font-extrabold mt-0.5 truncate">${escapeHTML(s.station_type)}</span>
                </div>
                <div class="border border-white/5 rounded-xl bg-[#31324C]/40 flex flex-col p-1.5 text-center">
                    <span class="text-[8px] text-[#B5B5B5] font-bold tracking-wider uppercase opacity-40">Access</span>
                    <span class="text-[11px] text-white font-extrabold mt-0.5 truncate">${escapeHTML(s.access_type || '—')}</span>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-x-6 text-xs text-white/70">
                <div><span class="opacity-60">Hours:</span> ${escapeHTML(s.operating_hours || 'N/A')}</div>
                <div><span class="opacity-60">Charging:</span> ${escapeHTML(s.charging_type || 'N/A')}</div>
            </div>
            <p class="text-white/60 text-[11px] font-medium leading-relaxed mt-3 line-clamp-3">${escapeHTML(s.description || 'No description provided.')}</p>`;

        const isAuthor = (typeof CURRENT_USER_ID !== "undefined" &&
                          s.created_by && String(s.created_by) === String(CURRENT_USER_ID))
                         || (currentFilterMode === 'mine');

        if (isAuthor) {
            const controls = document.createElement("div");
            controls.className = "flex gap-4 justify-end pt-3 mt-3 border-t border-white/5 text-xs";

            const editBtn = document.createElement("button");
            editBtn.className = "text-[#FFBB02] hover:underline font-semibold flex items-center gap-1";
            editBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg> Edit`;
            editBtn.onclick = () => openEdit(s);   // ← uses the new openEdit()

            const deleteBtn = document.createElement("button");
            deleteBtn.className = "text-red-400 hover:underline font-semibold flex items-center gap-1";
            deleteBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg> Delete`;
            deleteBtn.onclick = () => deleteStation(s.id);

            controls.appendChild(editBtn);
            controls.appendChild(deleteBtn);
            card.appendChild(controls);
        }

        fragment.appendChild(card);
    });

    feedContainer.appendChild(fragment);
}

/* ================= DELETE ================= */
async function deleteStation(id) {
    const confirmed = await showConfirm("Permanently delete this station? This cannot be undone.");
    if (!confirmed) return;

    try {
        const result = await api(`${API_BASE}/delete.php`, {
            method: "POST",
            body: JSON.stringify({ station_id: id })
        });
        showAlert(result.success ? "Success" : "Error", result.message || "Operation complete.", result.success ? "success" : "error");
        if (result.success) loadStations();
    } catch (err) {
        console.error("deleteStation error:", err);
        showAlert("System Error", "Failed to connect to server.", "error");
    }
}

/* ================= SUBMIT: CREATE ================= */
document.addEventListener("DOMContentLoaded", () => {

    buildModalHTML();

    /* ── Create form ── */
    const createForm = getEl("createForm");
    if (createForm) {
        createForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            // ✅ Read every value fresh from the DOM at submit time
            const payload = {
                station_name:        (getEl("create_station_name")?.value        ?? "").trim(),
                location_name:       (getEl("create_location_name")?.value       ?? "").trim(),
                latitude:            (getEl("create_latitude")?.value            ?? "").trim(),
                longitude:           (getEl("create_longitude")?.value           ?? "").trim(),
                station_type:         getEl("create_station_type")?.value        ?? "",
                access_type:          getEl("create_access_type")?.value         ?? "",
                availability_status:  getEl("create_availability_status")?.value ?? "",
                charging_type:        getEl("create_charging_type")?.value       ?? "",
                operating_hours:     (getEl("create_operating_hours")?.value     ?? "").trim(),
                description:         (getEl("create_description")?.value         ?? "").trim(),
            };

            if (!payload.station_name)  { showAlert("Validation Error", "Station name is required.", "error"); return; }
            if (!payload.location_name) { showAlert("Validation Error", "Location name is required.", "error"); return; }
            if (!payload.latitude || !payload.longitude) { showAlert("Missing Coordinates", "Pin a location on the map.", "error"); return; }
            if (!payload.station_type)  { showAlert("Validation Error", "Select a station type.", "error"); return; }
            if (!payload.availability_status) { showAlert("Validation Error", "Select an availability status.", "error"); return; }

            console.log("🚀 CREATE payload:", payload);

            try {
                const result = await api(`${API_BASE}/create.php`, {
                    method: "POST",
                    body:   JSON.stringify(payload)
                });

                console.log("📥 CREATE response:", result);

                if (result.success) {
                    showAlert("Success", result.message || "Station created successfully.");
                    closeCreatePopup();
                    createForm.reset();
                    loadStations();
                } else {
                    showAlert("Error", result.message || "Create failed.", "error");
                }
            } catch (err) {
                console.error("Create submit error:", err);
                showAlert("System Error", "Failed to reach server.", "error");
            }
        });
    }

    /* ── Edit / Update form ── */
    const editForm = getEl("editForm");
    if (editForm) {
        editForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            // ✅ ID comes ONLY from the hidden input — never from a JS variable
            const id = (getEl("edit_id")?.value ?? "").trim();
            if (!id) {
                showAlert("Error", "Station ID is missing. Please re-open the edit modal.", "error");
                return;
            }

            // ✅ Every field read fresh — no stale object data, no default overrides
            const payload = {
                id,
                station_name:        (getEl("edit_station_name")?.value        ?? "").trim(),
                location_name:       (getEl("edit_location_name")?.value       ?? "").trim(),
                latitude:            (getEl("edit_latitude")?.value            ?? "").trim(),
                longitude:           (getEl("edit_longitude")?.value           ?? "").trim(),
                station_type:         getEl("edit_station_type")?.value        ?? "",
                access_type:          getEl("edit_access_type")?.value         ?? "",
                availability_status:  getEl("edit_availability_status")?.value ?? "",
                charging_type:        getEl("edit_charging_type")?.value       ?? "",
                operating_hours:     (getEl("edit_operating_hours")?.value     ?? "").trim(),
                description:         (getEl("edit_description")?.value         ?? "").trim(),
            };

            if (!payload.station_name)  { showAlert("Validation Error", "Station name is required.", "error"); return; }
            if (!payload.location_name) { showAlert("Validation Error", "Location name is required.", "error"); return; }
            if (!payload.latitude || !payload.longitude) { showAlert("Missing Coordinates", "Pin a location on the map.", "error"); return; }
            if (!payload.station_type)  { showAlert("Validation Error", "Select a station type.", "error"); return; }
            if (!payload.availability_status) { showAlert("Validation Error", "Select an availability status.", "error"); return; }

            console.log("🚀 UPDATE payload:", payload);

            try {
                const result = await api(`${API_BASE}/update.php`, {
                    method: "POST",
                    body:   JSON.stringify(payload)
                });

                console.log("📥 UPDATE response:", result);

                if (result.success) {
                    showAlert("Success", result.message || "Station updated successfully.");
                    closeEditPopup();
                    loadStations();
                } else {
                    showAlert("Error", result.message || "Update failed.", "error");
                }
            } catch (err) {
                console.error("Edit submit error:", err);
                showAlert("System Error", "Failed to reach server.", "error");
            }
        });
    }

    loadUserLocation();
    loadStations();
    batteryDetection();
    setInterval(loadStations, 10000);
});

/* ================= PAGINATION ================= */
function renderPaginationControls() {
    const paginationContainer = getEl("pagination");
    if (!paginationContainer) return;
    paginationContainer.innerHTML = "";

    const totalPages = Math.ceil(filteredReports.length / perPage);
    if (totalPages <= 1) return;

    const fragment = document.createDocumentFragment();

    const prevBtn = document.createElement("button");
    prevBtn.textContent = "Prev";
    prevBtn.className = `px-3 py-1.5 text-xs font-bold rounded-lg border border-white/10 transition-all ${currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-transparent text-white/40' : 'bg-[#31324C]/40 text-white hover:bg-[#31324C]'}`;
    prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderStatisticsFeed(); renderPaginationControls(); } };
    fragment.appendChild(prevBtn);

    for (let i = 1; i <= totalPages; i++) {
        const pageBtn = document.createElement("button");
        pageBtn.textContent = i;
        pageBtn.className = `px-3 py-1.5 text-xs font-black rounded-lg transition-all border ${i === currentPage ? 'bg-[#FFBB02] text-black border-[#FFBB02]' : 'bg-[#31324C]/20 text-[#B5B5B5] border-white/5 hover:text-white'}`;
        pageBtn.onclick = () => { currentPage = i; renderStatisticsFeed(); renderPaginationControls(); };
        fragment.appendChild(pageBtn);
    }

    const nextBtn = document.createElement("button");
    nextBtn.textContent = "Next";
    nextBtn.className = `px-3 py-1.5 text-xs font-bold rounded-lg border border-white/10 transition-all ${currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-transparent text-white/40' : 'bg-[#31324C]/40 text-white hover:bg-[#31324C]'}`;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderStatisticsFeed(); renderPaginationControls(); } };
    fragment.appendChild(nextBtn);

    paginationContainer.appendChild(fragment);
}

/* ================= BATTERY ================= */
function batteryDetection() {
    if (!navigator.getBattery) return;
    navigator.getBattery().then(battery => {
        function update() {
            const levelSpan    = getEl("batteryLevel");
            const chargingSpan = getEl("batteryCharging");
            const statusBox    = getEl("batteryStatus");
            if (levelSpan)    levelSpan.innerText    = Math.round(battery.level * 100) + "%";
            if (chargingSpan) chargingSpan.innerText = battery.charging ? "Yes" : "No";
            if (battery.level <= 0.20 && !battery.charging) {
                const toast = getEl("battery-warning");
                if (toast) toast.classList.remove("invisible", "opacity-0");
                if (statusBox) { statusBox.innerText = "Low Battery"; statusBox.style.background = "#e74c3c"; }
            } else if (battery.charging) {
                if (statusBox) { statusBox.innerText = "Charging"; statusBox.style.background = "#2ecc71"; }
            } else {
                if (statusBox) { statusBox.innerText = "Normal"; statusBox.style.background = "#f39c12"; }
            }
        }
        update();
        battery.addEventListener("levelchange", update);
        battery.addEventListener("chargingchange", update);
    });
}

function closeBatteryWarning() {
    const toast = getEl("battery-warning");
    if (toast) toast.classList.add("invisible", "opacity-0");
}

/* ================= MOBILE MENU ================= */
const menuToggle = getEl('menuToggle');
const sidebar    = getEl('sidebar');
const overlay    = getEl('overlay');

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