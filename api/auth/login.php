<?php
/**
 * Login API Endpoint
 * POST /api/auth/login.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/database.php';
include_once '../../classes/User.php';

// Start session - regenerate ID for security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any existing session data
session_unset();
session_regenerate_id(true);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user
$user = new User($db);

// Attempt login
if ($user->login($data->email, $data->password)) {
    // Set session variables
    $_SESSION['user_id'] = $user->id;
    $_SESSION['email'] = $user->email;
    $_SESSION['role'] = $user->role;
    $_SESSION['first_name'] = $user->first_name;
    $_SESSION['last_name'] = $user->last_name;
    $_SESSION['logged_in'] = true;

    // Get user badges
    $badge_query = "SELECT name, description, earned_at FROM badges WHERE user_id = ?";
    $badge_stmt = $db->prepare($badge_query);
    $badge_stmt->execute([$user->id]);
    $badges = $badge_stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'preparedness_level' => $user->preparedness_level,
            'total_points' => $user->total_points,
            'badges' => $badges
        ],
        'session_id' => session_id()
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}
?>