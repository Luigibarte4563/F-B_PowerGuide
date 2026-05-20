// ── Landing page stuff (other pages) ──
const btn = document.getElementById('menuBtn');
const menu = document.getElementById('mobileMenu');
const header = document.getElementById('mainHeader');

if (btn) {
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
}

if (header) {
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 50);
    });
}

function togglePassword() {
    const password = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");
    if (password.type === "password") {
        password.type = "text";
        eyeIcon.className = "fas fa-eye-slash text-lg";
    } else {
        password.type = "password";
        eyeIcon.className = "fas fa-eye text-lg";
    }
}

function validateForm() {
    const contact = document.getElementById("contact").value;
    if (contact.length !== 11) {
        alert("Contact number must be exactly 11 digits.");
        return;
    }
    alert("Form valid! Ready to submit.");
}

function fadeOut(e) {
    e.preventDefault();
    const link = e.currentTarget.href;
    document.body.classList.remove("loaded");
    setTimeout(() => { window.location.href = link; }, 300);
}

// ── Sidebar (dashboard) ──
const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

if (toggle && sidebar && overlay) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
}

// ── Notifications ──
function toggleNotif() {
    const panel = document.getElementById('notifPanel');
    panel.classList.contains('is-open') ? closeNotif() : openNotif();
}

function openNotif() {
    const panel = document.getElementById('notifPanel');
    const bell = document.getElementById('bellIcon');
    const bellHover = document.getElementById('bellIconHover');
    panel.classList.add('is-open', 'opacity-100', 'pointer-events-auto', 'translate-y-0', 'scale-100');
    panel.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2', 'scale-95');
    bell.classList.add('!hidden');
    bellHover.classList.remove('hidden');
    bellHover.classList.add('block');
}

function closeNotif() {
    const panel = document.getElementById('notifPanel');
    const bell = document.getElementById('bellIcon');
    const bellHover = document.getElementById('bellIconHover');
    panel.classList.remove('is-open', 'opacity-100', 'pointer-events-auto', 'translate-y-0', 'scale-100');
    panel.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2', 'scale-95');
    bell.classList.remove('!hidden');
    bellHover.classList.remove('block');
    bellHover.classList.add('hidden');
}

document.addEventListener('click', function (e) {
    const panel = document.getElementById('notifPanel');
    const notifBtn = document.getElementById('notifBtn');
    if (panel && notifBtn && panel.classList.contains('is-open') &&
        !panel.contains(e.target) && !notifBtn.contains(e.target)) {
        closeNotif();
    }
});

// ── Profile ──
function openProfile() {
    const profile = document.getElementById("profile");
    const box = document.getElementById("profileBox");
    profile.classList.remove("opacity-0", "invisible", "pointer-events-none");
    profile.classList.add("opacity-100", "visible");
    box.classList.remove("scale-95", "translate-y-4", "opacity-0");
    box.classList.add("scale-100", "translate-y-0", "opacity-100");
}

function closeProfile() {
    const profile = document.getElementById("profile");
    const box = document.getElementById("profileBox");
    box.classList.remove("scale-100", "translate-y-0", "opacity-100");
    box.classList.add("scale-95", "translate-y-4", "opacity-0");
    profile.classList.remove("opacity-100", "visible");
    profile.classList.add("opacity-0", "invisible");
    setTimeout(() => { profile.classList.add("pointer-events-none"); }, 300);
}

// ── Battery ──
const levelEl = document.getElementById("level");
const statusEl = document.getElementById("status");
const batteryRing = document.getElementById("battery-ring");
const warningEl = document.getElementById("battery-warning");

if (levelEl && batteryRing) {
    if ('getBattery' in navigator) {
        navigator.getBattery().then(function (battery) {
            let warningDismissed = false;

            function updateBatteryInfo() {
                const level = Math.round(battery.level * 100);
                levelEl.textContent = level + "%";

                const offset = 314 - (314 * battery.level);
                batteryRing.style.strokeDashoffset = offset;

                if (battery.charging) {
                    statusEl.textContent = "Charging ⚡";
                    warningDismissed = false;
                    batteryRing.style.stroke = "#FFBB02";
                } else {
                    statusEl.textContent = "Not Charging";
                    if (level <= 40) {
                        if (!warningDismissed) {
                            openBatteryWarning(); // ← replace the two classList lines with this
                        }
                        batteryRing.style.stroke = "#EF4444";
                    } else {
                        warningDismissed = false;
                        closeBatteryWarning();
                        batteryRing.style.stroke = "#FFBB02";
                    }
                }
            }

            updateBatteryInfo();
            battery.addEventListener('levelchange', updateBatteryInfo);
            battery.addEventListener('chargingchange', updateBatteryInfo);
        });
    } else {
        levelEl.textContent = "N/A ❌";
        statusEl.textContent = "Try Chrome/Edge";
    }
}

function openBatteryWarning() {
    const warning = document.getElementById("battery-warning");
    const box = document.getElementById("batteryBox");
    warning.classList.remove("opacity-0", "invisible", "pointer-events-none");
    warning.classList.add("opacity-100", "visible");
    box.classList.remove("scale-95", "translate-y-4", "opacity-0");
    box.classList.add("scale-100", "translate-y-0", "opacity-100");
}

