<?php
session_start();

require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();

if ($user['role'] !== 'electric_company') {
    header("Location: " . FRONTEND_URL . "/src/auth/login.php");
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
    <title>Maintenance — PowerGuide Electric</title>

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

        .glow-blue:hover {
            box-shadow: 0 10px 25px -5px rgba(79, 195, 247, 0.2);
        }

        .glow-green:hover {
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

        .btn-blue {
            background: rgba(79, 195, 247, 0.12);
            color: #4FC3F7;
            border-color: rgba(79, 195, 247, 0.25);
        }

        .btn-green {
            background: rgba(0, 186, 0, 0.12);
            color: #00BA00;
            border-color: rgba(0, 186, 0, 0.25);
        }

        .btn-red {
            background: rgba(203, 52, 53, 0.12);
            color: #CB3435;
            border-color: rgba(203, 52, 53, 0.25);
        }

        .btn-warn {
            background: rgba(250, 176, 5, 0.12);
            color: #FAB005;
            border-color: rgba(250, 176, 5, 0.25);
        }

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

        /* Form inputs */
        .form-input {
            width: 100%;
            background: #0D0E2A;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 12px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: border-color 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: rgba(254, 187, 2, 0.4);
        }

        .form-input option {
            background: #0D0E2A;
        }

        .form-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 5px;
        }

        /* Maintenance cards */
        .maint-card {
            background: rgba(13, 14, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 16px;
            transition: all 0.2s;
        }

        .maint-card:hover {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Modal */
        #editModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.75);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        #editModal.open {
            display: flex;
        }

        .modal-box {
            background: #0D0E2A;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 28px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Status badge helpers */
        .badge-upcoming {
            color: #FAB005;
            background: rgba(250, 176, 5, 0.1);
            border: 1px solid rgba(250, 176, 5, 0.25);
        }

        .badge-ongoing {
            color: #4FC3F7;
            background: rgba(79, 195, 247, 0.1);
            border: 1px solid rgba(79, 195, 247, 0.25);
        }

        .badge-completed {
            color: #B5B5B5;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Barangay tag chips */
        .barangay-chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(254, 187, 2, 0.1);
            color: #FFBB02;
            border: 1px solid rgba(254, 187, 2, 0.2);
            margin: 2px;
        }

        /* Map container sizing */
        #mainMap {
            width: 100%;
            height: 100%;
            min-height: 400px;
            background: #0E0F26;
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

        <!-- Nav Links -->
        <div class="flex flex-col gap-2">
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
                class="flex items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black font-bold text-sm shadow-lg shadow-[#FEBB02]/10">
                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Maintenance</span>
            </a>
        </div>

        <!-- Profile -->
        <div class="mt-auto mb-6">
            <div
                class="flex flex-row items-center justify-between gap-3 px-4 py-3 rounded-2xl bg-[#31324C]/20 border border-white/5">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="h-10 w-10 rounded-xl overflow-hidden border border-[#FFBB02]/30 flex-shrink-0 bg-[#1A1B3A]">
                        <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <span
                            class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['name'] ?? 'Company Admin') ?></span>
                        <span class="text-[10px] font-medium text-[#B5B5B5] truncate uppercase">electric</span>
                    </div>
                </div>
                <a href="<?= BACKEND_URL ?>/public/logout.php"
                    class="p-2 text-[#B5B5B5] hover:text-[#CB3435] hover:bg-[#CB3435]/10 rounded-xl transition-all group"
                    title="Logout">
                    <svg class="w-5 h-5 transform group-hover:translate-x-0.5 transition-transform" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
                    Maintenance <span class="text-[#FFBB02]">Management</span>
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#4FC3F7] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#4FC3F7]"></span>
                    </span>
                    <span class="text-[10px] text-[#B5B5B5] font-medium tracking-widest uppercase">Live
                        Scheduling</span>
                </div>
            </div>
            <div
                class="text-sm font-medium text-[#B5B5B5] bg-[#31324C]/20 px-4 py-2 rounded-lg border border-white/5 self-end sm:self-auto">
                Last Sync: <span id="sync-time" class="text-white">—</span>
            </div>
        </header>

        <div class="p-6 lg:p-10 flex flex-col gap-7">

            <!-- STAT CARDS -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                <div
                    class="card-hover bg-[#31324C]/20 border border-white/5 rounded-3xl p-7 flex flex-col gap-5 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-28 h-28 bg-[#FAB005]/10 rounded-full blur-3xl -mr-8 -mt-8 group-hover:bg-[#FAB005]/20 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#FAB005]/10 border border-[#FAB005]/20 p-3 rounded-2xl text-[#FAB005]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#FAB005] text-[10px] font-black px-3 py-1 bg-[#FAB005]/10 rounded-lg tracking-widest uppercase border border-[#FAB005]/20">Upcoming</span>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="countUpcoming"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-1">Scheduled Maintenance</span>
                    </div>
                </div>

                <div
                    class="card-hover glow-blue bg-[#31324C]/20 border border-white/5 rounded-3xl p-7 flex flex-col gap-5 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-28 h-28 bg-[#4FC3F7]/10 rounded-full blur-3xl -mr-8 -mt-8 group-hover:bg-[#4FC3F7]/20 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#4FC3F7]/10 border border-[#4FC3F7]/20 p-3 rounded-2xl text-[#4FC3F7]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#4FC3F7] text-[10px] font-black px-3 py-1 bg-[#4FC3F7]/10 rounded-lg tracking-widest uppercase border border-[#4FC3F7]/20">Ongoing</span>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="countOngoing"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-1">Active Maintenance</span>
                    </div>
                </div>

                <div
                    class="card-hover glow-green bg-[#31324C]/20 border border-white/5 rounded-3xl p-7 flex flex-col gap-5 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-28 h-28 bg-[#00BA00]/10 rounded-full blur-3xl -mr-8 -mt-8 group-hover:bg-[#00BA00]/20 transition-all">
                    </div>
                    <div class="flex justify-between items-center z-10">
                        <div class="bg-[#00BA00]/10 border border-[#00BA00]/20 p-3 rounded-2xl text-[#00BA00]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-[#00BA00] text-[10px] font-black px-3 py-1 bg-[#00BA00]/10 rounded-lg tracking-widest uppercase border border-[#00BA00]/20">Done</span>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="countCompleted"
                            class="text-white text-5xl font-black tracking-tighter loading-pulse">0</span>
                        <span class="text-[#B5B5B5] text-sm font-medium mt-1">Completed Maintenance</span>
                    </div>
                </div>
            </section>

            <!-- TWO-COLUMN LAYOUT: FORM+LISTS | MAP -->
            <section class="grid grid-cols-1 xl:grid-cols-5 gap-6">

                <!-- LEFT: Form + Lists -->
                <div class="xl:col-span-2 flex flex-col gap-6">

                    <!-- CREATE FORM -->
                    <div class="rounded-2xl border border-white/5 bg-[#31324C]/10 p-6 shadow-xl">
                        <div class="flex items-center gap-2 mb-5">
                            <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-widest text-[#B5B5B5]">Schedule
                                Maintenance</span>
                        </div>

                        <form id="createForm" onsubmit="submitCreate(event)" class="flex flex-col gap-4">

                            <!-- Notify All toggle -->
                            <div
                                class="flex items-center justify-between bg-[#0D0E2A]/60 border border-white/5 rounded-xl px-4 py-3">
                                <div>
                                    <div class="text-xs font-bold text-white">Notify All Users</div>
                                    <div class="text-[10px] text-white/30 font-medium mt-0.5">Affects all areas in
                                        Dagupan</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="notifyAll" class="sr-only peer"
                                        onchange="toggleNotifyAll(this)">
                                    <div
                                        class="w-10 h-5 bg-[#31324C] rounded-full peer peer-checked:bg-[#FFBB02] transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5">
                                    </div>
                                </label>
                            </div>

                            <!-- Affected Areas -->
                            <div>
                                <label class="form-label">Affected Areas</label>
                                <textarea id="affectedAreas" class="form-input" rows="2"
                                    placeholder="Click barangays on map or select below..."
                                    style="resize:none;"></textarea>
                            </div>

                            <!-- Barangay Multi-select -->
                            <div id="barangaySelectWrap">
                                <label class="form-label">Select Barangays</label>
                                <select id="barangaySelect" multiple class="form-input" style="height:100px;"
                                    onchange="syncSelectToTextarea()">
                                    <option value="Bonuan Gueset">Bonuan Gueset</option>
                                    <option value="Bonuan Boquig">Bonuan Boquig</option>
                                    <option value="Bonuan Binloc">Bonuan Binloc</option>
                                    <option value="Lucao">Lucao</option>
                                    <option value="Tapuac">Tapuac</option>
                                    <option value="Tambac">Tambac</option>
                                    <option value="Pantal">Pantal</option>
                                    <option value="Bacayao Norte">Bacayao Norte</option>
                                    <option value="Bacayao Sur">Bacayao Sur</option>
                                    <option value="Malued">Malued</option>
                                    <option value="Mayombo">Mayombo</option>
                                    <option value="Mangin">Mangin</option>
                                    <option value="Tebeng">Tebeng</option>
                                    <option value="Pogo Chico">Pogo Chico</option>
                                    <option value="Pogo Grande">Pogo Grande</option>
                                    <option value="Herrero">Herrero</option>
                                    <option value="Poblacion Centro">Poblacion Centro</option>
                                    <option value="Poblacion Oeste">Poblacion Oeste</option>
                                    <option value="Poblacion Este">Poblacion Este</option>
                                </select>
                                <p class="text-[10px] text-white/30 mt-1 font-medium">Hold Ctrl/Cmd to multi-select ·
                                    Click map circles to toggle</p>
                            </div>

                            <!-- Date Row -->
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="form-label">Maintenance Date</label>
                                    <input type="date" id="maintDate" class="form-input" required>
                                </div>
                            </div>

                            <!-- Time Row -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Start Time</label>
                                    <input type="time" id="startTime" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label">End Time</label>
                                    <input type="time" id="endTime" class="form-input" required>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-input" rows="3"
                                    placeholder="Describe the maintenance work..." required
                                    style="resize:none;"></textarea>
                            </div>

                            <!-- Radius -->
                            <div>
                                <label class="form-label">Radius (meters)</label>
                                <input type="number" id="radius" class="form-input" value="500" min="100" max="5000"
                                    step="100">
                            </div>

                            <!-- Submit -->
                            <button type="submit" id="submitBtn"
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-[#FFBB02] text-black font-black text-sm tracking-wide hover:bg-[#E39A00] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Schedule Maintenance
                            </button>
                        </form>
                    </div>

                    <!-- ACTIVE MAINTENANCE LIST -->
                    <div class="rounded-2xl border border-white/5 bg-[#31324C]/10 p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#4FC3F7] animate-pulse"></span>
                                <span class="text-xs font-bold uppercase tracking-widest text-[#B5B5B5]">Active
                                    Maintenance</span>
                            </div>
                            <span id="activeCount"
                                class="text-[10px] font-bold text-white/30 px-2 py-1 bg-white/5 rounded-lg border border-white/5">0</span>
                        </div>
                        <div id="activeList" class="flex flex-col gap-3 max-h-[420px] overflow-y-auto custom-scrollbar">
                            <div class="text-xs text-white/30 text-center py-8 font-medium">No active maintenance
                                scheduled.</div>
                        </div>
                    </div>

                    <!-- COMPLETED MAINTENANCE LIST -->
                    <div class="rounded-2xl border border-white/5 bg-[#31324C]/10 p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#00BA00]"></span>
                                <span
                                    class="text-xs font-bold uppercase tracking-widest text-[#B5B5B5]">Completed</span>
                            </div>
                            <span id="completedCount"
                                class="text-[10px] font-bold text-white/30 px-2 py-1 bg-white/5 rounded-lg border border-white/5">0</span>
                        </div>
                        <div id="completedList"
                            class="flex flex-col gap-3 max-h-[320px] overflow-y-auto custom-scrollbar">
                            <div class="text-xs text-white/30 text-center py-8 font-medium">No completed maintenance
                                yet.</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Map -->
                <div class="xl:col-span-3 flex flex-col">
                    <div
                        class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#31324C]/10 flex flex-col h-full min-h-[700px]">

                        <!-- Map Header -->
                        <div class="flex items-center justify-between p-5 border-b border-white/5 bg-[#16172E]/40">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="font-bold text-sm">Maintenance Map</span>
                                <span class="text-[10px] text-white/30 font-medium">— click circles to select
                                    barangays</span>
                            </div>
                            <span
                                class="text-[#4FC3F7] px-2.5 py-1 bg-[#4FC3F7]/10 text-[10px] rounded-lg font-bold border border-[#4FC3F7]/20 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-[#4FC3F7] rounded-full animate-pulse"></span> LIVE
                            </span>
                        </div>

                        <!-- Map -->
                        <div class="relative flex-1">
                            <div id="mainMap" class="h-full w-full"></div>

                            <!-- Legend -->
                            <div
                                class="absolute bottom-4 left-4 border border-white/10 bg-[#1A1B33]/95 rounded-2xl z-[1000] p-3 shadow-xl backdrop-blur-md">
                                <div class="flex flex-col gap-1.5 min-w-[140px]">
                                    <span class="font-bold text-[10px] tracking-widest text-white/40 block mb-0.5">MAP
                                        LEGEND</span>
                                    <span class="font-semibold text-xs flex items-center text-white/90">
                                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-2 block"></span>Selectable
                                        Area
                                    </span>
                                    <span class="font-semibold text-xs flex items-center text-white/90">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 block"></span>Selected /
                                        Upcoming
                                    </span>
                                    <span class="font-semibold text-xs flex items-center text-white/90">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-400 mr-2 block"></span>Ongoing
                                    </span>
                                    <span class="font-semibold text-xs flex items-center text-white/90">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gray-400 mr-2 block"></span>Completed
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div><!-- /p-6 -->
    </main>

    <!-- ===================== EDIT MODAL ===================== -->
    <div id="editModal">
        <div class="modal-box slide-up">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="text-sm font-bold text-white uppercase tracking-widest">Edit Maintenance</span>
                </div>
                <button onclick="closeModal()" class="text-white/30 hover:text-white transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <input type="hidden" id="editId">

            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Maintenance Date</label>
                        <input type="date" id="editDate" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select id="editStatus" class="form-input">
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Start Time</label>
                        <input type="time" id="editStart" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">End Time</label>
                        <input type="time" id="editEnd" class="form-input" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea id="editDesc" class="form-input" rows="3" style="resize:none;"></textarea>
                </div>
                <div>
                    <label class="form-label">Radius (meters)</label>
                    <input type="number" id="editRadius" class="form-input" min="100" max="5000" step="100">
                </div>
                <div class="flex gap-3 pt-2">
                    <button onclick="submitUpdate()"
                        class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-[#4FC3F7] text-black font-black text-sm tracking-wide hover:bg-[#29B6F6] transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Update
                    </button>
                    <button onclick="closeModal()"
                        class="px-5 py-3 rounded-xl bg-white/5 border border-white/10 text-white/60 font-bold text-sm hover:bg-white/10 transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        /* ========== MOBILE MENU ========== */
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        /* ========== API ENDPOINTS ========== */
        const API_CREATE = "http://localhost/CrowdsourcedAPI/api/maintenance/create.php";
        const API_GET = "http://localhost/CrowdsourcedAPI/api/maintenance/get.php";
        const API_UPDATE = "http://localhost/CrowdsourcedAPI/api/maintenance/update.php";
        const API_DELETE = "http://localhost/CrowdsourcedAPI/api/maintenance/delete.php";

        /* ========== BARANGAY DATA ========== */
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
            "Poblacion Este": { lat: 16.0440, lng: 120.3360 }
        };

        /* ========== STATE ========== */
        let map, barangayLayerGroup, maintenanceLayerGroup;
        let barangayCircles = {}; // name → { circle, label }
        let selectedBarangays = new Set();
        let allMaintenance = [];

        /* ========== HELPERS ========== */
        function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

        function computeStatus(m) {
            const now = new Date();
            const dateStr = (m.maintenance_date || '').substring(0, 10);
            if (!dateStr) return 'upcoming';
            const startDT = new Date(`${dateStr}T${m.start_time || '00:00'}:00`);
            const endDT = new Date(`${dateStr}T${m.end_time || '23:59'}:00`);
            if (now < startDT) return 'upcoming';
            if (now > endDT) return 'completed';
            return 'ongoing';
        }

        function statusBadge(status) {
            const map = {
                upcoming: { cls: 'badge-upcoming', label: 'Upcoming' },
                ongoing: { cls: 'badge-ongoing', label: 'Ongoing' },
                completed: { cls: 'badge-completed', label: 'Completed' }
            };
            const s = map[status] || map.upcoming;
            return `<span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wide ${s.cls}">${s.label}</span>`;
        }

        function parseBarangays(raw) {
            if (!raw) return [];
            if (Array.isArray(raw)) return raw.filter(Boolean);
            if (typeof raw === 'string') {
                const trimmed = raw.trim();
                if (trimmed.startsWith('[')) {
                    try { return JSON.parse(trimmed).filter(Boolean); } catch (e) { }
                }
                return trimmed.split(',').map(s => s.trim()).filter(Boolean);
            }
            return [];
        }

        /* ========== MAP INIT ========== */
        function initMap() {
            map = L.map('mainMap', { zoomControl: false }).setView([16.045, 120.335], 13);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            barangayLayerGroup = L.layerGroup().addTo(map);
            maintenanceLayerGroup = L.layerGroup().addTo(map);

            drawBarangayCircles();

            // Fix resize glitch
            setTimeout(() => map.invalidateSize(), 300);
        }

        /* ========== SELECTABLE BARANGAY CIRCLES ========== */
        function drawBarangayCircles() {
            barangayLayerGroup.clearLayers();
            barangayCircles = {};

            Object.entries(barangayData).forEach(([name, coords]) => {
                const isSelected = selectedBarangays.has(name);
                const circle = L.circle([coords.lat, coords.lng], {
                    radius: 200,
                    color: isSelected ? '#ef4444' : '#22c55e',
                    fillColor: isSelected ? '#ef4444' : '#22c55e',
                    fillOpacity: 0.35,
                    weight: 2,
                    className: 'barangay-circle'
                });

                circle.bindTooltip(name, {
                    permanent: false,
                    direction: 'top',
                    className: 'leaflet-tooltip'
                });

                circle.on('click', () => toggleBarangaySelection(name));
                barangayLayerGroup.addLayer(circle);
                barangayCircles[name] = circle;
            });
        }

        function toggleBarangaySelection(name) {
            if (document.getElementById('notifyAll').checked) return;

            if (selectedBarangays.has(name)) {
                selectedBarangays.delete(name);
            } else {
                selectedBarangays.add(name);
            }

            // Update circle color
            if (barangayCircles[name]) {
                const selected = selectedBarangays.has(name);
                barangayCircles[name].setStyle({
                    color: selected ? '#ef4444' : '#22c55e',
                    fillColor: selected ? '#ef4444' : '#22c55e'
                });
            }

            // Sync to select + textarea
            syncSelectionToForm();
        }

        function syncSelectionToForm() {
            const textarea = document.getElementById('affectedAreas');
            const select = document.getElementById('barangaySelect');
            const arr = Array.from(selectedBarangays);
            textarea.value = arr.join(', ');

            // Sync multi-select
            Array.from(select.options).forEach(opt => {
                opt.selected = selectedBarangays.has(opt.value);
            });
        }

        function syncSelectToTextarea() {
            const select = document.getElementById('barangaySelect');
            const chosen = Array.from(select.selectedOptions).map(o => o.value);
            selectedBarangays = new Set(chosen);

            // Update circles
            Object.keys(barangayData).forEach(name => {
                if (barangayCircles[name]) {
                    const sel = selectedBarangays.has(name);
                    barangayCircles[name].setStyle({
                        color: sel ? '#ef4444' : '#22c55e',
                        fillColor: sel ? '#ef4444' : '#22c55e'
                    });
                }
            });

            document.getElementById('affectedAreas').value = chosen.join(', ');
        }

        function toggleNotifyAll(cb) {
            const wrap = document.getElementById('barangaySelectWrap');
            const textarea = document.getElementById('affectedAreas');
            if (cb.checked) {
                wrap.style.opacity = '0.4';
                wrap.style.pointerEvents = 'none';
                textarea.value = 'ALL AREAS';
                selectedBarangays.clear();
                drawBarangayCircles();
            } else {
                wrap.style.opacity = '1';
                wrap.style.pointerEvents = 'auto';
                textarea.value = '';
            }
        }

        /* ========== LOAD DATA ========== */
        async function loadData() {
            try {
                const res = await fetch(API_GET, { credentials: 'include' });
                const data = await res.json();
                if (!data.success) return;

                allMaintenance = (data.data || []).map(m => ({
                    ...m,
                    _status: computeStatus(m)
                }));

                updateCounters();
                renderLists();
                renderMaintenanceOnMap();

                const el = document.getElementById('sync-time');
                if (el) el.innerText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            } catch (e) {
                console.error('Load error:', e);
            }
        }

        /* ========== COUNTERS ========== */
        function updateCounters() {
            const upcoming = allMaintenance.filter(m => m._status === 'upcoming').length;
            const ongoing = allMaintenance.filter(m => m._status === 'ongoing').length;
            const completed = allMaintenance.filter(m => m._status === 'completed').length;
            setNum('countUpcoming', upcoming);
            setNum('countOngoing', ongoing);
            setNum('countCompleted', completed);
        }

        function setNum(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            el.innerText = val;
            el.classList.remove('loading-pulse');
        }

        /* ========== RENDER LISTS ========== */
        function renderLists() {
            const active = allMaintenance.filter(m => m._status !== 'completed');
            const completed = allMaintenance.filter(m => m._status === 'completed');

            document.getElementById('activeCount').innerText = active.length;
            document.getElementById('completedCount').innerText = completed.length;

            renderMaintList('activeList', active, false);
            renderMaintList('completedList', completed, true);
        }

        function renderMaintList(containerId, items, isCompleted) {
            const container = document.getElementById(containerId);
            if (!container) return;

            if (items.length === 0) {
                container.innerHTML = `<div class="text-xs text-white/30 text-center py-8 font-medium">${isCompleted ? 'No completed maintenance yet.' : 'No active maintenance scheduled.'}</div>`;
                return;
            }

            container.innerHTML = items.map(m => {
                const barangays = parseBarangays(
                    m.affected_barangays || m.barangays
                );
                const barangayChips = barangays.length > 0
                    ? barangays.slice(0, 4).map(b => `<span class="barangay-chip">${esc(b)}</span>`).join('')
                    + (barangays.length > 4 ? `<span class="barangay-chip">+${barangays.length - 4}</span>` : '')
                    : `<span class="barangay-chip">All Areas</span>`;

                const editBtn = !isCompleted
                    ? `<button onclick="openEdit(${m.maintenance_id || m.id})" class="action-btn btn-blue">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>`
                    : `<span class="text-[10px] font-bold text-[#B5B5B5] uppercase tracking-widest px-3 py-1.5 bg-white/5 border border-white/5 rounded-lg">Completed</span>`;

                return `
            <div class="maint-card" id="mcard-${m.maintenance_id || m.id}"  >
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-white font-bold text-xs truncate">${esc(m.company_name || 'Electric Company')}</span>
                        <span class="text-white/40 text-[10px] font-medium">${esc(m.maintenance_date || '')} · ${esc(m.start_time || '')} – ${esc(m.end_time || '')}</span>
                    </div>
                    ${statusBadge(m._status)}
                </div>

                <div class="flex items-center gap-3 text-[10px] text-white/40 font-semibold mb-2">
                    <span>📡 Radius: ${esc(m.radius || 500)}m</span>
                </div>

                <div class="flex flex-wrap mb-2">${barangayChips}</div>

                ${m.description ? `<p class="text-[11px] text-white/50 font-medium mb-3 line-clamp-2 leading-relaxed">${esc(m.description)}</p>` : ''}

                <div class="flex items-center gap-2 mt-1 pt-3 border-t border-white/5">
                    ${editBtn}
                    <button onclick="deleteMaint(${m.maintenance_id || m.id})" class="action-btn btn-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </div>
            </div>`;
            }).join('');
        }

        /* ========== MAINTENANCE MAP PINS ========== */
        function renderMaintenanceOnMap() {
            maintenanceLayerGroup.clearLayers();

            allMaintenance.forEach(m => {
                const barangays = parseBarangays(m.affected_barangays);
                const radius = parseInt(m.radius) || 500;
                const status = m._status;

                let pinColor, fillColor;
                switch (status) {
                    case 'upcoming': pinColor = '#ef4444'; fillColor = '#ef4444'; break;
                    case 'ongoing': pinColor = '#4FC3F7'; fillColor = '#4FC3F7'; break;
                    case 'completed': pinColor = '#9ca3af'; fillColor = '#9ca3af'; break;
                    default: pinColor = '#FFBB02'; fillColor = '#FFBB02';
                }

                const bList = barangays.join(', ') || 'All Areas';

                // Draw a circle per barangay
                barangays.forEach(name => {
                    const coords = barangayData[name];
                    if (!coords) return;

                    // Radius fill circle
                    L.circle([coords.lat, coords.lng], {
                        radius,
                        color: pinColor,
                        fillColor,
                        fillOpacity: 0.15,
                        weight: 1.5,
                        dashArray: status === 'completed' ? '4,4' : null
                    }).addTo(maintenanceLayerGroup);

                    // Center marker
                    const marker = L.circleMarker([coords.lat, coords.lng], {
                        radius: 7,
                        fillColor: pinColor,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.95
                    });

                    marker.bindPopup(`
                    <div style="font-family:Montserrat,sans-serif;font-size:12px;min-width:160px;">
                        <b style="font-size:13px;">${esc(name)}</b><br>
                        <span style="color:#666;">Company:</span> ${esc(m.company_name || '—')}<br>
                        <span style="color:#666;">Status:</span> <b>${esc(status)}</b><br>
                        <span style="color:#666;">Date:</span> ${esc(m.maintenance_date || '—')}<br>
                        <span style="color:#666;">Time:</span> ${esc(m.start_time || '—')} – ${esc(m.end_time || '—')}<br>
                        <span style="color:#666;">Radius:</span> ${esc(radius)}m<br>
                        ${m.description ? `<span style="color:#666;">Note:</span> ${esc(m.description)}` : ''}
                    </div>
                `);

                    maintenanceLayerGroup.addLayer(marker);
                });

                // If notify_all or no specific barangay: show center of Dagupan
                if (barangays.length === 0 || m.notify_all == 1) {
                    const center = [16.045, 120.335];
                    L.circle(center, {
                        radius: radius,
                        color: pinColor, fillColor, fillOpacity: 0.12, weight: 2
                    }).addTo(maintenanceLayerGroup);

                    L.circleMarker(center, {
                        radius: 9,
                        fillColor: pinColor, color: '#fff', weight: 2, fillOpacity: 0.9
                    }).bindPopup(`
                    <div style="font-family:Montserrat,sans-serif;font-size:12px;">
                        <b>All Dagupan Areas</b><br>
                        <span style="color:#666;">Status:</span> <b>${esc(status)}</b><br>
                        <span style="color:#666;">Date:</span> ${esc(m.maintenance_date || '—')}<br>
                        <span style="color:#666;">Time:</span> ${esc(m.start_time || '—')} – ${esc(m.end_time || '—')}
                    </div>
                `).addTo(maintenanceLayerGroup);
                }
            });
        }

        /* ========== CREATE FORM ========== */
        async function submitCreate(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Scheduling...';

            const notifyAll = document.getElementById('notifyAll').checked;
            const barangays = notifyAll ? [] : Array.from(selectedBarangays);

            const payload = {
                maintenance_date: document.getElementById('maintDate').value,
                start_time: document.getElementById('startTime').value,
                end_time: document.getElementById('endTime').value,
                description: document.getElementById('description').value,
                radius: parseInt(document.getElementById('radius').value) || 500,
                notify_all: notifyAll ? 1 : 0,
                barangays
            };

            try {
                const res = await fetch(API_CREATE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                showToast(data.message || 'Maintenance scheduled.', data.success ? 'success' : 'error');
                if (data.success) {
                    resetForm();
                    await loadData();
                }
            } catch (err) {
                showToast('Failed to create maintenance.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Schedule Maintenance`;
            }
        }

        function resetForm() {
            document.getElementById('createForm').reset();
            selectedBarangays.clear();
            drawBarangayCircles();
            document.getElementById('affectedAreas').value = '';
            const wrap = document.getElementById('barangaySelectWrap');
            wrap.style.opacity = '1';
            wrap.style.pointerEvents = 'auto';
        }

        /* ========== EDIT MODAL ========== */
        function openEdit(id) {

            const m = allMaintenance.find(
                x => (x.maintenance_id || x.id) == id
            );

            if (!m) {
                console.log("ALL MAINTENANCE:", allMaintenance);
                showToast('Maintenance not found.', 'error');
                return;
            }

            document.getElementById('editId').value =
                m.maintenance_id || m.id;

            document.getElementById('editDate').value =
                (m.maintenance_date || '').substring(0, 10);

            document.getElementById('editStart').value =
                m.start_time || '';

            document.getElementById('editEnd').value =
                m.end_time || '';

            document.getElementById('editDesc').value =
                m.description || '';

            document.getElementById('editRadius').value =
                m.radius || 500;

            document.getElementById('editStatus').value =
                m._status || 'upcoming';

            document.getElementById('editModal')
                .classList.add('open');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('open');
        }

        async function submitUpdate() {

            const id = document.getElementById('editId').value;

            if (!id) {
                showToast('Invalid maintenance ID.', 'error');
                return;
            }

            /* =========================
               GET ORIGINAL MAINTENANCE
            ========================= */
            const currentMaintenance = allMaintenance.find(
                m => (m.maintenance_id || m.id) == id
            );

            if (!currentMaintenance) {
                showToast('Maintenance data not found.', 'error');
                return;
            }

            /* =========================
               PRESERVE BARANGAYS
            ========================= */
            const barangays = parseBarangays(
                currentMaintenance.affected_barangays
            );

            const payload = {
                maintenance_id: parseInt(id),

                maintenance_date:
                    document.getElementById('editDate').value,

                start_time:
                    document.getElementById('editStart').value,

                end_time:
                    document.getElementById('editEnd').value,

                description:
                    document.getElementById('editDesc').value,

                radius:
                    parseInt(
                        document.getElementById('editRadius').value
                    ) || 500,

                status:
                    document.getElementById('editStatus').value,

                /* IMPORTANT */
                barangays: barangays
            };

            console.log("UPDATE PAYLOAD:", payload);

            try {

                const res = await fetch(API_UPDATE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify(payload)
                });

                const text = await res.text();

                console.log("RAW UPDATE RESPONSE:", text);

                let data;

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON:", text);

                    showToast(
                        'Server returned invalid response.',
                        'error'
                    );

                    return;
                }

                showToast(
                    data.message || 'Updated successfully.',
                    data.success ? 'success' : 'error'
                );

                if (data.success) {

                    closeModal();

                    await loadData();

                    renderMaintenanceOnMap();
                }

            } catch (err) {

                console.error("UPDATE ERROR:", err);

                showToast(
                    'Failed to update maintenance.',
                    'error'
                );
            }
        }

        /* ========== DELETE ========== */
        async function deleteMaint(id) {
            if (!id) return;
            if (!confirm('Delete this maintenance schedule? This cannot be undone.')) return;

            try {
                const res = await fetch(API_DELETE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ maintenance_id: id })
                });
                const data = await res.json();
                showToast(data.message || 'Deleted.', data.success ? 'success' : 'error');
                if (data.success) {
                    const card = document.getElementById(`mcard-${id}`);
                    if (card) card.remove();
                    await loadData();
                }
            } catch (err) {
                showToast('Delete failed.', 'error');
            }
        }

        /* ========== TOAST ========== */
        function showToast(msg, type = 'success') {
            const existing = document.getElementById('toast');
            if (existing) existing.remove();
            const colors = {
                success: 'bg-[#00BA00]/10 border-[#00BA00]/30 text-[#00BA00]',
                error: 'bg-[#CB3435]/10 border-[#CB3435]/30 text-[#CB3435]',
                warn: 'bg-[#FAB005]/10 border-[#FAB005]/30 text-[#FAB005]'
            };
            const icons = {
                success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                warn: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
            };
            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = `fixed bottom-6 right-6 z-[99999] flex items-center gap-2.5 px-5 py-3.5 rounded-2xl border text-sm font-bold backdrop-blur-sm shadow-xl slide-up ${colors[type] || colors.success}`;
            toast.innerHTML = `<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="${icons[type] || icons.success}"/></svg>${esc(msg)}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        /* ========== INIT ========== */
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            setTimeout(loadData, 400);
            setInterval(loadData, 30000);
        });
    </script>
</body>

</html>