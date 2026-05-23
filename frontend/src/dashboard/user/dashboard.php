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
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerGuide Dashboard</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap"
        rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        /* Recreating custom utility hover glows without local output.css */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
        }

        .glow-voltage:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 207, 255, 0.2);
        }

        .glow-uptime:hover {
            box-shadow: 0 10px 25px -5px rgba(250, 176, 5, 0.2);
        }

        .glow-reports:hover {
            box-shadow: 0 10px 25px -5px rgba(203, 52, 53, 0.2);
        }

        .glow-readiness:hover {
            box-shadow: 0 10px 25px -5px rgba(95, 203, 95, 0.2);
        }

        /* Custom scrollbar style for panels */
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

        /* Leaflet custom dark map adjustment to match the aesthetic theme */
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
    <button id="menuToggle"
        class="fixed top-4 left-4 z-50 lg:hidden bg-[#31324C] p-2 rounded-lg hover:bg-opacity-80 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black/60 z-30 hidden transition-opacity duration-300"></div>

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
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
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

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto custom-scrollbar bg-[#03041A]">

            <!-- HEADER -->
            <header
                class="mx-4 lg:mx-8 mt-14 lg:mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Welcome Back,
                        <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!
                    </h1>
                    <!-- <span class="text-xs lg:text-sm text-[#B5B5B5] flex items-center gap-2 mt-1">
                        Status Context: -->
                        <span class="flex items-center gap-1.5 text-[#00BA00] font-medium" id="status">
                            <!-- Initializing infrastructure links... -->
                        </span>
                    <!-- </span> -->
                </div>

                <div class="flex items-center gap-4 self-end sm:self-auto">
                    <!-- Bell Button Component -->
                    <div class="relative">
                        <button id="notifBtn" onclick="toggleNotifications()"
                            class="relative p-2 bg-[#31324C]/40 hover:bg-[#31324C]/70 rounded-xl transition-all group">
                            <svg class="w-6 h-6 text-[#FFBB02] group-hover:scale-110 transition-transform" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Alert badge -->
                            <span id="notifCount"
                                class="absolute -top-1 -right-1 h-5 min-w-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center border border-[#16172E] hidden">0</span>
                        </button>

                        <!-- Notification Panel Modal Popup -->
                        <div id="notifPanel"
                            class="absolute right-0 mt-3 w-[320px] sm:w-[360px] bg-[#16172E] border border-white/10 rounded-2xl shadow-2xl z-50 hidden overflow-hidden">
                            <div class="p-4 flex flex-col">
                                <div
                                    class="flex flex-row justify-between items-center mb-3 pb-2 border-b border-white/5">
                                    <div class="flex flex-row items-center gap-2">
                                        <svg class="w-4 h-4 text-[#B5B5B5]" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <h1 class="text-xs font-bold text-white uppercase tracking-wider">Live System
                                            Feed</h1>
                                    </div>
                                    <button onclick="markAllAsRead()"
                                        class="text-[10px] text-[#FFBB02] hover:underline font-bold tracking-wide uppercase bg-[#31324C]/30 px-2 py-1 rounded">Mark
                                        All</button>
                                </div>

                                <div id="notifFeed"
                                    class="flex flex-col gap-2.5 max-h-80 overflow-y-auto custom-scrollbar">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- STAT CARDS -->
            <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 px-4 lg:px-8">
                <!-- Stations Card -->
                <div
                    class="card-hover glow-voltage bg-[#31324C]/30 border border-white/5 rounded-2xl p-5 flex flex-col gap-4 min-h-[160px] cursor-pointer">
                    <div class="flex justify-between items-center">
                        <div class="bg-[#1A1B3A] p-2.5 rounded-xl text-[#00CFFF]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="text-[#00CFFF] text-[11px] font-bold tracking-widest">AVAILABLE STATIONS</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span id="totalStations" class="text-white text-3xl font-black tracking-tight">0</span>
                        <span class="text-[#B5B5B5] text-xs font-medium">Active power hubs tracked</span>
                    </div>
                </div>

                <!-- Active Outages Card -->
                <div
                    class="card-hover glow-reports bg-[#31324C]/30 border border-white/5 rounded-2xl p-5 flex flex-col gap-4 min-h-[160px] cursor-pointer">
                    <div class="flex justify-between items-center">
                        <div class="bg-[#1A1B3A] p-2.5 rounded-xl text-[#CB3435]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-[#CB3435] text-[11px] font-bold tracking-widest">ACTIVE OUTAGES</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span id="activeOutages" class="text-white text-3xl font-black tracking-tight">0</span>
                        <span class="text-[#B5B5B5] text-xs font-medium">Critical system network faults</span>
                    </div>
                </div>

                <!-- Maintenance Card -->
                <div
                    class="card-hover glow-uptime bg-[#31324C]/30 border border-white/5 rounded-2xl p-5 flex flex-col gap-4 min-h-[160px] cursor-pointer">
                    <div class="flex justify-between items-center">
                        <div class="bg-[#1A1B3A] p-2.5 rounded-xl text-[#FAB005]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="text-[#FAB005] text-[11px] font-bold tracking-widest">UPCOMING MAINTENANCE</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span id="maintenanceCount" class="text-white text-3xl font-black tracking-tight">0</span>
                        <span class="text-[#B5B5B5] text-xs font-medium">Scheduled grid updates</span>
                    </div>
                </div>

                <!-- Notifications Card -->
                <div
                    class="card-hover glow-readiness bg-[#31324C]/30 border border-white/5 rounded-2xl p-5 flex flex-col gap-4 min-h-[160px] cursor-pointer">
                    <div class="flex justify-between items-center">
                        <div class="bg-[#1A1B3A] p-2.5 rounded-xl text-[#5FCB5F]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <span class="text-[#5FCB5F] text-[11px] font-bold tracking-widest">UNREAD NOTIFS</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span id="notifTotal" class="text-white text-3xl font-black tracking-tight">0</span>
                        <span class="text-[#B5B5B5] text-xs font-medium">Broadcast alerts requiring review</span>
                    </div>
                </div>
            </section>

            <!-- CENTER SECTION -->
            <section class="flex flex-col gap-6 px-4 lg:px-8 mt-6 mb-8">

                <!-- TOP ROW: Map (Left) & Battery Status (Right) -->
                <div class="flex flex-col xl:flex-row gap-6">

                    <!-- LEFT: Map Container -->
                    <div class="flex flex-col flex-1 min-w-0">
                        <div
                            class="rounded-2xl border border-white/5 overflow-hidden shadow-xl bg-[#31324C]/20 flex flex-col h-full">
                            <div
                                class="flex flex-row justify-between items-center p-5 border-b border-white/5 bg-[#16172E]/40">
                                <div class="flex flex-row items-center gap-2.5">
                                    <div class="text-[#FFBB02]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-sm lg:text-base">Nearby Charging Hubs Map</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-[#00BA00] px-2.5 py-1 bg-[#00BA00]/10 text-xs rounded-lg font-bold border border-[#00BA00]/20 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-[#00BA00] rounded-full animate-pulse"></span> LIVE
                                        LOOP
                                    </span>
                                </div>
                            </div>

                            <!-- Map Element Block -->
                            <div id="map" class="w-full h-80 lg:h-[430px] z-10 bg-[#0E0F26]"></div>
                        </div>
                    </div>

                    <!-- RIGHT: Battery Detection Card with Circular Progress -->
                    <div class="flex flex-col xl:w-[380px] flex-shrink-0">
                        <div class="card-hover bg-[#31324C]/20 border border-white/5 rounded-2xl p-6 flex flex-col justify-between shadow-xl min-h-[440px] xl:h-full relative transition-all duration-300 overflow-hidden"
                            id="batteryCard">

                            <!-- Card Header -->
                            <div class="flex justify-between items-center">
                                <span class="text-white text-xs font-bold uppercase tracking-widest opacity-60">System
                                    Power</span>
                                <span id="batteryBadge"
                                    class="text-[11px] font-extrabold tracking-widest text-[#FFBB02] bg-white/5 px-3 py-1.5 rounded-lg border border-white/5 transition-colors duration-300">
                                    DETECTING STATUS
                                </span>
                            </div>

                            <!-- CENTER: Circular Progress Ring Visualizer -->
                            <div class="flex justify-center items-center my-auto py-4">
                                <div class="relative w-44 h-44">
                                    <!-- SVG Ring Layer -->
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                                        <!-- Background Track Circle -->
                                        <circle cx="60" cy="60" r="50" class="stroke-white/5" stroke-width="8"
                                            fill="transparent" />
                                        <!-- Active Dynamic Fill Circle -->
                                        <circle id="batteryProgressCircle" cx="60" cy="60" r="50" stroke="#FFBB02"
                                            stroke-width="8" fill="transparent" stroke-dasharray="314.16"
                                            stroke-dashoffset="314.16" stroke-linecap="round"
                                            class="transition-all duration-500 ease-out" />
                                    </svg>

                                    <!-- Center Label Text Container -->
                                    <div class="absolute inset-0 flex flex-col justify-center items-center">
                                        <span id="batteryPercentage"
                                            class="text-white text-4xl font-black tracking-tighter">--%</span>
                                        <span id="chargingStateText"
                                            class="text-[10px] font-bold uppercase text-[#00BA00] tracking-wider mt-0.5 min-h-[15px]"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Info Texts -->
                            <div class="flex items-start gap-3 border-t border-white/5 pt-4">
                                <div id="batteryIconWrapper"
                                    class="bg-[#FFBB02]/10 text-[#FFBB02] p-2 rounded-lg transition-colors duration-300 flex-shrink-0">
                                    <svg id="batteryIcon" class="w-5 h-5" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h16v4H3z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <span id="batteryStatusDesc"
                                        class="text-[#B5B5B5] text-xs font-medium leading-relaxed transition-colors duration-300">
                                        Initializing system telemetry links...
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- BOTTOM ROW: Wide Charging Hub Lists Feed Container -->
                <div class="w-full">
                    <div
                        class="rounded-2xl border border-white/5 bg-[#31324C]/20 flex flex-col p-6 shadow-xl min-h-[300px]">
                        <span class="text-white text-xs font-bold uppercase tracking-widest opacity-60 mb-4">Nearby
                            Power Stations</span>

                        <!-- Dynamic Station Feed Render Element Container (Wide) -->
                        <div id="list"
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 flex-1 overflow-y-auto custom-scrollbar pr-1 max-h-[400px]">
                            <!-- Station feed cards inject dynamically here -->
                        </div>

                        <!-- Pagination Navigation Element -->
                        <div id="pagination"
                            class="flex flex-row gap-1 justify-center items-center mt-5 pt-4 border-t border-white/5">
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <!-- RESPONSIVE NAV SCRIPTS + BUSINESS DATA LAYER IMPLEMENTATION -->
    <script>
        /* MOBILE MENU MANAGEMENT NAVIGATION INTERACTIVE CONTROLS */
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMobileSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        menuToggle.addEventListener('click', toggleMobileSidebar);
        overlay.addEventListener('click', toggleMobileSidebar);

        /* =========================
           GLOBAL STATES & ARRAYS
        ========================= */
        let map;
        let stationLayer;
        let userMarker = null;
        let stationsData = [];
        let currentPage = 1;
        const perPage = 3;
        let notifications = [];

        /* =========================
           MAP INITIALIZATION
        ========================= */
        function initMap() {
            map = L.map('map').setView([16.0431, 120.3330], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: "© OpenStreetMap"
            }).addTo(map);

            stationLayer = L.layerGroup().addTo(map);
        }

        /* =========================
           CUSTOM LEAFLET MAP ICONS
        ========================= */
        const stationIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/252/252025.png',
            iconSize: [35, 35],
            iconAnchor: [17, 35]
        });

        const userIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/64/64113.png',
            iconSize: [35, 35],
            iconAnchor: [17, 35]
        });

        /* =========================
           GEOLOCATION PIPELINE
        ========================= */
        async function loadUserLocation() {
            if (!navigator.geolocation) {
                document.getElementById("status").innerText = "Geolocation not supported";
                loadStations(16.0431, 120.3330);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const userLat = pos.coords.latitude;
                    const userLng = pos.coords.longitude;

                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }

                    userMarker = L.marker([userLat, userLng], { icon: userIcon })
                        .addTo(map)
                        .bindPopup("📍 Your Current Location");

                    loadStations(userLat, userLng);
                },
                (err) => {
                    console.error(err);
                    document.getElementById("status").innerText = "Failed to get your location";
                    loadStations(16.0431, 120.3330);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        /* =========================
           DISTANCE CALCULATOR (HAVERSINE FORMULA)
        ========================= */
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) *
                Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return (R * c).toFixed(2);
        }

        /* =========================
           DASHBOARD METRICS INITIALIZATION
        ========================= */
        async function loadDashboard() {
            try {
                /* ================= ACTIVE OUTAGES ================= */
                const active = await fetch("http://localhost/crowdsourcedapi/api/outage_report/get_active.php", { credentials: "include" });
                const activeData = await active.json();
                const activeEl = document.getElementById("activeOutages");
                if (activeEl) {
                    activeEl.innerText = activeData.count ?? activeData.total ?? activeData.total_active_reports ?? (Array.isArray(activeData.data) ? activeData.data.length : 0);
                }

                /* ================= MAINTENANCE ================= */
                const maintenance = await fetch("http://localhost/crowdsourcedapi/api/maintenance/get_upcoming.php", { credentials: "include" });
                const mData = await maintenance.json();
                const maintenanceEl = document.getElementById("maintenanceCount");
                if (maintenanceEl) {
                    maintenanceEl.innerText = mData.count ?? mData.upcoming_count ?? (Array.isArray(mData.data) ? mData.data.length : 0);
                }
            } catch (e) {
                console.error("Dashboard Metrics Parse Error:", e);
            }
        }

        /* =========================
           STATIONS LOADING & GEOGRAPHIC MANAGEMENT
        ========================= */
        async function loadStations(userLat, userLng) {
            try {
                const res = await fetch("http://localhost/crowdsourcedapi/api/power_station/get.php", { credentials: "include" });
                const result = await res.json();

                if (!result.success) {
                    document.getElementById("status").innerText = "Failed to load stations";
                    return;
                }

                let stations = result.data || [];

                // Calculate distances vectors dynamically
                stations = stations.map(station => {
                    const lat = parseFloat(station.latitude);
                    const lng = parseFloat(station.longitude);
                    let distance = null;
                    if (!isNaN(lat) && !isNaN(lng)) {
                        distance = calculateDistance(userLat, userLng, lat, lng);
                    }
                    return { ...station, distance };
                });

                // Sort configurations nearest first
                stations.sort((a, b) => (a.distance || 9999) - (b.distance || 9999));
                stationsData = stations;

                // Sync counter layout badges
                document.getElementById("totalStations").innerText = stationsData.length;
                document.getElementById("status").innerText = ``;

                renderStationsPage();
                renderStationsPagination();
                renderStationMapMarkers(userLat, userLng);

            } catch (err) {
                console.error("Load error:", err);
                document.getElementById("status").innerText = "Server error";
            }
        }

        /* =========================
           MAP MARKERS UPDATE LAYER LOOP
        ========================= */
        function renderStationMapMarkers(userLat, userLng) {
            stationLayer.clearLayers();
            let bounds = [];

            stationsData.forEach(s => {
                const lat = parseFloat(s.latitude);
                const lng = parseFloat(s.longitude);

                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = L.marker([lat, lng], { icon: stationIcon });
                    marker.bindPopup(`
                        <div class="text-black text-xs p-1">
                            <b class="text-sm border-b pb-1 mb-1 block">${s.station_name}</b>
                            <b>Type:</b> ${s.station_type}<br>
                            <b>Status:</b> ${s.availability_status}<br>
                            <b>Access:</b> ${s.access_type}<br>
                            <b>Location:</b> ${s.location_name}
                        </div>
                    `);
                    stationLayer.addLayer(marker);
                    bounds.push([lat, lng]);
                }
            });

            if (userMarker) bounds.push([userLat, userLng]);

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        /* =========================
           STATION LIST FEED RENDERING CARD TILES
        ========================= */
        function renderStationsPage() {
            const list = document.getElementById("list");
            list.innerHTML = "";

            const start = (currentPage - 1) * perPage;
            const pageData = stationsData.slice(start, start + perPage);

            if (pageData.length === 0) {
                list.innerHTML = `<div class="text-xs text-white/40 font-medium text-center py-8 col-span-full">No active station logs synced.</div>`;
                return;
            }

            pageData.forEach(s => {
                const card = document.createElement("div");
                card.className = "bg-[#0D0E2A]/70 border border-white/5 rounded-xl p-4 flex flex-col gap-2 text-left transition-all hover:border-white/10";

                let statusBadgeColor = "text-[#FAB005] bg-[#FAB005]/10 border-[#FAB005]/20";
                if (s.availability_status && s.availability_status.toLowerCase().includes('available')) {
                    statusBadgeColor = "text-[#00BA00] bg-[#00BA00]/10 border-[#00BA00]/20";
                } else if (s.availability_status && s.availability_status.toLowerCase().includes('offline')) {
                    statusBadgeColor = "text-[#CB3435] bg-[#CB3435]/10 border-[#CB3435]/20";
                }

                card.innerHTML = `
                    <div class="flex justify-between items-start gap-2">
                        <span class="text-white font-bold text-sm truncate max-w-[190px]">${s.station_name}</span>
                        <span class="px-2 py-0.5 border text-[9px] font-bold rounded-md ${statusBadgeColor} uppercase tracking-wide">${s.availability_status}</span>
                    </div>
                    <div class="text-[#B5B5B5] text-xs flex flex-col gap-0.5">
                        <span class="text-white/80 font-medium truncate">${s.location_name}</span>
                        <div class="flex justify-between mt-1 text-[11px] opacity-60 font-semibold uppercase">
                            <span>Type: ${s.station_type}</span>
                        </div>
                    </div>
                    ${s.description ? `<p class="text-[#B5B5B5]/70 text-[11px] line-clamp-2 border-t border-white/5 pt-1.5 mt-1">${s.description}</p>` : ''}
                `;
                list.appendChild(card);
            });
        }

        /* STATION PAGINATION CONTROLS MODULATION */
        function renderStationsPagination() {
            const p = document.getElementById("pagination");
            p.innerHTML = "";

            const pages = Math.ceil(stationsData.length / perPage);
            if (pages <= 1) return;

            for (let i = 1; i <= pages; i++) {
                const btn = document.createElement("button");
                btn.innerText = i;
                btn.className = `h-7 w-7 flex items-center justify-center rounded-lg font-bold text-[11px] transition-all duration-150 ${i === currentPage
                    ? "bg-[#FFBB02] text-black shadow-md shadow-[#FFBB02]/10"
                    : "bg-[#31324C]/40 text-[#B5B5B5] hover:bg-[#31324C]/80 hover:text-white"
                    }`;

                btn.onclick = () => {
                    currentPage = i;
                    renderStationsPage();
                    renderStationsPagination();
                };
                p.appendChild(btn);
            }
        }

        /* =========================
           BATTERY DETECTION INFRASTRUCTURE
        ========================= */
        function initDashboardBatteryRing() {
            const pctLabel = document.getElementById("batteryPercentage");
            const badgeLabel = document.getElementById("batteryBadge");
            const descLabel = document.getElementById("batteryStatusDesc");
            const chargingLabel = document.getElementById("chargingStateText");
            const iconWrapper = document.getElementById("batteryIconWrapper");
            const cardElement = document.getElementById("batteryCard");
            const iconSvg = document.getElementById("batteryIcon");
            const progressCircle = document.getElementById("batteryProgressCircle");

            const ringCircumference = 314.15926;

            if (!navigator.getBattery) {
                pctLabel.innerText = "N/A";
                badgeLabel.innerText = "UNSUPPORTED";
                descLabel.innerText = "Hardware API interface missing from this browser.";
                return;
            }

            navigator.getBattery().then((battery) => {
                function runSystemUpdate() {
                    const currentLevel = Math.round(battery.level * 100);
                    pctLabel.innerText = `${currentLevel}%`;

                    const offsetValue = ringCircumference - (battery.level * ringCircumference);
                    progressCircle.style.strokeDashoffset = offsetValue;
                    chargingLabel.innerText = battery.charging ? "Charging" : "";

                    cardElement.style.boxShadow = "";
                    cardElement.style.borderColor = "rgba(255,255,255,0.05)";

                    if (battery.charging && currentLevel >= 95) {
                        badgeLabel.innerText = "FULLY CHARGED";
                        badgeLabel.style.color = "#5FCB5F";
                        descLabel.innerText = "AC wall power online. Station infrastructure holding max cell reserves.";
                        progressCircle.style.stroke = "#5FCB5F";
                        progressCircle.style.filter = "drop-shadow(0 0 4px rgba(95, 203, 95, 0.4))";
                        iconWrapper.className = "p-2 rounded-lg bg-[#5FCB5F]/10 text-[#5FCB5F]";
                        cardElement.style.boxShadow = "0 20px 25px -5px rgba(95, 203, 95, 0.03)";
                        iconSvg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h14a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm16 4h2v2h-2v-2zM6 10h2v4H6v-4zm4 0h2v4h-2v-4zm4 0h2v4h-2v-4z" />`;
                    }
                    else if (currentLevel <= 20) {
                        badgeLabel.innerText = "CRITICAL LOW";
                        badgeLabel.style.color = "#CB3435";
                        descLabel.innerText = battery.charging
                            ? "Low capacity state. Secondary energy link active, restoring levels."
                            : "Severe local power drainage. Plug in immediate system backups.";
                        progressCircle.style.stroke = "#CB3435";
                        progressCircle.style.filter = "drop-shadow(0 0 6px rgba(203, 52, 53, 0.6))";
                        iconWrapper.className = "p-2 rounded-lg bg-[#CB3435]/10 text-[#CB3435]";
                        cardElement.style.borderColor = "rgba(203, 52, 53, 0.2)";
                        cardElement.style.boxShadow = "0 20px 25px -5px rgba(203, 52, 53, 0.08)";
                        iconSvg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h14a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm16 4h2v2h-2v-2zM6 10h1v4H6v-4z" />`;
                    }
                    else {
                        badgeLabel.innerText = battery.charging ? "RECHARGING" : "BATTERY NOMINAL";
                        badgeLabel.style.color = "#FFBB02";
                        descLabel.innerText = battery.charging
                            ? "DC voltage streaming steady. Rebuilding capacity distributions."
                            : "System runtime operating on stable standalone lithium architecture.";
                        progressCircle.style.stroke = "#FFBB02";
                        progressCircle.style.filter = "drop-shadow(0 0 4px rgba(255, 187, 2, 0.4))";
                        iconWrapper.className = "p-2 rounded-lg bg-[#FFBB02]/10 text-[#FFBB02]";
                        cardElement.style.boxShadow = "0 20px 25px -5px rgba(255, 187, 2, 0.03)";
                        iconSvg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h14a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm16 4h2v2h-2v-2zM6 10h5v4H6v-4z" />`;
                    }
                }
                runSystemUpdate();
                battery.addEventListener("levelchange", runSystemUpdate);
                battery.addEventListener("chargingchange", runSystemUpdate);
            });
        }

        /* =========================
           LIVE FEED NOTIFICATIONS
        ========================= */
        async function loadNotif() {
            try {
                const res = await fetch("http://localhost/crowdsourcedapi/api/notification/get.php", { credentials: "include" });
                const data = await res.json();
                notifications = data.data || [];
                renderNotif();
            } catch (e) {
                console.error("Notification connection link lost:", e);
            }
        }

        function renderNotif() {
            const feed = document.getElementById("notifFeed");
            const badge = document.getElementById("notifCount");
            const unread = notifications.filter(n => n.is_read == 0);

            if (unread.length) {
                badge.classList.remove("hidden");
                badge.innerText = unread.length;
            } else {
                badge.classList.add("hidden");
            }

            document.getElementById("notifTotal").innerText = unread.length;

            if (notifications.length === 0) {
                feed.innerHTML = `<span class="text-xs text-white/40 font-medium text-center py-6">No alerts available</span>`;
                return;
            }

            feed.innerHTML = notifications.map(n => `
                <div class="flex flex-row items-start gap-3 bg-[#0D0E2A] rounded-xl p-3 border ${n.is_read == 0 ? 'border-[#FFBB02]/30 bg-[#31324C]/10' : 'border-white/5 opacity-70'}">
                    <div class="w-8 h-8 rounded-lg ${n.is_read == 0 ? 'bg-[#FFBB02]/20 text-[#FFBB02]' : 'bg-[#31324C]/40 text-[#B5B5B5]'} flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-white font-bold text-xs truncate">${n.title}</span>
                        <p class="text-[#B5B5B5] text-[11px] leading-relaxed break-words">${n.message}</p>
                    </div>
                </div>
            `).join("");
        }

        function toggleNotifications() {
            document.getElementById("notifPanel").classList.toggle("hidden");
        }

        async function markAllAsRead() {
            try {
                await fetch("http://localhost/crowdsourcedapi/api/notification/mark_all_as_read.php", {
                    method: "POST",
                    credentials: "include"
                });
                notifications = notifications.map(n => ({ ...n, is_read: 1 }));
                renderNotif();
            } catch (e) {
                console.error("Unable to update notification states indices:", e);
            }
        }

        /* =========================
           RUN-TIME INITIALIZATION PIPELINE
        ========================= */
        document.addEventListener("DOMContentLoaded", () => {
            initMap();
            loadUserLocation();
            loadDashboard();
            loadNotif();
            initDashboardBatteryRing();

            // Long poll system intervals for real-time tracking loops
            setInterval(loadUserLocation, 10000);
            setInterval(loadNotif, 15000);
        });
    </script>
</body>

</html>