<?php
/**
 * Statistics Data API
 * GET /api/responder/statistics-data.php
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

// Get filters
$barangay = isset($_GET['barangay']) ? $_GET['barangay'] : '';
$drill_type = isset($_GET['drill_type']) ? $_GET['drill_type'] : '';

// Build WHERE clause
$where_user = "WHERE role = 'resident'";
$where_drill = "WHERE 1=1";

if ($barangay) {
    $where_user .= " AND barangay = :barangay";
    $where_drill .= " AND user_id IN (SELECT id FROM users WHERE barangay = :barangay)";
}

if ($drill_type) {
    $where_drill .= " AND drill_type = :drill_type";
}

// Summary statistics
$summary_query = "SELECT 
    COUNT(*) as total_residents,
    COUNT(CASE WHEN id IN (SELECT DISTINCT user_id FROM drill_progress) THEN 1 END) as active_participants,
    AVG(total_points) as avg_points,
    (COUNT(CASE WHEN id IN (SELECT user_id FROM drill_progress WHERE status = 'completed') THEN 1 END) * 100.0 / COUNT(*)) as completion_rate
FROM users $where_user";

$stmt = $db->prepare($summary_query);
if ($barangay) $stmt->bindParam(':barangay', $barangay);
$stmt->execute();
$summary = $stmt->fetch();

// Preparedness distribution
$prep_query = "SELECT 
    SUM(CASE WHEN preparedness_level = 'beginner' THEN 1 ELSE 0 END) as beginner,
    SUM(CASE WHEN preparedness_level = 'aware' THEN 1 ELSE 0 END) as aware,
    SUM(CASE WHEN preparedness_level = 'prepared' THEN 1 ELSE 0 END) as prepared,
    SUM(CASE WHEN preparedness_level = 'community-ready' THEN 1 ELSE 0 END) as community_ready
FROM users $where_user";

$stmt = $db->prepare($prep_query);
if ($barangay) $stmt->bindParam(':barangay', $barangay);
$stmt->execute();
$preparedness = $stmt->fetch();

// Drill types distribution
$drill_query = "SELECT 
    SUM(CASE WHEN drill_type = 'earthquake' THEN 1 ELSE 0 END) as earthquake,
    SUM(CASE WHEN drill_type = 'flood' THEN 1 ELSE 0 END) as flood,
    SUM(CASE WHEN drill_type = 'fire' THEN 1 ELSE 0 END) as fire,
    SUM(CASE WHEN drill_type = 'typhoon' THEN 1 ELSE 0 END) as typhoon
FROM drill_progress $where_drill";

$stmt = $db->prepare($drill_query);
if ($barangay) $stmt->bindParam(':barangay', $barangay);
if ($drill_type) $stmt->bindParam(':drill_type', $drill_type);
$stmt->execute();
$drill_types = $stmt->fetch();

// By barangay
$barangay_query = "SELECT barangay, COUNT(*) as count 
FROM users 
WHERE role = 'resident' AND barangay IS NOT NULL 
GROUP BY barangay 
ORDER BY count DESC 
LIMIT 10";

$stmt = $db->query($barangay_query);
$by_barangay = $stmt->fetchAll();

// Points distribution
$points_query = "SELECT 
    CASE 
        WHEN total_points = 0 THEN '0'
        WHEN total_points BETWEEN 1 AND 50 THEN '1-50'
        WHEN total_points BETWEEN 51 AND 100 THEN '51-100'
        WHEN total_points BETWEEN 101 AND 200 THEN '101-200'
        ELSE '200+'
    END as range,
    COUNT(*) as count
FROM users
$where_user
GROUP BY range
ORDER BY 
    CASE range
        WHEN '0' THEN 1
        WHEN '1-50' THEN 2
        WHEN '51-100' THEN 3
        WHEN '101-200' THEN 4
        WHEN '200+' THEN 5
    END";

$stmt = $db->prepare($points_query);
if ($barangay) $stmt->bindParam(':barangay', $barangay);
$stmt->execute();
$points_distribution = $stmt->fetchAll();

// Top performers
$top_query = "SELECT 
    CONCAT(first_name, ' ', last_name) as name,
    barangay,
    preparedness_level as level,
    total_points as points,
    (SELECT COUNT(*) FROM drill_progress WHERE user_id = users.id AND status = 'completed') as drills_completed
FROM users
$where_user
ORDER BY total_points DESC
LIMIT 10";

$stmt = $db->prepare($top_query);
if ($barangay) $stmt->bindParam(':barangay', $barangay);
$stmt->execute();
$top_performers = $stmt->fetchAll();

// Return all data
echo json_encode([
    'success' => true,
    'summary' => [
        'total_residents' => (int)$summary['total_residents'],
        'active_participants' => (int)$summary['active_participants'],
        'avg_points' => round($summary['avg_points'], 1),
        'completion_rate' => round($summary['completion_rate'], 1)
    ],
    'preparedness' => $preparedness,
    'drill_types' => $drill_types,
    'by_barangay' => $by_barangay,
    'points_distribution' => $points_distribution,
    'top_performers' => $top_performers
]);
?>