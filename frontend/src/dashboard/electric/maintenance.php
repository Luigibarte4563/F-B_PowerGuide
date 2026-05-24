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
$firstName = htmlspecialchars(explode(' ', $user['name'] ?? 'Admin')[0]);
$fullName = htmlspecialchars($user['name'] ?? 'Company Admin');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Management – PowerGuide</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        /* ── Base ───────────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #03041A;
            color: #fff;
        }

        /* ── Scrollbar ──────────────────────────────────── */
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

        /* ── Animations ─────────────────────────────────── */
        .loading-pulse {
            animation: pulse 2s cubic-bezier(.4, 0, .6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .35
            }
        }

        .card-hover {
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .card-hover:hover {
            transform: translateY(-3px);
        }

        /* ── Leaflet dark invert ─────────────────────────── */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-container {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }

        /* ── Modals ─────────────────────────────────────── */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            z-index: 200;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-box {
            background: #0D0E2A;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 24px;
            width: 100%;
            max-width: 600px;
            margin: 16px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .7);
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .modal-box-sm {
            max-width: 400px;
        }

        /* ── Barangay chip grid ──────────────────────────── */
        .chip-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
        }

        .chip {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 9px;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, .07);
            background: rgba(49, 50, 76, .25);
            font-size: 11px;
            font-weight: 600;
            transition: all .15s ease;
            user-select: none;
            line-height: 1;
        }

        .chip input[type="checkbox"] {
            display: none;
        }

        .chip .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            flex-shrink: 0;
            transition: background .15s;
        }

        .chip.checked {
            background: rgba(254, 187, 2, .13);
            border-color: rgba(254, 187, 2, .38);
            color: #FEBB02;
        }

        .chip.checked .dot {
            background: #FEBB02;
        }

        .chip:hover {
            border-color: rgba(254, 187, 2, .22);
        }

        /* ── Stat glows ─────────────────────────────────── */
        .glow-yellow:hover {
            box-shadow: 0 12px 30px -6px rgba(254, 187, 2, .18);
        }

        .glow-green:hover {
            box-shadow: 0 12px 30px -6px rgba(95, 203, 95, .15);
        }

        .glow-blue:hover {
            box-shadow: 0 12px 30px -6px rgba(79, 195, 247, .15);
        }

        /* ── Tooltip-style barangay tag ─────────────────── */
        .btag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 9.5px;
            font-weight: 700;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            color: #B5B5B5;
        }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased h-screen overflow-hidden flex">

    <!-- ═══════════════════════════════════════════════════════
     MOBILE TOGGLE
