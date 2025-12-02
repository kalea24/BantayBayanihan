<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'resident') {
    header('Location: ../auth/login.php');
    exit;
}

include_once '../config/database.php';
include_once '../classes/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->getById($_SESSION['user_id']);

// Get user's drill progress
$progress_query = "SELECT COUNT(*) as completed_drills, 
                   SUM(total_points) as drill_points 
                   FROM drill_progress 
                   WHERE user_id = ? AND status = 'completed'";
$stmt = $db->prepare($progress_query);
$stmt->execute([$_SESSION['user_id']]);
$progress = $stmt->fetch();

// Get user's badges
$badges_query = "SELECT COUNT(*) as badge_count FROM badges WHERE user_id = ?";
$stmt = $db->prepare($badges_query);
$stmt->execute([$_SESSION['user_id']]);
$badges = $stmt->fetch();

// Get checklist completion
$checklist_query = "SELECT 
                     COUNT(*) as total_items,
                     SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_items
                     FROM checklist_items 
                     WHERE user_id = ?";
$stmt = $db->prepare($checklist_query);
$stmt->execute([$_SESSION['user_id']]);
$checklist = $stmt->fetch();

$checklist_percentage = $checklist['total_items'] > 0 
    ? round(($checklist['completed_items'] / $checklist['total_items']) * 100) 
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard - Bantay Bayanihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .feature-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .progress-ring {
            width: 120px;
            height: 120px;
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
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="evacuate.php">Evacuate</a>
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

    <div class="container mt-4">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Welcome back, <?= htmlspecialchars($user->first_name) ?>! 👋</h2>
                <p class="text-muted">Stay prepared, stay safe. Your safety is our priority.</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card text-center">
                    <div class="feature-icon">⭐</div>
                    <h3><?= $user->total_points ?></h3>
                    <p class="mb-0">Total Points</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="feature-icon">🏆</div>
                    <h3><?= $badges['badge_count'] ?></h3>
                    <p class="mb-0">Badges Earned</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="feature-icon">✅</div>
                    <h3><?= $progress['completed_drills'] ?? 0 ?></h3>
                    <p class="mb-0">Drills Completed</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="feature-icon">📋</div>
                    <h3><?= $checklist_percentage ?>%</h3>
                    <p class="mb-0">Checklist Done</p>
                </div>
            </div>
        </div>

        <!-- Preparedness Level -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Your Preparedness Level</h5>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">
                                    <span class="badge bg-warning text-dark p-3">
                                        <?= strtoupper(str_replace('-', ' ', $user->preparedness_level)) ?>
                                    </span>
                                </h3>
                            </div>
                            <div>
                                <p class="mb-0 text-muted">Keep earning points to level up!</p>
                                <div class="progress" style="width: 300px; height: 25px;">
                                    <?php
                                    $levels = ['beginner' => 0, 'aware' => 50, 'prepared' => 100, 'community-ready' => 200];
                                    $current_level = $levels[$user->preparedness_level];
                                    $next_level = 200;
                                    foreach ($levels as $level => $points) {
                                        if ($points > $current_level) {
                                            $next_level = $points;
                                            break;
                                        }
                                    }
                                    $percentage = ($user->total_points / $next_level) * 100;
                                    ?>
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= min($percentage, 100) ?>%">
                                        <?= $user->total_points ?> / <?= $next_level ?> pts
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Features -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-3">Quick Access</h4>
            </div>

            <div class="col-md-4">
                <div class="card feature-card" onclick="window.location.href='evacuate.php'">
                    <div class="card-body text-center">
                        <div class="feature-icon">🗺️</div>
                        <h5>Evacuate</h5>
                        <p class="text-muted">Find nearest evacuation centers and get directions</p>
                        <a href="evacuate.php" class="btn btn-danger">Go to Map</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card" onclick="window.location.href='drill.php'">
                    <div class="card-body text-center">
                        <div class="feature-icon">🎮</div>
                        <h5>Drill Mode</h5>
                        <p class="text-muted">Practice emergency procedures and earn points</p>
                        <a href="drill.php" class="btn btn-primary">Start Drill</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card" onclick="window.location.href='chat.php'">
                    <div class="card-body text-center">
                        <div class="feature-icon">🤖</div>
                        <h5>Bantay AI</h5>
                        <p class="text-muted">Ask questions about disaster preparedness</p>
                        <a href="chat.php" class="btn btn-success">Chat Now</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips & Reminders -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">⚠️ Safety Reminders</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>Keep your emergency kit ready and up-to-date</li>
                            <li>Know your evacuation routes and meeting points</li>
                            <li>Save emergency hotlines in your phone (911, Red Cross: 143)</li>
                            <li>Monitor weather updates and alerts</li>
                            <li>Participate in community drills regularly</li>
                            <li>Share preparedness information with family and neighbors</li>
                        </ul>
                        <div class="mt-3">
                            <strong>Emergency Hotlines:</strong>
                            <span class="badge bg-danger ms-2">911 - National Emergency</span>
                            <span class="badge bg-danger ms-2">143 - Red Cross</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>