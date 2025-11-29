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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drill Mode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .drill-card { cursor: pointer; transition: transform 0.2s; }
        .drill-card:hover { transform: scale(1.05); }
        .points-badge { font-size: 1.5rem; font-weight: bold; }
        .level-badge { 
            padding: 8px 16px; 
            border-radius: 20px; 
            font-weight: bold;
        }
        .task-item { padding: 15px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; }
        .task-completed { background: #d4edda; border-color: #c3e6cb; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🚨 Bantay Bayanihan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="evacuate.php">Evacuate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="drill.php">Drill Mode</a>
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
        <!-- User Progress Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <h2 class="mb-0">🎮 Drill Mode</h2>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="points-badge">⭐ <span id="totalPoints"><?= $user->total_points ?></span></div>
                                <small>Total Points</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="level-badge bg-warning text-dark" id="levelBadge">
                                    <?= strtoupper(str_replace('-', ' ', $user->preparedness_level)) ?>
                                </span>
                            </div>
                            <div class="col-md-3 text-center">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#badgesModal">
                                    🏆 View Badges
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drill Types -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h3>Choose a Drill Type</h3>
            </div>
            <div class="col-md-3">
                <div class="card drill-card" onclick="loadDrill('earthquake')">
                    <div class="card-body text-center">
                        <h1>🌍</h1>
                        <h5>Earthquake</h5>
                        <p class="text-muted">Drop, Cover, Hold</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card drill-card" onclick="loadDrill('flood')">
                    <div class="card-body text-center">
                        <h1>🌊</h1>
                        <h5>Flood</h5>
                        <p class="text-muted">Evacuation prep</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card drill-card" onclick="loadDrill('fire')">
                    <div class="card-body text-center">
                        <h1>🔥</h1>
                        <h5>Fire</h5>
                        <p class="text-muted">Fire safety</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card drill-card" onclick="loadDrill('typhoon')">
                    <div class="card-body text-center">
                        <h1>🌀</h1>
                        <h5>Typhoon</h5>
                        <p class="text-muted">Storm readiness</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drill Content Area -->
        <div id="drillContent" class="row" style="display: none;">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 id="drillTitle"></h4>
                    </div>
                    <div class="card-body">
                        <!-- Tasks -->
                        <div id="tasksList"></div>

                        <!-- Quiz Section -->
                        <div id="quizSection" style="display: none;">
                            <h5>Knowledge Check</h5>
                            <div id="quizQuestions"></div>
                            <button class="btn btn-success mt-3" onclick="submitQuiz()">Submit Quiz</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <div id="progressDetails"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Badges Modal -->
    <div class="modal fade" id="badgesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🏆 Your Badges</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="badgesContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/drill.js"></script>
</body>
</html>