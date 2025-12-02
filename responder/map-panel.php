<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'responder') {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 700px; border-radius: 8px; }
        .site-list-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }
        .site-list-item:hover {
            background: #f8f9fa;
        }
        .site-list-item.active {
            background: #e3f2fd;
            border-left: 4px solid #0dcaf0;
        }
        .sidebar {
            height: 700px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">👨‍🚒 Responder Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="map-panel.php">Map Panel</a>
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
        <div class="row mb-3">
            <div class="col-md-12">
                <h2>🗺️ Evacuation Site Management</h2>
                <p class="text-muted">View, add, and manage evacuation centers</p>
            </div>
        </div>

        <div class="row">
            <!-- Map Column -->
            <div class="col-md-9">
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
                        ➕ Add New Site
                    </button>
                    <button class="btn btn-info" onclick="showAllSites()">
                        👁️ View All Sites
                    </button>
                    <span class="ms-3">
                        <span class="badge bg-success">Active: <span id="activeCount">0</span></span>
                        <span class="badge bg-secondary">Total: <span id="totalCount">0</span></span>
                    </span>
                </div>
                <div id="map"></div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Evacuation Sites</h5>
                        <input type="text" class="form-control form-control-sm mt-2" 
                               id="searchSites" placeholder="Search sites...">
                    </div>
                    <div class="card-body p-0 sidebar" id="sitesList">
                        <div class="text-center p-3 text-muted">Loading sites...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Site Modal -->
    <div class="modal fade" id="addSiteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Evacuation Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSiteForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Name *</label>
                                <input type="text" class="form-control" id="siteName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type *</label>
                                <select class="form-select" id="siteType" required>
                                    <option value="school">School</option>
                                    <option value="barangay_hall">Barangay Hall</option>
                                    <option value="court">Court</option>
                                    <option value="covered_court">Covered Court</option>
                                    <option value="gym">Gym</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barangay *</label>
                                <input type="text" class="form-control" id="siteBarangay" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="siteCapacity" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <input type="text" class="form-control" id="siteAddress" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude *</label>
                                <input type="number" step="any" class="form-control" id="siteLat" required>
                                <small class="text-muted">Click on map to get coordinates</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude *</label>
                                <input type="number" step="any" class="form-control" id="siteLng" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Facilities</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" value="water" id="fac_water">
                                    <label class="form-check-label" for="fac_water">Water</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" value="electricity" id="fac_elec">
                                    <label class="form-check-label" for="fac_elec">Electricity</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" value="toilets" id="fac_toilet">
                                    <label class="form-check-label" for="fac_toilet">Toilets</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" value="medical" id="fac_medical">
                                    <label class="form-check-label" for="fac_medical">Medical</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" value="communication" id="fac_comm">
                                    <label class="form-check-label" for="fac_comm">Communication</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contactName">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="contactPhone">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="siteNotes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSite()">Save Site</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Site Modal (similar structure) -->
    <div class="modal fade" id="editSiteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Evacuation Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="editSiteContent">
                        <!-- Will be populated dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="deactivateSite()">Deactivate</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateSite()">Update</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([14.5995, 120.9842], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let sites = [];
        let markers = [];
        let selectedSiteId = null;

        // Click on map to get coordinates
        map.on('click', function(e) {
            document.getElementById('siteLat').value = e.latlng.lat.toFixed(6);
            document.getElementById('siteLng').value = e.latlng.lng.toFixed(6);
        });

        // Load all sites
        async function loadSites() {
            try {
                const response = await fetch('../api/evacuation/get-sites.php');
                const data = await response.json();
                
                if (data.success) {
                    sites = data.data;
                    displaySites();
                    document.getElementById('activeCount').textContent = sites.filter(s => s.is_active).length;
                    document.getElementById('totalCount').textContent = sites.length;
                }
            } catch (error) {
                console.error('Error loading sites:', error);
            }
        }

        // Display sites on map and list
        function displaySites() {
            // Clear existing markers
            markers.forEach(m => map.removeLayer(m));
            markers = [];

            // Add markers
            sites.forEach(site => {
                const icon = L.divIcon({
                    html: `<div style="font-size: 30px;">${getIcon(site.type)}</div>`,
                    className: 'custom-marker',
                    iconSize: [30, 30]
                });

                const marker = L.marker([site.latitude, site.longitude], { icon })
                    .addTo(map)
                    .bindPopup(createPopup(site))
                    .on('click', () => selectSite(site.id));

                markers.push(marker);
            });

            // Update sidebar list
            const list = document.getElementById('sitesList');
            list.innerHTML = sites.map(site => `
                <div class="site-list-item" onclick="selectSite(${site.id})" id="site-${site.id}">
                    <div><strong>${site.name}</strong></div>
                    <small class="text-muted">${site.barangay} • ${site.type.replace('_', ' ')}</small>
                </div>
            `).join('');
        }

        function getIcon(type) {
            const icons = {
                school: '🏫',
                barangay_hall: '🏛️',
                court: '🏀',
                covered_court: '🏀',
                gym: '🏋️',
                other: '📍'
            };
            return icons[type] || '📍';
        }

        function createPopup(site) {
            const facilities = Array.isArray(site.facilities) ? site.facilities.join(', ') : 'None';
            return `
                <div>
                    <h6><strong>${site.name}</strong></h6>
                    <p class="mb-1"><strong>Type:</strong> ${site.type.replace('_', ' ')}</p>
                    <p class="mb-1"><strong>Capacity:</strong> ${site.capacity || 'N/A'}</p>
                    <p class="mb-1"><strong>Facilities:</strong> ${facilities}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="editSite(${site.id})">Edit</button>
                </div>
            `;
        }

        function selectSite(siteId) {
            // Remove previous selection
            document.querySelectorAll('.site-list-item').forEach(el => {
                el.classList.remove('active');
            });

            // Add selection
            const element = document.getElementById(`site-${siteId}`);
            if (element) {
                element.classList.add('active');
                element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            selectedSiteId = siteId;

            // Center map on site
            const site = sites.find(s => s.id === siteId);
            if (site) {
                map.setView([site.latitude, site.longitude], 16);
            }
        }

        function showAllSites() {
            if (markers.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds());
            }
        }

        // Save new site
        async function saveSite() {
            const facilities = [];
            ['water', 'elec', 'toilet', 'medical', 'comm'].forEach(fac => {
                if (document.getElementById(`fac_${fac}`).checked) {
                    facilities.push(document.getElementById(`fac_${fac}`).value);
                }
            });

            const data = {
                name: document.getElementById('siteName').value,
                type: document.getElementById('siteType').value,
                barangay: document.getElementById('siteBarangay').value,
                address: document.getElementById('siteAddress').value,
                latitude: document.getElementById('siteLat').value,
                longitude: document.getElementById('siteLng').value,
                capacity: document.getElementById('siteCapacity').value || 0,
                facilities: facilities,
                contact_name: document.getElementById('contactName').value,
                contact_phone: document.getElementById('contactPhone').value,
                notes: document.getElementById('siteNotes').value
            };

            try {
                const response = await fetch('../api/responder/add-site.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Site added successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('addSiteModal')).hide();
                    document.getElementById('addSiteForm').reset();
                    loadSites();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to add site');
            }
        }

        // Search sites
        document.getElementById('searchSites').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.site-list-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(search) ? 'block' : 'none';
            });
        });

        // Load sites on page load
        loadSites();
    </script>
</body>
</html>