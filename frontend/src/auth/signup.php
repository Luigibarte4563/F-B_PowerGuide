<?php
session_start();

require_once __DIR__ . '/../../../backend/src/config/env.php';
require_once __DIR__ . '/../../../backend/src/config/app.php';

$googleClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerGuide - Sign Up</title>

    <!-- Tailwind CSS Play CDN (Zero Dependencies) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Configuration for Custom Classes -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    borderRadius: {
                        '4xl': '2rem',
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght=100;300;400;600;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Identity Services SDK -->
    <script src="https://accounts.google.com/gsi/client" defer></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body id="signup" class="min-h-screen flex justify-center items-center bg-[#03041A] p-6 text-white">

    <!-- Signup Container Box -->
    <div class="border border-white/10 shadow-[0_0_20px_#FFBB02] rounded-4xl flex flex-col items-center justify-start p-10 opacity-80 bg-[#03041A]"
        style="width: 30rem; min-height: 42rem;">

        <!-- Logo -->
        <div class="flex justify-center items-center w-full gap-3" style="margin-top:24px;">
            <img class="w-10 md:w-16" src="../../img/logo.png" alt="Logo">

            <div class="flex flex-col justify-center items-start">
                <span class="text-white text-lg md:text-xl font-semibold leading-tight">
                    POWER<span class="text-[#FFBB02]">GUIDE</span>
                </span>

                <span class="text-white font-medium text-sm md:text-xs tracking-widest opacity-70 leading-none mt-1">
                    SECURITY AND RELIABILITY
                </span>
            </div>
        </div>

        <!-- Form Wrapper -->
        <form id="signupForm" action="<?= BACKEND_URL ?>/src/api/auth/register.php" method="POST" class="flex flex-col justify-center mt-6 w-80">

            <!-- Backend Session Status Messages -->
            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="text-red-500 text-sm mb-3 font-semibold text-center bg-red-500/10 py-2 px-3 rounded-xl border border-red-500/20">
                    <?= htmlspecialchars($_SESSION['register_error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="text-green-500 text-sm mb-3 font-semibold text-center bg-green-500/10 py-2 px-3 rounded-xl border border-green-500/20">
                    <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Email Field -->
            <div class="mb-3">
                <span class="text-white font-semibold text-sm">Email</span>
                <input type="email" name="email" placeholder="johndoe@gmail.com" required 
                    class="w-full h-11 pl-4 pr-4 rounded-xl border border-white/70 bg-transparent text-sm font-medium text-white outline-none placeholder:text-white/40 focus:border-[#FFBB02] mt-1">
            </div>

            <!-- Full Name Field -->
            <div class="mb-3">
                <span class="text-white font-semibold text-sm">Full Name</span>
                <input type="text" name="name" placeholder="John C. Doe" required 
                    class="w-full h-11 pl-4 pr-4 rounded-xl border border-white/70 bg-transparent text-sm font-medium text-white outline-none placeholder:text-white/40 focus:border-[#FFBB02] mt-1">
            </div>

            <!-- Password Field -->
            <div class="mb-3 relative">
                <span class="text-white font-semibold text-sm">Password</span>
                <div class="w-full relative mt-1">
                    <input type="password" name="password" placeholder="********" id="password" required 
                        class="w-full h-11 pl-4 pr-11 bg-transparent border border-white/70 rounded-xl text-sm font-medium text-white outline-none placeholder:text-white/40 focus:border-[#FFBB02]">
                    <button type="button" onclick="togglePassword()" class="cursor-pointer"
                        onmouseover="this.style.color='#FFBB02'" onmouseout="this.style.color='#9ca3af'"
                        style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #9ca3af;">
                        <i id="eyeIcon" class="fas fa-eye text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="mb-4">
                <span class="text-white font-semibold text-sm">Confirm Password</span>
                <input type="password" name="confirm_password" placeholder="********" id="confirm_password" required 
                    class="w-full h-11 pl-4 pr-4 rounded-xl border border-white/70 bg-transparent text-sm font-medium text-white outline-none placeholder:text-white/40 focus:border-[#FFBB02] mt-1">
                <!-- Status match container text -->
                <div id="match-message" class="text-xs font-semibold mt-1 h-4"></div>
            </div>

            <!-- Create Account Button -->
            <div class="w-full flex items-center justify-center">
                <!-- Initialized with disabled="true" until verification passes -->
                <button type="submit" id="submitBtn" disabled class="int-btn font-bold py-2.5 bg-[#FFBB02] text-black rounded-xl hover:bg-[#D99A00] active:bg-[#FFC833] cursor-pointer w-full text-sm hover:scale-[1.01] active:scale-[0.99] transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Create Account
                </button>
            </div>

            <!-- Divider -->
            <div class="flex flex-row items-center gap-3 my-4">
                <hr class="flex-1 border-[#AFAFAF]" />
                <span class="text-base text-[#B5B5B5] font-bold">or</span>
                <hr class="flex-1 border-[#AFAFAF]" />
            </div>

            <!-- Sign up with Google Container Block -->
            <div class="w-full relative h-11 group flex justify-center items-center">
                <div id="g_id_onload" 
                    data-client_id="<?= htmlspecialchars($googleClientId, ENT_QUOTES, 'UTF-8') ?>" 
                    data-auto_prompt="false"
                    data-callback="handleCredentialResponse"
                    data-auto_select="false">
                </div>

                <div class="g_id_signin absolute inset-0 z-20 opacity-0 cursor-pointer flex justify-center w-full"
                    data-type="standard" 
                    data-shape="rectangular" 
                    data-theme="outline"
                    data-text="signup_with" 
                    data-size="large" 
                    data-logo_alignment="left" 
                    data-width="320">
                </div>

                <button type="button" tabindex="-1" 
                    class="int-btn absolute inset-0 z-10 flex items-center justify-center gap-2 h-full bg-[#03041A] border border-[#FFBB02] rounded-xl text-sm font-semibold text-white group-hover:bg-[#FFBB02] group-active:bg-[#FFC833] group-hover:text-black group-hover:scale-[1.01] group-active:scale-[0.99] transition duration-300 w-full pointer-events-none">

                    <svg width="16" height="16" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6 l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14 .76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98-6.19C6.51 42.62 14.62 48 24 48z" />
                    </svg>

                    Sign up with Google
                </button>
            </div>

            <!-- Login Navigation Link -->
            <div class="flex justify-center mt-3">
                <span class="text-white text-sm mb-4">
                    Already have an account?
                    <a href="loginq.php"
                        style="color: #FFBB02; font-weight: bold; font-size: 0.9rem; text-decoration: none;"
                        onmouseover="this.style.color='#D99A00'" onmouseout="this.style.color='#FFBB02'"
                        onmousedown="this.style.color='#FFC833'" onmouseup="this.style.color='#D99A00'">
                        Log in
                    </a>
                </span>
            </div>
        </form>
    </div>

    <script>
        const clientIdCheck = "<?= $googleClientId ?>";
        if (!clientIdCheck) {
            console.error("PowerGuide Error: GOOGLE_CLIENT_ID environment variable is missing or empty.");
        }

        function togglePassword() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }

        document.getElementById('signupForm').addEventListener('submit', function(event) {
            console.log("Form ready to submit.");
        });

        /* ================= APPLIED JAVASCRIPT LOGIC ================= */
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm_password");
        const matchMessage = document.getElementById("match-message");
        const submitBtn = document.getElementById("submitBtn");

        function checkMatch() {
            if (!confirmPassword) return;

            // Block empty inputs from triggering positive validation
            if (password.value === "" && confirmPassword.value === "") {
                matchMessage.innerHTML = "";
                submitBtn.disabled = true;
                return;
            }

            if (password.value === confirmPassword.value) {
                matchMessage.innerHTML = "Passwords match";
                matchMessage.style.color = "#22c55e"; 
                submitBtn.disabled = false;
            } else {
                matchMessage.innerHTML = "Passwords do not match";
                matchMessage.style.color = "#ef4444"; 
                submitBtn.disabled = true;
            }
        }

        if (confirmPassword) {
            confirmPassword.addEventListener("input", checkMatch);
            password.addEventListener("input", checkMatch);
        }

        function decodeJWT(token) {
            const base64Url = token.split(".")[1];
            const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");

            return JSON.parse(
                decodeURIComponent(
                    atob(base64)
                        .split("")
                        .map(c => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
                        .join("")
                )
            );
        }

        window.handleCredentialResponse = function(response) {
            const data = decodeJWT(response.credential);

            const formData = new FormData();
            formData.append("name", data.name);
            formData.append("email", data.email);
            formData.append("picture", data.picture);
            formData.append("sub", data.sub);

            fetch("<?= BACKEND_URL ?>/src/api/auth/google_auth.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    alert(res.message || "Login failed");
                    return;
                }

                if (res.role === "electric_company") {
                    window.location.href = "<?= BACKEND_URL ?>/public/dashboard/electric/dashboard.php";
                }
                else {
                    window.location.href = "<?= BACKEND_URL ?>/public/dashboard/user/user.php";
                }
            })
            .catch(err => console.error(err));
        }
    </script>
</body>

</html>