══════════════════════════════════════════════════════════ -->
    <button id="menuToggle"
        class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg border border-white/10 hover:bg-[#3e3f60] transition-all">
        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    <div id="overlay" class="fixed inset-0 bg-black/60 z-30 hidden"></div>

    <!-- ═══════════════════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════════════════════ -->
    <nav id="sidebar" class="flex flex-col fixed lg:sticky top-0 h-screen w-[280px] lg:w-[300px]
            text-[#B5B5B5] pt-8 px-5 border-r border-white/5 bg-[#03041A] z-40
            -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex-shrink-0">

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
                    ELECTRIC COMPANY
                </span>
            </div>
        </div>

        <!-- Nav -->
        <div class="flex flex-col gap-1.5">
            <span class="text-[11px] font-bold tracking-widest text-white px-4 pt-2 mb-2 opacity-50">MANAGEMENT</span>

            <a href="dashboard.php"
                class="group flex items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="outages.php"
                class="group flex items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Manage Outages</span>
            </a>

            <a href="maintenance.php"
                class="flex items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:bg-[#FEBB02] hover:text-black font-bold text-sm shadow-lg shadow-[#FEBB02]/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Maintenance</span>
            </a>
        </div>

        <!-- Profile -->
        <div class="mt-auto mb-6">
            <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-2xl bg-white/5 border border-white/5">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar"
                        class="h-9 w-9 rounded-xl object-cover border border-[#FFBB02]/30 flex-shrink-0">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-white truncate"><?= $fullName ?></div>
                        <div class="text-[10px] font-semibold text-white/40 uppercase tracking-wider">Administrator
                        </div>
                    </div>
                </div>
                <a href="<?= BACKEND_URL ?>/public/logout.php"
                    class="p-2 text-[#B5B5B5] hover:text-[#CB3435] hover:bg-[#CB3435]/10 rounded-xl transition-all"
                    title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════════════════ -->
    <main class="flex-1 overflow-y-auto custom-scrollbar flex flex-col w-full">

        <!-- Header -->
        <header class="px-6 lg:px-10 pt-20 lg:pt-10 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4
                   border-b border-white/5 bg-[#03041A] sticky top-0 z-20">
            <div>
                <h1 class="text-3xl lg:text-4xl font-black tracking-tight leading-tight">
                    Maintenance <span class="text-[#FFBB02]">Management</span>
                </h1>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FFBB02] opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-[#FFBB02]"></span>
                    </span>
                    <span class="text-[11px] text-[#B5B5B5] font-semibold tracking-widest uppercase">Location-Mapped
                        System · Auto-Refresh</span>
                </div>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto flex-shrink-0">
                <div class="text-xs font-semibold text-[#B5B5B5] bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                    Synced: <span id="sync-time" class="text-white font-bold">—</span>
                </div>
                <button onclick="openCreateModal()"
                    class="flex items-center gap-2 bg-[#FEBB02] hover:bg-[#E5A800] text-black font-black text-sm px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-[#FEBB02]/15 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Schedule
                </button>
            </div>
        </header>

        <div class="p-6 lg:p-10 flex flex-col gap-8">

            <!-- ── Stat Cards ─────────────────────────────── -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                <div
                    class="card-hover glow-yellow bg-[#31324C]/20 border border-white/5 rounded-2xl p-7 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute -top-6 -right-6 w-24 h-24 bg-[#FEBB02]/8 rounded-full blur-2xl group-hover:bg-[#FEBB02]/15 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#FEBB02]/10 border border-[#FEBB02]/20 p-3 rounded-xl text-[#FEBB02]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#FEBB02] text-[10px] font-black px-2.5 py-1 bg-[#FEBB02]/10 rounded-lg tracking-widest uppercase border border-[#FEBB02]/20">Ongoing</span>
                    </div>
                    <div class="z-10">
                        <span id="count-ongoing"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <p class="text-[#B5B5B5] text-xs font-semibold mt-1">Active Maintenance</p>
                    </div>
                </div>

                <div
                    class="card-hover glow-blue bg-[#31324C]/20 border border-white/5 rounded-2xl p-7 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute -top-6 -right-6 w-24 h-24 bg-[#4FC3F7]/8 rounded-full blur-2xl group-hover:bg-[#4FC3F7]/15 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#4FC3F7]/10 border border-[#4FC3F7]/20 p-3 rounded-xl text-[#4FC3F7]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#4FC3F7] text-[10px] font-black px-2.5 py-1 bg-[#4FC3F7]/10 rounded-lg tracking-widest uppercase border border-[#4FC3F7]/20">Scheduled</span>
                    </div>
                    <div class="z-10">
                        <span id="count-scheduled"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <p class="text-[#B5B5B5] text-xs font-semibold mt-1">Upcoming Schedules</p>
                    </div>
                </div>

                <div
                    class="card-hover glow-green bg-[#31324C]/20 border border-white/5 rounded-2xl p-7 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute -top-6 -right-6 w-24 h-24 bg-[#5FCB5F]/8 rounded-full blur-2xl group-hover:bg-[#5FCB5F]/15 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#5FCB5F]/10 border border-[#5FCB5F]/20 p-3 rounded-xl text-[#5FCB5F]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#5FCB5F] text-[10px] font-black px-2.5 py-1 bg-[#5FCB5F]/10 rounded-lg tracking-widest uppercase border border-[#5FCB5F]/20">Completed</span>
                    </div>
                    <div class="z-10">
                        <span id="count-completed"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <p class="text-[#B5B5B5] text-xs font-semibold mt-1">Finished Maintenance</p>
                    </div>
                </div>

            </section>

            <!-- ── Live Map ───────────────────────────────── -->
            <section class="rounded-2xl border border-white/5 overflow-hidden bg-[#31324C]/20 shadow-xl">

                <div class="flex items-center justify-between px-5 py-4 border-b border-white/5 bg-[#16172E]/50">
                    <div class="flex items-center gap-2.5">
                        <div class="text-[#FFBB02]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="font-bold text-sm">Maintenance Coverage Map</span>
                        <span
                            class="text-[10px] text-[#B5B5B5] bg-white/5 border border-white/5 px-2 py-0.5 rounded-md font-semibold">Live
                            DB Coordinates</span>
                    </div>
                    <span
                        class="text-[#FFBB02] px-2.5 py-1 bg-[#FFBB02]/10 text-[10px] rounded-lg font-black border border-[#FFBB02]/20 flex items-center gap-1.5 uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 bg-[#FFBB02] rounded-full animate-pulse"></span> Auto-Refresh
                    </span>
                </div>

                <div id="maintenance-map" class="w-full h-80 lg:h-[420px] bg-[#0E0F26]"></div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-5 px-5 py-3 border-t border-white/5 bg-[#0D0E2A]/60">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#FEBB02]"></span><span
                            class="text-[10px] text-[#B5B5B5] font-semibold">Ongoing</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#4FC3F7]"></span><span
                            class="text-[10px] text-[#B5B5B5] font-semibold">Scheduled</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#5FCB5F]"></span><span
                            class="text-[10px] text-[#B5B5B5] font-semibold">Completed</span></div>
                </div>
            </section>

            <!-- ── Maintenance Schedule List + Pagination ───── -->
            <section class="mb-8">
                <div class="w-full">
                    <div
                        class="rounded-2xl border border-white/5 bg-[#31324C]/20 flex flex-col p-6 shadow-xl min-h-[300px]">

                        <!-- Header row with label + filter tabs -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-white text-xs font-bold uppercase tracking-widest opacity-60">
                                Maintenance Schedule
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button onclick="setFilter('all')" id="tab-all" class="tab-btn text-[10px] font-black px-3 py-1 rounded-lg border transition-all uppercase tracking-widest
                                           bg-[#FFBB02] text-black border-[#FFBB02]/30">
                                    All
                                </button>
                                <button onclick="setFilter('active')" id="tab-active"
                                    class="tab-btn text-[10px] font-black px-3 py-1 rounded-lg border transition-all uppercase tracking-widest
                                           bg-[#31324C]/40 text-[#B5B5B5] border-white/8 hover:bg-[#31324C]/80 hover:text-white">
                                    Active
                                    <span id="badge-active" class="ml-1 text-[#FEBB02]">0</span>
                                </button>
                                <button onclick="setFilter('completed')" id="tab-completed"
                                    class="tab-btn text-[10px] font-black px-3 py-1 rounded-lg border transition-all uppercase tracking-widest
                                           bg-[#31324C]/40 text-[#B5B5B5] border-white/8 hover:bg-[#31324C]/80 hover:text-white">
                                    Completed
                                    <span id="badge-completed" class="ml-1 text-[#5FCB5F]">0</span>
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic card grid -->
                        <div id="list"
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 flex-1 overflow-y-auto custom-scrollbar pr-1 max-h-[400px]">
                            <div
                                class="col-span-full text-xs text-white/25 font-semibold text-center py-12 loading-pulse">
                                Loading maintenance data…
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div id="pagination"
                            class="flex flex-row gap-1 justify-center items-center mt-5 pt-4 border-t border-white/5">
                        </div>

                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- ═══════════════════════════════════════════════════════
     CREATE MODAL
══════════════════════════════════════════════════════════ -->
    <div id="create-modal" class="modal-backdrop" onclick="handleCreateBackdrop(event)">
        <div class="modal-box">

            <!-- Modal Header -->
            <div
                class="flex items-center justify-between px-7 py-5 border-b border-white/6 bg-[#16172E]/60 flex-shrink-0">
                <div>
                    <h2 class="text-base font-black text-white">New Maintenance Schedule</h2>
                    <p class="text-[11px] text-[#B5B5B5] font-medium mt-0.5">Select barangays · coordinates saved to DB
                    </p>
                </div>
                <button onclick="closeCreateModal()"
                    class="text-[#B5B5B5] hover:text-white hover:bg-white/8 p-2 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Scrollable Body -->
            <div class="flex-1 overflow-y-auto custom-scrollbar px-7 py-6 flex flex-col gap-5">

                <!-- Company -->
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5] mb-1.5 block">Company
                        Name</label>
                    <input id="f-company" type="text" placeholder="e.g. PowerGuide Pangasinan" class="w-full bg-[#31324C]/30 border border-white/8 rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/25
                              focus:outline-none focus:border-[#FEBB02]/50 transition-all">
                </div>

                <!-- Date + Time row -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label
                            class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5] mb-1.5 block">Date</label>
                        <input id="f-date" type="date" class="w-full bg-[#31324C]/30 border border-white/8 rounded-xl px-3 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-[#FEBB02]/50 transition-all [color-scheme:dark]">
                    </div>
                    <div>
                        <label
                            class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5] mb-1.5 block">Start
                            Time</label>
                        <input id="f-start" type="time" class="w-full bg-[#31324C]/30 border border-white/8 rounded-xl px-3 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-[#FEBB02]/50 transition-all [color-scheme:dark]">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5] mb-1.5 block">End
                            Time</label>
                        <input id="f-end" type="time" class="w-full bg-[#31324C]/30 border border-white/8 rounded-xl px-3 py-2.5 text-sm text-white
                                  focus:outline-none focus:border-[#FEBB02]/50 transition-all [color-scheme:dark]">
                    </div>
                </div>

                <!-- Radius slider -->
                <div>
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5] mb-1.5 flex items-center gap-1">
                        Coverage Radius
                        <span class="text-[#FEBB02] font-black" id="radius-val">1000</span>
                        <span class="text-white/30">m</span>
                    </label>
                    <input id="f-radius" type="range" min="300" max="5000" step="100" value="1000"
                        oninput="document.getElementById('radius-val').innerText = this.value"
                        class="w-full accent-[#FEBB02] mt-1">
                    <div class="flex justify-between text-[10px] text-white/20 font-semibold mt-0.5">
                        <span>300m</span><span>5,000m</span>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label
                        class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5] mb-1.5 block">Description
                        <span class="text-white/20 normal-case font-medium">(optional)</span></label>
                    <textarea id="f-desc" rows="2" placeholder="Brief description of the maintenance work…" class="w-full bg-[#31324C]/30 border border-white/8 rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/25
                                 focus:outline-none focus:border-[#FEBB02]/50 transition-all resize-none"></textarea>
                </div>

                <!-- Barangay selector -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[#B5B5B5]">Affected
                            Barangays</label>
                        <div class="flex gap-1.5">
                            <button onclick="selectAll()"
                                class="text-[10px] font-bold px-2.5 py-1 bg-[#FEBB02]/10 text-[#FEBB02] border border-[#FEBB02]/20 rounded-lg hover:bg-[#FEBB02]/20 transition-all">All</button>
                            <button onclick="clearAll()"
                                class="text-[10px] font-bold px-2.5 py-1 bg-white/5 text-[#B5B5B5] border border-white/8 rounded-lg hover:bg-white/10 transition-all">Clear</button>
                        </div>
                    </div>
                    <div id="chip-grid" class="chip-grid max-h-52 overflow-y-auto custom-scrollbar pr-1"></div>
                    <div class="mt-2 text-[10px] text-[#B5B5B5] font-medium">
                        Selected: <span id="chip-count" class="text-[#FEBB02] font-black">0</span> barangay(s)
                    </div>
                </div>

                <!-- Error -->
                <div id="create-error"
                    class="hidden text-xs text-[#CB3435] bg-[#CB3435]/8 border border-[#CB3435]/20 rounded-xl px-4 py-3 font-semibold">
                </div>

            </div>

            <!-- Modal Footer -->
            <div
                class="flex items-center justify-end gap-3 px-7 py-4 border-t border-white/5 bg-[#0D0E2A]/80 flex-shrink-0">
                <button onclick="closeCreateModal()"
                    class="px-5 py-2.5 rounded-xl text-sm font-bold text-[#B5B5B5] hover:text-white border border-white/8 hover:bg-white/5 transition-all">
                    Cancel
                </button>
                <button id="create-btn" onclick="submitCreate()"
                    class="flex items-center gap-2 bg-[#FEBB02] hover:bg-[#E5A800] text-black font-black text-sm px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-[#FEBB02]/15 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    <svg id="create-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span id="create-label">Create Schedule</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
     VIEW / DETAIL MODAL
══════════════════════════════════════════════════════════ -->
    <div id="view-modal" class="modal-backdrop" onclick="if(event.target===this)closeViewModal()">
        <div class="modal-box">
            <div
                class="flex items-center justify-between px-7 py-5 border-b border-white/6 bg-[#16172E]/60 flex-shrink-0">
                <h2 class="text-base font-black text-white">Maintenance Details</h2>
                <button onclick="closeViewModal()"
                    class="text-[#B5B5B5] hover:text-white hover:bg-white/8 p-2 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="view-body" class="flex-1 overflow-y-auto custom-scrollbar px-7 py-6"></div>
            <div class="flex justify-end gap-3 px-7 py-4 border-t border-white/5 bg-[#0D0E2A]/80 flex-shrink-0">
                <button onclick="closeViewModal()"
                    class="px-5 py-2.5 rounded-xl text-sm font-bold text-[#B5B5B5] hover:text-white border border-white/8 hover:bg-white/5 transition-all">Close</button>
                <button id="view-delete-btn" onclick="triggerDeleteFromView()"
                    class="flex items-center gap-2 bg-[#CB3435] hover:bg-[#B02C2D] text-white font-black text-sm px-5 py-2.5 rounded-xl transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════════════════════════════════ -->
    <div id="delete-modal" class="modal-backdrop" onclick="if(event.target===this)closeDeleteModal()">
        <div class="modal-box modal-box-sm text-center">
            <div class="px-7 py-8 flex flex-col items-center gap-5">
                <div
                    class="w-16 h-16 bg-[#CB3435]/10 border border-[#CB3435]/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#CB3435]" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-black text-lg">Delete Schedule?</h3>
                    <p class="text-[#B5B5B5] text-sm mt-1.5 leading-relaxed">This will permanently delete the
                        maintenance record and all linked barangay location data.</p>
                </div>
                <div class="flex gap-3 w-full">
                    <button onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-[#B5B5B5] border border-white/8 hover:bg-white/5 transition-all">
                        Cancel
                    </button>
                    <button id="confirm-delete-btn" onclick="confirmDelete()"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-black bg-[#CB3435] hover:bg-[#B02C2D] text-white transition-all active:scale-95">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════ -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        /* ╔═══════════════════════════════════════════════════════════╗
           ║  CENTRALIZED BARANGAY DATA — single source of truth       ║
           ╚═══════════════════════════════════════════════════════════╝ */
        const barangayData = {
            "Bonuan Gueset": { lat: 16.0585, lng: 120.3345 },
            "Bonuan Boquig": { lat: 16.0600, lng: 120.3200 },
            "Bonuan Binloc": { lat: 16.0620, lng: 120.3100 },
            "Lucao": { lat: 16.0435, lng: 120.3310 },
            "Tapuac": { lat: 16.0460, lng: 120.3450 },
            "Tambac": { lat: 16.0520, lng: 120.3400 },
            "Pantal": { lat: 16.0468, lng: 120.3330 },
            "Bacayao Norte": { lat: 16.0300, lng: 120.3200 },
            "Bacayao Sur": { lat: 16.0250, lng: 120.3250 },
            "Malued": { lat: 16.0400, lng: 120.3200 },
            "Mayombo": { lat: 16.0480, lng: 120.3100 },
            "Mangin": { lat: 16.0550, lng: 120.3500 },
            "Tebeng": { lat: 16.0600, lng: 120.3450 },
            "Pogo Chico": { lat: 16.0510, lng: 120.3600 },
            "Pogo Grande": { lat: 16.0550, lng: 120.3650 },
            "Herrero": { lat: 16.0450, lng: 120.3350 },
            "Poblacion Centro": { lat: 16.0430, lng: 120.3335 },
            "Poblacion Oeste": { lat: 16.0410, lng: 120.3300 },
            "Poblacion Este": { lat: 16.0440, lng: 120.3360 },
        };

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  API ENDPOINTS                                            ║
           ╚═══════════════════════════════════════════════════════════╝ */
        const API = {
            get: "http://localhost/CrowdsourcedAPI/api/maintenance/get.php",
            create: "http://localhost/CrowdsourcedAPI/api/maintenance/create.php",
            delete: "http://localhost/CrowdsourcedAPI/api/maintenance/delete.php",
        };

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  GLOBAL STATE                                             ║
           ╚═══════════════════════════════════════════════════════════╝ */
        let maintenanceMap = null;
        let mapLayers = null;
        let allData = [];
        let pendingDeleteId = null;
        let viewingId = null;

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  MOBILE SIDEBAR                                           ║
           ╚═══════════════════════════════════════════════════════════╝ */
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('overlay').classList.toggle('hidden');
        });
        document.getElementById('overlay').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('overlay').classList.add('hidden');
        });

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  XSS SAFE HELPER                                          ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function esc(str) {
            return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  STATUS HELPERS                                           ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function statusLabel(s) {
            return ({ ongoing: 'Ongoing', scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled' })[s] || s;
        }
        function statusColor(s) {
            return ({ ongoing: '#FEBB02', scheduled: '#4FC3F7', completed: '#5FCB5F', cancelled: '#B5B5B5' })[s] || '#FEBB02';
        }
        function statusBadge(s) {
            const map = {
                ongoing: 'text-[#FEBB02] bg-[#FEBB02]/10 border-[#FEBB02]/25',
                scheduled: 'text-[#4FC3F7] bg-[#4FC3F7]/10 border-[#4FC3F7]/25',
                completed: 'text-[#5FCB5F] bg-[#5FCB5F]/10 border-[#5FCB5F]/25',
                cancelled: 'text-[#B5B5B5] bg-white/5 border-white/10',
            };
            return map[s] || map.ongoing;
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  MAP INIT — single map                                    ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function initMap() {
            maintenanceMap = L.map('maintenance-map', { zoomControl: false }).setView([16.044, 120.333], 13);
            L.control.zoom({ position: 'bottomright' }).addTo(maintenanceMap);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(maintenanceMap);
            mapLayers = L.layerGroup().addTo(maintenanceMap);
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  MAP RENDER — uses coordinates from maintenance_locations ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function renderMap(data) {
            if (!mapLayers) return;
            mapLayers.clearLayers();
            const bounds = [];

            data.forEach(m => {
                const locs = m.locations || [];
                const color = statusColor(m.status);
                const radius = parseInt(m.radius) || 1000;

                locs.forEach(loc => {
                    const lat = parseFloat(loc.latitude);
                    const lng = parseFloat(loc.longitude);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const circle = L.circle([lat, lng], {
                        color, fillColor: color,
                        fillOpacity: 0.15, weight: 2.5, radius,
                    });
                    circle.bindPopup(`
                <div style="min-width:190px;font-family:sans-serif;font-size:12px;line-height:1.6">
                    <b style="display:block;font-size:13px;border-bottom:1px solid #ddd;padding-bottom:5px;margin-bottom:6px">
                        ${esc(m.company_name)}
                    </b>
                    <b>Barangay:</b> ${esc(loc.barangay_name)}<br>
                    <b>Date:</b> ${esc(m.maintenance_date)}<br>
                    <b>Time:</b> ${esc(m.start_time)} – ${esc(m.end_time)}<br>
                    <b>Status:</b> <span style="color:${color};font-weight:700">${esc(statusLabel(m.status))}</span><br>
                    <b>Radius:</b> ${radius}m
                    ${m.description ? `<p style="margin-top:6px;padding-top:6px;border-top:1px solid #eee">${esc(m.description)}</p>` : ''}
                </div>
            `);
                    mapLayers.addLayer(circle);
                    bounds.push([lat, lng]);
                });
            });

            if (bounds.length > 0 && maintenanceMap) {
                maintenanceMap.fitBounds(bounds, { padding: [45, 45], maxZoom: 14 });
            }
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  SINGLE MAINTENANCE LOADER                                ║
           ╚═══════════════════════════════════════════════════════════╝ */
        async function loadMaintenance() {
            try {
                const res = await fetch(API.get, { credentials: 'include' });
                const json = await res.json();

                // Update sync timestamp
                const syncEl = document.getElementById('sync-time');
                if (syncEl) syncEl.innerText = new Date().toLocaleTimeString([],
                    { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                if (!json.success) throw new Error(json.message);

                allData = json.data || [];

                // ── Stat counts ──────────────────────────────────
                const counts = { ongoing: 0, scheduled: 0, completed: 0 };
                allData.forEach(m => { if (counts[m.status] !== undefined) counts[m.status]++; });
                ['ongoing', 'scheduled', 'completed'].forEach(s => {
                    const el = document.getElementById(`count-${s}`);
                    if (el) { el.innerText = counts[s]; el.classList.remove('loading-pulse'); }
                });

                // ── Map — from DB coordinates ────────────────────
                renderMap(allData);

                // ── Badge counts ─────────────────────────────────
                const activeCount = allData.filter(m => ['ongoing', 'scheduled'].includes(m.status)).length;
                const completedCount = allData.filter(m => m.status === 'completed').length;

                // Update filter tab badges
                const badgeActive = document.getElementById('badge-active');
                if (badgeActive) badgeActive.innerText = activeCount;
                const badgeCompleted = document.getElementById('badge-completed');
                if (badgeCompleted) badgeCompleted.innerText = completedCount;

                // Apply current filter and re-render paginated list
                applyFilter();
                renderMaintenancePage();
                renderPagination();

            } catch (err) {
                console.error('loadMaintenance:', err);
            }
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  PAGINATION STATE                                         ║
           ╚═══════════════════════════════════════════════════════════╝ */
        let filteredData = [];
        let currentPage = 1;
        const perPage = 6;
        let activeFilter = 'all'; // 'all' | 'active' | 'completed'

        /* ── Filter tab switcher ─────────────────────────────────── */
        function setFilter(filter) {
            activeFilter = filter;
            currentPage = 1;

            // Update tab button styles
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = btn.className
                    .replace('bg-[#FFBB02] text-black border-[#FFBB02]/30', '')
                    .replace('bg-[#31324C]/40 text-[#B5B5B5] border-white/8', '')
                    .trim();
                btn.classList.add('bg-[#31324C]/40', 'text-[#B5B5B5]', 'border-white/8');
            });
            const active = document.getElementById(`tab-${filter}`);
            if (active) {
                active.classList.remove('bg-[#31324C]/40', 'text-[#B5B5B5]', 'border-white/8');
                active.classList.add('bg-[#FFBB02]', 'text-black', 'border-[#FFBB02]/30');
            }

            // Re-slice filteredData from allData
            applyFilter();
            renderMaintenancePage();
            renderPagination();
        }

        function applyFilter() {
            if (activeFilter === 'active') {
                filteredData = allData.filter(m => ['ongoing', 'scheduled'].includes(m.status));
            } else if (activeFilter === 'completed') {
                filteredData = allData.filter(m => m.status === 'completed');
            } else {
                filteredData = [...allData];
            }
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  PAGE RENDERER — slices filteredData by currentPage       ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function renderMaintenancePage() {
            const list = document.getElementById('list');
            if (!list) return;
            list.innerHTML = '';

            const start = (currentPage - 1) * perPage;
            const pageData = filteredData.slice(start, start + perPage);

            if (!pageData.length) {
                list.innerHTML = `<div class="col-span-full text-xs text-white/25 font-semibold text-center py-10">No maintenance records found.</div>`;
                return;
            }

            pageData.forEach(m => {
                const locs = m.locations || [];
                const badge = statusBadge(m.status);
                const color = statusColor(m.status);

                // Barangay tags — up to 3 + overflow count
                const tagHTML = locs.slice(0, 3).map(l => `<span class="btag">${esc(l.barangay_name)}</span>`).join(' ')
                    + (locs.length > 3 ? ` <span class="btag" style="color:rgba(255,255,255,.3)">+${locs.length - 3}</span>` : '');

                const card = document.createElement('div');
                card.className = 'bg-[#0D0E2A]/70 border border-white/5 rounded-xl p-4 flex flex-col gap-2 text-left transition-all hover:border-white/10 cursor-pointer card-hover';
                card.onclick = () => openViewModal(m.id);
                card.innerHTML = `
            <!-- Header: company + status badge -->
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <span class="text-white font-bold text-sm truncate block max-w-[180px]">${esc(m.company_name)}</span>
                    <span class="text-[#B5B5B5] text-[10px] font-semibold mt-0.5 block">
                        📅 ${esc(m.maintenance_date)} &nbsp;·&nbsp; ${esc(m.start_time)} – ${esc(m.end_time)}
                    </span>
                </div>
                <span class="px-2 py-0.5 border text-[9px] font-bold rounded-md ${badge} uppercase tracking-wide whitespace-nowrap flex-shrink-0">
                    ${esc(statusLabel(m.status))}
                </span>
            </div>

            <!-- Barangay count + tags -->
            <div>
                <span class="text-[#B5B5B5]/60 text-[10px] font-black uppercase tracking-widest block mb-1">
                    📍 ${locs.length} Barangay${locs.length !== 1 ? 's' : ''}
                </span>
                <div class="flex flex-wrap gap-1">${tagHTML}</div>
            </div>

            <!-- Description -->
            ${m.description ? `<p class="text-[#B5B5B5]/70 text-[11px] line-clamp-2 border-t border-white/5 pt-1.5 mt-0.5">${esc(m.description)}</p>` : ''}

            <!-- Footer: radius + action buttons -->
            <div class="flex items-center justify-between border-t border-white/5 pt-2 mt-0.5">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:${color}"></span>
                    <span class="text-[10px] text-[#B5B5B5] font-semibold">Radius: <span class="text-white font-bold">${esc(String(m.radius))}m</span></span>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="event.stopPropagation();openViewModal(${parseInt(m.id)})"
                            class="text-[10px] font-bold text-[#4FC3F7] hover:text-white hover:bg-[#4FC3F7]/10 px-2.5 py-1.5 rounded-lg transition-all border border-transparent hover:border-[#4FC3F7]/20">
                        View
                    </button>
                    <button onclick="event.stopPropagation();openDeleteModal(${parseInt(m.id)})"
                            class="text-[10px] font-bold text-[#CB3435] hover:text-white hover:bg-[#CB3435]/10 px-2.5 py-1.5 rounded-lg transition-all border border-transparent hover:border-[#CB3435]/20">
                        Delete
                    </button>
                </div>
            </div>
        `;
                list.appendChild(card);
            });
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  PAGINATION — exact dashboard style                       ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function renderPagination() {
            const p = document.getElementById('pagination');
            if (!p) return;
            p.innerHTML = '';

            const pages = Math.ceil(filteredData.length / perPage);
            if (pages <= 1) return;

            for (let i = 1; i <= pages; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                btn.className = `h-7 w-7 flex items-center justify-center rounded-lg font-bold text-[11px] transition-all duration-150 ${i === currentPage
                        ? 'bg-[#FFBB02] text-black shadow-md shadow-[#FFBB02]/10'
                        : 'bg-[#31324C]/40 text-[#B5B5B5] hover:bg-[#31324C]/80 hover:text-white'
                    }`;
                btn.onclick = () => {
                    currentPage = i;
                    renderMaintenancePage();
                    renderPagination();
                };
                p.appendChild(btn);
            }
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  BARANGAY CHIP BUILDER                                    ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function buildChips() {
            const grid = document.getElementById('chip-grid');
            grid.innerHTML = '';
            Object.keys(barangayData).forEach(name => {
                const label = document.createElement('label');
                label.className = 'chip';
                label.innerHTML = `<input type="checkbox" value="${esc(name)}"><span class="dot"></span>${esc(name)}`;
                const cb = label.querySelector('input');
                cb.addEventListener('change', () => {
                    label.classList.toggle('checked', cb.checked);
                    document.getElementById('chip-count').innerText =
                        document.querySelectorAll('#chip-grid input:checked').length;
                });
                grid.appendChild(label);
            });
        }
        function selectAll() {
            document.querySelectorAll('#chip-grid input').forEach(cb => {
                cb.checked = true; cb.closest('label').classList.add('checked');
            });
            document.getElementById('chip-count').innerText = Object.keys(barangayData).length;
        }
        function clearAll() {
            document.querySelectorAll('#chip-grid input').forEach(cb => {
                cb.checked = false; cb.closest('label').classList.remove('checked');
            });
            document.getElementById('chip-count').innerText = 0;
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  CREATE MODAL                                             ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function openCreateModal() {
            buildChips();
            document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('f-company').value = '';
            document.getElementById('f-start').value = '';
            document.getElementById('f-end').value = '';
            document.getElementById('f-desc').value = '';
            document.getElementById('f-radius').value = 1000;
            document.getElementById('radius-val').innerText = '1000';
            document.getElementById('chip-count').innerText = '0';
            document.getElementById('create-error').classList.add('hidden');
            document.getElementById('create-modal').classList.add('open');
        }
        function closeCreateModal() {
            document.getElementById('create-modal').classList.remove('open');
        }
        function handleCreateBackdrop(e) {
            if (e.target === document.getElementById('create-modal')) closeCreateModal();
        }

        async function submitCreate() {
            const errEl = document.getElementById('create-error');
            const btn = document.getElementById('create-btn');
            const spinner = document.getElementById('create-spinner');
            const label = document.getElementById('create-label');
            errEl.classList.add('hidden');

            const company = document.getElementById('f-company').value.trim();
            const date = document.getElementById('f-date').value;
            const start = document.getElementById('f-start').value;
            const end = document.getElementById('f-end').value;
            const radius = parseInt(document.getElementById('f-radius').value);
            const desc = document.getElementById('f-desc').value.trim();
            const barangays = [...document.querySelectorAll('#chip-grid input:checked')].map(cb => cb.value);

            if (!company) { showCreateError('Company name is required.'); return; }
            if (!date) { showCreateError('Date is required.'); return; }
            if (!start) { showCreateError('Start time is required.'); return; }
            if (!end) { showCreateError('End time is required.'); return; }
            if (start >= end) { showCreateError('End time must be after start time.'); return; }
            if (!barangays.length) { showCreateError('Select at least one barangay.'); return; }

            btn.disabled = true; spinner.classList.remove('hidden'); label.innerText = 'Creating…';

            try {
                const res = await fetch(API.create, {
                    method: 'POST', credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ company_name: company, maintenance_date: date, start_time: start, end_time: end, radius, description: desc, barangays }),
                });
                const json = await res.json();
                if (!json.success) { showCreateError(json.message || 'Failed to create.'); return; }
                closeCreateModal();
                await loadMaintenance();
            } catch (err) {
                showCreateError('Network error. Check your connection.');
            } finally {
                btn.disabled = false; spinner.classList.add('hidden'); label.innerText = 'Create Schedule';
            }
        }
        function showCreateError(msg) {
            const el = document.getElementById('create-error');
            el.innerText = msg; el.classList.remove('hidden');
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  VIEW MODAL                                               ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function openViewModal(id) {
            const m = allData.find(x => x.id == id);
            if (!m) return;
            viewingId = id;

            const locs = m.locations || [];
            const badge = statusBadge(m.status);
            const color = statusColor(m.status);

            const locRows = locs.map(l => `
        <tr class="border-b border-white/5 last:border-0">
            <td class="py-2.5 pr-4 text-sm font-semibold text-white">${esc(l.barangay_name)}</td>
            <td class="py-2.5 pr-4 text-xs text-[#B5B5B5] font-mono">${parseFloat(l.latitude).toFixed(6)}</td>
            <td class="py-2.5 text-xs text-[#B5B5B5] font-mono">${parseFloat(l.longitude).toFixed(6)}</td>
        </tr>
    `).join('');

            document.getElementById('view-body').innerHTML = `
        <div class="flex flex-col gap-6">

            <!-- Status + Company -->
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-white font-black text-xl">${esc(m.company_name)}</div>
                    <div class="text-[#B5B5B5] text-xs font-semibold mt-0.5">Maintenance ID #${parseInt(m.id)}</div>
                </div>
                <span class="px-3 py-1.5 border text-[10px] font-black rounded-xl ${badge} uppercase tracking-widest">
                    ${esc(statusLabel(m.status))}
                </span>
            </div>

            <!-- Detail grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white/3 border border-white/6 rounded-xl p-4">
                    <div class="text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-widest mb-1">Date</div>
                    <div class="text-white font-bold text-sm">${esc(m.maintenance_date)}</div>
                </div>
                <div class="bg-white/3 border border-white/6 rounded-xl p-4">
                    <div class="text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-widest mb-1">Time Window</div>
                    <div class="text-white font-bold text-sm">${esc(m.start_time)} – ${esc(m.end_time)}</div>
                </div>
                <div class="bg-white/3 border border-white/6 rounded-xl p-4">
                    <div class="text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-widest mb-1">Coverage Radius</div>
                    <div class="text-white font-bold text-sm">${esc(String(m.radius))} m</div>
                </div>
                <div class="bg-white/3 border border-white/6 rounded-xl p-4">
                    <div class="text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-widest mb-1">Barangays Affected</div>
                    <div class="text-white font-bold text-sm">${locs.length}</div>
                </div>
            </div>

            ${m.description ? `
            <div class="bg-white/3 border border-white/6 rounded-xl p-4">
                <div class="text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-widest mb-2">Description</div>
                <p class="text-[#B5B5B5] text-sm leading-relaxed">${esc(m.description)}</p>
            </div>` : ''}

            <!-- Location table -->
            <div>
                <div class="text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-widest mb-3">
                    📍 Maintenance Locations (from DB)
                </div>
                <div class="bg-white/3 border border-white/6 rounded-xl overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/8 bg-white/3">
                                <th class="px-4 py-2.5 text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-wider">Barangay</th>
                                <th class="px-4 py-2.5 text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-wider">Latitude</th>
                                <th class="px-4 py-2.5 text-[10px] font-black text-[#B5B5B5]/60 uppercase tracking-wider">Longitude</th>
                            </tr>
                        </thead>
                        <tbody class="px-4">
                            ${locRows || '<tr><td colspan="3" class="py-4 text-center text-xs text-white/25">No locations stored.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    `;

            document.getElementById('view-modal').classList.add('open');
        }
        function closeViewModal() {
            document.getElementById('view-modal').classList.remove('open');
            viewingId = null;
        }
        function triggerDeleteFromView() {
            closeViewModal();
            openDeleteModal(viewingId || pendingDeleteId);
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  DELETE MODAL                                             ║
           ╚═══════════════════════════════════════════════════════════╝ */
        function openDeleteModal(id) {
            pendingDeleteId = id;
            document.getElementById('delete-modal').classList.add('open');
        }
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('open');
            pendingDeleteId = null;
        }
        async function confirmDelete() {
            if (!pendingDeleteId) return;
            const btn = document.getElementById('confirm-delete-btn');
            btn.disabled = true; btn.innerText = 'Deleting…';
            try {
                const res = await fetch(API.delete, {
                    method: 'DELETE', credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: pendingDeleteId }),
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.message);
                closeDeleteModal();
                await loadMaintenance();
            } catch (err) {
                alert('Delete failed: ' + err.message);
            } finally {
                btn.disabled = false; btn.innerText = 'Delete';
            }
        }

        /* ╔═══════════════════════════════════════════════════════════╗
           ║  INIT                                                     ║
           ╚═══════════════════════════════════════════════════════════╝ */
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            loadMaintenance();
            setInterval(loadMaintenance, 15000);
        });
    </script>

</body>

</html>