<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POWERGUIDE</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    borderRadius: {
                        '4xl': '2rem',
                    },

                    spacing: {
                        '17': '4.25rem',
                        '25': '6.25rem',
                    },

                    colors: {
                        brand: {
                            dark: '#03041A',
                            card: '#0C0D2A',
                            footer: '#0B0C22',
                            yellow: '#FFBB02'
                        }
                    },

                    fontFamily: {
                        montserrat: ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #03041A;
            color: white;
        }
    </style>
</head>

<body class="bg-black text-white opacity-100">

    <!-- Header Navigation -->
    <header id="mainHeader" class="flex flex-row sticky top-0 z-50 h-16 md:h-20 items-center justify-between px-6 md:px-12 bg-black/80 backdrop-blur-md border-b border-white/5">
        
        <!-- Logo -->
        <div class="flex items-center">
            <a href="#" class="flex items-center gap-3">
                <img class="w-10 object-contain" src="../img/logo.png" alt="Logo">
                <div class="flex flex-col justify-center items-start">
                    <span class="text-white font-bold text-base md:text-lg leading-tight tracking-widest">
                        POWER<span class="text-brand-yellow">GUIDE</span>
                    </span>
                    <span class="text-white/50 font-semibold text-[9px] tracking-widest leading-none mt-0.5">
                        SECURITY & RELIABILITY
                    </span>
                </div>
            </a>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-8">

            <!-- Navbar Links -->
            <ul class="hidden md:flex flex-row items-center font-medium gap-8 text-white/60 text-xs md:text-sm">
                <li class="hover:text-white transition-colors">
                    <a href="#about" class="py-2 block">About</a>
                </li>

                <li class="hover:text-white transition-colors">
                    <a href="#features" class="py-2 block">Features</a>
                </li>
            </ul>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-4 text-xs md:text-sm font-semibold">
                
                <a href="./auth/login.php">
                    Login
                </a>

                <a href="./auth/signup.php">
                    <button class="cursor-pointer px-5 py-2 bg-brand-yellow text-black rounded-full hover:bg-[#e0a400] transition-colors">
                        Sign Up
                    </button>
                </a>

                <button id="menuBtn" class="md:hidden text-white p-2 focus:outline-none hover:text-brand-yellow transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

            </div>
        </div>
    </header>

    <!-- Mobile Drawer -->
    <div id="mobileMenu" class="hidden md:hidden bg-brand-dark border-b border-white/5 text-gray-300 flex flex-col items-center gap-6 py-6 text-sm sticky top-16 md:top-20 z-40">
        <a href="#about" class="mobile-link hover:text-white transition-colors">About</a>
        <a href="#features" class="mobile-link hover:text-white transition-colors">Features</a>
        <a href="./auth/login.php" class="mobile-link hover:text-white transition-colors">Login</a>
    </div>

    <!-- Hero Section -->
    <section id="hero-section" class="bg-brand-dark flex flex-col justify-center min-h-[90vh] px-6 relative overflow-hidden">
        <img src="../img/landingbg.png" alt="" class="absolute inset-0 w-full h-full object-cover opacity-60 pointer-events-none select-none" />

    <!-- Minimal radial gradient for depth -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,187,2,0.05)_0%,transparent_60%)] pointer-events-none"></div>

        <div class="max-w-4xl mx-auto text-center relative z-10 py-20">
            
            <div class="flex justify-center mb-8">
                <p class="flex items-center px-4 py-2 border border-brand-yellow/30 rounded-full text-brand-yellow text-xs font-semibold tracking-wide">
                    <span class="relative flex h-2 w-2 mr-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-yellow opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-yellow"></span>
                    </span>
                    ACTIVE MONITORING IN DAGUPAN
                </p>
            </div>
            
            <h1 class="text-4xl sm:text-5xl md:text-7xl font-black leading-tight tracking-tight mb-8">
                When Power Fails,<br>
                Power<span class="text-brand-yellow">Guide</span> Prevails
            </h1>
            
            <p class="text-white/60 text-base md:text-xl font-medium max-w-2xl mx-auto mb-12 leading-relaxed">
                Stay informed with real-time outage tracking and smart battery management for the modern Dagupeño lifestyle.
            </p>
            
            <div class="flex justify-center gap-4 sm:gap-6 flex-col sm:flex-row items-center">
                <a href="auth/login.php" onclick="fadeOut(event)" class="w-full sm:w-auto">
                    <button class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-brand-yellow text-black text-sm md:text-base font-bold hover:bg-[#e0a400] transition-colors tracking-wide">
                        Get Started for Free
                    </button>
                </a>
                <a href="auth/login.php" onclick="fadeOut(event)" class="w-full sm:w-auto">
                    <button class="w-full sm:w-auto px-8 py-3.5 rounded-full border border-white/20 text-white text-sm md:text-base font-bold hover:bg-white/10 transition-colors tracking-wide">
                        Report Outages
                    </button>
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="min-h-screen bg-[#03041A] flex flex-col lg:flex-row items-center py-20 lg:py-0 px-6 md:px-12 scroll-mt-20 gap-16 lg:gap-8 border-t border-white/5">
            
        <!-- Typography-driven Stats Cards -->
        <div class="w-full lg:w-1/2 flex justify-center">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full max-w-2xl">
                
                <!-- No Internet -->
                <div class="flex flex-col items-start justify-center p-8 w-full bg-brand-card rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    
                    <img src="../img/nointernet.png" alt="No Internet" class="w-20 h-20 object-contain mb-5">

                    <h3 class="font-bold text-xl md:text-2xl text-white mb-3">
                        No Internet
                    </h3>

                    <p class="font-medium text-white/50 text-sm leading-relaxed">
                        Loss of connectivity during brownouts
                    </p>
                </div>

                <!-- Unprepared -->
                <div class="flex flex-col items-start justify-center p-8 w-full bg-brand-card rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    
                    <img src="../img/unprepared.jpg" alt="Unprepared" class="w-14 h-14 object-cover rounded-xl mb-5">

                    <span class="text-4xl md:text-5xl font-black text-brand-yellow mb-2">
                        85%
                    </span>

                    <h3 class="font-bold text-base text-white mb-2">
                        Unprepared Rate
                    </h3>

                    <p class="font-medium text-white/50 text-sm leading-relaxed">
                        Unpreparedness rate in local communities
                    </p>
                </div>

                <!-- Dead Battery -->
                <div class="flex flex-col items-start justify-center p-8 w-full bg-brand-card rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    
                    <img src="../img/deadbattery.png" alt="Dead Battery" class="w-20 h-20 object-contain mb-5">

                    <h3 class="font-bold text-xl md:text-2xl text-white mb-3">
                        Dead Battery
                    </h3>

                    <p class="font-medium text-white/50 text-sm leading-relaxed">
                        Unexpected drain on vital devices
                    </p>
                </div>

                <!-- Lack of Data -->
                <div class="flex flex-col items-start justify-center p-8 w-full bg-brand-card rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    
                    <img src="../img/data.png" alt="Lack of Data" class="w-14 h-14 object-contain mb-5">

                    <h3 class="font-bold text-xl md:text-2xl text-white mb-3">
                        Lack of Data
                    </h3>

                    <p class="font-medium text-white/50 text-sm leading-relaxed">
                        Unawareness of city updates and schedules
                    </p>
                </div>

            </div>
        </div>

        <!-- About Copy -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-start lg:pl-10 max-w-2xl mx-auto">
            <span class="text-sm font-bold text-brand-yellow tracking-[0.2em] uppercase mb-4">
                The Context
            </span>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight mb-8">
                Why PowerGuide?
            </h2>

            <p class="font-medium text-base md:text-lg text-white/60 mb-10 leading-relaxed">
                In Dagupan City, power stability remains a challenge.
                From maintenance interruptions to weather-related
                brownouts, users find themselves in the dark without
                a clear plan.
            </p>

            <div class="border-l-2 border-brand-yellow pl-6 py-2">
                <p class="text-white font-medium text-lg md:text-xl leading-relaxed italic">
                    "Our mission is to provide the data you need
                    to bridge the gap between power failure and
                    grid restoration, keeping you connected when it
                    matters the most."
                </p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-brand-dark flex flex-col justify-center items-center px-6 py-24 scroll-mt-20 border-t border-white/5">
        
        <div class="text-center mb-16 max-w-3xl mx-auto">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black mb-6">
                <span class="text-brand-yellow">Powerful</span> Tools for <br/>
                <span class="text-brand-yellow">Power</span> Failures
            </h2>
            <p class="font-medium text-white/60 text-base md:text-lg leading-relaxed">
                A unified suite designed to help you navigate through every outage efficiently.
            </p>
        </div>

        <div class="flex flex-col gap-6 md:gap-8 w-full max-w-6xl">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                
                <!-- Feature 1 -->
                <div class="bg-brand-card p-8 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="mb-6 text-brand-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-white mb-3">Outage Reporting</h3>
                    <p class="font-medium text-sm text-white/50 leading-relaxed">
                        Users can report power issues in their area with location, description, severity, and optional image proof.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-brand-card p-8 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="mb-6 text-brand-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-white mb-3">Real-Time Monitoring</h3>
                    <p class="font-medium text-sm text-white/50 leading-relaxed">
                        The system shows live outage and maintenance statuses on a map to help users distinguish real problems from planned work.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-brand-card p-8 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="mb-6 text-brand-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-white mb-3">Maintenance Scheduling</h3>
                    <p class="font-medium text-sm text-white/50 leading-relaxed">
                        Electric companies can publish scheduled power interruptions with time, date, affected areas, and status updates.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-brand-card p-8 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="mb-6 text-brand-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-white mb-3">Maintenance Mapping</h3>
                    <p class="font-medium text-sm text-white/50 leading-relaxed">
                        The system stores and displays exact affected barangay locations for maintenance activities clearly on the map.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-brand-card p-8 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="mb-6 text-brand-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-white mb-3">Charging Finder</h3>
                    <p class="font-medium text-sm text-white/50 leading-relaxed">
                        Users can locate nearby charging stations, solar hubs, and power access points with real-time availability.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-brand-card p-8 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="mb-6 text-brand-yellow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-white mb-3">Notification Alerts</h3>
                    <p class="font-medium text-sm text-white/50 leading-relaxed">
                        The system sends real-time alerts about outages, maintenance, and emergencies to keep users informed instantly.
                    </p>
                </div>
            </div>

            <!-- Smart System Overview -->
            <div class="w-full mt-4">
                <div class="flex flex-col md:flex-row items-center justify-between px-8 md:px-12 py-10 rounded-3xl bg-brand-card border border-brand-yellow/20">
                    <div class="flex-1 md:pr-10 text-center md:text-left">
                        <div class="flex justify-center md:justify-start items-center gap-4 mb-4">
                            <div class="text-brand-yellow">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                            </div>
                            <span class="font-bold text-2xl md:text-3xl text-white">Smart System Overview</span>
                        </div>
                        <span class="font-medium text-sm md:text-base text-white/60 block leading-relaxed">
                            PowerGuide connects reporting, scheduling, mapping, charging access, and notifications into one <strong class="text-white font-semibold">unified, real-time power awareness platform</strong>.
                        </span>
                    </div>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="bg-brand-footer px-6 md:px-12 py-16 border-t border-white/5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 border-b border-white/5 pb-16 max-w-7xl mx-auto">

            <div class="flex flex-col gap-6 lg:col-span-4">
                <a href="#" class="flex items-center gap-3">
                    <img class="w-10 object-contain" src="../img/logo.png" alt="Logo">
                    <div class="flex flex-col justify-center items-start">
                        <span class="text-white font-bold text-lg md:text-xl leading-tight tracking-widest">
                            POWER<span class="text-brand-yellow">GUIDE</span>
                        </span>
                        <span class="text-white/50 font-semibold text-[9px] tracking-widest leading-none mt-0.5">
                            SECURITY & RELIABILITY
                        </span>
                    </div>
                </a>

                <p class="text-white/40 font-medium text-sm leading-relaxed pr-4">
                    The ultimate companion for managing power outages and device longevity. Dedicated to keeping communities connected during unexpected blackouts.
                </p>

                <div class="flex flex-row gap-4 mt-2">
    
                    <a href="https://www.facebook.com/" 
                    class="w-10 h-10 border border-white/10 hover:border-brand-yellow hover:text-brand-yellow transition-colors rounded-full flex justify-center items-center text-white/60">
                        <i class="fa-brands fa-facebook-f text-sm"></i>
                    </a>

                    <a href="https://www.twitter.com/" 
                    class="w-10 h-10 border border-white/10 hover:border-brand-yellow hover:text-brand-yellow transition-colors rounded-full flex justify-center items-center text-white/60">
                        <i class="fa-brands fa-x-twitter text-sm"></i>
                    </a>

                    <a href="https://www.github.com/" 
                    class="w-10 h-10 border border-white/10 hover:border-brand-yellow hover:text-brand-yellow transition-colors rounded-full flex justify-center items-center text-white/60">
                        <i class="fa-brands fa-github text-sm"></i>
                    </a>

                </div>
            </div>

            <div class="lg:col-span-2">
                <h3 class="font-bold text-base text-white mb-6 tracking-wide">Product</h3>
                <ul class="font-medium text-white/50 text-sm flex flex-col gap-4">
                    <li><a class="hover:text-white transition-colors" href="features.html">Features</a></li>
                    <li><a class="hover:text-white transition-colors" href="reporting-map.html">Reporting Map</a></li>
                    <li><a class="hover:text-white transition-colors" href="battery-tracker.html">Battery Tracker</a></li>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h3 class="font-bold text-base text-white mb-6 tracking-wide">Support</h3>
                <ul class="font-medium text-white/50 text-sm flex flex-col gap-4">
                    <li><a class="hover:text-white transition-colors" href="helpcenter.html">Help Center</a></li>
                    <li><a class="hover:text-white transition-colors" href="report-issue.html">Report an issue</a></li>
                    <li><a class="hover:text-white transition-colors" href="privacy.html">Privacy Policy</a></li>
                    <li><a class="hover:text-white transition-colors" href="terms.html">Terms of Service</a></li>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h3 class="font-bold text-base text-white mb-6 tracking-wide">Our Team</h3>
                <div class="flex items-center gap-3">
                    <img src="../img/logo.png" alt="Team Logo" class="w-6 h-6 object-contain grayscale opacity-70">
                    <span class="font-medium text-white/50 text-sm">
                        Voltes Six
                    </span>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h3 class="font-bold text-base text-white mb-6 tracking-wide">The Builders</h3>
                <ul class="font-medium text-white/50 text-sm flex flex-col gap-3">
                    <li class="cursor-default">Luigi Barte</li>
                    <li class="cursor-default">Walter Ballesteros Jr.</li>
                    <li class="cursor-default">Don Rudyrick Barberan</li>
                    <li class="cursor-default">Christian Blanco</li>
                    <li class="cursor-default">Noerly Yvonne Mae Idos</li>
                    <li class="cursor-default">Angela Martin</li>
                </ul>
            </div>

        </div>

        <div class="w-full flex justify-center pt-8">
            <span class="text-white/30 text-xs tracking-widest uppercase text-center font-medium">
                V6 &copy; 2026 POWERGUIDE. BUILT FOR THE COMMUNITY.
            </span>
        </div>
    </footer>

    <script>
        // Toggle mobile drawer navigation menu
        const menuBtn = document.getElementById('menuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (menuBtn && mobileMenu) {
            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Close drawer menu automatically when clicking item target anchors
        const mobileLinks = document.querySelectorAll('.mobile-link');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (mobileMenu) mobileMenu.classList.add('hidden');
            });
        });

        // Soft Page Navigation Animation Helper
        function fadeOut(event) {
            event.preventDefault();
            const destination = event.currentTarget.getAttribute('href');
            document.body.classList.add('fading-out');

            setTimeout(() => {
                window.location.href = destination;
            }, 400);
        }
    </script>
</body>
</html>