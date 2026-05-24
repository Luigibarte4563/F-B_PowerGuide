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
    <title>Maintenance Scheduling — PowerGuide Electric</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap"
        rel="stylesheet">

    <!-- Leaflet CSS -->
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
            box-shadow: 0 10px 25px -5px rgba(255, 187, 2, 0.15);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0D0E2A;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #31324C;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #FFBB02;
        }

        /* Invert Map to Match Dark Theme */

        /* Modals Animation */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-animate {
            animation: modalFadeIn 0.25s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.15);
            border-top-color: #FFBB02;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* General Forms */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        .action-btn {
            transition: all 0.2s;
        }

        .action-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            filter: brightness(1.2);
        }

        .action-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased h-screen overflow-hidden flex">

    <!-- Mobile toggle -->
    <button id="menuToggle" onclick="toggleMobileSidebar()"
        class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg border border-white/10 hover:bg-opacity-80 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div id="overlay" onclick="toggleMobileSidebar()"
        class="fixed inset-0 bg-black/60 z-30 hidden backdrop-blur-sm transition-all"></div>

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
                class="group flex items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#31324C]/40 transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="outages.php"
                class="group flex items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#31324C]/40 transition-all font-semibold text-sm">
                <svg class="w-5 h-5 text-[#B5B5B5]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Manage Outages</span>
            </a>

            <!-- Active Menu -->
            <a href="maintenance.php"
                class="flex items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black font-bold text-sm shadow-lg shadow-[#FEBB02]/20">
                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Maintenance</span>
            </a>
        </div>

        <!-- Profile Panel -->
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

                <!-- Logout Button -->
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

    <!-- ===================== MAIN CONTENT ===================== -->
    <main class="flex-1 overflow-y-auto custom-scrollbar flex flex-col relative w-full">

        <!-- HEADER -->
        <header
            class="px-6 lg:px-10 pt-20 lg:pt-8 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-white/5 bg-[#03041A] sticky top-0 z-20">
            <div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-tight">
                    Maintenance <span class="text-[#FFBB02]">Scheduling</span>
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#4FC3F7] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#4FC3F7]"></span>
                    </span>
                    <span class="text-[10px] text-[#B5B5B5] font-medium tracking-widest uppercase">System
                        Dashboard</span>
                </div>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                <div
                    class="text-sm font-medium text-[#B5B5B5] bg-[#31324C]/20 px-4 py-2.5 rounded-xl border border-white/5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Last Sync: <span id="sync-time" class="text-white">—</span>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-10 flex flex-col gap-7">

            <!-- STAT CARDS -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total -->
                <div
                    class="card-hover bg-[#31324C]/20 border border-white/5 rounded-3xl p-6 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full blur-2xl -mr-6 -mt-6 group-hover:bg-white/10 transition-all">
                    </div>
                    <div class="bg-white/5 border border-white/10 p-3 rounded-2xl text-white w-max">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="stat-total" class="text-white text-4xl font-black tracking-tighter">0</span>
                        <span class="text-[#B5B5B5] text-xs font-bold uppercase tracking-widest mt-1">Total
                            Records</span>
                    </div>
                </div>

                <!-- Upcoming -->
                <div
                    class="card-hover bg-[#31324C]/20 border border-[#FFBB02]/10 rounded-3xl p-6 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-[#FFBB02]/10 rounded-full blur-2xl -mr-6 -mt-6 group-hover:bg-[#FFBB02]/20 transition-all">
                    </div>
                    <div class="bg-[#FFBB02]/10 border border-[#FFBB02]/20 p-3 rounded-2xl text-[#FFBB02] w-max">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="stat-upcoming" class="text-white text-4xl font-black tracking-tighter">0</span>
                        <span class="text-[#FFBB02] text-xs font-bold uppercase tracking-widest mt-1">Upcoming</span>
                    </div>
                </div>

                <!-- Ongoing -->
                <div
                    class="card-hover bg-[#31324C]/20 border border-[#4FC3F7]/10 rounded-3xl p-6 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-[#4FC3F7]/10 rounded-full blur-2xl -mr-6 -mt-6 group-hover:bg-[#4FC3F7]/20 transition-all">
                    </div>
                    <div class="bg-[#4FC3F7]/10 border border-[#4FC3F7]/20 p-3 rounded-2xl text-[#4FC3F7] w-max">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="stat-ongoing" class="text-white text-4xl font-black tracking-tighter">0</span>
                        <span class="text-[#4FC3F7] text-xs font-bold uppercase tracking-widest mt-1">Ongoing</span>
                    </div>
                </div>

                <!-- Completed -->
                <div
                    class="card-hover bg-[#31324C]/20 border border-[#00BA00]/10 rounded-3xl p-6 flex flex-col gap-4 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-[#00BA00]/10 rounded-full blur-2xl -mr-6 -mt-6 group-hover:bg-[#00BA00]/20 transition-all">
                    </div>
                    <div class="bg-[#00BA00]/10 border border-[#00BA00]/20 p-3 rounded-2xl text-[#00BA00] w-max">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 z-10">
                        <span id="stat-completed" class="text-white text-4xl font-black tracking-tighter">0</span>
                        <span class="text-[#00BA00] text-xs font-bold uppercase tracking-widest mt-1">Completed</span>
                    </div>
                </div>
            </section>

            <!-- MAIN DASHBOARD MAP -->
            <section class="rounded-3xl border border-white/5 overflow-hidden shadow-2xl bg-[#31324C]/10 relative">
                <div
                    class="flex items-center justify-between p-5 border-b border-white/5 bg-[#16172E]/80 backdrop-blur-md absolute top-0 w-full z-[400]">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-[#FFBB02]" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span class="font-bold text-sm tracking-wide">Maintenance Areas Map</span>
                    </div>
                    <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-widest">
                        <span class="flex items-center gap-1.5"><span
                                class="w-2.5 h-2.5 rounded-full bg-[#00BA00] shadow-[0_0_8px_#00BA00]"></span>
                            Normal</span>
                        <span class="flex items-center gap-1.5"><span
                                class="w-2.5 h-2.5 rounded-full bg-[#FFBB02] shadow-[0_0_8px_#FFBB02]"></span>
                            Upcoming</span>
                        <span class="flex items-center gap-1.5"><span
                                class="w-2.5 h-2.5 rounded-full bg-[#4FC3F7] shadow-[0_0_8px_#4FC3F7]"></span>
                            Ongoing</span>
                        <span class="flex items-center gap-1.5"><span
                                class="w-2.5 h-2.5 rounded-full bg-gray-500"></span> Completed</span>
                    </div>
                </div>
                <!-- LEAFLET MAP -->
                <div id="mainMap" class="w-full h-80 lg:h-[400px] z-10 bg-[#0E0F26]"></div>
            </section>

            <!-- TABLE CONTROLS & TABLE -->
            <section class="rounded-3xl border border-white/5 bg-[#31324C]/10 flex flex-col shadow-xl overflow-hidden">

                <!-- Toolbar -->
                <div
                    class="p-5 border-b border-white/5 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-[#16172E]/40">
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <!-- Search -->
                        <div class="relative w-full sm:w-64">
                            <svg class="w-4 h-4 absolute left-3 top-3 text-white/40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchInput" placeholder="Search records..."
                                class="w-full bg-[#0D0E2A] border border-white/10 text-white text-xs font-semibold rounded-xl pl-9 pr-4 py-2.5 focus:outline-none focus:border-[#FFBB02]/50 transition-colors placeholder:text-white/20"
                                onkeyup="handleFilterSearch()">
                        </div>

                        <!-- Status Filter -->
                        <select id="statusFilter"
                            class="bg-[#0D0E2A] border border-white/10 text-white text-xs font-semibold rounded-xl px-4 py-2.5 cursor-pointer focus:outline-none focus:border-[#FFBB02]/50 transition-colors min-w-[140px]"
                            onchange="handleFilterSearch()">
                            <option value="all">All Statuses</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Create Button -->
                    <button onclick="openCreateModal()"
                        class="action-btn bg-gradient-to-r from-[#FFBB02] to-[#E39A00] text-black font-bold text-xs px-5 py-2.5 rounded-xl flex items-center gap-2 shadow-lg shadow-[#FFBB02]/20 w-full sm:w-auto justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Schedule Maintenance
                    </button>
                </div>

                <!-- Table Wrapper -->
                <div class="w-full overflow-x-auto custom-scrollbar min-h-[300px]">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-white/5 text-[10px] uppercase tracking-widest text-white/40 font-bold border-b border-white/10">
                                <th class="px-5 py-4">ID</th>
                                <th class="px-5 py-4">Schedule</th>
                                <th class="px-5 py-4">Affected Area</th>
                                <th class="px-5 py-4">Radius</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="text-xs font-medium text-white/80 divide-y divide-white/5">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-white/5 flex items-center justify-between bg-[#16172E]/40">
                    <span id="pageInfo" class="text-[10px] font-bold text-white/30 uppercase tracking-widest">Showing 0
                        to 0 of 0</span>
                    <div id="pagination" class="flex gap-1.5"></div>
                </div>
            </section>

        </div>
    </main>

    <!-- ===================== MODALS ===================== -->

    <!-- CREATE/EDIT MODAL -->
    <div id="maintenanceModal"
        class="fixed inset-0 bg-black/80 z-[1000] hidden items-center justify-center p-4 backdrop-blur-sm transition-opacity">
        <div
            class="bg-[#1A1B3A] border border-white/10 rounded-3xl w-full max-w-5xl shadow-2xl modal-animate flex flex-col max-h-[90vh]">

            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/5">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-[#FFBB02]/10 rounded-xl text-[#FFBB02]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 id="modalTitle" class="text-xl font-black tracking-tight text-white">Schedule Maintenance</h2>
                </div>
                <button onclick="closeModal('maintenanceModal')"
                    class="text-white/30 hover:text-white bg-white/5 hover:bg-[#CB3435] rounded-lg p-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body Split -->
            <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col lg:flex-row">

                <!-- Form Side -->
                <div class="w-full lg:w-1/2 p-6 flex flex-col gap-5 border-b lg:border-b-0 lg:border-r border-white/5">

                    <form id="maintenanceForm" class="flex flex-col gap-4">
                        <input type="hidden" id="formId">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Date -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Date
                                    *</label>
                                <input type="date" id="formDate" required
                                    class="bg-[#0D0E2A] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#FFBB02]/50 text-white w-full">
                            </div>

                            <!-- Radius -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Radius (km)
                                    *</label>
                                <input type="number" step="0.1" id="formRadius" required placeholder="e.g. 5"
                                    class="bg-[#0D0E2A] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#FFBB02]/50 text-white w-full placeholder:text-white/20">
                            </div>

                            <!-- Start Time -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Start Time
                                    *</label>
                                <input type="time" id="formStart" required
                                    class="bg-[#0D0E2A] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#FFBB02]/50 text-white w-full">
                            </div>

                            <!-- End Time -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">End Time
                                    *</label>
                                <input type="time" id="formEnd" required
                                    class="bg-[#0D0E2A] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#FFBB02]/50 text-white w-full">
                            </div>
                        </div>

                        <!-- Status (Only shown in Edit) -->
                        <div id="statusDiv" class="hidden flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Status</label>
                            <select id="formStatus"
                                class="bg-[#0D0E2A] border border-white/10 text-white text-sm font-semibold rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#FFBB02]/50">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Description
                                *</label>
                            <textarea id="formDesc" rows="3" required placeholder="Details about the maintenance..."
                                class="bg-[#0D0E2A] border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#FFBB02]/50 text-white w-full resize-none placeholder:text-white/20 custom-scrollbar"></textarea>
                        </div>

                        <!-- Selected Barangays Textarea (Readonly) -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Affected
                                Barangays (Auto-fills from Map)</label>
                            <textarea id="formBarangays" rows="2" readonly placeholder="Select areas on the map →"
                                class="bg-[#0D0E2A]/50 border border-white/5 rounded-xl px-4 py-2.5 text-xs text-[#FFBB02] w-full resize-none cursor-not-allowed font-medium"></textarea>
                        </div>

                        <!-- Notify Checkbox -->
                        <label class="flex items-center gap-3 mt-2 cursor-pointer w-max group">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" id="formNotify"
                                    class="peer appearance-none w-5 h-5 border-2 border-white/20 rounded-md bg-[#0D0E2A] checked:bg-[#00BA00] checked:border-[#00BA00] transition-all">
                                <svg class="w-3.5 h-3.5 absolute text-black opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"
                                    fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span
                                class="text-xs font-bold text-white/70 group-hover:text-white transition-colors">Notify
                                affected users via SMS/Email</span>
                        </label>
                    </form>
                </div>

                <!-- Map Side -->
                <div class="w-full lg:w-1/2 p-6 flex flex-col gap-3 bg-[#16172E]/50">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Select Area</span>
                        <button type="button" onclick="clearMapSelection()"
                            class="text-[10px] text-[#CB3435] font-bold uppercase tracking-widest hover:underline">Clear
                            Selection</button>
                    </div>
                    <div class="flex-1 rounded-2xl border border-white/10 overflow-hidden relative min-h-[300px]">
                        <div id="modalMap" class="w-full h-full bg-[#0E0F26] absolute inset-0 z-10"></div>

                        <!-- Mini Legend -->
                        <div
                            class="absolute bottom-3 left-3 bg-[#1A1B3A]/90 backdrop-blur-sm border border-white/10 rounded-xl p-2 z-[400] flex flex-col gap-1 text-[9px] font-bold uppercase tracking-widest">
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-[#FF2E1F] shadow-[0_0_8px_#FF2E1F]"></span>
                                Selected</span>
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-[#00BA00] shadow-[0_0_8px_#00BA00]"></span>
                                Unselected</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-white/5 flex justify-end gap-3 bg-[#16172E]/80">
                <button onclick="closeModal('maintenanceModal')"
                    class="action-btn px-5 py-2.5 rounded-xl font-bold text-xs bg-white/5 hover:bg-white/10 text-white">Cancel</button>
                <button id="btnSubmit" onclick="saveMaintenance()"
                    class="action-btn bg-[#FFBB02] text-black font-bold text-xs px-6 py-2.5 rounded-xl shadow-lg shadow-[#FFBB02]/20 min-w-[120px] flex items-center justify-center">
                    Save Record
                </button>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div id="deleteModal"
        class="fixed inset-0 bg-black/80 z-[1100] hidden items-center justify-center p-4 backdrop-blur-sm">
        <div
            class="bg-[#1A1B3A] border border-[#CB3435]/20 rounded-3xl w-full max-w-sm shadow-2xl modal-animate p-6 flex flex-col items-center text-center gap-4">
            <div
                class="w-16 h-16 rounded-full bg-[#CB3435]/10 border border-[#CB3435]/20 flex items-center justify-center text-[#CB3435] mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-black text-white">Delete Maintenance?</h3>
            <p class="text-xs text-white/50 font-medium px-2">This action cannot be undone. Are you sure you want to
                permanently delete this record?</p>
            <div class="flex gap-3 w-full mt-4">
                <button onclick="closeModal('deleteModal')"
                    class="flex-1 py-3 rounded-xl font-bold text-xs bg-white/5 hover:bg-white/10 text-white transition-colors">Cancel</button>
                <button id="btnConfirmDelete"
                    class="flex-1 py-3 rounded-xl font-bold text-xs bg-[#CB3435] hover:bg-[#A32223] text-white transition-colors shadow-lg shadow-[#CB3435]/20 flex items-center justify-center">Delete</button>
            </div>
        </div>
    </div>


    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        /* =========================================
           CONFIG & CONSTANTS
        ========================================= */
        const API_BASE = "http://localhost/crowdsourcedAPI/api/maintenance";
        const ENDPOINTS = {
            GET: `${API_BASE}/get.php`,
            UPCOMING: `${API_BASE}/get_upcoming.php`,
            COMPLETED: `${API_BASE}/get_complete.php`,
            CREATE: `${API_BASE}/create.php`,
            UPDATE: `${API_BASE}/update.php`,
            DELETE: `${API_BASE}/delete.php`
        };

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

        /* =========================================
           STATE
        ========================================= */
        let maintenanceRecords = [];
        let filteredRecords = [];
        let currentPage = 1;
        const rowsPerPage = 5;

        // Form State
        let selectedBarangays = new Set();
        let isEditing = false;
        let deleteTargetId = null;

        // Maps
        let mainMap, modalMap;
        let mainLayerGroup, modalLayerGroup;

        /* =========================================
           INITIALIZATION
        ========================================= */
        document.addEventListener("DOMContentLoaded", () => {
            initMaps();
            fetchData();
            setInterval(fetchData, 60000); // refresh every 60 seconds
        });

        function toggleMobileSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('overlay').classList.toggle('hidden');
        }

        /* =========================================
           MAP SETUP
        ========================================= */
        function initMaps() {
            // Main Dashboard Map
            mainMap = L.map('mainMap', { zoomControl: false }).setView([16.045, 120.335], 13);
            L.control.zoom({ position: 'bottomright' }).addTo(mainMap);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(mainMap);
            mainLayerGroup = L.layerGroup().addTo(mainMap);

            // Modal Form Map
            modalMap = L.map('modalMap', { zoomControl: false }).setView([16.045, 120.335], 13);
            L.control.zoom({ position: 'bottomright' }).addTo(modalMap);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(modalMap);
            modalLayerGroup = L.layerGroup().addTo(modalMap);
        }

        /* =========================================
           DATA FETCHING
        ========================================= */
        async function fetchData() {
            try {
                updateSyncTime();

                // Fetch Core Data & Counts
                const [resAll, resUp, resComp] = await Promise.all([
                    fetch(ENDPOINTS.GET, { credentials: "include" }),
                    fetch(ENDPOINTS.UPCOMING, { credentials: "include" }),
                    fetch(ENDPOINTS.COMPLETED, { credentials: "include" })
                ]);

                const dataAll = await resAll.json();
                const dataUp = await resUp.json();
                const dataComp = await resComp.json();

                if (dataAll.success) {
                    maintenanceRecords = dataAll.data || [];
                    maintenanceRecords = maintenanceRecords.map(r => {
                        const computedStatus = computeMaintenanceStatus(r);

                        return {
                            ...r,
                            status: computedStatus // override DB status dynamically
                        };
                    });
                    // Parse barangays carefully
                    maintenanceRecords.forEach(r => {
                        try {
                            // Check for new backend response format or fallback to string parsing
                            if (r.barangays && Array.isArray(r.barangays)) {
                                r.barangayList = r.barangays;
                            } else if (typeof r.affected_barangays === 'string') {
                                r.barangayList = JSON.parse(r.affected_barangays);
                            } else {
                                r.barangayList = Array.isArray(r.affected_barangays) ? r.affected_barangays : [r.affected_barangays];
                            }
                        } catch (e) {
                            r.barangayList = [r.affected_barangays];
                        }
                    });
                }

                function computeMaintenanceStatus(record) {
                    const now = new Date();

                    const startDateTime = new Date(`${record.maintenance_date} ${record.start_time}`);
                    const endDateTime = new Date(`${record.maintenance_date} ${record.end_time}`);

                    if (now < startDateTime) {
                        return "upcoming";
                    } else if (now >= startDateTime && now <= endDateTime) {
                        return "ongoing";
                    } else {
                        return "completed";
                    }
                }

                // Cards Calculation
                const total = maintenanceRecords.length;
                let ongoing = maintenanceRecords.filter(r => (r.status || "").toLowerCase() === 'ongoing').length;

                // Use explicit API endpoints for upcoming and completed counts if possible
                let upcoming = dataUp.success
                    ? dataUp.upcoming_count || 0
                    : 0;

                let completed = dataComp.success
                    ? (Array.isArray(dataComp.data) ? dataComp.data.length : 0)
                    : 0;

                updateDashboardCounts(total, upcoming, ongoing, completed);

                handleFilterSearch(); // Filters and renders table + maps

            } catch (err) {
                console.error("Fetch Error:", err);
                showToast("Failed to fetch maintenance data", "error");
                document.getElementById('sync-time').innerText = "Sync Failed";
                document.getElementById('sync-time').classList.add("text-[#CB3435]");
            }
        }

        function updateDashboardCounts(total, upcoming, ongoing, completed) {
            document.getElementById('stat-total').innerText = total;
            document.getElementById('stat-upcoming').innerText = upcoming;
            document.getElementById('stat-ongoing').innerText = ongoing;
            document.getElementById('stat-completed').innerText = completed;
        }

        function updateSyncTime() {
            const t = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const el = document.getElementById('sync-time');
            el.innerText = t;
            el.classList.remove("text-[#CB3435]");
        }

        /* =========================================
           FILTERS & TABLE
        ========================================= */
        function handleFilterSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const statusFilt = document.getElementById('statusFilter').value.toLowerCase();

            filteredRecords = maintenanceRecords.filter(r => {
                const matchStatus = statusFilt === 'all' || (r.status || "").toLowerCase() === statusFilt;
                const searchStr = `${r.company_name || ''} ${r.description || ''} ${(r.barangayList || []).join(' ')}`.toLowerCase();
                const matchSearch = searchStr.includes(query);
                return matchStatus && matchSearch;
            });

            currentPage = 1;
            renderTable();
            renderMainMapMarkers();
        }

        function renderTable() {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = "";

            const total = filteredRecords.length;
            const start = (currentPage - 1) * rowsPerPage;
            const paginated = filteredRecords.slice(start, start + rowsPerPage);

            document.getElementById('pageInfo').innerText = total === 0 ? "No records found" : `Showing ${start + 1} to ${Math.min(start + rowsPerPage, total)} of ${total}`;
            renderPagination(total);

            if (paginated.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-white/40 italic">No maintenance records match your filters.</td></tr>`;
                return;
            }

            paginated.forEach(r => {
                const status = (r.status || "upcoming").toLowerCase();
                let statusColor = "bg-[#FFBB02]/10 text-[#FFBB02] border-[#FFBB02]/20"; // default upcoming
                if (status === 'ongoing') statusColor = "bg-[#4FC3F7]/10 text-[#4FC3F7] border-[#4FC3F7]/20";
                if (status === 'completed') statusColor = "bg-[#00BA00]/10 text-[#00BA00] border-[#00BA00]/20";
                if (status === 'cancelled') statusColor = "bg-[#CB3435]/10 text-[#CB3435] border-[#CB3435]/20";

                const dateStr = new Date(r.maintenance_date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                const bList = (r.barangayList || []).join(', ');
                const bDisplay = bList.length > 25 ? bList.substring(0, 25) + '...' : bList;

                const tr = document.createElement('tr');
                tr.className = "hover:bg-white/5 transition-colors cursor-pointer group";
                tr.onclick = (e) => {
                    if (!e.target.closest('button')) zoomToBarangays(r.barangayList);
                };

                tr.innerHTML = `
                    <td class="px-5 py-4 font-bold text-white/50">#${r.id}</td>
                    <td class="px-5 py-4">
                        <div class="flex flex-col gap-0.5">
                            <span class="font-bold text-white">${dateStr}</span>
                            <span class="text-[10px] font-medium text-white/40">${r.start_time} - ${r.end_time}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-col gap-0.5">
                            <span class="font-semibold text-white/90" title="${escapeHTML(bList)}">${escapeHTML(bDisplay)}</span>
                            <span class="text-[10px] text-white/40 max-w-[200px] truncate">${escapeHTML(r.description || '')}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 font-medium">${r.radius ? r.radius + ' km' : '—'}</td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded-lg border ${statusColor}">
                            ${escapeHTML(status)}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="openEditModal(${r.id})" class="p-1.5 bg-[#4FC3F7]/10 hover:bg-[#4FC3F7] text-[#4FC3F7] hover:text-[#03041A] rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </button>
                            <button onclick="confirmDelete(${r.id})" class="p-1.5 bg-[#CB3435]/10 hover:bg-[#CB3435] text-[#CB3435] hover:text-white rounded-lg transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderPagination(total) {
            const pBox = document.getElementById('pagination');
            pBox.innerHTML = "";
            const totalPages = Math.ceil(total / rowsPerPage);
            if (totalPages <= 1) return;

            const btnClass = "w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold transition-colors ";

            const prev = document.createElement('button');
            prev.innerHTML = "&#8592;";
            prev.className = btnClass + (currentPage > 1 ? "bg-white/10 hover:bg-white/20 text-white" : "bg-white/5 text-white/20 cursor-not-allowed");
            prev.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
            pBox.appendChild(prev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                btn.className = btnClass + (i === currentPage ? "bg-[#FFBB02] text-black shadow-lg shadow-[#FFBB02]/20" : "bg-white/5 hover:bg-white/10 text-white/60 hover:text-white");
                btn.onclick = () => { currentPage = i; renderTable(); };
                pBox.appendChild(btn);
            }

            const next = document.createElement('button');
            next.innerHTML = "&#8594;";
            next.className = btnClass + (currentPage < totalPages ? "bg-white/10 hover:bg-white/20 text-white" : "bg-white/5 text-white/20 cursor-not-allowed");
            next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
            pBox.appendChild(next);
        }

        /* =========================================
           MAP LOGIC
        ========================================= */
        function getBarangayMaintenanceStatus(name) {
            let status = 'normal';

            for (const r of maintenanceRecords) {
                if (!(r.barangayList || []).includes(name)) continue;

                const rStat = (r.status || "").toLowerCase();
                if (rStat === 'ongoing') return 'ongoing';
                if (rStat === 'upcoming' && status !== 'ongoing') status = 'upcoming';
                if (rStat === 'completed' && status === 'normal') status = 'completed';
            }
            return status;
        }

        function renderMainMapMarkers() {
            mainLayerGroup.clearLayers();
            for (const [name, coords] of Object.entries(barangayData)) {

                const mStatus = getBarangayMaintenanceStatus(name);
                let color = "#00BA00"; // normal
                if (mStatus === 'ongoing') color = "#4FC3F7";
                if (mStatus === 'upcoming') color = "#FFBB02";
                if (mStatus === 'completed') color = "#6B7280"; // Gray

                const marker = L.circleMarker([coords.lat, coords.lng], {
                    radius: 8,
                    fillColor: color,
                    color: "#ffffff",
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: 0.8
                });

                marker.bindPopup(`<b class="text-xs uppercase tracking-widest">${name}</b><br><span class="text-[10px] text-gray-600">Status: ${mStatus}</span>`);
                mainLayerGroup.addLayer(marker);
            }
        }

        function renderModalMapMarkers() {
            modalLayerGroup.clearLayers();
            for (const [name, coords] of Object.entries(barangayData)) {

                const isSelected = selectedBarangays.has(name);
                const color = isSelected ? "#FF2E1F" : "#00BA00";

                const marker = L.circleMarker([coords.lat, coords.lng], {
                    radius: 8,
                    fillColor: color,
                    color: "#ffffff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: isSelected ? 1 : 0.6
                });

                marker.on('click', () => toggleBarangaySelection(name));
                marker.bindTooltip(name, { direction: 'top', className: 'text-[10px] font-bold bg-[#1A1B3A] text-white border-none' });

                modalLayerGroup.addLayer(marker);
            }
        }

        function toggleBarangaySelection(name) {
            if (selectedBarangays.has(name)) {
                selectedBarangays.delete(name);
            } else {
                selectedBarangays.add(name);
            }
            renderModalMapMarkers();
            updateFormBarangaysText();
        }

        function clearMapSelection() {
            selectedBarangays.clear();
            renderModalMapMarkers();
            updateFormBarangaysText();
        }

        function updateFormBarangaysText() {
            const txt = document.getElementById('formBarangays');
            if (selectedBarangays.size === 0) {
                txt.value = "";
            } else {
                txt.value = Array.from(selectedBarangays).join(', ');
            }
        }

        function zoomToBarangays(bList) {
            if (!bList || bList.length === 0) return;
            const bounds = L.latLngBounds([]);
            let hasValid = false;
            bList.forEach(b => {
                const coords = barangayData[b];
                if (coords) {
                    bounds.extend([coords.lat, coords.lng]);
                    hasValid = true;
                }
            });
            if (hasValid) {
                mainMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }
        }

        /* =========================================
           MODALS
        ========================================= */
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.getElementById(id).classList.add('flex');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.getElementById(id).classList.remove('flex');
        }

        function openCreateModal() {
            isEditing = false;
            document.getElementById('modalTitle').innerText = "Schedule Maintenance";
            document.getElementById('maintenanceForm').reset();
            document.getElementById('formId').value = "";
            document.getElementById('statusDiv').classList.add('hidden');

            selectedBarangays.clear();
            updateFormBarangaysText();

            openModal('maintenanceModal');

            setTimeout(() => {
                modalMap.invalidateSize();
                renderModalMapMarkers();
                modalMap.setView([16.045, 120.335], 13);
            }, 50);
        }

        function openEditModal(id) {
            const r = maintenanceRecords.find(x => x.id == id);
            if (!r) return;

            isEditing = true;
            document.getElementById('modalTitle').innerText = `Edit Record #${id}`;
            document.getElementById('formId').value = id;
            document.getElementById('formDate').value = r.maintenance_date;
            document.getElementById('formStart').value = r.start_time;
            document.getElementById('formEnd').value = r.end_time;
            document.getElementById('formRadius').value = r.radius;
            document.getElementById('formDesc').value = r.description;
            document.getElementById('formNotify').checked = r.notify_all == 1;

            document.getElementById('statusDiv').classList.remove('hidden');
            document.getElementById('formStatus').value = (r.status || 'upcoming').toLowerCase();

            selectedBarangays = new Set(r.barangayList);
            updateFormBarangaysText();

            openModal('maintenanceModal');

            setTimeout(() => {
                modalMap.invalidateSize();
                renderModalMapMarkers();
                const bounds = L.latLngBounds([]);
                (r.barangayList || []).forEach(b => {
                    if (barangayData[b]) bounds.extend([barangayData[b].lat, barangayData[b].lng]);
                });
                if (bounds.isValid()) modalMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
            }, 50);
        }

        function confirmDelete(id) {
            deleteTargetId = id;
            openModal('deleteModal');
        }


        /* =========================================
           CRUD ACTIONS (FETCH)
        ========================================= */
        async function saveMaintenance() {
            const form = document.getElementById('maintenanceForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (selectedBarangays.size === 0) {
                showToast("Please select at least one barangay on the map", "error");
                return;
            }

            const btn = document.getElementById('btnSubmit');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner w-4 h-4 border-2"></span>`;
            btn.disabled = true;

            // FIX: Sending barangays as an actual Array, not JSON stringified
            const payload = {
                maintenance_date: document.getElementById('formDate').value,
                start_time: document.getElementById('formStart').value,
                end_time: document.getElementById('formEnd').value,
                radius: document.getElementById('formRadius').value,
                description: document.getElementById('formDesc').value,
                barangays: Array.from(selectedBarangays),
                notify_all: document.getElementById('formNotify').checked ? 1 : 0
            };

            let endpoint = ENDPOINTS.CREATE;

            if (isEditing) {
                endpoint = ENDPOINTS.UPDATE;
                payload.maintenance_id = document.getElementById('formId').value;
                payload.status = document.getElementById('formStatus').value;
            }

            try {
                const res = await fetch(endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.success) {
                    showToast(`Maintenance ${isEditing ? 'updated' : 'scheduled'} successfully!`);
                    closeModal('maintenanceModal');
                    await fetchData();
                } else {
                    showToast(result.message || "Operation failed", "error");
                }
            } catch (err) {
                showToast("Server error during save", "error");
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', async function () {
            if (!deleteTargetId) return;
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner w-4 h-4 border-2 border-t-white"></span>`;
            btn.disabled = true;

            try {
                const res = await fetch(ENDPOINTS.DELETE, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({ maintenance_id: deleteTargetId })
                });
                const result = await res.json();

                if (result.success) {
                    showToast("Maintenance record deleted");
                    maintenanceRecords = maintenanceRecords.filter(r => r.id != deleteTargetId);
                    handleFilterSearch();
                    fetchData();
                    closeModal('deleteModal');
                } else {
                    showToast(result.message || "Failed to delete", "error");
                }
            } catch (e) {
                showToast("Server error during deletion", "error");
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
                deleteTargetId = null;
            }
        });

        /* =========================================
           UTILITIES
        ========================================= */
        function escapeHTML(str) {
            return String(str ?? "")
                .replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function showToast(msg, type = "success") {
            const existing = document.getElementById('toast');
            if (existing) existing.remove();

            const isError = type === "error";
            const colorClass = isError ? "bg-[#CB3435]/10 border-[#CB3435]/30 text-[#CB3435]" : "bg-[#00BA00]/10 border-[#00BA00]/30 text-[#00BA00]";
            const iconPath = isError ? "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" : "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z";

            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = `fixed bottom-6 right-6 z-[2000] flex items-center gap-3 px-5 py-3.5 rounded-2xl border text-sm font-bold backdrop-blur-md shadow-2xl modal-animate ${colorClass}`;
            toast.innerHTML = `
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="${iconPath}"/>
                </svg>
                ${escapeHTML(msg)}
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    </script>
</body>

</html>