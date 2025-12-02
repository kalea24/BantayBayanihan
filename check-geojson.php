<?php
/**
 * GeoJSON Field Inspector
 * Run this to see what fields are in your GeoJSON files
 * Access: http://localhost/disaster-prep/check-geojson.php
 * DELETE after checking!
 */

$geojson_files = [
    'Schools' => 'assets/geojson/evac_schools.geojson',
    'Barangay Halls' => 'assets/geojson/evac_brgy_hall.geojson',
    'Courts' => 'assets/geojson/evac_courts.geojson'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GeoJSON Field Inspector</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .field-name { 
            background: #f0f0f0; 
            padding: 2px 6px; 
            border-radius: 3px;
            font-family: monospace;
            font-weight: bold;
        }
        .field-value {
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>🔍 GeoJSON Field Inspector</h2>
        <p class="text-muted">This shows what fields exist in your GeoJSON files</p>

        <?php foreach ($geojson_files as $type => $filepath): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><?= $type ?></h4>
                    <small><?= $filepath ?></small>
                </div>
                <div class="card-body">
                    <?php
                    if (!file_exists($filepath)) {
                        echo "<div class='alert alert-danger'>❌ File not found!</div>";
                        continue;
                    }

                    $geojson = json_decode(file_get_contents($filepath), true);

                    if (!$geojson || !isset($geojson['features'])) {
                        echo "<div class='alert alert-danger'>❌ Invalid GeoJSON format</div>";
                        continue;
                    }

                    $featureCount = count($geojson['features']);
                    echo "<p><strong>Total features:</strong> $featureCount</p>";

                    if ($featureCount > 0) {
                        echo "<h6>Sample Feature #1:</h6>";
                        $firstFeature = $geojson['features'][0];
                        $props = $firstFeature['properties'];
                        $coords = $firstFeature['geometry']['coordinates'];

                        echo "<table class='table table-sm table-bordered'>";
                        echo "<tr><th>Field Name</th><th>Value</th><th>Type</th></tr>";
                        
                        foreach ($props as $key => $value) {
                            $type = gettype($value);
                            $displayValue = is_array($value) ? json_encode($value) : $value;
                            if (strlen($displayValue) > 100) {
                                $displayValue = substr($displayValue, 0, 100) . '...';
                            }
                            
                            echo "<tr>";
                            echo "<td><span class='field-name'>$key</span></td>";
                            echo "<td><span class='field-value'>" . htmlspecialchars($displayValue) . "</span></td>";
                            echo "<td><small>$type</small></td>";
                            echo "</tr>";
                        }
                        
                        echo "<tr>";
                        echo "<td><span class='field-name'>coordinates</span></td>";
                        echo "<td><span class='field-value'>[{$coords[0]}, {$coords[1]}]</span></td>";
                        echo "<td><small>array</small></td>";
                        echo "</tr>";
                        
                        echo "</table>";

                        // Suggest field mapping
                        echo "<div class='alert alert-info'>";
                        echo "<strong>💡 Suggested Field Mapping:</strong><br>";
                        
                        // Try to detect name field
                        $possibleNameFields = ['name', 'NAME', 'sitename', 'SITENAME', 'site_name', 'SITE_NAME', 
                                              'facility', 'FACILITY', 'Place_Name', 'placename', 'School', 'SCHOOL'];
                        $detectedName = null;
                        foreach ($possibleNameFields as $field) {
                            if (isset($props[$field])) {
                                $detectedName = $field;
                                break;
                            }
                        }
                        
                        if ($detectedName) {
                            echo "✅ <strong>Name field:</strong> <code>$detectedName</code> → Value: \"{$props[$detectedName]}\"<br>";
                        } else {
                            echo "❌ <strong>Name field:</strong> NOT FOUND! Available fields: " . implode(', ', array_keys($props)) . "<br>";
                            echo "<small>You may need to manually add a 'name' field or tell me which field to use.</small><br>";
                        }
                        
                        // Try to detect barangay field
                        $possibleBrgyFields = ['barangay', 'BARANGAY', 'brgy', 'BRGY', 'Barangay'];
                        $detectedBrgy = null;
                        foreach ($possibleBrgyFields as $field) {
                            if (isset($props[$field])) {
                                $detectedBrgy = $field;
                                break;
                            }
                        }
                        
                        if ($detectedBrgy) {
                            echo "✅ <strong>Barangay field:</strong> <code>$detectedBrgy</code> → Value: \"{$props[$detectedBrgy]}\"<br>";
                        } else {
                            echo "⚠️ <strong>Barangay field:</strong> NOT FOUND<br>";
                        }
                        
                        echo "</div>";

                        // Show all features
                        if ($featureCount > 1) {
                            echo "<details class='mt-3'>";
                            echo "<summary><strong>Show all $featureCount features</strong></summary>";
                            echo "<table class='table table-sm mt-2'>";
                            echo "<tr><th>#</th><th>Name Field Values</th><th>Coordinates</th></tr>";
                            
                            foreach ($geojson['features'] as $index => $feature) {
                                $p = $feature['properties'];
                                $c = $feature['geometry']['coordinates'];
                                
                                // Try to find any name-like field
                                $displayName = 'N/A';
                                foreach ($possibleNameFields as $field) {
                                    if (isset($p[$field])) {
                                        $displayName = $p[$field];
                                        break;
                                    }
                                }
                                
                                echo "<tr>";
                                echo "<td>" . ($index + 1) . "</td>";
                                echo "<td>$displayName</td>";
                                echo "<td><small>[{$c[0]}, {$c[1]}]</small></td>";
                                echo "</tr>";
                            }
                            
                            echo "</table>";
                            echo "</details>";
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="alert alert-warning">
            <strong>⚠️ SECURITY:</strong> Delete this file after checking!<br>
            <code>del C:\xampp\htdocs\disaster-prep\check-geojson.php</code>
        </div>

        <div class="card bg-light">
            <div class="card-body">
                <h5>What to do with this information:</h5>
                <ol>
                    <li>Look at the <strong>Field Name</strong> column to see what fields exist</li>
                    <li>If you see a name field (like "School", "Place_Name", etc.), note it</li>
                    <li>Tell me which field contains the site names</li>
                    <li>I'll update the import script to use that field</li>
                    <li>Re-run import-geojson.php</li>
                    <li>Sites should now have proper names!</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>