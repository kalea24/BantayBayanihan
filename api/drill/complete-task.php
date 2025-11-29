<?php
/**
 * Complete Drill Task API
 * POST /api/drill/complete-task.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

include_once '../../config/database.php';
include_once '../../classes/DrillProgress.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->drill_type) || empty($data->task_id) || empty($data->task_name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$drillProgress = new DrillProgress($db);

$result = $drillProgress->completeTask(
    $_SESSION['user_id'],
    $data->drill_type,
    $data->task_id,
    $data->task_name,
    $data->points_earned
);

echo json_encode($result);
?>