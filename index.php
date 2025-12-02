<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role'] === 'responder') {
        header('Location: responder/index.php');
    } else {
        header('Location: resident/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantay Bayanihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bantay Bayanihan</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="auth/login.php">Login</a>
                <a class="nav-link" href="auth/register.php">Register</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h1>Welcome to Bantay Bayanihan</h1>
                <p class="lead">Enhance community safety through GIS mapping, AI assistance, and gamified preparedness training.</p>
                
                <div class="row mt-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h3>🏘️ Resident Portal</h3>
                                <p>Find evacuation routes, participate in drills, and chat with Bantay AI</p>
                                <a href="auth/register.php?role=resident" class="btn btn-primary">Get Started</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h3>👨‍🚒 Responder Portal</h3>
                                <p>Monitor community readiness and manage evacuation sites</p>
                                <a href="auth/login.php" class="btn btn-success">Access Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>