
<?php
/**
 * GET BADGES - save as: api/drill/get-badges.php
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

$database = new Database();
$db = $database->getConnection();

$query = "SELECT name, description, earned_at FROM badges 
          WHERE user_id = ? ORDER BY earned_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$badges = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $badges]);
?>