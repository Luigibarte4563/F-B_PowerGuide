<?php
session_start();
require_once __DIR__ . '/../../../../backend/src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../../backend/src/config/app.php';

$user = requireAuth();
$isGoogleUser = !empty($user['google_id']) || ($user['auth_provider'] ?? '') === 'google';
$defaultPicture = "https://i.imgur.com/8Km9tLL.png";
$picture = $user['picture'] ?? $defaultPicture;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerGuide Maintenance Map</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .leaflet-container { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #31324C; border-radius: 10px; }
    </style>
</head>

<body class="bg-[#03041A] text-white antialiased">
    <!-- UI Layout remains consistent with your provided structure -->
    <!-- (Retain your Sidebar and Header HTML here for full functionality) -->
    
    <main class="flex-1 overflow-y-auto bg-[#03041A]">
        <!-- ... [Insert your Header & Content Section HTML] ... -->
        <div id="map" class="w-full h-[550px] bg-[#0E0F26]"></div>
        <div id="maintenanceFeed" class="flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-3.5 pt-3.5 pr-1">
            <p class="text-xs text-white/40 text-center py-8">Loading schedule vectors...</p>
        </div>
    </main>

    <script>
        const API_URL = 'http://localhost/crowdsourcedAPI/api/maintenance_map/get.php';
        let map;
        let layersGroup = L.layerGroup();
        let allMarkers = [];

        // Barangay Coordinate Dictionary
        const barangayData = {
            "Bonuan Gueset": { lat: 16.0585, lng: 120.3345 },
            "Lucao": { lat: 16.0435, lng: 120.3310 },
            "Tapuac": { lat: 16.0460, lng: 120.3450 },
            "Pantal": { lat: 16.0468, lng: 120.3330 },
            // Add remaining barangays here...
        };

        function initMap() {
            map = L.map('map', {
                minZoom: 12,
                maxZoom: 15,
                maxBounds: [[15.95, 120.25], [16.15, 120.45]]
            }).setView([16.0431, 120.3330], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            layersGroup.addTo(map);
        }

        async function fetchMaintenanceData() {
            try {
                const response = await fetch(API_URL);
                const result = await response.json();
                const data = Array.isArray(result) ? result : (result.data || []);

                layersGroup.clearLayers();
                allMarkers = [];
                const feedContainer = document.getElementById('maintenanceFeed');
                feedContainer.innerHTML = '';

                const now = new Date();
                let activeCount = 0;

                data.forEach(item => {
                    const start = new Date(item.start_time);
                    const end = new Date(item.end_time);
                    
                    // STATUS LOGIC
                    let status = (now < start) ? 'pending' : ((now <= end) ? 'ongoing' : 'done');
                    
                    if (status === 'done') return; // Requirement: Hide completed items
                    
                    activeCount++;
                    const color = (status === 'pending') ? '#e74c3c' : '#3498db';

                    // Update Feed
                    feedContainer.innerHTML += `
                        <div class="bg-[#1A1B33] p-4 rounded-xl border-l-4" style="border-left-color: ${color}">
                            <h3 class="font-bold text-sm">${item.company}</h3>
                            <p class="text-[10px] uppercase font-bold text-white/50">${status}</p>
                            <p class="text-xs text-white/80 mt-1">${item.description || 'No description'}</p>
                        </div>`;

                    // Mapping
                    const areas = Array.isArray(item.affected_barangays) ? item.affected_barangays : [item.affected_barangays];
                    areas.forEach(bName => {
                        const coords = barangayData[bName];
                        if (coords) {
                            const marker = L.circle([coords.lat, coords.lng], {
                                radius: item.radius || 2000,
                                color: color,
                                fillColor: color,
                                fillOpacity: 0.25
                            }).addTo(layersGroup).bindPopup(`${bName}: ${item.company} (${status})`);
                            allMarkers.push({ marker, name: bName.toLowerCase() });
                        }
                    });
                });
            } catch (err) {
                console.error("Sync Error:", err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            fetchMaintenanceData();
            setInterval(fetchMaintenanceData, 30000); // 30s Refresh
        });
    </script>
</body>
</html>