<?php
/**
 * Registration API Endpoint
 * POST /api/auth/register.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/database.php';
include_once '../../classes/User.php';

// Start session
session_start();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (empty($data->email) || empty($data->password) || 
    empty($data->first_name) || empty($data->last_name)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide all required fields'
    ]);
    exit;
}

// Validate email format
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate password length
if (strlen($data->password) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user
$user = new User($db);

// Check if email already exists
if ($user->emailExists($data->email)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email already registered'
    ]);
    exit;
}

// Set user properties
$user->first_name = $data->first_name;
$user->last_name = $data->last_name;
$user->email = $data->email;
$user->password = $data->password;
$user->role = $data->role ?? 'resident';
$user->phone = $data->phone ?? null;
$user->barangay = $data->barangay ?? null;
$user->purok = $data->purok ?? null;

// Start transaction for atomic operation
try {
    $db->beginTransaction();
    
    // Create user
    if ($user->register()) {
        // Set session variables
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] = $user->role;
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;
        $_SESSION['logged_in'] = true;

        // Create default checklist items for new user
        $checklist_template = '{"emergency_kit": ["Water (3-day supply)", "Food (3-day supply)", "First aid kit", "Flashlight"], "evacuation_plan": ["Know evacuation routes", "Family meeting point"], "communication": ["ICE contacts in phone", "Battery backup"], "documents": ["IDs and important papers"], "supplies": ["Medications", "Cash"]}';
        
        $template = json_decode($checklist_template, true);
        foreach ($template as $category => $items) {
            foreach ($items as $item) {
                $query = "INSERT INTO checklist_items (user_id, category, item_name) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$user->id, $category, $item]);
            }
        }

        // Commit transaction
        $db->commit();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'preparedness_level' => 'beginner',
                'total_points' => 0
            ],
            'session_id' => session_id()
        ]);
    } else {
        $db->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ]);
    }
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>