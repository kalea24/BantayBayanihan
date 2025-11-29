<?php
/**
 * SUBMIT QUIZ - save as: api/drill/submit-quiz.php
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

$data = json_decode(file_get_contents("php://input"));

$database = new Database();
$db = $database->getConnection();
$drillProgress = new DrillProgress($db);

$result = $drillProgress->submitQuiz(
    $_SESSION['user_id'],
    $data->drill_type,
    $data->quiz_id,
    $data->score,
    $data->total_questions
);

echo json_encode($result);
?>

