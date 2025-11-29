<?php
/**
 * DRILL STATS - Save as: api/responder/drill-stats.php
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
    SUM(CASE WHEN drill_type = 'earthquake' THEN 1 ELSE 0 END) as earthquake,
    SUM(CASE WHEN drill_type = 'flood' THEN 1 ELSE 0 END) as flood,
    SUM(CASE WHEN drill_type = 'fire' THEN 1 ELSE 0 END) as fire,
    SUM(CASE WHEN drill_type = 'typhoon' THEN 1 ELSE 0 END) as typhoon
FROM drill_progress";

$result = $db->query($query)->fetch();
echo json_encode($result);
?>
