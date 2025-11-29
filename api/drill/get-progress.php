<?php
/**
 * GET PROGRESS - save as: api/drill/get-progress.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

include_once '../../config/database.php';
include_once '../../classes/DrillProgress.php';

$drill_type = $_GET['drill_type'] ?? null;

if (!$drill_type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Drill type required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$drillProgress = new DrillProgress($db);

$progress = $drillProgress->getOrCreate($_SESSION['user_id'], $drill_type);

echo json_encode(['success' => true, 'data' => $progress]);
?>