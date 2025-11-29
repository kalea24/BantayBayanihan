<?php
/**
 * Session Debug Tool
 * Place in root: debug-session.php
 * Access: http://localhost/disaster-prep/debug-session.php
 * 
 * DELETE THIS FILE AFTER DEBUGGING!
 */

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3>🔧 Session Debug Tool</h3>
                <p class="mb-0">Use this to diagnose session issues</p>
            </div>
            <div class="card-body">
                
                <h5>Session Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Session Status</th>
                        <td>
                            <?php 
                            $status = session_status();
                            if ($status === PHP_SESSION_ACTIVE) {
                                echo '<span class="badge bg-success">ACTIVE</span>';
                            } elseif ($status === PHP_SESSION_NONE) {
                                echo '<span class="badge bg-warning">NONE</span>';
                            } else {
                                echo '<span class="badge bg-danger">DISABLED</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Session ID</th>
                        <td><?= session_id() ?: '<span class="text-muted">No session ID</span>' ?></td>
                    </tr>
                    <tr>
                        <th>Session Name</th>
                        <td><?= session_name() ?></td>
                    </tr>
                    <tr>
                        <th>Session Save Path</th>
                        <td><?= session_save_path() ?: '<span class="text-muted">Default</span>' ?></td>
                    </tr>
                    <tr>
                        <th>Cookie Params</th>
                        <td>
                            <pre><?php print_r(session_get_cookie_params()); ?></pre>
                        </td>
                    </tr>
                </table>

                <h5 class="mt-4">Session Data</h5>
                <?php if (empty($_SESSION)): ?>
                    <div class="alert alert-warning">
                        ⚠️ No session data found. You are not logged in.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        ✅ Session data exists
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION as $key => $value): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($key) ?></code></td>
                                    <td><code><?= htmlspecialchars(print_r($value, true)) ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h5 class="mt-4">Cookie Information</h5>
                <?php if (empty($_COOKIE)): ?>
                    <div class="alert alert-warning">No cookies set</div>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cookie Name</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_COOKIE as $name => $value): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($name) ?></code></td>
                                    <td><code><?= htmlspecialchars(substr($value, 0, 50)) ?>...</code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h5 class="mt-4">PHP Configuration</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>session.use_cookies</th>
                        <td><?= ini_get('session.use_cookies') ? 'Yes' : 'No' ?></td>
                    </tr>
                    <tr>
                        <th>session.use_only_cookies</th>
                        <td><?= ini_get('session.use_only_cookies') ? 'Yes' : 'No' ?></td>
                    </tr>
                    <tr>
                        <th>session.cookie_httponly</th>
                        <td><?= ini_get('session.cookie_httponly') ? 'Yes' : 'No' ?></td>
                    </tr>
                    <tr>
                        <th>session.cookie_lifetime</th>
                        <td><?= ini_get('session.cookie_lifetime') ?> seconds</td>
                    </tr>
                </table>

                <div class="mt-4">
                    <h5>Quick Actions</h5>
                    <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                    <a href="auth/login.php" class="btn btn-success">Go to Login</a>
                    <a href="api/auth/logout.php" class="btn btn-danger">Logout</a>
                    <button onclick="location.reload()" class="btn btn-secondary">Refresh</button>
                </div>

                <div class="alert alert-info mt-4">
                    <strong>Debugging Steps:</strong>
                    <ol>
                        <li>Check if session status is ACTIVE</li>
                        <li>After login, refresh this page - you should see session data</li>
                        <li>Click logout, then refresh - session data should be gone</li>
                        <li>Try logging in again - session should be recreated</li>
                    </ol>
                </div>

                <div class="alert alert-danger mt-3">
                    <strong>⚠️ SECURITY WARNING:</strong> Delete this file after debugging!<br>
                    Run: <code>del C:\xampp\htdocs\disaster-prep\debug-session.php</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>