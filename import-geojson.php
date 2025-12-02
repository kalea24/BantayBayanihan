<?php
/**
 * Import GeoJSON Evacuation Sites to Database
 * Run this once: http://localhost/disaster-prep/import-geojson.php
 */

include_once 'config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Your GeoJSON files
$geojson_files = [
    'schools' => 'assets/geojson/evac_schools.geojson',
    'brgy_halls' => 'assets/geojson/evac_brgy_hall.geojson',
    'courts' => 'assets/geojson/evac_courts.geojson'
];

$total_imported = 0;
$errors = [];

foreach ($geojson_files as $type => $filepath) {
    // Check if file exists
    if (!file_exists($filepath)) {
        $errors[] = "File not found: $filepath";
        continue;
    }

    // Read GeoJSON file
    $geojson = json_decode(file_get_contents($filepath), true);

    if (!$geojson || !isset($geojson['features'])) {
        $errors[] = "Invalid GeoJSON format: $filepath";
        continue;
    }

    // Map type names - YOUR GEOJSON USES EXACT NAMES
    $type_map = [
        'schools' => 'school',
        'brgy_halls' => 'barangay_hall',
        'courts' => 'court'
    ];
    
    // Determine type from properties if available
    $site_type = $type_map[$type];
    
    // Try to get more specific type from Type field
    if (isset($props['Type'])) {
        $type_value = strtolower($props['Type']);
        if (strpos($type_value, 'covered court') !== false) {
            $site_type = 'covered_court';
        } elseif (strpos($type_value, 'barangay hall') !== false) {
            $site_type = 'barangay_hall';
        } elseif (strpos($type_value, 'school') !== false) {
            $site_type = 'school';
        } elseif (strpos($type_value, 'court') !== false) {
            $site_type = 'court';
        }
    }

    // Insert each feature
    foreach ($geojson['features'] as $feature) {
        try {
            $props = $feature['properties'];
            $coords = $feature['geometry']['coordinates'];
            
            // GeoJSON uses [longitude, latitude]
            $lng = $coords[0];
            $lat = $coords[1];

            // Extract name - YOUR GEOJSON USES "Facility"
            $name = $props['Facility'] ?? 
                    $props['FACILITY'] ?? 
                    $props['facility'] ??
                    $props['name'] ?? 
                    $props['NAME'] ?? 
                    $props['sitename'] ?? 
                    $props['SITENAME'] ?? 
                    $props['site_name'] ?? 
                    $props['SITE_NAME'] ??
                    $props['Place_Name'] ??
                    $props['placename'] ??
                    $props['School'] ??
                    $props['SCHOOL'] ??
                    'Unnamed Site';
            
            // Clean up the name if needed
            $name = trim($name);
            
            // If still unnamed, try to construct from type + barangay
            if ($name === 'Unnamed Site' || empty($name)) {
                $possibleName = ($props['type'] ?? $props['TYPE'] ?? $props['Type'] ?? '') . ' ' . 
                               ($props['barangay'] ?? $props['BARANGAY'] ?? $props['Barangay'] ?? '');
                if (trim($possibleName) !== '') {
                    $name = trim($possibleName);
                }
            }

            // Extract barangay - YOUR GEOJSON USES "Barangay"
            $barangay = $props['Barangay'] ??
                       $props['BARANGAY'] ?? 
                       $props['barangay'] ?? 
                       $props['brgy'] ?? 
                       $props['BRGY'] ??
                       'Unknown';

            // Extract address (use Facility as address if no specific address field)
            $address = $props['address'] ?? 
                      $props['ADDRESS'] ?? 
                      $props['location'] ??
                      $props['LOCATION'] ??
                      $props['Facility'] ??
                      $barangay;

            // Extract capacity if available
            $capacity = isset($props['capacity']) ? intval($props['capacity']) : 
                       (isset($props['CAPACITY']) ? intval($props['CAPACITY']) : 
                       (isset($props['Capacity']) ? intval($props['Capacity']) : 0));

            // Prepare SQL
            $query = "INSERT INTO evacuation_sites 
                      (name, type, barangay, address, location, capacity, facilities, is_active)
                      VALUES 
                      (:name, :type, :barangay, :address, 
                       ST_GeomFromText(CONCAT('POINT(', :lng, ' ', :lat, ')')),
                       :capacity, :facilities, 1)";

            $stmt = $db->prepare($query);

            // Default facilities based on type
            $default_facilities = [
                'school' => '["water", "electricity", "toilets"]',
                'barangay_hall' => '["water", "electricity", "toilets", "communication"]',
                'court' => '["water", "toilets"]',
                'covered_court' => '["water", "toilets"]'
            ];

            $facilities = $default_facilities[$site_type] ?? '[]';

            // Bind and execute
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':type', $site_type);
            $stmt->bindParam(':barangay', $barangay);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':lng', $lng);
            $stmt->bindParam(':lat', $lat);
            $stmt->bindParam(':capacity', $capacity);
            $stmt->bindParam(':facilities', $facilities);

            $stmt->execute();
            $total_imported++;

        } catch (Exception $e) {
            $errors[] = "Error importing feature from $type: " . $e->getMessage();
        }
    }
}

// Display results
?>
<!DOCTYPE html>
<html>
<head>
    <title>GeoJSON Import Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>GeoJSON Import Results</h1>
        
        <?php if ($total_imported > 0): ?>
            <div class="alert alert-success">
                ✅ Successfully imported <?= $total_imported ?> evacuation sites!
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-warning">
                <h4>Errors:</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Go to Homepage</a>
            <a href="resident/evacuate.php" class="btn btn-success">View Evacuation Map</a>
        </div>

        <hr>

        <h3>Imported Sites:</h3>
        <?php
        // Show imported sites
        $query = "SELECT name, type, barangay, 
                  ST_X(location) as lng, ST_Y(location) as lat 
                  FROM evacuation_sites 
                  ORDER BY id DESC 
                  LIMIT 50";
        $stmt = $db->query($query);
        $sites = $stmt->fetchAll();
        ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Barangay</th>
                    <th>Coordinates</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                    <tr>
                        <td><?= htmlspecialchars($site['name']) ?></td>
                        <td><?= htmlspecialchars($site['type']) ?></td>
                        <td><?= htmlspecialchars($site['barangay']) ?></td>
                        <td><?= number_format($site['lat'], 6) ?>, <?= number_format($site['lng'], 6) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
// Note: After successful import, you can delete or move this file for security
?>