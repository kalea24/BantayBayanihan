<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'responder') {
    header('Location: ../auth/login.php');
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'resident') as total_residents,
    (SELECT COUNT(DISTINCT user_id) FROM drill_progress) as drill_participants,
    (SELECT COUNT(*) FROM evacuation_sites WHERE is_active = 1) as active_sites,
    (SELECT COUNT(*) FROM users WHERE preparedness_level = 'community-ready') as ready_residents";
$stats = $db->query($stats_query)->fetch();

$participation_rate = $stats['total_residents'] > 0 
    ? round(($stats['drill_participants'] / $stats['total_residents']) * 100, 1) 
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 3rem; }
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
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistics.php">Statistics</a>
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
        <h2>Community Readiness Dashboard</h2>
        <p class="text-muted">Overview of disaster preparedness in your barangay</p>

        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card stat-card border-primary">
                    <div class="card-body text-center">
                        <div class="stat-icon text-primary">👥</div>
                        <h3><?= $stats['total_residents'] ?></h3>
                        <p class="text-muted mb-0">Total Residents</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-success">
                    <div class="card-body text-center">
                        <div class="stat-icon text-success">✅</div>
                        <h3><?= $participation_rate ?>%</h3>
                        <p class="text-muted mb-0">Drill Participation</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-warning">
                    <div class="card-body text-center">
                        <div class="stat-icon text-warning">🏆</div>
                        <h3><?= $stats['ready_residents'] ?></h3>
                        <p class="text-muted mb-0">Community Ready</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-info">
                    <div class="card-body text-center">
                        <div class="stat-icon text-info">📍</div>
                        <h3><?= $stats['active_sites'] ?></h3>
                        <p class="text-muted mb-0">Evacuation Sites</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Preparedness Level Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="preparednessChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Drill Participation by Type</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="drillChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent User Registrations</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Barangay</th>
                                    <th>Preparedness Level</th>
                                    <th>Points</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users_query = "SELECT first_name, last_name, barangay, preparedness_level, 
                                               total_points, created_at 
                                               FROM users WHERE role = 'resident' 
                                               ORDER BY created_at DESC LIMIT 10";
                                $users = $db->query($users_query)->fetchAll();
                                
                                foreach ($users as $user):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['barangay'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= ucfirst(str_replace('-', ' ', $user['preparedness_level'])) ?>
                                        </span>
                                    </td>
                                    <td>⭐ <?= $user['total_points'] ?></td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preparedness Level Chart
        const prepCtx = document.getElementById('preparednessChart').getContext('2d');
        
        fetch('../api/responder/preparedness-stats.php')
            .then(r => r.json())
            .then(data => {
                new Chart(prepCtx, {
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
                    }
                });
            });

        // Drill Participation Chart
        const drillCtx = document.getElementById('drillChart').getContext('2d');
        
        fetch('../api/responder/drill-stats.php')
            .then(r => r.json())
            .then(data => {
                new Chart(drillCtx, {
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
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            });
    </script>
</body>
</html>