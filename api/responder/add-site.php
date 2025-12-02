<?php
/**
 * Add Evacuation Site API
 * POST /api/responder/add-site.php
 */

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'responder') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include_once '../../config/database.php';
include_once '../../classes/EvacuationSite.php';

$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (empty($data->name) || empty($data->type) || empty($data->barangay) || 
    empty($data->address) || empty($data->latitude) || empty($data->longitude)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$evacuationSite = new EvacuationSite($db);

$evacuationSite->name = $data->name;
$evacuationSite->type = $data->type;
$evacuationSite->barangay = $data->barangay;
$evacuationSite->address = $data->address;
$evacuationSite->latitude = $data->latitude;
$evacuationSite->longitude = $data->longitude;
$evacuationSite->capacity = $data->capacity ?? 0;
$evacuationSite->facilities = $data->facilities ?? [];
$evacuationSite->wheelchair_accessible = $data->wheelchair_accessible ?? false;
$evacuationSite->has_parking = $data->has_parking ?? false;
$evacuationSite->contact_name = $data->contact_name ?? null;
$evacuationSite->contact_phone = $data->contact_phone ?? null;
$evacuationSite->notes = $data->notes ?? null;

if ($evacuationSite->create()) {
    echo json_encode([
        'success' => true,
        'message' => 'Evacuation site added successfully',
        'site_id' => $evacuationSite->id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add site']);
}
?>