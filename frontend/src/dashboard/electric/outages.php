<?php
session_start();

require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();

if ($user['role'] !== 'electric_company') {
    header("Location: " . BASE_URL . "/dashboard/user/user.php");
    exit;
}

if (!defined('PUBLIC_URL'))
    define('PUBLIC_URL', '/public');
if (!defined('BASE_URL'))
    define('BASE_URL', '');
if (!defined('BACKEND_URL'))
    define('BACKEND_URL', '');

$defaultPicture = "https://i.imgur.com/8Km9tLL.png";
$picture = $user['picture'] ?? $defaultPicture;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Outages — PowerGuide Electric</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #03041A;
            color: white;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-3px);
        }

        .glow-active:hover {
            box-shadow: 0 10px 25px -5px rgba(203, 52, 53, 0.2);
        }

        .glow-resolved:hover {
            box-shadow: 0 10px 25px -5px rgba(95, 203, 95, 0.2);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0D0E2A;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #31324C;
            border-radius: 10px;
        }

        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .4
            }
        }

        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-container {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }

        /* Report card selected state */
        .report-card.selected {
            border-color: rgba(254, 187, 2, 0.4) !important;
            background: rgba(254, 187, 2, 0.04) !important;
        }

        /* Action button base */
        .action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .action-btn:hover {
            filter: brightness(1.15);
            transform: translateY(-1px);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .action-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }

        .btn-verify {
            background: rgba(79, 195, 247, 0.12);
            color: #4FC3F7;
            border-color: rgba(79, 195, 247, 0.25);
        }

        .btn-resolve {
            background: rgba(0, 186, 0, 0.12);
            color: #00BA00;
            border-color: rgba(0, 186, 0, 0.25);
        }

        .btn-review {
            background: rgba(250, 176, 5, 0.12);
            color: #FAB005;
            border-color: rgba(250, 176, 5, 0.25);
        }

        .btn-reject {
            background: rgba(203, 52, 53, 0.12);
            color: #CB3435;
            border-color: rgba(203, 52, 53, 0.25);
        }

        .btn-bulk-blue {
            background: rgba(79, 195, 247, 0.10);
            color: #4FC3F7;
            border-color: rgba(79, 195, 247, 0.2);
        }

        .btn-bulk-warn {
            background: rgba(250, 176, 5, 0.10);
            color: #FAB005;
            border-color: rgba(250, 176, 5, 0.2);
        }

        .btn-bulk-green {
            background: rgba(0, 186, 0, 0.10);
            color: #00BA00;
            border-color: rgba(0, 186, 0, 0.2);
        }

        .btn-bulk-red {
            background: rgba(203, 52, 53, 0.10);
            color: #CB3435;
            border-color: rgba(203, 52, 53, 0.2);
        }

        /* Slide-in for detail panel */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(12px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .slide-up {
            animation: slideUp 0.22s ease forwards;
        }

        /* Spinner */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-top-color: #FFBB02;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased h-screen overflow-hidden flex">

    <!-- Mobile toggle -->
    <button id="menuToggle"
        class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg border border-white/10 hover:bg-opacity-80 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div id="overlay" class="fixed inset-0 bg-black/60 z-30 hidden"></div>

    <!-- ===================== SIDEBAR ===================== -->
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

            <a href="dashboard.php" class="group flex flex-row items-center gap-3.5 px-4 h-12 rounded-xl font-semibold text-sm transition-all shadow-lg shadow-[#FEBB02]/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="outages.php" class="group flex flex-row items-center gap-3.5 px-4 h-12 rounded-xl bg-[#FEBB02] text-black hover:bg-[#31324C]/40 hover:text-white transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] text-black group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

    <!-- ===================== MAIN ===================== -->
    <main class="flex-1 overflow-y-auto custom-scrollbar flex flex-col relative w-full">

        <!-- HEADER -->
        <header
            class="px-6 lg:px-10 pt-20 lg:pt-8 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-white/5 bg-[#03041A] sticky top-0 z-20">
            <div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-tight">
                    Outage <span class="text-[#FFBB02]">Management</span>
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00BA00] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00BA00]"></span>
                    </span>
                    <span class="text-[10px] text-[#B5B5B5] font-medium tracking-widest uppercase">Live
                        Monitoring</span>
                </div>
            </div>
            <div
                class="text-sm font-medium text-[#B5B5B5] bg-[#31324C]/20 px-4 py-2 rounded-lg border border-white/5 self-end sm:self-auto">
                Last Sync: <span id="sync-time" class="text-white">—</span>
            </div>
        </header>

        <div class="p-6 lg:p-10 flex flex-col gap-7">

            <!-- STAT CARDS -->
            <section class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div
                    class="card-hover glow-active bg-[#31324C]/20 border border-white/5 rounded-3xl p-7 flex flex-col gap-5 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-28 h-28 bg-[#CB3435]/10 rounded-full blur-3xl -mr-8 -mt-8 group-hover:bg-[#CB3435]/20 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#CB3435]/10 border border-[#CB3435]/20 p-3 rounded-2xl text-[#CB3435]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#CB3435] text-[10px] font-black px-3 py-1 bg-[#CB3435]/10 rounded-lg tracking-widest uppercase border border-[#CB3435]/20">Active</span>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="activeOutages"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-1">Active Outage Reports</span>
                    </div>
                </div>

                <div
                    class="card-hover glow-resolved bg-[#31324C]/20 border border-white/5 rounded-3xl p-7 flex flex-col gap-5 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-28 h-28 bg-[#5FCB5F]/10 rounded-full blur-3xl -mr-8 -mt-8 group-hover:bg-[#5FCB5F]/20 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#5FCB5F]/10 border border-[#5FCB5F]/20 p-3 rounded-2xl text-[#5FCB5F]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#5FCB5F] text-[10px] font-black px-3 py-1 bg-[#5FCB5F]/10 rounded-lg tracking-widest uppercase border border-[#5FCB5F]/20">Resolved</span>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="resolvedOutages"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-1">Successfully Restored Issues</span>
                    </div>
                </div>

            </section>

            <!-- FILTER + BULK ACTIONS BAR -->
            <section class="rounded-2xl border border-white/5 bg-[#31324C]/10 p-5 flex flex-col gap-4">

                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 010 2H4a1 1 0 01-1-1zM6 10h12M9 16h6" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-widest text-[#B5B5B5]">Filter & Bulk
                        Actions</span>
                </div>

                <!-- Row 1: filter -->
                <div class="flex flex-wrap gap-3 items-center">
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Barangay</label>
                        <select id="barangayFilter"
                            class="bg-[#0D0E2A] border border-white/10 text-white text-xs font-semibold rounded-xl px-3 py-2 pr-8 cursor-pointer focus:outline-none focus:border-[#FFBB02]/40 transition-colors appearance-none min-w-[180px]"
                            onchange="applyFilter()">
                            <option value="all">All Barangays</option>
                            <option value="Bonuan Gueset">Bonuan Gueset</option>
                            <option value="Bonuan Boquig">Bonuan Boquig</option>
                            <option value="Bonuan Binloc">Bonuan Binloc</option>
                            <option value="Lucao">Lucao</option>
                            <option value="Tapuac">Tapuac</option>
                            <option value="Tambac">Tambac</option>
                            <option value="Pantal">Pantal</option>
                            <option value="Herrero-Perez">Herrero-Perez</option>
                            <option value="Mayombo">Mayombo</option>
                            <option value="Poblacion Oeste">Poblacion Oeste</option>
                            <option value="Poblacion Este">Poblacion Este</option>
                        </select>
                    </div>

                    <!-- Result count badge -->
                    <div class="flex flex-col gap-1 self-end">
                        <span id="resultCount"
                            class="text-[10px] font-bold text-white/30 px-3 py-2 bg-white/5 rounded-xl border border-white/5">—
                            reports</span>
                    </div>
                </div>

                <!-- Row 2: bulk barangay buttons -->
                <div class="flex flex-wrap gap-2 items-center">
                    <span
                        class="text-[10px] text-white/30 font-bold uppercase tracking-widest mr-1 self-center">Selected
                        Barangay:</span>
                    <button class="action-btn btn-bulk-blue" onclick="updateBarangay('under_review')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Under Review
                    </button>
                    <button class="action-btn btn-bulk-warn" onclick="updateBarangay('verified')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Verify Barangay
                    </button>
                    <button class="action-btn btn-bulk-green" onclick="updateBarangay('resolved')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Resolve Barangay
                    </button>
                </div>

                <!-- Row 3: Dagupan-wide buttons -->
                <div class="flex flex-wrap gap-2 items-center border-t border-white/5 pt-3.5">
                    <span class="text-[10px] text-white/30 font-bold uppercase tracking-widest mr-1 self-center">All
                        Dagupan:</span>
                    <button class="action-btn btn-bulk-warn" onclick="updateDagupan('verified')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Verify ALL Dagupan
                    </button>
                    <button class="action-btn btn-bulk-red" onclick="updateDagupan('resolved')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Resolve ALL Dagupan
                    </button>
                </div>
            </section>

            <!-- MAP -->
            <section class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#31324C]/10">
                <div class="flex items-center justify-between p-5 border-b border-white/5 bg-[#16172E]/40">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-bold text-sm">Live Outage Map</span>
                    </div>
                    <span
                        class="text-[#00BA00] px-2.5 py-1 bg-[#00BA00]/10 text-[10px] rounded-lg font-bold border border-[#00BA00]/20 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-[#00BA00] rounded-full animate-pulse"></span> LIVE
                    </span>
                </div>
                <div id="map" class="w-full h-72 lg:h-[360px] z-10 bg-[#0E0F26]"></div>
            </section>

            <!-- REPORTS LIST + DETAIL PANEL -->
            <section class="grid grid-cols-1 xl:grid-cols-5 gap-6 mb-8">

                <!-- LEFT: Report list feed -->
                <div
                    class="xl:col-span-3 rounded-2xl border border-white/5 bg-[#31324C]/10 flex flex-col p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-white text-xs font-bold uppercase tracking-widest opacity-60">Outage
                            Reports</span>
                        <span id="pageInfo" class="text-[10px] text-white/30 font-medium"></span>
                    </div>

                    <div id="list"
                        class="grid grid-cols-1 md:grid-cols-2 gap-3 flex-1 overflow-y-auto custom-scrollbar pr-1 max-h-[480px]">
                        <!-- Cards injected here -->
                    </div>

                    <div id="pagination"
                        class="flex flex-row gap-1 justify-center items-center mt-5 pt-4 border-t border-white/5"></div>
                </div>

                <!-- RIGHT: Control / Detail Panel -->
                <div
                    class="xl:col-span-2 rounded-2xl border border-white/5 bg-[#31324C]/10 flex flex-col p-6 shadow-xl">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-widest text-[#B5B5B5]">Control Panel</span>
                    </div>

                    <!-- Default idle state -->
                    <div id="detailBox"
                        class="flex-1 flex flex-col items-center justify-center text-center gap-3 min-h-[300px]">
                        <div
                            class="w-14 h-14 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-center">
                            <svg class="w-7 h-7 text-white/20" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                            </svg>
                        </div>
                        <p class="text-xs text-white/20 font-medium">Click <span class="text-white/40">Manage</span> on
                            any report<br>to open the control panel.</p>
                    </div>
                </div>

            </section>
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        /* =========================================
           MOBILE MENU
        ========================================= */
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMobileSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        menuToggle.addEventListener('click', toggleMobileSidebar);
        overlay.addEventListener('click', toggleMobileSidebar);

        /* =========================================
           GLOBAL STATE
        ========================================= */
        const EC_API = "http://localhost/crowdsourcedAPI/api/outage_report_electric_com";
        const PUB_API = "http://localhost/crowdsourcedAPI/api/outage_report";

        let map, layerGroup;
        let allReports = [];
        let filteredReports = [];
        let selectedId = null;
        let currentPage = 1;
        const perPage = 6;

        /* =========================================
           MAP INITIALIZATION
        ========================================= */
        function initMap() {
            map = L.map('map', { zoomControl: false }).setView([16.04, 120.33], 12);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            layerGroup = L.layerGroup().addTo(map);
        }

        /* =========================================
           SAFE TEXT HELPER
        ========================================= */
        function escapeHTML(str) {
            return String(str ?? "")
                .replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        /* =========================================
           FORMATTERS
        ========================================= */
        function formatCategory(val) {
            return ({
                power_outage: "Power Outage", low_voltage: "Low Voltage",
                power_fluctuation: "Power Fluctuation", transformer_explosion: "Transformer Explosion",
                fallen_power_line: "Fallen Power Line", electrical_fire: "Electrical Fire",
                scheduled_maintenance: "Scheduled Maintenance", unknown_issue: "Unknown Issue"
            })[val] || escapeHTML(val);
        }
        function formatSeverity(val) {
            return ({ minor: "Minor", moderate: "Moderate", critical: "Critical" })[val] || escapeHTML(val);
        }
        function formatHazard(val) {
            return ({
                none: "None", smoke: "Smoke", sparks: "Sparks", fire: "Fire",
                fallen_wire: "Fallen Wire", explosion_sound: "Explosion Sound"
            })[val] || escapeHTML(val);
        }
        function formatStatus(val) {
            return ({
                active: "Active", under_review: "Under Review", verified: "Verified",
                resolved: "Resolved", rejected: "Rejected"
            })[val] || escapeHTML(val);
        }

        /* =========================================
           COLOR MAPPINGS
        ========================================= */
        function getStatusColor(s) {
            switch ((s || "").toLowerCase()) {
                case "resolved": return "text-[#00BA00] bg-[#00BA00]/10 border-[#00BA00]/20";
                case "verified": return "text-[#4FC3F7] bg-[#4FC3F7]/10 border-[#4FC3F7]/20";
                case "under_review": return "text-[#FAB005] bg-[#FAB005]/10 border-[#FAB005]/20";
                case "rejected": return "text-[#B5B5B5] bg-white/5 border-white/10";
                default: return "text-[#CB3435] bg-[#CB3435]/10 border-[#CB3435]/20";
            }
        }
        function getSeverityColor(s) {
            switch ((s || "").toLowerCase()) {
                case "critical": return "text-[#CB3435]";
                case "moderate": return "text-[#FAB005]";
                default: return "text-[#00BA00]";
            }
        }

        /* =========================================
           LOAD DATA (from EC endpoint)
        ========================================= */
        async function loadOutages() {
            try {
                // Sync time
                const syncEl = document.getElementById('sync-time');
                if (syncEl) {
                    syncEl.innerText = new Date().toLocaleTimeString([], {
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    });
                    syncEl.classList.remove("text-[#CB3435]");
                }

                const res = await fetch(`${EC_API}/get.php`, { credentials: "include" });
                const result = await res.json();

                if (!result.success) {
                    setErrorState();
                    return;
                }

                allReports = result.data || [];

                // Count stats from data
                const activeCount = allReports.filter(r =>
                    !['resolved', 'rejected'].includes((r.status || "").toLowerCase())
                ).length;
                const resolvedCount = allReports.filter(r =>
                    (r.status || "").toLowerCase() === "resolved"
                ).length;

                setCard("activeOutages", activeCount);
                setCard("resolvedOutages", resolvedCount);

                applyFilter();

            } catch (err) {
                console.error("Load failed:", err);
                setErrorState();
            }
        }

        /* =========================================
           FILTER
        ========================================= */
        function applyFilter() {
            const selected = document.getElementById("barangayFilter").value;

            filteredReports = selected === "all"
                ? [...allReports]
                : allReports.filter(r =>
                    (r.location_name || "").toLowerCase().includes(selected.toLowerCase())
                );

            // Update result count badge
            const countEl = document.getElementById('resultCount');
            if (countEl) countEl.innerText = `${filteredReports.length} report${filteredReports.length !== 1 ? 's' : ''}`;

            currentPage = 1;
            renderMapMarkers(filteredReports);
            renderList();
            renderPaginationControls();
        }

        /* =========================================
           UI HELPERS
        ========================================= */
        function setCard(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.innerText = value || 0;
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
            if (syncEl) { syncEl.innerText = "Sync Failed"; syncEl.classList.add("text-[#CB3435]"); }
        }

        /* =========================================
           MAP MARKERS
        ========================================= */
        function renderMapMarkers(reports) {
            layerGroup.clearLayers();
            const bounds = [];

            reports.forEach(r => {
                const lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                const marker = L.marker([lat, lng]);
                marker.bindPopup(`
                <div style="min-width:180px;font-size:12px;line-height:1.6">
                    <b style="display:block;border-bottom:1px solid #eee;padding-bottom:4px;margin-bottom:6px">
                        ${escapeHTML(formatCategory(r.category))}
                    </b>
                    <b>Status:</b> ${escapeHTML(formatStatus(r.status))}<br>
                    <b>Severity:</b> ${escapeHTML(formatSeverity(r.severity))}<br>
                    <b>Location:</b> ${escapeHTML(r.location_name)}<br>
                    <b>Affected Houses:</b> ${escapeHTML(r.affected_houses ?? 1)}<br>
                    ${r.hazard_type && r.hazard_type !== 'none'
                        ? `<b>Hazard:</b> ${escapeHTML(formatHazard(r.hazard_type))}<br>` : ''}
                    ${r.description ? `<p style="margin-top:4px;border-top:1px solid #eee;padding-top:4px">${escapeHTML(r.description)}</p>` : ''}
                </div>
            `);
                layerGroup.addLayer(marker);
                bounds.push([lat, lng]);
            });

            if (bounds.length > 0 && map) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
        }

        /* =========================================
           LIST RENDER
        ========================================= */
        function renderList() {
            const list = document.getElementById("list");
            if (!list) return;
            list.innerHTML = "";

            const start = (currentPage - 1) * perPage;
            const pageData = filteredReports.slice(start, start + perPage);

            // Page info
            const infoEl = document.getElementById('pageInfo');
            if (infoEl && filteredReports.length > 0) {
                infoEl.innerText = `${start + 1}–${Math.min(start + perPage, filteredReports.length)} of ${filteredReports.length}`;
            }

            if (pageData.length === 0) {
                list.innerHTML = `
                <div class="text-xs text-white/30 font-medium text-center py-12 col-span-full">
                    No reports match the current filter.
                </div>`;
                return;
            }

            pageData.forEach(r => {
                const card = document.createElement("div");
                const isSelected = r.id == selectedId;
                card.className = `report-card bg-[#0D0E2A]/70 border border-white/5 rounded-xl p-4 flex flex-col gap-2 text-left transition-all hover:border-white/10 cursor-pointer ${isSelected ? 'selected' : ''}`;
                card.dataset.id = r.id;

                const statusColor = getStatusColor(r.status);
                const severityColor = getSeverityColor(r.severity);
                const hazardBadge = (r.hazard_type && r.hazard_type !== 'none')
                    ? `<span class="px-1.5 py-0.5 border text-[9px] font-bold rounded-md text-[#FAB005] bg-[#FAB005]/10 border-[#FAB005]/20 uppercase">⚠ ${escapeHTML(formatHazard(r.hazard_type))}</span>`
                    : '';

                card.innerHTML = `
                <div class="flex justify-between items-start gap-2">
                    <span class="text-white font-bold text-xs truncate max-w-[150px]">
                        ${escapeHTML(formatCategory(r.category))}
                    </span>
                    <span class="px-1.5 py-0.5 border text-[9px] font-bold rounded-md ${statusColor} uppercase tracking-wide whitespace-nowrap">
                        ${escapeHTML(formatStatus(r.status))}
                    </span>
                </div>
                <span class="text-white/70 font-medium text-[11px] truncate">📍 ${escapeHTML(r.location_name)}</span>
                <div class="flex items-center flex-wrap gap-2 text-[10px]">
                    <span class="font-semibold ${severityColor}">● ${escapeHTML(formatSeverity(r.severity))}</span>
                    <span class="text-white/30">🏠 ${escapeHTML(r.affected_houses ?? 1)}</span>
                    ${hazardBadge}
                </div>
                <button
                    onclick="openOutage(${r.id})"
                    class="mt-1 text-[10px] font-bold px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/60 hover:bg-[#FFBB02]/10 hover:border-[#FFBB02]/30 hover:text-[#FFBB02] transition-all self-start">
                    Manage →
                </button>
            `;
                list.appendChild(card);
            });
        }

        /* =========================================
           DETAIL PANEL
        ========================================= */
        function openOutage(id) {
            selectedId = id;

            // Highlight selected card
            document.querySelectorAll('.report-card').forEach(c => {
                c.classList.toggle('selected', c.dataset.id == id);
            });

            const r = filteredReports.find(o => o.id == id) || allReports.find(o => o.id == id);
            if (!r) return;

            const statusColor = getStatusColor(r.status);
            const severityColor = getSeverityColor(r.severity);

            document.getElementById("detailBox").innerHTML = `
            <div class="slide-up flex flex-col gap-5 h-full">

                <!-- Title block -->
                <div class="flex flex-col gap-1.5 pb-4 border-b border-white/5">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-white font-black text-base leading-snug">
                            ${escapeHTML(formatCategory(r.category))}
                        </span>
                        <span class="px-2 py-0.5 border text-[9px] font-bold rounded-md ${statusColor} uppercase tracking-wide whitespace-nowrap flex-shrink-0">
                            ${escapeHTML(formatStatus(r.status))}
                        </span>
                    </div>
                    <span class="text-[#B5B5B5] text-xs font-medium">📍 ${escapeHTML(r.location_name)}</span>
                </div>

                <!-- Detail rows -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#0D0E2A]/60 rounded-xl p-3 flex flex-col gap-0.5 border border-white/5">
                        <span class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Severity</span>
                        <span class="text-sm font-bold ${severityColor}">${escapeHTML(formatSeverity(r.severity))}</span>
                    </div>
                    <div class="bg-[#0D0E2A]/60 rounded-xl p-3 flex flex-col gap-0.5 border border-white/5">
                        <span class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Affected</span>
                        <span class="text-sm font-bold text-white">${escapeHTML(r.affected_houses ?? 1)} houses</span>
                    </div>
                    ${r.hazard_type && r.hazard_type !== 'none' ? `
                    <div class="bg-[#FAB005]/5 rounded-xl p-3 flex flex-col gap-0.5 border border-[#FAB005]/15 col-span-2">
                        <span class="text-[10px] text-[#FAB005]/60 font-bold uppercase tracking-widest">⚠ Hazard</span>
                        <span class="text-sm font-bold text-[#FAB005]">${escapeHTML(formatHazard(r.hazard_type))}</span>
                    </div>` : ''}
                    ${r.description ? `
                    <div class="bg-[#0D0E2A]/60 rounded-xl p-3 flex flex-col gap-0.5 border border-white/5 col-span-2">
                        <span class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Description</span>
                        <span class="text-xs text-[#B5B5B5] leading-relaxed line-clamp-3">${escapeHTML(r.description)}</span>
                    </div>` : ''}
                </div>

                <!-- Action buttons -->
                <div class="mt-auto flex flex-col gap-2.5 pt-4 border-t border-white/5">
                    <span class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Update Status</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="action-btn btn-review w-full justify-center"   onclick="updateSingle(${r.id}, 'under_review')">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Under Review
                        </button>
                        <button class="action-btn btn-verify w-full justify-center"   onclick="updateSingle(${r.id}, 'verified')">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Verify
                        </button>
                        <button class="action-btn btn-resolve w-full justify-center"  onclick="updateSingle(${r.id}, 'resolved')">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Resolve
                        </button>
                        <button class="action-btn btn-reject w-full justify-center"   onclick="updateSingle(${r.id}, 'rejected')">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        `;
        }

        /* =========================================
           SINGLE UPDATE
        ========================================= */
        async function updateSingle(id, status) {
            const btns = document.querySelectorAll('#detailBox button');
            btns.forEach(b => b.disabled = true);

            try {
                const res = await fetch(`${EC_API}/update_single.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({ id, status })
                });
                const result = await res.json();
                showToast(result.message || "Status updated.", result.success ? "success" : "error");
                await loadOutages();
                // Re-open the same report after refresh so panel stays populated
                if (selectedId) openOutage(selectedId);
            } catch (err) {
                console.error(err);
                showToast("Failed to update report.", "error");
                btns.forEach(b => b.disabled = false);
            }
        }

        /* =========================================
           BARANGAY BULK UPDATE
        ========================================= */
        async function updateBarangay(status) {
            const barangay = document.getElementById("barangayFilter").value;
            if (barangay === "all") {
                showToast("Please select a specific barangay first.", "warn"); return;
            }
            if (!confirm(`Apply "${formatStatus(status)}" to all reports in ${barangay}?`)) return;

            try {
                const res = await fetch(`${EC_API}/update_barangay.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({ barangay, status })
                });
                const result = await res.json();
                showToast(result.message || "Barangay updated.", result.success ? "success" : "error");
                await loadOutages();
            } catch (err) {
                console.error(err);
                showToast("Barangay update failed.", "error");
            }
        }

        /* =========================================
           DAGUPAN-WIDE UPDATE
        ========================================= */
        async function updateDagupan(status) {
            if (!confirm(`Apply "${formatStatus(status)}" to ALL Dagupan reports?`)) return;

            try {
                const res = await fetch(`${EC_API}/update_dagupan.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({ status })
                });
                const result = await res.json();
                showToast(result.message || "Dagupan reports updated.", result.success ? "success" : "error");
                await loadOutages();
            } catch (err) {
                console.error(err);
                showToast("Dagupan update failed.", "error");
            }
        }

        /* =========================================
           TOAST NOTIFICATION
        ========================================= */
        function showToast(msg, type = "success") {
            const existing = document.getElementById('toast');
            if (existing) existing.remove();

            const colors = {
                success: "bg-[#00BA00]/10 border-[#00BA00]/30 text-[#00BA00]",
                error: "bg-[#CB3435]/10 border-[#CB3435]/30 text-[#CB3435]",
                warn: "bg-[#FAB005]/10 border-[#FAB005]/30 text-[#FAB005]"
            };
            const icons = {
                success: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
                error: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z",
                warn: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            };

            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-5 py-3.5 rounded-2xl border text-sm font-bold backdrop-blur-sm shadow-xl slide-up ${colors[type] || colors.success}`;
            toast.innerHTML = `
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="${icons[type] || icons.success}"/>
            </svg>
            ${escapeHTML(msg)}
        `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        /* =========================================
           PAGINATION
        ========================================= */
        function buildPageRange(current, total) {
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

            const included = new Set([1, total,
                ...Array.from({ length: 3 }, (_, i) => current - 1 + i)
            ]);
            const range = [];
            for (let i = 1; i <= total; i++) if (included.has(i)) range.push(i);

            const result = [];
            let prev = null;
            for (const page of range) {
                if (prev !== null) {
                    if (page - prev === 2) result.push(prev + 1);
                    else if (page - prev > 2) result.push('...');
                }
                result.push(page);
                prev = page;
            }
            return result;
        }

        function goToPage(page) {
            const pages = Math.ceil(filteredReports.length / perPage);
            if (page < 1 || page > pages) return;
            currentPage = page;
            renderList();
            renderPaginationControls();
        }

        function renderPaginationControls() {
            const p = document.getElementById("pagination");
            if (!p) return;
            p.innerHTML = "";

            const pages = Math.ceil(filteredReports.length / perPage);
            if (pages <= 1) return;

            const base = "h-7 min-w-[28px] px-1 flex items-center justify-center rounded-lg font-bold text-[11px] transition-all duration-150";
            const active = "bg-[#FFBB02] text-black shadow-md shadow-[#FFBB02]/10";
            const inactive = "bg-[#31324C]/40 text-[#B5B5B5] hover:bg-[#31324C]/80 hover:text-white";
            const disabled = "bg-[#31324C]/20 text-white/20 cursor-not-allowed";

            // Prev
            const prev = document.createElement("button");
            prev.innerHTML = "&#8592;";
            prev.className = `${base} ${currentPage === 1 ? disabled : inactive}`;
            if (currentPage > 1) prev.onclick = () => goToPage(currentPage - 1);
            p.appendChild(prev);

            // Pages
            buildPageRange(currentPage, pages).forEach(item => {
                if (item === '...') {
                    const dots = document.createElement("span");
                    dots.innerText = "…";
                    dots.className = "h-7 w-5 flex items-center justify-center text-[#B5B5B5]/40 text-[11px] select-none";
                    p.appendChild(dots);
                } else {
                    const btn = document.createElement("button");
                    btn.innerText = item;
                    btn.className = `${base} ${item === currentPage ? active : inactive}`;
                    btn.onclick = () => goToPage(item);
                    p.appendChild(btn);
                }
            });

            // Next
            const next = document.createElement("button");
            next.innerHTML = "&#8594;";
            next.className = `${base} ${currentPage === pages ? disabled : inactive}`;
            if (currentPage < pages) next.onclick = () => goToPage(currentPage + 1);
            p.appendChild(next);
        }

        /* =========================================
           INIT
        ========================================= */
        document.addEventListener("DOMContentLoaded", () => {
            initMap();
            setTimeout(loadOutages, 400);
            setInterval(loadOutages, 10000);
        });
    </script>
</body>

</html>