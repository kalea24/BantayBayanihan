<?php
/**
 * Get Nearest Evacuation Sites API
 * GET /api/evacuation/nearest.php?lat=14.5995&lng=120.9842&limit=5
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/database.php';
include_once '../../classes/EvacuationSite.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get query parameters
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

// Validate coordinates
if ($lat === null || $lng === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Latitude and longitude are required'
    ]);
    exit;
}

// Validate coordinate ranges
if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid coordinates'
    ]);
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize evacuation site
$evacuationSite = new EvacuationSite($db);

// Get nearest sites
$sites = $evacuationSite->getNearest($lat, $lng, $limit);

// Process facilities JSON
foreach ($sites as &$site) {
    $site['facilities'] = json_decode($site['facilities'], true);
    $site['distance_km'] = round($site['distance_km'], 2);
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'data' => $sites,
    'user_location' => [
        'lat' => $lat,
        'lng' => $lng
    ]
]);
?>