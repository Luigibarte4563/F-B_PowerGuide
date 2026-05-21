<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POWERGUIDE</title>

    <!-- Tailwind CSS Play CDN (Removes node_modules dependency) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Engine Custom Configurations -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    borderRadius: {
                        '4xl': '2rem',
                    },
                    spacing: {
                        '25': '6.25rem',
                        '17': '4.25rem',
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght=100;300;400;600;700;900&display=swap"
        rel="stylesheet">

    <!-- Internal CSS Fallbacks & Tweaks -->
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            transition: opacity 0.4s ease;
        }

        /* Handles the fade-out page transition requested by onclick="fadeOut(event)" */
        body.fading-out {
            opacity: 0;
        }

        /* Custom glow drop shadows that Tailwind classes parse cleanly */
        .shadow-glow-blue {
            box-shadow: 0 0 20px #0F63CE;
        }

        .shadow-glow-yellow {
            box-shadow: 0 0 20px #FFBB02;
        }

        .shadow-glow-red {
            box-shadow: 0 0 20px #FE0000;
        }

        .shadow-glow-green {
            box-shadow: 0 0 20px #00E48D;
        }
    </style>
</head>

<body class="bg-black opacity-100">

    <!-- Header -->
    <header id="mainHeader"
        class="flex flex-row sticky top-0 z-50 h-16 md:h-25 text-2xl items-center justify-between px-6 md:px-12 bg-black/50 backdrop-blur-2xl border-b border-white/10">

        <!-- Logo & Brand Section -->
        <div class="flex items-center">
            <a href="#" class="flex items-center gap-3">
                <img class="w-10 md:w-14 object-contain" src="../img/logo.png" alt="Logo">
                <div class="flex flex-col justify-center items-start">
                    <span class="text-white font-bold text-base md:text-xl leading-tight tracking-wide">
                        POWER<span class="text-[#FFBB02]">GUIDE</span>
                    </span>
                    <span class="text-white font-medium text-[10px] md:text-xs tracking-widest opacity-70 leading-none">
                        SECURITY AND RELIABILITY
                    </span>
                </div>
            </a>
        </div>

        <!-- Centered Nav Links (hidden on mobile) -->
        <ul class="hidden md:flex flex-row items-center font-bold gap-8 text-gray-400 text-xs md:text-sm">
            <li class="hover:text-white active:text-gray-300 transition-colors">
                <a href="#about" class="py-2 block">About</a>
            </li>
            <li class="hover:text-white active:text-gray-300 transition-colors">
                <a href="#features" class="py-2 block">Features</a>
            </li>
        </ul>

        <!-- CTAs and Controls Section -->
        <div class="flex items-center gap-4 md:gap-6 text-xs md:text-sm font-bold">
            <a href="./auth/login.php" onclick="fadeOut(event)" class="inline-block">
                <button
                    class="int-btn cursor-pointer px-4 py-2 bg-[#FFBB02] text-black rounded-full hover:bg-[#D99A00] active:bg-[#FFC833] transition-colors">
                    Login
                </button>
            </a>

            <a href="./auth/signup.php" onclick="fadeOut(event)" class="inline-block">
                <button
                    class="int-btn cursor-pointer px-4 py-2 bg-[#BCBCBC]/25 rounded-full hover:bg-white/30 hover:text-white transition-colors">
                    <span class="text-white/85">Sign Up</span>
                </button>
            </a>

            <!-- Hamburger button (mobile only) -->
            <button id="menuBtn"
                class="md:hidden text-white p-2 focus:outline-none hover:text-[#FFBB02] transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Mobile Navigation Drawer Menu -->
    <div id="mobileMenu"
        class="hidden md:hidden bg-black/90 backdrop-blur-2xl text-gray-300 flex flex-col items-center gap-6 py-6 text-base border-b border-white/10 sticky top-16 z-40">
        <a href="#about" class="mobile-link">About</a>
        <a href="#features" class="mobile-link">Features</a>
    </div>

    <!-- Hero Section -->
    <section id="hero-section">
        <div class="relative min-h-screen">
            <img src="../img/landingbg.png" alt="Background" class="absolute inset-0 w-full h-full object-cover">
            <div
                class="flex flex-col text-white h-full min-h-screen text-center justify-start pt-20 md:pt-17 relative z-10 px-4 py-20">
                <div class="flex m-5 justify-center">
                    <p
                        class="flex items-center px-5 py-2.5 border-2 rounded-full border-[#FFBB02] text-[#FFBB02] text-xs md:text-sm font-bold">

                        <!-- Pulse circle -->
                        <span class="relative flex h-3 w-3 mr-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FFBB02] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#FFBB02]"></span>
                        </span>

                        ACTIVE MONITORING IN DAGUPAN
                    </p>
                </div><br>
                <div>
                    <span class="text-3xl sm:text-4xl font-black md:text-6xl lg:text-6xl leading-tight">
                        When Power Fails,<br>Power<span class="text-[#FFBB02]">Guide</span> Prevails
                    </span><br><br>
                    <p class="text-white text-sm md:text-lg font-medium px-4 md:px-0">
                        Stay informed with real-time outage tracking and smart
                        <br class="hidden md:block"> battery management for the modern Dagupeño lifestyle.
                    </p><br><br>
                </div>
                <div class="flex justify-center gap-4 md:gap-20 flex-wrap">
                    <a href="login.html" onclick="fadeOut(event)">
                        <button
                            class="int-btn cursor-pointer px-4 py-3 rounded-lg bg-[#FFBB02] text-black text-sm md:text-base hover:bg-[#D99A00] active:bg-[#FFC833] transition-colors font-bold">
                            Get Started for Free
                        </button>
                    </a>

                    <a href="dashboard.html" onclick="fadeOut(event)">
                        <button
                            class="int-btn group cursor-pointer px-6 md:px-10 py-3 rounded-lg bg-[#BCBCBC]/25 text-sm md:text-base hover:bg-white/50 active:bg-white/60 transition-colors font-bold">
                            <span
                                class="text-white/75 group-hover:text-black group-active:text-black transition-colors">
                                Report Outages
                            </span>
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <!-- FIX: Added ID directly to the container and set precise dynamic scroll margins matching header heights -->
    <section id="about"
        class="min-h-screen bg-[#03041A] flex flex-col lg:flex-row items-center border-t border-[#03041A] py-16 lg:py-0 scroll-mt-16 md:scroll-mt-25">
        <!-- Left: Cards -->
        <div class="w-full lg:w-1/2 flex justify-center items-center">
            <div class="flex text-white flex-col sm:flex-row gap-8 px-6 lg:px-10 w-full">

                <!-- Column 1 -->
                <div class="flex flex-col gap-8 flex-1">
                    <div
                        class="flex flex-col items-center justify-center p-6 h-56 w-full bg-[#0C0D2A] rounded-4xl shadow-glow-blue text-sm text-center">
                        <img class="h-16 object-contain" src="../img/nointernet.png" alt="No Internet">
                        <span class="mt-3 font-bold text-base">No Internet</span>
                        <span class="font-medium mt-1 text-white/70 leading-tight">
                            Loss of connectivity during<br> brownouts
                        </span>
                    </div>

                    <div
                        class="flex flex-col items-center justify-center p-6 h-56 w-full bg-[#0C0D2A] rounded-4xl shadow-glow-yellow text-sm text-center">
                        <span class="text-5xl font-black text-[#FFBA00] mb-1">85%</span>
                        <span class="font-bold text-base">Unprepared Rate</span>
                        <span class="mt-1 font-medium text-white/70 leading-tight">
                            Unpreparedness rate in local <br> communities
                        </span>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="flex flex-col gap-8 flex-1">
                    <div
                        class="flex flex-col items-center justify-center p-6 h-56 w-full bg-[#0C0D2A] rounded-4xl shadow-glow-red text-sm text-center">
                        <img class="h-16 object-contain" src="../img/deadbattery.png" alt="Dead Battery">
                        <span class="font-bold text-base mt-3">Dead Battery</span>
                        <span class="font-medium mt-1 text-white/70 leading-tight">
                            Unexpected drain on<br> vital devices
                        </span>
                    </div>

                    <div
                        class="flex flex-col items-center justify-center p-6 h-56 w-full bg-[#0C0D2A] rounded-4xl shadow-glow-green text-sm text-center">
                        <img class="h-16 object-contain" src="../img/data.png" alt="Lack of Data">
                        <span class="font-bold text-base mt-3">Lack of Data</span>
                        <span class="font-medium mt-1 text-white/70 leading-tight">
                            Unawareness of city updates<br> and schedules
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right: Text -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-6 md:px-12 py-10 lg:py-0 text-center">
            <span class="text-white w-full">
                <span class="text-lg md:text-xl font-extrabold text-[#FFBB02] tracking-wider block mb-2">
                    THE CONTEXT
                </span>

                <h2 class="text-3xl sm:text-4xl font-black md:text-5xl lg:text-6xl text-white leading-tight mb-6">
                    Why PowerGuide?
                </h2>

                <p class="font-medium text-base md:text-lg text-white/80 mb-8 max-w-xl mx-auto"
                    style="line-height: 2rem;">
                    In Dagupan City, power stability remains a challenge.
                    From maintenance interruptions to weather-related
                    brownouts, users find themselves in the dark without
                    a clear plan.
                </p>
            </span>

            <div
                class="border-2 border-[#FFBB03]/30 rounded-4xl italic shadow-glow-yellow bg-[#0C0D2A] p-6 md:p-8 w-full max-w-xl mx-auto">
                <p class="text-white font-medium text-base md:text-lg" style="line-height: 1.8rem;">
                    "Our mission is to provide the data you need
                    to bridge the gap between power failure and
                    grid restoration, keeping you connected when it
                    matters the most."
                </p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <hr class="border-t border-[#03041A]">

    <!-- FIX: Configured precise dynamic scroll margins to account for header dimensions -->
    <section id="features" class="min-h-screen bg-[#03041A] flex flex-col justify-center items-center px-4 py-16 scroll-mt-16 md:scroll-mt-25">
        <div class="text-white text-center">
            <h1 class="text-3xl font-bold sm:text-4xl md:text-6xl">
                <span class="text-[#FFBB02]">Powerful </span><span>Tools for </span>
                <span class="text-[#FFBB02]">Power </span><span>Failures</span>
            </h1><br>
            <span class="flex font-medium justify-center text-center text-sm md:text-base flex-col items-center">
                A unified suite designed to help you navigate <br> through every outage efficiently.
                <br><br>                <span class="border-t border-[#FFBB02] flex justify-center items-center w-24 md:w-48"></span>
            </span><br><br>
        </div>

        <div class="flex flex-col gap-6 md:gap-10 w-full max-w-6xl">
            <!-- Row 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                <span class="shadow-glow-yellow text-white px-6 md:px-10 py-8 md:py-10 rounded-4xl bg-[#0C0D2A]">
                    <span class="flex justify-start items-center gap-4">
                        <span class="bg-[#16184E] rounded-[10px] flex-shrink-0 p-1">
                            <img class="h-8 w-8 m-1" src="../img/location.png" alt="Icon">
                        </span>
                        <span class="font-bold">Outage Reporting</span>
                    </span><br>
                    <span class="font-medium text-sm md:text-base block">
                        Crowdsourced reporting that allows
                        you to pinpoint locations, specify times,
                        and provide descriptions to help the
                        community stay informed.
                    </span>
                </span>

                <span class="shadow-glow-yellow text-white px-6 md:px-10 py-8 md:py-10 rounded-4xl bg-[#0C0D2A]">
                    <span class="flex justify-start items-center gap-4">
                        <span class="bg-[#16184E] rounded-[10px] flex-shrink-0">
                            <img class="h-[37px] w-[37px] m-1" src="../img/realtime.png" alt="Icon">
                        </span>
                        <span class="font-bold">Real-Time Monitoring</span>
                    </span><br>
                    <span class="font-medium text-sm md:text-base block">
                        View a dynamic map of nearby
                        outages. Check verification status to
                        distinguish between planned
                        maintenance and emergency faults.
                    </span>
                </span>

                <span class="shadow-glow-yellow text-white px-6 md:px-10 py-8 md:py-10 rounded-4xl bg-[#0C0D2A]">
                    <span class="flex justify-start items-center gap-4">
                        <span class="bg-[#16184E] rounded-[10px] flex-shrink-0">
                            <img class="h-[37px] w-[37px] m-1" src="../img/battery.png" alt="Icon">
                        </span>
                        <span class="font-bold">Smart Battery Tracking</span>
                    </span><br>
                    <span class="font-medium text-sm md:text-base block">
                        Automatic and manual battery
                        monitoring alerts you when it's time to
                        find a charging station before your
                        device powers down.
                    </span>
                </span>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                <span class="shadow-glow-yellow text-white px-6 md:px-10 py-8 md:py-10 rounded-4xl bg-[#0C0D2A]">
                    <span class="flex justify-start items-center gap-4">
                        <span class="bg-[#16184E] rounded-[10px] flex-shrink-0 p-1">
                            <img class="h-8 w-8 m-1" src="../img/lightbulb.png" alt="Icon">
                        </span>
                        <span class="font-bold">Proactive Advice</span>
                    </span><br>
                    <span class="font-medium text-sm md:text-base block">
                        AI-driven power-saving tips tailored to
                        your specific battery level and the
                        expected duration of local outages.
                    </span>
                </span>

                <span class="shadow-glow-yellow text-white px-6 md:px-10 py-8 md:py-10 rounded-4xl bg-[#0C0D2A]">
                    <span class="flex justify-start items-center gap-4">
                        <span class="bg-[#16184E] rounded-[10px] flex-shrink-0 p-1">
                            <img class="h-8 w-8 m-1" src="../img/plug.png" alt="Icon">
                        </span>
                        <span class="font-bold">Charging Finder</span>
                    </span><br>
                    <span class="font-medium text-sm md:text-base block">
                        Find public charging stations, solar
                        hubs, or local business partners that
                        offer device charging during extended
                        outages.
                    </span>
                </span>

                <span class="shadow-glow-yellow text-white px-6 md:px-10 py-8 md:py-10 rounded-4xl bg-[#0C0D2A]">
                    <span class="flex justify-start items-center gap-4">
                        <span class="bg-[#16184E] rounded-[10px] flex-shrink-0 p-1">
                            <img class="h-8 w-8 m-1" src="../img/bell-gradient.png" alt="Icon">
                        </span>
                        <span class="font-bold">Notification Alerts</span>
                    </span><br>
                    <span class="font-medium text-sm md:text-base block">
                        Get instant notifications whether
                        online or offline to serve the purpose of
                        retaining awareness under any
                        condition.
                    </span>
                </span>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="flex flex-col bg-[#0B0C22] px-6 md:px-10 py-10 gap-10 border-t border-white/20">
        <div class="flex flex-col lg:flex-row justify-evenly gap-10 border-b border-white/20 pb-10">

            <!-- Brand -->
            <div class="flex flex-col gap-4">
                <div class="flex">
                    <a href="#" class="flex items-center gap-2">
                        <img class="w-10 md:w-16" src="../img/logo.png" alt="Logo">
                        <div class="flex flex-col justify-center items-start">
                            <span class="text-white font-bold text-lg md:text-xl leading-tight">
                                POWER<span class="text-[#FFBB02]">GUIDE</span>
                            </span>
                            <span
                                class="text-white font-medium text-sm md:text-xs tracking-widest opacity-70 leading-none">
                                SECURITY AND RELIABILITY
                            </span>
                        </div>
                    </a>
                </div>

                <span class="text-white/50 font-medium text-sm md:text-base block">
                    The ultimate companion for managing <br>
                    power outages and device longevity. <br>
                    Dedicated to keeping communities <br>
                    connected during unexpected <br>
                    blackouts.
                </span>

                <div class="flex flex-row gap-3">
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/"
                        class="group w-8 h-8 md:w-10 md:h-10 bg-[#1E1F3B] hover:bg-[#FFBB02] transition-all duration-300 rounded-full flex justify-center items-center">
                        <span class="text-white group-hover:text-black font-bold text-sm">F</span>
                    </a>

                    <!-- Twitter -->
                    <a href="https://www.twitter.com/"
                        class="group w-8 h-8 md:w-10 md:h-10 bg-[#1E1F3B] hover:bg-[#FFBB02] transition-all duration-300 rounded-full flex justify-center items-center">
                        <span class="text-white group-hover:text-black font-bold text-sm">T</span>
                    </a>

                    <!-- Github -->
                    <a href="https://www.github.com/"
                        class="group w-8 h-8 md:w-10 md:h-10 bg-[#1E1F3B] hover:bg-[#FFBB02] transition-all duration-300 rounded-full flex justify-center items-center">
                        <span class="text-white group-hover:text-black font-bold text-sm">G</span>
                    </a>
                </div>
            </div>

            <!-- Product -->
            <div class="text-white text-lg md:text-2xl">
                <span class="font-bold">Product</span><br><br>
                <ul class="font-medium text-white/50 text-sm md:text-base flex flex-col gap-3">
                    <li><a class="transition-colors duration-200 hover:text-white" href="features.html">Features</a>
                    </li>
                    <li><a class="transition-colors duration-200 hover:text-white" href="reporting-map.html">Reporting
                            Map</a></li>
                    <li><a class="transition-colors duration-200 hover:text-white" href="battery-tracker.html">Battery
                            Tracker</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="text-white text-lg md:text-2xl">
                <span class="font-bold">Support</span><br><br>
                <ul class="font-medium text-white/50 text-sm md:text-base flex flex-col gap-3">
                    <li><a class="transition-colors duration-200 hover:text-white" href="helpcenter.html">Help
                            Center</a></li>
                    <li><a class="transition-colors duration-200 hover:text-white" href="report-issue.html">Report an
                            issue</a></li>
                    <li><a class="transition-colors duration-200 hover:text-white" href="privacy.html">Privacy
                            Policy</a></li>
                    <li><a class="transition-colors duration-200 hover:text-white" href="terms.html">Terms of
                            Service</a></li>
                </ul>
            </div>

            <!-- Team Name -->
            <div class="text-white text-lg md:text-2xl">
                <span class="font-bold">Our Team</span><br><br>
                <div class="flex items-center gap-3">
                    <img src="../img/logo.png" alt="Team Logo" class="w-6 h-6 md:w-8 md:h-8">
                    <span class="font-medium text-white/50 text-sm md:text-base">
                        Voltes Six
                    </span>
                </div>
            </div>

            <!-- Members -->
            <div class="text-white text-lg md:text-2xl">
                <span class="font-bold">The Builders</span><br><br>
                <ul class="font-medium text-white/50 text-sm md:text-base flex flex-col gap-3">
                    <li>Luigi Barte</li>
                    <li>Walter Ballesteros</li>
                    <li>Don Rudyrick Barberan</li>
                    <li>Christian Blanco</li>
                    <li>Noerly Yvonne Mae Idos</li>
                    <li>Angela Martin</li>
                </ul>
            </div>
        </div>

        <div class="w-full flex justify-center">
            <span class="text-white/50 text-xs tracking-widest uppercase text-center">
                V6 @ 2026 POWERGUIDE. BUILT FOR THE COMMUNITY.
            </span>
        </div>
    </footer>

    <!-- Internal JS Logic Operations -->
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