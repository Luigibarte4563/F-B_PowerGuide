<?php
session_start();

require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();

if ($user['role'] !== 'electric_company') {
    header("Location: " . BASE_URL . "/dashboard/user/user.php");
    exit;
}

// Fallback for missing constants to prevent rendering errors in preview if not defined
if (!defined('PUBLIC_URL')) define('PUBLIC_URL', '/public');
if (!defined('BASE_URL')) define('BASE_URL', '');

$defaultPicture = "https://i.imgur.com/8Km9tLL.png";
$picture = $user['picture'] ?? $defaultPicture;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electric Company Panel - PowerGuide</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #03041A;
            color: white;
        }

        /* Custom utility hover glows */
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
        }
        .glow-active:hover {
            box-shadow: 0 10px 25px -5px rgba(203, 52, 53, 0.2);
        }
        .glow-resolved:hover {
            box-shadow: 0 10px 25px -5px rgba(95, 203, 95, 0.2);
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0D0E2A;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #31324C;
            border-radius: 10px;
        }

        /* Loading skeleton pulse */
        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        /* Dark map adjustment */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-container {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased h-screen overflow-hidden flex">

    <!-- Mobile Menu Toggle -->
    <button id="menuToggle" class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg border border-white/10 hover:bg-opacity-80 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black/60 z-30 hidden transition-opacity duration-300"></div>

    <!-- SIDEBAR NAV -->
    <nav id="sidebar" class="flex flex-col fixed lg:sticky top-0 h-screen w-[280px] lg:w-[320px] 
            text-[#B5B5B5] pt-8 px-5 border-r-2 border-white/5 bg-[#03041A] z-40
            -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex-shrink-0">
        
        <!-- Logo -->
        <div class="flex items-center gap-3 ml-2 mb-10">
            <div class="w-10 h-10 bg-gradient-to-br from-[#FFBB02] to-[#E39A00] rounded-xl flex items-center justify-center shadow-lg shadow-[#FFBB02]/10">
                <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
            </div>
            <div class="flex flex-col justify-center items-start">
                <span class="text-white font-bold text-lg tracking-tight leading-tight">
                    POWER<span class="text-[#FFBB02]">GUIDE</span>
                </span>
                <span class="text-white font-semibold text-[9px] tracking-widest opacity-60 leading-none mt-0.5">
                    ELECTRIC COMPANY
                </span>
            </div>
        </div>

        <!-- Nav Links -->
        <div class="flex flex-col gap-2">
            <span class="text-[11px] font-bold tracking-widest text-white px-4 mb-1 opacity-50">MANAGEMENT</span>

            <a href="dashboard.php" class="group flex flex-row items-center gap-3.5 px-4 h-12 rounded-xl bg-[#FEBB02] text-black font-semibold text-sm transition-all shadow-lg shadow-[#FEBB02]/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="outages.php" class="group flex flex-row items-center gap-3.5 px-4 h-12 rounded-xl hover:bg-[#31324C]/40 hover:text-white transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Manage Outages</span>
            </a>

            <a href="maintenance.php" class="group flex flex-row items-center gap-3.5 px-4 h-12 rounded-xl hover:bg-[#31324C]/40 hover:text-white transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Maintenance</span>
            </a>
        </div>

        <!-- Profile Info Panel -->
        <div class="mt-auto mb-6">
            <div class="flex flex-row items-center justify-between gap-3 px-4 py-3 rounded-2xl bg-[#31324C]/20 border border-white/5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="h-10 w-10 rounded-xl overflow-hidden border border-[#FFBB02]/30 flex-shrink-0 bg-[#1A1B3A]">
                        <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <span class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['name'] ?? 'Company Admin') ?></span>
                        <span class="text-[10px] font-medium text-[#B5B5B5] truncate uppercase">Administrator</span>
                    </div>
                </div>

                <!-- Logout Button -->
                <a href="<?= BACKEND_URL ?>/public/logout.php" class="p-2 text-[#B5B5B5] hover:text-[#CB3435] hover:bg-[#CB3435]/10 rounded-xl transition-all group" title="Logout">
                    <svg class="w-5 h-5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-1 overflow-y-auto custom-scrollbar flex flex-col relative w-full">
        
        <!-- HEADER -->
        <header class="px-6 lg:px-10 pt-20 lg:pt-10 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 border-b border-white/5 bg-[#03041A] sticky top-0 z-20">
            <div>
                <!-- Original large header size retained -->
                <h1 class="text-3xl lg:text-4xl font-black tracking-tight mb-2">Welcome Back, <br class="hidden lg:block"/><span class="text-[#FFBB02]"><?= htmlspecialchars(explode(' ', $user['name'] ?? 'Admin')[0]) ?></span></h1>
                <div class="flex items-center gap-2">
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00BA00] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#00BA00]"></span>
                    </span>
                    <span class="text-xs text-[#B5B5B5] font-medium tracking-widest uppercase">System Telemetry Live</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4 self-end sm:self-auto">
                <div class="text-sm font-medium text-[#B5B5B5] bg-[#31324C]/20 px-4 py-2 rounded-lg border border-white/5">
                    Last Sync: <span id="sync-time" class="text-white">Just now</span>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-10 flex-1 flex flex-col gap-8">
            
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-[#B5B5B5]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h3 class="text-sm font-bold uppercase tracking-widest text-[#B5B5B5]">Grid Status Overview</h3>
            </div>

            <!-- STAT CARDS (Reduced to 2 oversized primary cards) -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Active Outages Card -->
                <div class="card-hover glow-active bg-[#31324C]/20 border border-white/5 rounded-3xl p-8 flex flex-col gap-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#CB3435]/10 rounded-full blur-3xl -mr-10 -mt-10 transition-all group-hover:bg-[#CB3435]/20"></div>
                    
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#CB3435]/10 border border-[#CB3435]/20 p-3.5 rounded-2xl text-[#CB3435]">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-[#CB3435] text-xs font-black px-3 py-1 bg-[#CB3435]/10 rounded-lg tracking-widest uppercase border border-[#CB3435]/20">Active</span>
                    </div>
                    
                    <div class="flex flex-col gap-1 z-10">
                        <!-- Original text-6xl sizing restored -->
                        <span id="activeOutages" class="text-white text-6xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-2">Active Outage Reports</span>
                    </div>
                </div>

                <!-- Resolved Outages Card -->
                <div class="card-hover glow-resolved bg-[#31324C]/20 border border-white/5 rounded-3xl p-8 flex flex-col gap-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#5FCB5F]/10 rounded-full blur-3xl -mr-10 -mt-10 transition-all group-hover:bg-[#5FCB5F]/20"></div>
                    
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#5FCB5F]/10 border border-[#5FCB5F]/20 p-3.5 rounded-2xl text-[#5FCB5F]">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-[#5FCB5F] text-xs font-black px-3 py-1 bg-[#5FCB5F]/10 rounded-lg tracking-widest uppercase border border-[#5FCB5F]/20">Resolved</span>
                    </div>
                    
                    <div class="flex flex-col gap-1 z-10">
                        <!-- Original text-6xl sizing restored -->
                        <span id="resolvedOutages" class="text-white text-6xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-2">Successfully Restored Issues</span>
                    </div>
                </div>
            </section>

            <!-- CENTER SECTION -->
            <section class="flex flex-col gap-6 mt-6 mb-8">

                <!-- Full Width Map Container -->
                <div class="flex flex-col w-full">
                    <div class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#31324C]/20 flex flex-col h-full">
                        <div class="flex flex-row justify-between items-center p-5 border-b border-white/5 bg-[#16172E]/40">
                            <div class="flex flex-row items-center gap-2.5">
                                <div class="text-[#FFBB02]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <span class="font-bold text-sm lg:text-base">System Outage Map</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[#00BA00] px-2.5 py-1 bg-[#00BA00]/10 text-xs rounded-lg font-bold border border-[#00BA00]/20 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-[#00BA00] rounded-full animate-pulse"></span> LIVE LOOP
                                </span>
                            </div>
                        </div>

                        <!-- Map Element Block -->
                        <div id="map" class="w-full h-80 lg:h-[430px] z-10 bg-[#0E0F26]"></div>
                    </div>
                </div>

                <!-- BOTTOM ROW: Wide Reports Lists Feed Container -->
                <div class="w-full">
                    <div class="rounded-2xl border border-white/5 bg-[#31324C]/20 flex flex-col p-6 shadow-xl min-h-[300px]">
                        <span class="text-white text-xs font-bold uppercase tracking-widest opacity-60 mb-4">Latest Reports</span>

                        <!-- Dynamic Station Feed Render Element Container (Wide) -->
                        <div id="list" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 flex-1 overflow-y-auto custom-scrollbar pr-1 max-h-[400px]">
                            <!-- Station feed cards inject dynamically here -->
                        </div>

                        <!-- Pagination Navigation Element -->
                        <div id="pagination" class="flex flex-row gap-1 justify-center items-center mt-5 pt-4 border-t border-white/5">
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
    /* =========================================
       MOBILE MENU LOGIC
    ========================================= */
    const menuToggle = document.getElementById('menuToggle');
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('overlay');

    function toggleMobileSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    menuToggle.addEventListener('click', toggleMobileSidebar);
    overlay.addEventListener('click', toggleMobileSidebar);

    /* =========================================
       GLOBAL STATE
    ========================================= */
    const API_BASE = "http://localhost/crowdsourcedAPI/api/outage_report";
    let map;
    let layerGroup;
    let allCachedReports = [];
    let filteredReports  = [];
    let currentPage      = 1;
    const perPage        = 6;

    /* =========================================
       MAP INITIALIZATION
    ========================================= */
    function initMap() {
        map = L.map('map', { zoomControl: false }).setView([16.04, 120.33], 12);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        layerGroup = L.layerGroup().addTo(map);
    }

    /* =========================================
       SAFE TEXT HELPER (XSS PROTECTION)
    ========================================= */
    function escapeHTML(str) {
        return String(str ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /* =========================================
       LABEL FORMATTERS
    ========================================= */
    // Convert snake_case enum values to readable labels
    function formatCategory(val) {
        const map = {
            power_outage:           "Power Outage",
            low_voltage:            "Low Voltage",
            power_fluctuation:      "Power Fluctuation",
            transformer_explosion:  "Transformer Explosion",
            fallen_power_line:      "Fallen Power Line",
            electrical_fire:        "Electrical Fire",
            scheduled_maintenance:  "Scheduled Maintenance",
            unknown_issue:          "Unknown Issue"
        };
        return map[val] || escapeHTML(val);
    }

    function formatSeverity(val) {
        const map = {
            minor:    "Minor",
            moderate: "Moderate",
            critical: "Critical"
        };
        return map[val] || escapeHTML(val);
    }

    function formatHazard(val) {
        const map = {
            none:           "None",
            smoke:          "Smoke",
            sparks:         "Sparks",
            fire:           "Fire",
            fallen_wire:    "Fallen Wire",
            explosion_sound:"Explosion Sound"
        };
        return map[val] || escapeHTML(val);
    }

    function formatStatus(val) {
        const map = {
            active:       "Active",
            under_review: "Under Review",
            verified:     "Verified",
            resolved:     "Resolved",
            rejected:     "Rejected"
        };
        return map[val] || escapeHTML(val);
    }

    /* =========================================
       STATUS → COLOR MAPPING
    ========================================= */
    function getStatusColor(status) {
        switch ((status || "").toLowerCase()) {
            case "resolved":     return "text-[#00BA00] bg-[#00BA00]/10 border-[#00BA00]/20";
            case "verified":     return "text-[#4FC3F7] bg-[#4FC3F7]/10 border-[#4FC3F7]/20";
            case "under_review": return "text-[#FAB005] bg-[#FAB005]/10 border-[#FAB005]/20";
            case "rejected":     return "text-[#B5B5B5] bg-white/5      border-white/10";
            case "active":
            default:             return "text-[#CB3435] bg-[#CB3435]/10 border-[#CB3435]/20";
        }
    }

    /* =========================================
       SEVERITY → COLOR MAPPING
    ========================================= */
    function getSeverityColor(severity) {
        switch ((severity || "").toLowerCase()) {
            case "critical": return "text-[#CB3435]";
            case "moderate": return "text-[#FAB005]";
            case "minor":
            default:         return "text-[#00BA00]";
        }
    }

    /* =========================================
       GRID SYNCHRONIZATION (FETCH DATA)
    ========================================= */
    async function initGridSynchronization() {
        try {
            // Update sync time
            const now    = new Date();
            const syncEl = document.getElementById('sync-time');
            if (syncEl) {
                syncEl.innerText = now.toLocaleTimeString([], {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
                syncEl.classList.remove("text-[#CB3435]");
            }

            // Fetch all three endpoints in parallel
            const [allRes, activeRes, resolvedRes] = await Promise.all([
                fetch(`${API_BASE}/get.php`,         { credentials: "include" }).catch(() => null),
                fetch(`${API_BASE}/get_active.php`,  { credentials: "include" }).catch(() => null),
                fetch(`${API_BASE}/get_resolve.php`, { credentials: "include" }).catch(() => null)
            ]);

            let allResult      = { data: [], total: 0 };
            let activeResult   = { data: [], total_active_reports: 0 };
            let resolvedResult = { data: [], total_resolved: 0 };

            if (allRes      && allRes.ok)      allResult      = await allRes.json();
            if (activeRes   && activeRes.ok)   activeResult   = await activeRes.json();
            if (resolvedRes && resolvedRes.ok) resolvedResult = await resolvedRes.json();

            const allReports      = allResult.data      || [];
            const activeReports   = activeResult.data   || [];
            const resolvedReports = resolvedResult.data || [];

            // --- Statistic cards ---
            // Active = reports where is_active is true or status is not resolved/rejected
            // Fall back to counting from merged data if the API doesn't return totals
            const activeCount = activeResult.total_active_reports
                ?? activeReports.length
                ?? allReports.filter(r => r.is_active == 1 || r.is_active === true).length;

            const resolvedCount = resolvedResult.total_resolved
                ?? resolvedReports.length
                ?? allReports.filter(r => (r.status || "").toLowerCase() === "resolved").length;

            setCard("activeOutages",   activeCount);
            setCard("resolvedOutages", resolvedCount);

            // --- Merge: get.php seeds first, active/resolved override by id ---
            const combinedMap = new Map();
            allReports.forEach(r      => combinedMap.set(r.id, r));
            activeReports.forEach(r   => combinedMap.set(r.id, r));
            resolvedReports.forEach(r => combinedMap.set(r.id, r));

            allCachedReports = Array.from(combinedMap.values());
            filteredReports  = [...allCachedReports];

            // Render UI
            renderMapMarkers(filteredReports);
            renderStatisticsFeed();
            renderPaginationControls();

        } catch (e) {
            console.error("Data syncing failed:", e);
            setErrorState();
        }
    }

    /* =========================================
       UI UPDATE HELPERS
    ========================================= */
    function setCard(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        const currentVal = parseInt(el.innerText) || 0;
        const targetVal  = value || 0;
        if (currentVal !== targetVal) el.innerText = targetVal;
        el.classList.remove("loading-pulse");
    }

    function setErrorState() {
        ["activeOutages", "resolvedOutages"].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.innerText = "—";
            el.classList.remove("loading-pulse");
            el.classList.add("text-[#CB3435]");
        });
        const syncEl = document.getElementById('sync-time');
        if (syncEl) {
            syncEl.innerText = "Sync Failed";
            syncEl.classList.add("text-[#CB3435]");
        }
    }

    /* =========================================
       MAP MARKERS UPDATE
    ========================================= */
    function renderMapMarkers(reports) {
        layerGroup.clearLayers();
        const bounds = [];

        reports.forEach(r => {
            const lat = parseFloat(r.latitude);
            const lng = parseFloat(r.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            const marker = L.marker([lat, lng]);
            marker.bindPopup(`
                <div class="text-black text-xs p-1" style="min-width:180px">
                    <b class="text-sm border-b pb-1 mb-1 block">
                        ${escapeHTML(formatCategory(r.category))}
                    </b>
                    <b>Status:</b> ${escapeHTML(formatStatus(r.status))}<br>
                    <b>Severity:</b> ${escapeHTML(formatSeverity(r.severity))}<br>
                    <b>Location:</b> ${escapeHTML(r.location_name)}<br>
                    <b>Affected Houses:</b> ${escapeHTML(r.affected_houses ?? 1)}<br>
                    ${r.hazard_type && r.hazard_type !== 'none'
                        ? `<b>Hazard:</b> ${escapeHTML(formatHazard(r.hazard_type))}<br>`
                        : ''}
                    ${r.description
                        ? `<p class="mt-1 border-t pt-1">${escapeHTML(r.description)}</p>`
                        : ''}
                </div>
            `);
            layerGroup.addLayer(marker);
            bounds.push([lat, lng]);
        });

        if (bounds.length > 0 && map) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
        }
    }

    /* =========================================
       LIST FEED RENDERING
    ========================================= */
    function renderStatisticsFeed() {
        const list = document.getElementById("list");
        if (!list) return;

        list.innerHTML = "";

        const start    = (currentPage - 1) * perPage;
        const pageData = filteredReports.slice(start, start + perPage);

        if (pageData.length === 0) {
            list.innerHTML = `
                <div class="text-xs text-white/40 font-medium text-center py-8 col-span-full">
                    No outage reports found.
                </div>`;
            return;
        }

        pageData.forEach(r => {
            const card = document.createElement("div");
            card.className = "bg-[#0D0E2A]/70 border border-white/5 rounded-xl p-4 flex flex-col gap-2 text-left transition-all hover:border-white/10";

            const statusColor   = getStatusColor(r.status);
            const severityColor = getSeverityColor(r.severity);

            // Hazard badge — only show when hazard is present
            const hazardBadge = (r.hazard_type && r.hazard_type !== 'none')
                ? `<span class="px-2 py-0.5 border text-[9px] font-bold rounded-md text-[#FAB005] bg-[#FAB005]/10 border-[#FAB005]/20 uppercase tracking-wide">
                       ⚠ ${escapeHTML(formatHazard(r.hazard_type))}
                   </span>`
                : '';

            card.innerHTML = `
                <!-- Header row: category + status badge -->
                <div class="flex justify-between items-start gap-2">
                    <span class="text-white font-bold text-sm truncate max-w-[180px]">
                        ${escapeHTML(formatCategory(r.category))}
                    </span>
                    <span class="px-2 py-0.5 border text-[9px] font-bold rounded-md ${statusColor} uppercase tracking-wide whitespace-nowrap">
                        ${escapeHTML(formatStatus(r.status))}
                    </span>
                </div>

                <!-- Location -->
                <span class="text-white/80 font-medium text-xs truncate">
                    📍 ${escapeHTML(r.location_name)}
                </span>

                <!-- Severity + Affected houses row -->
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="font-semibold ${severityColor}">
                        ● ${escapeHTML(formatSeverity(r.severity))}
                    </span>
                    <span class="text-white/40">
                        🏠 ${escapeHTML(r.affected_houses ?? 1)} affected
                    </span>
                    ${hazardBadge}
                </div>

                <!-- Description -->
                ${r.description
                    ? `<p class="text-[#B5B5B5]/70 text-[11px] line-clamp-2 border-t border-white/5 pt-1.5 mt-0.5">
                           ${escapeHTML(r.description)}
                       </p>`
                    : ''}
            `;
            list.appendChild(card);
        });
    }

    /* =========================================
       PAGINATION
    ========================================= */
    function renderPaginationControls() {
        const p = document.getElementById("pagination");
        if (!p) return;
        p.innerHTML = "";

        const pages = Math.ceil(filteredReports.length / perPage);
        if (pages <= 1) return;

        for (let i = 1; i <= pages; i++) {
            const btn = document.createElement("button");
            btn.innerText = i;
            btn.className = `h-7 w-7 flex items-center justify-center rounded-lg font-bold text-[11px] transition-all duration-150 ${
                i === currentPage
                    ? "bg-[#FFBB02] text-black shadow-md shadow-[#FFBB02]/10"
                    : "bg-[#31324C]/40 text-[#B5B5B5] hover:bg-[#31324C]/80 hover:text-white"
            }`;
            btn.onclick = () => {
                currentPage = i;
                renderStatisticsFeed();
                renderPaginationControls();
            };
            p.appendChild(btn);
        }
    }

    /* =========================================
       INIT
    ========================================= */
    document.addEventListener("DOMContentLoaded", () => {
        initMap();
        setTimeout(initGridSynchronization, 500);
        setInterval(initGridSynchronization, 15000);
    });
</script>
</body>
</html>