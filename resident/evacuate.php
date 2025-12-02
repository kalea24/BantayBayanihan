<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'resident') {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evacuation Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { 
            height: 600px; 
            width: 100%; 
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .site-info-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .distance-badge {
            font-size: 1.2rem;
            padding: 8px 16px;
        }
        .leaflet-popup-content {
            margin: 15px;
        }
        .control-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Bantay Bayanihan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="evacuate.php">Evacuate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drill.php">Drill Mode</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chat.php">Bantay AI</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">👤 <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../api/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Map Column -->
            <div class="col-md-9">
                <div class="control-panel mb-3">
                    <h4>🗺️ Evacuation Centers Map</h4>
                    <p class="text-muted mb-3">Find the nearest safe evacuation center in your area</p>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" id="findNearestBtn" onclick="findNearestSites()">
                            📍 Find Nearest Centers
                        </button>
                        <button class="btn btn-success" id="myLocationBtn" onclick="goToMyLocation()">
                            🎯 Go to My Location
                        </button>
                        <button class="btn btn-info" onclick="showAllSites()">
                            👁️ Show All Sites
                        </button>
                        <button class="btn btn-secondary" onclick="clearRoutes()">
                            🗑️ Clear Routes
                        </button>
                    </div>
                </div>

                <!-- Map Container -->
                <div id="map"></div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-md-3">
                <div class="control-panel">
                    <h5>📍 Nearest Centers</h5>
                    <p class="small text-muted">Click "Find Nearest Centers" to see results</p>
                    <div id="nearestSitesList">
                        <div class="text-center text-muted py-4">
                            <p>Use your location to find nearest evacuation centers</p>
                        </div>
                    </div>
                </div>

                <div class="control-panel mt-3">
                    <h5>ℹ️ Legend</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">🏫 <strong>School</strong></li>
                        <li class="mb-2">🏛️ <strong>Barangay Hall</strong></li>
                        <li class="mb-2">🏀 <strong>Court/Gym</strong></li>
                        <li class="mb-2">📍 <strong>You</strong> - Your location</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map (default center: Baguio City)
        const map = L.map('map').setView([16.4023, 120.5960], 13);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Store markers and user location
        let markers = [];
        let userMarker = null;
        let userLocation = null;
        let routeLines = [];

        // Custom icons
        const siteIcons = {
            school: '🏫',
            barangay_hall: '🏛️',
            court: '🏀',
            covered_court: '🏀',
            gym: '🏋️',
            other: '📍'
        };

        // Load all evacuation sites
        async function loadAllSites() {
            try {
                const response = await fetch('../api/evacuation/get-sites.php');
                const data = await response.json();
                
                if (data.success) {
                    data.data.forEach(site => {
                        addSiteMarker(site);
                    });
                    
                    console.log(`Loaded ${data.data.length} evacuation sites`);
                }
            } catch (error) {
                console.error('Error loading sites:', error);
                alert('Failed to load evacuation sites. Please refresh the page.');
            }
        }

        // Add site marker to map
        function addSiteMarker(site) {
            const icon = siteIcons[site.type] || '📍';
            
            // Create custom div icon
            const customIcon = L.divIcon({
                html: `<div style="font-size: 30px;">${icon}</div>`,
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            const marker = L.marker([site.latitude, site.longitude], { icon: customIcon })
                .addTo(map);
            
            // Popup content
            const facilities = Array.isArray(site.facilities) 
                ? site.facilities.join(', ') 
                : 'Not specified';
            
            marker.bindPopup(`
                <div style="min-width: 200px;">
                    <h6><strong>${site.name}</strong></h6>
                    <p class="mb-1"><strong>Type:</strong> ${site.type.replace('_', ' ')}</p>
                    <p class="mb-1"><strong>Address:</strong> ${site.address}</p>
                    <p class="mb-1"><strong>Barangay:</strong> ${site.barangay}</p>
                    <p class="mb-1"><strong>Capacity:</strong> ${site.capacity || 'N/A'} people</p>
                    <p class="mb-1"><strong>Facilities:</strong> ${facilities}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="getDirections(${site.latitude}, ${site.longitude}, '${site.name}')">
                        Get Directions
                    </button>
                </div>
            `);
            
            markers.push({ marker, site });
        }
        
        // Find nearest sites
        async function findNearestSites() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            document.getElementById('findNearestBtn').disabled = true;
            document.getElementById('findNearestBtn').innerHTML = '🔄 Finding...';

            navigator.geolocation.getCurrentPosition(async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                userLocation = { lat, lng };

                // Add user marker
                if (userMarker) {
                    map.removeLayer(userMarker);
                }

                const userIcon = L.divIcon({
                    html: '<div style="font-size: 40px;">📍</div>',
                    className: 'user-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                });

                userMarker = L.marker([lat, lng], { icon: userIcon })
                    .addTo(map)
                    .bindPopup('<strong>You are here</strong>')
                    .openPopup();

                // Center map on user
                map.setView([lat, lng], 14);

                try {
                    // Get nearest sites from API
                    const response = await fetch(
                        `../api/evacuation/nearest.php?lat=${lat}&lng=${lng}&limit=5`
                    );
                    const data = await response.json();

                    if (data.success) {
                        displayNearestSites(data.data);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to find nearest sites');
                }

                document.getElementById('findNearestBtn').disabled = false;
                document.getElementById('findNearestBtn').innerHTML = '📍 Find Nearest Centers';
            }, function(error) {
                alert('Unable to get your location. Please enable location services.');
                document.getElementById('findNearestBtn').disabled = false;
                document.getElementById('findNearestBtn').innerHTML = '📍 Find Nearest Centers';
            });
        }

        // Display nearest sites in sidebar
        function displayNearestSites(sites) {
            const list = document.getElementById('nearestSitesList');
            list.innerHTML = '';

            sites.forEach((site, index) => {
                const facilities = Array.isArray(site.facilities) 
                    ? site.facilities.join(', ') 
                    : 'Not specified';

                const siteCard = `
                    <div class="site-info-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">${index + 1}. ${site.name}</h6>
                            <span class="badge bg-primary distance-badge">${site.distance_km} km</span>
                        </div>
                        <p class="small mb-1"><strong>📍</strong> ${site.barangay}</p>
                        <p class="small mb-2"><strong>🏢</strong> ${site.type.replace('_', ' ')}</p>
                        <button class="btn btn-sm btn-success w-100" onclick="getDirections(${site.latitude}, ${site.longitude}, '${site.name}')">
                            🧭 Get Directions
                        </button>
                    </div>
                `;
                list.innerHTML += siteCard;
            });
        }

        // Get directions
        function getDirections(toLat, toLng, siteName) {
            if (!userLocation) {
                alert('Please find your location first by clicking "Find Nearest Centers"');
                return;
            }

            // Clear previous routes
            clearRoutes();

            // Draw line
            const routeLine = L.polyline([
                [userLocation.lat, userLocation.lng],
                [toLat, toLng]
            ], {
                color: 'blue',
                weight: 4,
                opacity: 0.7
            }).addTo(map);

            routeLines.push(routeLine);

            // Fit bounds to show both points
            map.fitBounds([
                [userLocation.lat, userLocation.lng],
                [toLat, toLng]
            ]);

            // Calculate distance
            const distance = map.distance([userLocation.lat, userLocation.lng], [toLat, toLng]);
            const distanceKm = (distance / 1000).toFixed(2);
            const estimatedTime = Math.ceil(distanceKm * 15); // 15 min per km walking

            alert(`Route to ${siteName}\n\nDistance: ${distanceKm} km\nEstimated walking time: ${estimatedTime} minutes`);
        }

        // Go to user location
        function goToMyLocation() {
            if (userLocation) {
                map.setView([userLocation.lat, userLocation.lng], 15);
                if (userMarker) {
                    userMarker.openPopup();
                }
            } else {
                alert('Please find your location first by clicking "Find Nearest Centers"');
            }
        }

        // Show all sites
        function showAllSites() {
            if (markers.length > 0) {
                const group = L.featureGroup(markers.map(m => m.marker));
                map.fitBounds(group.getBounds());
            }
        }

        // Clear routes
        function clearRoutes() {
            routeLines.forEach(line => map.removeLayer(line));
            routeLines = [];
        }

        // Load sites on page load
        loadAllSites();
    </script>
</body>
</html>