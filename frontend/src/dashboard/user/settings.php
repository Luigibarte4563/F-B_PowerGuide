<?php
session_start();

require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();

$isGoogleUser =
    !empty($user['google_id']) ||
    ($user['auth_provider'] ?? '') === 'google';

$defaultPicture = "https://i.imgur.com/8Km9tLL.png";
$picture = !empty($user['picture'])
    ? "http://localhost" . $user['picture']
    : $defaultPicture;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PowerGuide</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
        }

        /* Custom scrollbar style */
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
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl hover:bg-[#FEBB02] hover:text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5 text-[#B5B5B5] group-hover:text-black transition-colors" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                    class="group flex flex-row items-center gap-3.5 px-4 h-11 rounded-xl bg-[#FEBB02] text-black hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-in-out font-semibold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>
            </div>

            <!-- Profile Info Panel -->
            <div
                class="flex flex-row items-center justify-between gap-3 px-4 py-3 mt-auto mb-8 rounded-2xl bg-[#31324C]/20 border border-white/5 text-left">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="h-10 w-10 rounded-xl overflow-hidden border border-[#FFBB02]/30 flex-shrink-0 bg-[#31324C]">
                        <img src="<?= htmlspecialchars($picture) ?>" class="w-10 h-10 rounded-xl object-cover">
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
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Account Settings</h1>
                    <span class="flex items-center gap-1.5 text-[#B5B5B5] font-medium text-sm mt-1">
                        Manage your profile, security, and location preferences.
                    </span>
                </div>
            </header>

            <!-- SETTINGS GRID -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-4 lg:px-8 mb-8">

                <!-- LEFT COLUMN -->
                <div class="flex flex-col gap-6">

                    <!-- ✏️ EDIT PROFILE MODULE -->
                    <div class="bg-[#31324C]/20 border border-white/5 rounded-2xl p-6 shadow-xl card-hover">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                            <div class="bg-[#FFBB02]/10 p-2.5 rounded-xl text-[#FFBB02]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold tracking-wide">Edit Profile</h2>
                        </div>

                        <form action="<?= BACKEND_URL ?>/src/api/user/update_profile.php" method="POST"
                            enctype="multipart/form-data" class="flex flex-col gap-4">

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">Full
                                    Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                    required
                                    class="w-full bg-[#1A1B3A] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02] transition-colors">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">Email
                                    Address</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                    required
                                    class="w-full bg-[#1A1B3A] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#FFBB02] focus:ring-1 focus:ring-[#FFBB02] transition-colors <?php echo $isGoogleUser ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                    <?php echo $isGoogleUser ? 'readonly title="Google accounts cannot change email here"' : ''; ?>>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">Profile
                                    Picture</label>
                                <div class="flex items-center gap-4">
                                    <img src="<?= htmlspecialchars($picture) ?>" alt="Current Avatar"
                                        class="w-12 h-12 rounded-xl object-cover border border-white/10 bg-[#1A1B3A]">
                                    <input type="file" name="picture" accept="image/*"
                                        class="w-full text-sm text-[#B5B5B5] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#FFBB02] file:text-black hover:file:bg-[#E39A00] file:cursor-pointer transition-colors">
                                </div>
                            </div>

                            <button type="submit"
                                class="mt-2 w-full bg-[#FFBB02] text-black font-bold py-3 rounded-xl hover:bg-[#E39A00] transition-colors shadow-lg shadow-[#FFBB02]/20">
                                Save Profile Changes
                            </button>
                        </form>
                    </div>

                    <!-- 📍 MY LOCATION MODULE -->
                    <div class="bg-[#31324C]/20 border border-white/5 rounded-2xl p-6 shadow-xl card-hover">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                            <div class="bg-[#00CFFF]/10 p-2.5 rounded-xl text-[#00CFFF]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold tracking-wide">My Location</h2>
                        </div>

                        <div class="flex flex-col gap-4">
                            <!-- Current Location Status -->
                            <div class="bg-[#1A1B3A] border border-white/5 rounded-xl p-4 flex flex-col gap-1">
                                <span class="text-xs font-bold text-[#00CFFF] uppercase tracking-wider">Current
                                    Status</span>
                                <span id="current_location" class="text-white font-medium truncate text-sm">Loading
                                    location...</span>
                                <span id="current_coords" class="text-[11px] text-[#B5B5B5] font-mono mt-1">Waiting for
                                    GPS telemetry...</span>
                            </div>

                            <div class="flex flex-col gap-1.5 mt-2">
                                <label class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">Manual
                                    Override</label>
                                <div class="flex gap-2">
                                    <input type="text" id="location_name" placeholder="e.g., Downtown Grid Alpha"
                                        class="flex-1 bg-[#1A1B3A] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00CFFF] focus:ring-1 focus:ring-[#00CFFF] transition-colors">
                                    <button onclick="updateLocation()"
                                        class="bg-[#31324C] hover:bg-[#00CFFF] hover:text-black text-white px-4 py-2 rounded-xl font-bold text-sm transition-all border border-white/10 hover:border-transparent">
                                        Save
                                    </button>
                                </div>
                            </div>

                            <div class="relative flex items-center py-2">
                                <div class="flex-grow border-t border-white/10"></div>
                                <span
                                    class="flex-shrink-0 mx-4 text-[#B5B5B5] text-xs font-bold uppercase tracking-widest">OR</span>
                                <div class="flex-grow border-t border-white/10"></div>
                            </div>

                            <button onclick="useCurrentLocation()"
                                class="w-full flex items-center justify-center gap-2 bg-[#00CFFF]/10 text-[#00CFFF] border border-[#00CFFF]/30 font-bold py-3 rounded-xl hover:bg-[#00CFFF] hover:text-black transition-all shadow-lg shadow-[#00CFFF]/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Sync GPS Coordinates
                            </button>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN -->
                <div class="flex flex-col gap-6">

                    <!-- 🔐 CHANGE PASSWORD MODULE -->
                    <div class="bg-[#31324C]/20 border border-white/5 rounded-2xl p-6 shadow-xl card-hover h-fit">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                            <div class="bg-[#CB3435]/10 p-2.5 rounded-xl text-[#CB3435]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold tracking-wide">Security & Password</h2>
                        </div>

                        <?php if (!$isGoogleUser): ?>
                            <form action="<?= BACKEND_URL ?>/src/api/user/update_password.php" method="POST"
                                class="flex flex-col gap-4">

                                <div class="flex flex-col gap-1.5">
                                    <label
                                        class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">Current
                                        Password</label>
                                    <input type="password" name="current_password" placeholder="••••••••" required
                                        class="w-full bg-[#1A1B3A] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#CB3435] focus:ring-1 focus:ring-[#CB3435] transition-colors">
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">New
                                        Password</label>
                                    <input type="password" name="new_password" placeholder="••••••••" required
                                        class="w-full bg-[#1A1B3A] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#CB3435] focus:ring-1 focus:ring-[#CB3435] transition-colors">
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label
                                        class="text-xs font-semibold text-[#B5B5B5] uppercase tracking-wider ml-1">Confirm
                                        New Password</label>
                                    <input type="password" name="confirm_password" placeholder="••••••••" required
                                        class="w-full bg-[#1A1B3A] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#CB3435] focus:ring-1 focus:ring-[#CB3435] transition-colors">
                                </div>

                                <button type="submit"
                                    class="mt-2 w-full bg-[#CB3435]/20 text-[#CB3435] border border-[#CB3435]/50 font-bold py-3 rounded-xl hover:bg-[#CB3435] hover:text-white transition-all shadow-lg shadow-[#CB3435]/10">
                                    Update Password
                                </button>
                            </form>
                        <?php else: ?>
                            <div
                                class="flex flex-col items-center justify-center py-8 text-center bg-[#1A1B3A] rounded-xl border border-white/5">
                                <div
                                    class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                                    <!-- Google G Logo SVG -->
                                    <svg viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                                            <path fill="#4285F4"
                                                d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z" />
                                            <path fill="#34A853"
                                                d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z" />
                                            <path fill="#FBBC05"
                                                d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z" />
                                            <path fill="#EA4335"
                                                d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z" />
                                        </g>
                                    </svg>
                                </div>
                                <h3 class="text-white font-bold mb-2">Google Authenticated</h3>
                                <p class="text-sm text-[#B5B5B5] px-6">
                                    Your account is linked via Google. Password management is handled securely through your
                                    Google Account settings, not here.
                                </p>
                                <a href="https://myaccount.google.com/security" target="_blank"
                                    class="mt-4 text-[#4285F4] hover:text-white text-sm font-bold transition-colors">
                                    Manage Google Account &rarr;
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- App Info Card (Optional Polish) -->
                    <div class="bg-[#31324C]/20 border border-white/5 rounded-2xl p-6 text-center mt-auto">
                        <span class="text-white font-bold text-lg tracking-tight leading-tight">
                            POWER<span class="text-[#FFBB02]">GUIDE</span>
                        </span>
                        <p class="text-xs text-[#B5B5B5] mt-2">Version 1.0.0 (Beta)<br>System telemetry and grid
                            monitoring active.</p>
                    </div>

                </div>

            </section>
        </main>
    </div>

    <!-- SCRIPTS -->
    <script>
        /* =========================
           MOBILE MENU MANAGEMENT
        ========================= */
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMobileSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        if (menuToggle && overlay) {
            menuToggle.addEventListener('click', toggleMobileSidebar);
            overlay.addEventListener('click', toggleMobileSidebar);
        }

        /* =========================
           API BASE (FIXED)
        ========================= */
        const API_BASE = "http://localhost/PowerGuides/backend";

        /* =========================
           LOAD LOCATION
        ========================= */
        async function loadLocation() {
            try {
                const res = await fetch(`http://localhost/crowdsourcedAPI/api/user_location/get.php`, {
                    credentials: "include"
                });

                if (!res.ok) throw new Error("Failed to fetch location");

                const data = await res.json();

                const locName = data.data?.location_name || data.location_name || "No location saved";
                const lat = data.data?.latitude ?? data.latitude ?? "-";
                const lng = data.data?.longitude ?? data.longitude ?? "-";

                document.getElementById("current_location").innerText = "📍 " + locName;
                document.getElementById("current_coords").innerText = `Lat: ${lat} | Lng: ${lng}`;

            } catch (err) {
                console.error("Load location error:", err);
                document.getElementById("current_location").innerText = "📍 Failed to load data";
                document.getElementById("current_coords").innerText = "";
            }
        }

        /* =========================
           UPDATE LOCATION (MANUAL)
        ========================= */
        async function updateLocation(e) {
            const btn = e.currentTarget;
            const input = document.getElementById("location_name");
            const location = input.value.trim();

            if (!location) {
                alert("Please enter a location name.");
                return;
            }

            const originalText = btn.innerText;

            try {
                btn.innerText = "Saving...";
                btn.disabled = true;

                const res = await fetch(`http://localhost/crowdsourcedAPI/api/user_location/location.php`, {
                    method: "POST",
                    credentials: "include",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ location_name: location })
                });

                if (!res.ok) throw new Error("Save failed");

                input.value = "";
                await loadLocation();

                btn.innerText = "Saved!";
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                }, 1500);

            } catch (err) {
                console.error("Update location error:", err);
                alert("Failed to save location.");
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }

        /* =========================
           USE CURRENT GPS LOCATION
        ========================= */
        function useCurrentLocation(e) {
            const btn = e.currentTarget;
            const originalContent = btn.innerHTML;

            if (!navigator.geolocation) {
                alert("Geolocation is not supported.");
                return;
            }

            btn.disabled = true;
            btn.innerHTML = "Syncing...";

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    try {
                        const res = await fetch(`http://localhost/crowdsourcedAPI/api/user_location/location.php`, {
                            method: "POST",
                            credentials: "include",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                location_name: "My Current GPS Location",
                                latitude: pos.coords.latitude,
                                longitude: pos.coords.longitude,
                                from_gps: true
                            })
                        });

                        if (!res.ok) throw new Error("GPS save failed");

                        await loadLocation();

                        btn.innerHTML = "✓ Synced";
                        setTimeout(() => {
                            btn.innerHTML = originalContent;
                            btn.disabled = false;
                        }, 1500);

                    } catch (err) {
                        console.error(err);
                        alert("Failed to sync GPS location.");
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                    }
                },
                (err) => {
                    console.error(err);
                    alert("Location permission denied or unavailable.");
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        }

        /* =========================
           INIT
        ========================= */
        document.addEventListener("DOMContentLoaded", () => {
            loadLocation();
        });
    </script>
</body>

</html>