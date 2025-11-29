<?php
/**
 * Get All Evacuation Sites API
 * GET /api/evacuation/get-sites.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/database.php';
include_once '../../classes/EvacuationSite.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize evacuation site
$evacuationSite = new EvacuationSite($db);

// Get all sites
$sites = $evacuationSite->getAll();

// Process facilities JSON
foreach ($sites as &$site) {
    $site['facilities'] = json_decode($site['facilities'], true);
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'data' => $sites,
    'count' => count($sites)
]);
?>