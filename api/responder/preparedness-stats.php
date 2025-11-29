<?php
/**
 * PREPAREDNESS STATS - Save as: api/responder/preparedness-stats.php
 */
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'responder') {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
    SUM(CASE WHEN preparedness_level = 'beginner' THEN 1 ELSE 0 END) as beginner,
    SUM(CASE WHEN preparedness_level = 'aware' THEN 1 ELSE 0 END) as aware,
    SUM(CASE WHEN preparedness_level = 'prepared' THEN 1 ELSE 0 END) as prepared,
    SUM(CASE WHEN preparedness_level = 'community-ready' THEN 1 ELSE 0 END) as community_ready
FROM users WHERE role = 'resident'";

$result = $db->query($query)->fetch();
echo json_encode($result);
?>