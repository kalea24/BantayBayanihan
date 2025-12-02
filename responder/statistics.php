<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'responder') {
    header('Location: ../auth/login.php');
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get barangay list for filter
$barangay_query = "SELECT DISTINCT barangay FROM users WHERE barangay IS NOT NULL ORDER BY barangay";
$barangays = $db->query($barangay_query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Responder Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="map-panel.php">Map Panel</a>
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
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>📊 Community Preparedness Statistics</h2>
                <p class="text-muted">Detailed analytics on disaster preparedness in your community</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Filters</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Barangay</label>
                                <select class="form-select" id="barangayFilter">
                                    <option value="">All Barangays</option>
                                    <?php foreach ($barangays as $brgy): ?>
                                        <option value="<?= htmlspecialchars($brgy['barangay']) ?>">
                                            <?= htmlspecialchars($brgy['barangay']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Drill Type</label>
                                <select class="form-select" id="drillFilter">
                                    <option value="">All Types</option>
                                    <option value="earthquake">Earthquake</option>
                                    <option value="flood">Flood</option>
                                    <option value="fire">Fire</option>
                                    <option value="typhoon">Typhoon</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label><br>
                                <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                                <button class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="totalResidents">-</h3>
                        <p class="text-muted mb-0">Total Residents</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="activeParticipants">-</h3>
                        <p class="text-muted mb-0">Active Participants</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="avgPoints">-</h3>
                        <p class="text-muted mb-0">Avg Points</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="completionRate">-</h3>
                        <p class="text-muted mb-0">Drill Completion Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Preparedness Level Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="preparednessChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Drill Participation by Type</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="drillTypeChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Participation by Barangay</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="barangayChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Points Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="pointsChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performers Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>🏆 Top Performers</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Barangay</th>
                                    <th>Level</th>
                                    <th>Points</th>
                                    <th>Drills Completed</th>
                                </tr>
                            </thead>
                            <tbody id="topPerformersTable">
                                <tr>
                                    <td colspan="6" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let charts = {};

        // Load all statistics
        async function loadStatistics(barangay = '', drillType = '') {
            try {
                // Load summary stats
                const params = new URLSearchParams();
                if (barangay) params.append('barangay', barangay);
                if (drillType) params.append('drill_type', drillType);

                const response = await fetch(`../api/responder/statistics-data.php?${params}`);
                const data = await response.json();

                // Update summary cards
                document.getElementById('totalResidents').textContent = data.summary.total_residents;
                document.getElementById('activeParticipants').textContent = data.summary.active_participants;
                document.getElementById('avgPoints').textContent = Math.round(data.summary.avg_points);
                document.getElementById('completionRate').textContent = data.summary.completion_rate + '%';

                // Update charts
                updatePreparednessChart(data.preparedness);
                updateDrillTypeChart(data.drill_types);
                updateBarangayChart(data.by_barangay);
                updatePointsChart(data.points_distribution);

                // Update top performers
                updateTopPerformers(data.top_performers);

            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        function updatePreparednessChart(data) {
            const ctx = document.getElementById('preparednessChart').getContext('2d');
            
            if (charts.preparedness) {
                charts.preparedness.destroy();
            }

            charts.preparedness = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Beginner', 'Aware', 'Prepared', 'Community Ready'],
                    datasets: [{
                        data: [
                            data.beginner || 0,
                            data.aware || 0,
                            data.prepared || 0,
                            data.community_ready || 0
                        ],
                        backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0', '#198754']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function updateDrillTypeChart(data) {
            const ctx = document.getElementById('drillTypeChart').getContext('2d');
            
            if (charts.drillType) {
                charts.drillType.destroy();
            }

            charts.drillType = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Earthquake', 'Flood', 'Fire', 'Typhoon'],
                    datasets: [{
                        label: 'Participants',
                        data: [
                            data.earthquake || 0,
                            data.flood || 0,
                            data.fire || 0,
                            data.typhoon || 0
                        ],
                        backgroundColor: '#198754'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function updateBarangayChart(data) {
            const ctx = document.getElementById('barangayChart').getContext('2d');
            
            if (charts.barangay) {
                charts.barangay.destroy();
            }

            charts.barangay = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.barangay),
                    datasets: [{
                        label: 'Residents',
                        data: data.map(d => d.count),
                        backgroundColor: '#0dcaf0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function updatePointsChart(data) {
            const ctx = document.getElementById('pointsChart').getContext('2d');
            
            if (charts.points) {
                charts.points.destroy();
            }

            charts.points = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.range),
                    datasets: [{
                        label: 'Number of Residents',
                        data: data.map(d => d.count),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function updateTopPerformers(data) {
            const tbody = document.getElementById('topPerformersTable');
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No data available</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((user, index) => `
                <tr>
                    <td><strong>${index + 1}</strong></td>
                    <td>${user.name}</td>
                    <td>${user.barangay || 'N/A'}</td>
                    <td><span class="badge bg-info">${user.level}</span></td>
                    <td>⭐ ${user.points}</td>
                    <td>${user.drills_completed}</td>
                </tr>
            `).join('');
        }

        function applyFilters() {
            const barangay = document.getElementById('barangayFilter').value;
            const drillType = document.getElementById('drillFilter').value;
            loadStatistics(barangay, drillType);
        }

        function resetFilters() {
            document.getElementById('barangayFilter').value = '';
            document.getElementById('drillFilter').value = '';
            loadStatistics();
        }

        // Load on page load
        loadStatistics();
    </script>
</body>
</html>