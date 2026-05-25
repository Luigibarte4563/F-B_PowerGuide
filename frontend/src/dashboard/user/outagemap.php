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
        .leaflet-container {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }

        /* ❗ KEEP markers unaffected */
        .leaflet-pane .leaflet-marker-pane,
        .leaflet-pane .leaflet-overlay-pane {
            filter: none !important;
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

                <a href="dashboard.php"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="outagemap.php"
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black text-black transition-colors" fill="none"
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

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 overflow-y-auto bg-[#03041A]">

            <!-- HEADER BAR MATCHING THE COMPACT DASHBOARD ARCHITECTURE STYLE -->
            <header
                class="mx-4 lg:mx-8 mt-14 lg:mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Interactive Outage Map</h1>
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
                                    Critical Severity
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#FFBB02] mr-2 block shadow-sm"></span>
                                    Moderate Severity
                                </span>
                                <span class="font-semibold text-xs flex items-center text-white/90">
                                    <span class="w-2.5 h-2.5 rounded-md bg-[#34FB34] mr-2 block shadow-sm"></span>
                                    Minor Severity
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
                                    All Reports
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
                            node inside this viewport to match the fault line area.</p>
                    </div>
                </div>

                <!-- RIGHT PANEL: Input Form -->
                <form id="outageForm"
                    class="flex-1 flex flex-col justify-between overflow-y-auto p-6 md:p-8 bg-transparent"
                    onsubmit="handleFormSubmit(event)">
                    <input type="hidden" id="report_id" value="">
                    <input type="hidden" id="formLatitude" value="">
                    <input type="hidden" id="formLongitude" value="">

                    <div class="space-y-5">
                        <!-- Header -->
                        <div>
                            <h3 id="formTitle" class="text-2xl font-bold text-white tracking-tight">Report an Incident
                            </h3>
                            <p class="text-xs text-white/50 mt-1">Specify system metrics and location markers for field
                                dispatch analysis.</p>
                        </div>

                        <!-- GPS Trigger Button -->
                        <div>
                            <button type="button" onclick="useCurrentLocation()"
                                class="w-full h-11 px-4 bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 rounded-xl text-xs font-semibold text-white/90 flex items-center justify-center gap-2.5 transition-all active:scale-[0.98] hover:border-white/20 shadow-sm">
                                <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Use My Current Location
                            </button>
                        </div>

                        <!-- Row 1: Severity & Houses -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-white/40 font-semibold text-[10px] tracking-wider mb-1.5 block uppercase">Severity
                                    Level</label>
                                <div class="relative">
                                    <select id="formSeverity"
                                        class="w-full h-11 pl-3 pr-8 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white/90 outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all cursor-pointer appearance-none focus:bg-[#141527]">
                                        <option value="minor">Minor Issue</option>
                                        <option value="moderate">Moderate Outage</option>
                                        <option value="critical">Critical Emergency</option>
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
                                    class="text-white/40 font-semibold text-[10px] tracking-wider mb-1.5 block uppercase">Houses
                                    Affected</label>
                                <input id="formAffectedHouses" required min="1" value="1"
                                    class="w-full px-3 h-11 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white/90 outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all focus:bg-[#141527]"
                                    type="number">
                            </div>
                        </div>

                        <!-- Row 2: Category & Hazard -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-white/40 font-semibold text-[10px] tracking-wider mb-1.5 block uppercase">Category</label>
                                <div class="relative">
                                    <select id="formCategory"
                                        class="w-full h-11 pl-3 pr-8 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white/90 outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all cursor-pointer appearance-none focus:bg-[#141527]">
                                        <option value="power_outage">Power Outage</option>
                                        <option value="low_voltage">Low Voltage</option>
                                        <option value="power_fluctuation">Power Fluctuation</option>
                                        <option value="transformer_explosion">Transformer Explosion</option>
                                        <option value="fallen_power_line">Fallen Power Line</option>
                                        <option value="electrical_fire">Electrical Fire</option>
                                        <option value="scheduled_maintenance">Scheduled Maintenance</option>
                                        <option value="unknown_issue">Unknown Issue</option>
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
                                    class="text-white/40 font-semibold text-[10px] tracking-wider mb-1.5 block uppercase">Hazard
                                    Log</label>
                                <div class="relative">
                                    <select id="formHazardType"
                                        class="w-full h-11 pl-3 pr-8 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white/90 outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all cursor-pointer appearance-none focus:bg-[#141527]">
                                        <option value="none">None</option>
                                        <option value="smoke">Smoke</option>
                                        <option value="sparks">Sparks</option>
                                        <option value="fire">Fire</option>
                                        <option value="fallen_wire">Fallen Wire</option>
                                        <option value="explosion_sound">Explosion Sound</option>
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

                        <!-- Location Input -->
                        <div>
                            <label
                                class="text-white/40 font-semibold text-[10px] tracking-wider mb-1.5 block uppercase">Location
                                Name</label>
                            <input id="formLocation" required
                                class="w-full px-4 h-11 bg-white/[0.03] border border-white/10 rounded-xl text-sm text-white outline-none placeholder-white/20 focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 transition-all focus:bg-[#141527]"
                                type="text" placeholder="e.g., Barangay, Street, Landmark">
                        </div>

                        <!-- Description Input -->
                        <div>
                            <label
                                class="text-white/40 font-semibold text-[10px] tracking-wider mb-1.5 block uppercase">Description
                                / Notes</label>
                            <textarea id="formDescription"
                                class="w-full h-20 border border-white/10 p-3 rounded-xl bg-white/[0.03] text-white placeholder-white/20 focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02]/20 outline-none text-sm resize-none transition-all focus:bg-[#141527]"
                                placeholder="Describe visible damage, sounds, or weather context..."></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full h-12 mt-6 rounded-xl bg-gradient-to-r from-[#FFBB02] to-[#E5A800] hover:from-[#FFC422] hover:to-[#F5B400] text-[#03041A] font-bold text-sm tracking-wide transition-all shadow-[0_4px_20px_rgba(255,187,2,0.15)] hover:shadow-[0_4px_25px_rgba(255,187,2,0.3)] transform hover:-translate-y-0.5 active:translate-y-0 shrink-0">
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
        /* ================= ALERT CONFIGURATION ================= */
        const modalTypes = {
            success: { color: 'text-[#34FB34]', bg: 'bg-[#34FB34]/10', shadow: 'shadow-[0_0_15px_rgba(52,251,52,0.4)]', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>' },
            error: { color: 'text-[#FF2E1F]', bg: 'bg-[#FF2E1F]/10', shadow: 'shadow-[0_0_15px_rgba(255,46,31,0.4)]', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>' },
            warning: { color: 'text-[#FFBB02]', bg: 'bg-[#FFBB02]/10', shadow: 'shadow-[0_0_15px_rgba(255,187,2,0.4)]', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>' },
            info: { color: 'text-[#00E5FF]', bg: 'bg-[#00E5FF]/10', shadow: 'shadow-[0_0_15px_rgba(0,229,255,0.4)]', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
        };

        /* ================= STREAMING_CHUNK: CUSTOM ALERTS & CONFIRMS ================= */
        function injectCustomModals() {
            if (!document.getElementById('custom-modal-container')) {
                const container = document.createElement('div');
                container.id = 'custom-modal-container';
                container.className = 'fixed inset-0 z-[9999] pointer-events-none flex flex-col items-center justify-center p-4';
                document.body.appendChild(container);
            }
        }

        function showCustomAlert(message, type = 'info', title = 'Notice') {
            const style = modalTypes[type] || modalTypes.info;
            const container = document.getElementById('custom-modal-container');

            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 pointer-events-auto flex items-center justify-center p-4 bg-[#1C1D30]/80 backdrop-blur-sm transition-opacity duration-200 opacity-0';

            const modal = document.createElement('div');
            modal.className = `bg-[#13142A] border border-white/10 rounded-2xl p-6 shadow-2xl max-w-sm w-full mx-4 transform scale-95 transition-transform duration-200 text-center`;

            modal.innerHTML = `
                <div class="w-12 h-12 rounded-full mb-4 flex items-center justify-center mx-auto ${style.bg} ${style.color} ${style.shadow}">
                    ${style.icon}
                </div>
                <h3 class="text-lg font-bold text-white mb-2">${title}</h3>
                <p class="text-[#B5B5B5] text-sm mb-6">${message}</p>
                <button class="w-full ${style.bg} ${style.color} border border-white/10 font-bold py-2.5 rounded-lg hover:brightness-125 transition-all">
                    Acknowledge
                </button>
            `;

            overlay.appendChild(modal);
            container.appendChild(overlay);

            const closeBtn = modal.querySelector('button');
            closeBtn.onclick = () => {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                modal.classList.remove('scale-100');
                modal.classList.add('scale-95');
                setTimeout(() => overlay.remove(), 200);
            };

            // Trigger enter animation
            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
                modal.classList.remove('scale-95');
                modal.classList.add('scale-100');
            });
        }

        function showCustomConfirm(message, title = 'Confirm Action') {
            return new Promise((resolve) => {
                const style = modalTypes.error; // Defaults to red/error style for destructive confirm actions
                const container = document.getElementById('custom-modal-container');

                const overlay = document.createElement('div');
                overlay.className = 'absolute inset-0 pointer-events-auto flex items-center justify-center p-4 bg-[#1C1D30]/80 backdrop-blur-sm transition-opacity duration-200 opacity-0';

                const modal = document.createElement('div');
                modal.className = `bg-[#13142A] border border-white/10 rounded-2xl p-6 shadow-2xl max-w-sm w-full mx-4 transform scale-95 transition-transform duration-200 text-center`;

                modal.innerHTML = `
                    <div class="w-12 h-12 rounded-full mb-4 flex items-center justify-center mx-auto ${style.bg} ${style.color} ${style.shadow}">
                        ${style.icon}
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">${title}</h3>
                    <p class="text-[#B5B5B5] text-sm mb-6">${message}</p>
                    <div class="flex gap-3">
                        <button id="custom-confirm-no" class="flex-1 bg-transparent border border-white/10 text-white font-bold py-2.5 rounded-lg hover:bg-white/5 transition-colors">
                            Cancel
                        </button>
                        <button id="custom-confirm-yes" class="flex-1 bg-red-500 text-white font-bold py-2.5 rounded-lg hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20">
                            Proceed
                        </button>
                    </div>
                `;

                overlay.appendChild(modal);
                container.appendChild(overlay);

                const btnYes = modal.querySelector('#custom-confirm-yes');
                const btnNo = modal.querySelector('#custom-confirm-no');

                const cleanup = () => {
                    overlay.classList.remove('opacity-100');
                    overlay.classList.add('opacity-0');
                    modal.classList.remove('scale-100');
                    modal.classList.add('scale-95');
                    setTimeout(() => overlay.remove(), 200);
                };

                btnYes.onclick = () => { cleanup(); resolve(true); };
                btnNo.onclick = () => { cleanup(); resolve(false); };

                // Trigger enter animation
                requestAnimationFrame(() => {
                    overlay.classList.remove('opacity-0');
                    overlay.classList.add('opacity-100');
                    modal.classList.remove('scale-95');
                    modal.classList.add('scale-100');
                });
            });
        }

        /* ================= STREAMING_CHUNK:Configuring the Leaflet mapping layers... ================= */
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
        const perPage = 3;

        /* ================= SAFE TEXT HELPER (XSS PROTECTION) ================= */
        function escapeHTML(str) {
            return String(str ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        /* ================= STREAMING_CHUNK:Initializing the picker environment modal map... ================= */
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
                setModalCoordinates(e.latlng.lat, e.latlng.lng, false);
            });
        }

        /* ================= STREAMING_CHUNK:Implementing geocoding & coordination synchronization... ================= */
        function setModalCoordinates(lat, lng, skipGeocode = false) {
            document.getElementById("formLatitude").value = Number(lat).toFixed(6);
            document.getElementById("formLongitude").value = Number(lng).toFixed(6);

            if (modalSelectionMarker) {
                modalSelectionMarker.setLatLng([lat, lng]);
            } else {
                modalSelectionMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);
                modalSelectionMarker.on('dragend', function (event) {
                    const marker = event.target;
                    const position = marker.getLatLng();
                    setModalCoordinates(position.lat, position.lng, false);
                });
            }
            modalMap.panTo([lat, lng]);

            // Auto-fill the location name field with a clean reverse-geocoding lookup sequence
            if (!skipGeocode) {
                const locationInput = document.getElementById("formLocation");
                if (locationInput) {
                    locationInput.value = "Fetching address from GPS telemetry...";
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            locationInput.value = data.display_name || `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
                        })
                        .catch(() => {
                            locationInput.value = `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
                        });
                }
            }
        }

        /* ================= STREAMING_CHUNK:Implementing high-accuracy geolocation utilities... ================= */
        function useCurrentLocation() {
            if (!navigator.geolocation) {
                showCustomAlert("Geolocation is not supported by your browser.", "error", "System Error");
                return;
            }

            const locationInput = document.getElementById("formLocation");
            if (locationInput) {
                locationInput.value = "Interrogating GPS Satellite telemetry...";
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    console.log("GPS:", lat, lng);

                    // Set hidden coordinate bounds and run geocoding lookup sequence
                    setModalCoordinates(lat, lng, false);

                    // Ensure modal Leaflet map canvas target exists
                    if (!modalMap) {
                        initModalMap();
                    }

                    // Force map viewport layout refresh before panning
                    setTimeout(() => {
                        modalMap.invalidateSize();
                        modalMap.setView([lat, lng], 16);
                    }, 100);
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    if (locationInput && locationInput.value.includes("GPS")) {
                        locationInput.value = "";
                    }

                    let msg = "Unable to fetch location.";

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            msg = "Permission denied. Please allow location access.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            msg = "Location unavailable.";
                            break;
                        case error.TIMEOUT:
                            msg = "Location request timed out.";
                            break;
                    }

                    showCustomAlert(msg, "error", "GPS Error");
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        /* ================= STREAMING_CHUNK:Managing popup workflows and modal actions... ================= */
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
                    setModalCoordinates(16.043, 120.333, true); // Don't lookup generic default location coordinate
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

        /* ================= STREAMING_CHUNK:Declaring API business logic and CRUD routines... ================= */
        const API_BASE = "http://localhost/crowdsourcedAPI/api/outage_report";

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
                hazard_type: document.getElementById("formHazardType").value,
                affected_houses: parseInt(document.getElementById("formAffectedHouses").value) || 1,
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

                showCustomAlert(
                    result.message || "Record updated successfully.",
                    result.success ? "success" : "error",
                    result.success ? "Update Success" : "Update Failed"
                );

                if (result.success) {
                    closeForm();
                    initGridSynchronization();
                }
            } catch (err) {
                console.error(err);
                showCustomAlert("Update failed. Please check your connection.", "error", "System Error");
            }
        }

        async function deleteReport(id) {
            const isConfirmed = await showCustomConfirm("Are you sure you want to permanently delete this report?");
            if (!isConfirmed) return;

            try {
                const res = await fetch(`http://localhost/crowdsourcedAPI/api/outage_report/delete.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({ id })
                });
                const result = await res.json();

                showCustomAlert(
                    result.message || "Record deleted successfully.",
                    result.success ? "success" : "error",
                    result.success ? "Deleted" : "Deletion Failed"
                );

                if (result.success) {
                    initGridSynchronization();
                }
            } catch (err) {
                console.error(err);
                showCustomAlert("Delete failed. Please check your connection.", "error", "System Error");
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
                hazard_type: document.getElementById("formHazardType").value,
                affected_houses: parseInt(document.getElementById("formAffectedHouses").value) || 1,
                description: document.getElementById("formDescription").value,
                latitude: document.getElementById("formLatitude").value,
                longitude: document.getElementById("formLongitude").value
            };

            try {
                const result = await createReport(payload);

                showCustomAlert(
                    result.message || "Operation Completed successfully",
                    result.success ? "success" : "error",
                    result.success ? "Success" : "Submission Failed"
                );

                if (result.success) {
                    closePopup();
                    initGridSynchronization();
                }
            } catch (err) {
                console.error(err);
                showCustomAlert("API Submission encountered an error.", "error", "System Error");
            }
        }

        /* ================= FILTERING LOGIC MANAGEMENT TABS ================= */
        function toggleFilterMode(mode) {
            currentFilterMode = mode;
            const btnAll = document.getElementById("filterBtnAll");
            const btnMine = document.getElementById("filterBtnMine");

            if (mode === 'mine') {
                btnMine.className = "text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all";
                btnAll.className = "text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#13142A]/40 text-[#B5B5B5] hover:text-white transition-all";
            } else {
                btnAll.className = "text-xs font-bold rounded-lg px-3 py-1 border border-[#FFBB02] bg-[#FFBB02] text-black transition-all";
                btnMine.className = "text-xs font-bold rounded-lg px-3 py-1 border border-white/10 bg-[#13142A]/40 text-[#B5B5B5] hover:text-white transition-all";
            }
            currentPage = 1;
            document.getElementById("mapSearch").value = "";
            initGridSynchronization();
        }

        /* ================= STREAMING_CHUNK:Synchronizing the data points with the Leaflet map... ================= */
        async function initGridSynchronization() {
            try {
                // 1. Fetch both endpoints concurrently
                const [publicRes, mineRes] = await Promise.all([
                    fetch(`${API_BASE}/get.php`, { credentials: "include" }),
                    fetch(`${API_BASE}/get_my_report.php`, { credentials: "include" })
                ]);

                const publicResult = await publicRes.json();
                const mineResult = await mineRes.json();

                const publicReports = publicResult.data || [];
                const myReports = mineResult.data || [];

                // 2. Merge reports using a Map to prevent duplicate markers
                const combinedMap = new Map();
                publicReports.forEach(r => combinedMap.set(r.id, r));
                myReports.forEach(r => combinedMap.set(r.id, r));

                // 3. Get the full merged array from the Map
                let allReports = Array.from(combinedMap.values());

                // 4. In "all" mode, strip out resolved/rejected — keep them visible in "mine" mode
                if (currentFilterMode === "all") {
                    allReports = allReports.filter(r => {
                        const status = String(r.status || "").toLowerCase().trim();
                        return status !== "resolved" && status !== "rejected";
                    });
                }

                // 5. Assign to cache based on active tab
                allCachedReports = (currentFilterMode === "mine") ? myReports : allReports;

                // 6. Render map using the now-filtered dataset
                renderMapMarkers(allCachedReports);

                // 7. Handle feed filtering and pagination
                const currentKeyword = document.getElementById("mapSearch")?.value || "";
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

                // =========================================
                // HIDE RESOLVED + REJECTED MARKERS
                // =========================================
                const status = String(r.status || "")
                    .toLowerCase()
                    .trim();

                if (status === "resolved" || status === "rejected") {
                    return;
                }

                // =========================================
                // VALIDATE COORDINATES
                // =========================================
                const lat = parseFloat(r.latitude);
                const lng = parseFloat(r.longitude);

                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }

                activeAlertsCount++;

                // =========================================
                // MARKER COLOR BY SEVERITY
                // =========================================
                const severity = String(r.severity ?? "")
                    .toLowerCase()
                    .trim();

                let markerColor = "#FFD400"; // default = moderate (clean yellow)

                /* =========================================
                   SEVERITY COLOR MAPPING (STRICT)
                ========================================= */

                // Critical
                if (
                    severity === "critical" ||
                    severity.includes("critical") ||
                    severity === "high"
                ) {
                    markerColor = "#FF2E1F"; // red
                }

                // Moderate
                else if (
                    severity === "moderate" ||
                    severity.includes("moderate") ||
                    severity === "medium"
                ) {
                    markerColor = "#FFD400"; // bright yellow (FIXED)
                }

                // Minor
                else if (
                    severity === "minor" ||
                    severity.includes("minor") ||
                    severity === "low"
                ) {
                    markerColor = "#34FB34"; // green
                }

                // Fallback (important for debugging bad data)
                else {
                    markerColor = "#9CA3AF"; // gray (unknown severity)
                }

                // =========================================
                // FORMAT CATEGORY
                // =========================================
                const formattedCategory = (
                    r.category ?? "power_outage"
                ).replace(/_/g, " ");

                // =========================================
                // CREATE MAP PIN
                // =========================================
                L.circleMarker([lat, lng], {
                    radius: 9,
                    fillColor: markerColor,
                    color: "#ffffff",   // always white border for clarity
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                })

                    .bindPopup(`

        <div class="text-white text-xs">

            <strong class="text-sm block border-b border-white/10 pb-1 mb-1">
                ${escapeHTML(r.location_name)}
            </strong>

            <p class="mb-1 text-white/50 uppercase tracking-widest text-[9px] font-bold">
                ${escapeHTML(formattedCategory)}
            </p>

            <p class="mb-1 text-white/80">
                ${escapeHTML(r.description || 'No system notes noted.')}
            </p>

            <div class="flex items-center justify-between mt-2 pt-1 border-t border-white/5">

                <span
                    class="inline-block px-1.5 py-0.5 rounded font-black text-[9px] uppercase"
                    style="background:${markerColor}; color:#000;"
                >
                    ${escapeHTML(r.severity || "moderate")}
                </span>

                <span class="text-[10px] text-white/60 font-semibold">
                    Status: ${escapeHTML(r.status || 'active')}
                </span>

            </div>

        </div>

    `)

                    .addTo(layerGroup);

            });

            // Defensive Check: Ensure the element exists before accessing .innerHTML
            const counterElement = document.getElementById("activeOutageCounter");

            if (counterElement) {
                counterElement.innerHTML = `
                    <svg class="w-4 h-4 inline-block mr-1 fill-current text-[#00BA00]" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Active Incidents: ${activeAlertsCount} Locations Reporting
                `;
            } else {
                // Fails silently but gracefully in production; logs a warning for development
                console.warn("UI Element missing: #activeOutageCounter not found in the DOM.");
            }
        }

        function filterBarangay(keyword) {
            filteredReports = allCachedReports.filter(r =>
                r.location_name.toLowerCase().includes(keyword.toLowerCase()) ||
                (r.description && r.description.toLowerCase().includes(keyword.toLowerCase())) ||
                (r.category && r.category.toLowerCase().includes(keyword.toLowerCase()))
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
                const severity = (r.severity || "moderate").toLowerCase();
                let badgeStyle = "bg-[#FFBB021A]/[10%] text-[#FFBB02]";
                let textLabel = "MODERATE";

                if (severity === "critical") {
                    badgeStyle = "bg-[#FF3C2F1A]/[10%] text-[#FF2E1F]";
                    textLabel = "CRITICAL";
                } else if (severity === "minor") {
                    badgeStyle = "bg-[#22FF221A]/[10%] text-[#34FB34]";
                    textLabel = "MINOR";
                }

                const displayCategory = (r.category || 'power_outage').replace(/_/g, ' ');
                const displayHazard = (r.hazard_type || 'none').replace(/_/g, ' ');
                const displayStatus = (r.status || 'active').replace(/_/g, ' ');

                const isAuthor = (typeof CURRENT_USER_ID !== 'undefined' && r.user_id && String(r.user_id) === String(CURRENT_USER_ID)) || (currentFilterMode === 'mine');

                const card = document.createElement("div");
                card.className = "card-hover flex flex-col p-4 border border-white/5 rounded-2xl bg-[#1C1D30]/30 transition-all hover:border-white/10";

                card.innerHTML = `
                    <div class="flex flex-col">
                        <span class="font-bold flex justify-between items-center text-white text-base md:text-lg">
                            <span class="truncate max-w-[240px]">${escapeHTML(r.location_name)}</span>
                            <span class="flex ${badgeStyle} px-2 py-1 text-[10px] items-center rounded-lg font-black tracking-wider shadow-sm shrink-0 uppercase">
                                ${escapeHTML(textLabel)}
                            </span>
                        </span>
                        <span class="font-medium text-xs text-[#B5B5B5] mt-1 flex items-center gap-1.5 capitalize">
                            <svg class="w-3.5 h-3.5 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            ${escapeHTML(displayCategory)}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-3.5">
                        <div class="border border-white/5 rounded-xl bg-[#13142A]/40 flex flex-col p-2 text-center">
                            <span class="text-[9px] text-[#B5B5B5] font-bold tracking-wide uppercase opacity-50">AFFECTED</span>
                            <span class="text-xs text-white font-extrabold mt-0.5">${parseInt(r.affected_houses) || 1} Hse</span>
                        </div>
                        <div class="border border-white/5 rounded-xl bg-[#31324C]/40 flex flex-col p-2 text-center">
                            <span class="text-[9px] text-[#B5B5B5] font-bold tracking-wide uppercase opacity-50">HAZARD</span>
                            <span class="text-xs text-[#FFBB02] font-extrabold mt-0.5 truncate capitalize">${escapeHTML(displayHazard)}</span>
                        </div>
                        <div class="border border-white/5 rounded-xl bg-[#31324C]/40 flex flex-col p-2 text-center">
                            <span class="text-[9px] text-[#B5B5B5] font-bold tracking-wide uppercase opacity-50">STATUS</span>
                            <span class="text-xs text-white font-extrabold mt-0.5 truncate capitalize">${escapeHTML(displayStatus)}</span>
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

        /* ================= STREAMING_CHUNK:Configuring system edit modes... ================= */
        function editReportByObject(r) {
            if (!r) return;

            document.getElementById("report_id").value = r.id;
            document.getElementById("formLocation").value = r.location_name;
            document.getElementById("formCategory").value = r.category;
            document.getElementById("formSeverity").value = r.severity;
            document.getElementById("formHazardType").value = r.hazard_type || "none";
            document.getElementById("formAffectedHouses").value = r.affected_houses || 1;
            document.getElementById("formDescription").value = r.description || "";

            document.getElementById("formTitle").innerText = "Update Incident Report Layout";
            openPopup(true);

            setTimeout(() => {
                if (r.latitude && r.longitude) {
                    // Load coords in edit state while preserving saved customized location names
                    setModalCoordinates(parseFloat(r.latitude), parseFloat(r.longitude), true);
                }
            }, 100);
        }

        /* ================= STREAMING_CHUNK:Implementing local hardware battery warnings... ================= */
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
            injectCustomModals(); // Inject the UI blocks for customized alerts
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