function closeBatteryWarning() {
    const warning = document.getElementById("battery-warning");
    const box = document.getElementById("batteryBox");
    box.classList.remove("scale-100", "translate-y-0", "opacity-100");
    box.classList.add("scale-95", "translate-y-4", "opacity-0");
    warning.classList.remove("opacity-100", "visible");
    warning.classList.add("opacity-0", "invisible");
    setTimeout(() => { warning.classList.add("pointer-events-none"); }, 300);
}

document.addEventListener("keydown", function (event) {

    const warning = document.getElementById("battery-warning");

    // Check if Enter key is pressed
    if (event.key === "Enter") {

        // Check if warning is currently visible
        if (!warning.classList.contains("invisible")) {
            closeBatteryWarning();
        }
    }
});

function openPopup() {
    const popup = document.getElementById("popup");
    const box = document.getElementById("popupBox");

    popup.classList.remove("invisible", "pointer-events-none", "opacity-0");
    popup.classList.add("opacity-100", "visible");

    requestAnimationFrame(() => {
        box.classList.remove("scale-95", "translate-y-4", "opacity-0");
        box.classList.add("scale-100", "translate-y-0", "opacity-100");
    });
}

function closePopup() {
    const popup = document.getElementById("popup");
    const box = document.getElementById("popupBox");

    box.classList.remove("scale-100", "translate-y-0", "opacity-100");
    box.classList.add("scale-95", "translate-y-4", "opacity-0");

    popup.classList.remove("opacity-100", "visible");
    popup.classList.add("opacity-0");

    setTimeout(() => {
        popup.classList.add("invisible", "pointer-events-none");
    }, 300);
}

function openSync() {
    const sync = document.getElementById("sync");
    const box = document.getElementById("syncBox");
    sync.classList.remove("opacity-0", "invisible", "pointer-events-none");
    sync.classList.add("opacity-100", "visible");
    box.classList.remove("scale-95", "translate-y-4", "opacity-0");
    box.classList.add("scale-100", "translate-y-0", "opacity-100");
}

function closeSync() {
    const sync = document.getElementById("sync");
    const box = document.getElementById("syncBox");
    box.classList.remove("scale-100", "translate-y-0", "opacity-100");
    box.classList.add("scale-95", "translate-y-4", "opacity-0");
    sync.classList.remove("opacity-100", "visible");
    sync.classList.add("opacity-0", "invisible");
    setTimeout(() => { sync.classList.add("pointer-events-none"); }, 300);
}

// ── Custom Dropdowns ──
function toggleDropdown(id, event) {
    event.stopPropagation(); // prevent the document listener from immediately closing it

    const wrapper = document.getElementById(id);
    const menu = wrapper.querySelector('.select-options');
    const arrow = wrapper.querySelector('.select-arrow');
    const isOpen = !menu.classList.contains('pointer-events-none');

    // Close ALL dropdowns first
    closeAllDropdowns();

    // If it wasn't open, open it now
    if (!isOpen) {
        menu.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
        menu.classList.add('opacity-100', 'scale-100');
        arrow.classList.add('rotate-180');
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.custom-select').forEach(wrapper => {
        const menu = wrapper.querySelector('.select-options');
        const arrow = wrapper.querySelector('.select-arrow');
        menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
        menu.classList.remove('opacity-100', 'scale-100');
        arrow.classList.remove('rotate-180');
    });
}

// Close on outside click
document.addEventListener('click', closeAllDropdowns);

// Handle option selection via event delegation
document.addEventListener('click', function (e) {
    const option = e.target.closest('.select-option');
    if (!option) return;

    const wrapper = option.closest('.custom-select');
    const label = wrapper.querySelector('.select-label');
    const hiddenInput = wrapper.querySelector('input[type="hidden"]');

    label.textContent = option.textContent.trim();
    label.classList.remove('text-white/50');
    label.classList.add('text-white');

    if (hiddenInput) hiddenInput.value = option.textContent.trim();

    closeAllDropdowns();
});

// Select an option
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('select-option')) {
        const wrapper = e.target.closest('.custom-select');
        wrapper.querySelector('.select-label').textContent = e.target.textContent;
        wrapper.querySelector('.select-label').classList.remove('text-white/50');
        wrapper.querySelector('input[type="hidden"]').value = e.target.textContent;
        wrapper.querySelector('.select-options').classList.add('opacity-0', 'scale-95', 'pointer-events-none');
        wrapper.querySelector('.select-arrow').classList.remove('rotate-180');
    }

    // Close on outside click
    if (!e.target.closest('.custom-select')) {
        document.querySelectorAll('.custom-select').forEach(sel => {
            sel.querySelector('.select-options').classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            sel.querySelector('.select-arrow').classList.remove('rotate-180');
        });
    }
});

const popupEl = document.getElementById('popup');
if (popupEl) {
    popupEl.addEventListener('click', function (e) {
        if (!document.getElementById('popupBox').contains(e.target)) {
            closePopup();
        }
    });
}

const profileEl = document.getElementById('profile');
if (profileEl) {
    profileEl.addEventListener('click', function (e) {
        if (!document.getElementById('profileBox').contains(e.target)) {
            closeProfile();
        }
    });
}

const syncEl = document.getElementById('sync');
if (syncEl) {
    syncEl.addEventListener('click', function (e) {
        if (!document.getElementById('syncBox').contains(e.target)) {
            closeSync();
        }
    });
}

document.getElementById('popupBox').addEventListener('click', function (e) {
    const outageOptions = document.getElementById('outageOptions');

    if (!outageOptions.contains(e.target)) {
        document.querySelectorAll('input[name="outage"]').forEach(r => r.checked = false);
    }
});