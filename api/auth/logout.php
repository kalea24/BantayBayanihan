<?php
/**
 * LOGOUT - Save as: api/auth/logout.php
 */
session_start();

// Unset all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Redirect
header('Location: ../../index.php');
exit;
?>
