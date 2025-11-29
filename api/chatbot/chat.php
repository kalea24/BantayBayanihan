<?php
/**
 * Bantay AI Chatbot API
 * Rule-based chatbot - no API key needed!
 * For advanced AI, integrate OpenAI/Claude later
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

$data = json_decode(file_get_contents("php://input"));
$message = strtolower(trim($data->message ?? ''));

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message required']);
    exit;
}

// Log chat
$database = new Database();
$db = $database->getConnection();

// Generate response based on keywords
$response = generateResponse($message, $db);

// Save to database
$query = "INSERT INTO chat_logs (user_id, message, response, session_id) 
          VALUES (?, ?, ?, ?)";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id'], $data->message, $response, session_id()]);

echo json_encode([
    'success' => true,
    'response' => $response
]);

/**
 * Generate chatbot response based on keywords
 */
function generateResponse($message, $db) {
    // Evacuation center queries
    if (preg_match('/evacuation|evacuate|center|shelter/i', $message) && 
        preg_match('/nearest|near|close|where/i', $message)) {
        
        // Get nearest evacuation sites
        $query = "SELECT name, barangay, address FROM evacuation_sites WHERE is_active = 1 LIMIT 3";
        $stmt = $db->query($query);
        $sites = $stmt->fetchAll();
        
        $response = "Here are the nearest evacuation centers:\n\n";
        foreach ($sites as $site) {
            $response .= "📍 <strong>{$site['name']}</strong><br>Location: {$site['address']}, {$site['barangay']}<br><br>";
        }
        $response .= "You can view them on the map by going to the 'Evacuate' page.";
        
        return $response;
    }

    // Earthquake queries
    if (preg_match('/earthquake|lindol/i', $message)) {
        return "🌍 <strong>Earthquake Safety Tips:</strong><br><br>
                <strong>During an earthquake:</strong><br>
                1. <strong>Drop, Cover, Hold</strong> - Drop to your hands and knees, cover your head and neck under sturdy furniture, hold on until shaking stops<br>
                2. Stay away from windows, mirrors, and hanging objects<br>
                3. If outdoors, move to an open area away from buildings, trees, and power lines<br>
                4. If driving, pull over safely and stay inside the vehicle<br><br>
                <strong>After an earthquake:</strong><br>
                • Check for injuries and damage<br>
                • Be prepared for aftershocks<br>
                • Listen to official announcements<br>
                • Evacuate if instructed<br><br>
                Would you like to practice earthquake drills? Visit our Drill Mode!";
    }

    // Flood queries
    if (preg_match('/flood|baha/i', $message)) {
        return "🌊 <strong>Flood Safety Tips:</strong><br><br>
                <strong>Before a flood:</strong><br>
                • Know your evacuation route<br>
                • Prepare a go-bag with important documents, clothes, food, and water<br>
                • Elevate valuables to higher ground<br>
                • Clear drainage systems around your home<br><br>
                <strong>During a flood:</strong><br>
                • Move to higher ground immediately<br>
                • Never walk or drive through floodwater - just 6 inches can knock you down!<br>
                • Turn off electricity if flooding begins<br>
                • Listen to local authorities<br><br>
                <strong>After a flood:</strong><br>
                • Avoid floodwater (may be contaminated)<br>
                • Check for structural damage before re-entering home<br>
                • Throw away contaminated food";
    }

    // Fire queries
    if (preg_match('/fire|sunog/i', $message)) {
        return "🔥 <strong>Fire Safety Tips:</strong><br><br>
                <strong>Fire Prevention:</strong><br>
                • Install smoke detectors and test monthly<br>
                • Never leave cooking unattended<br>
                • Keep flammable materials away from heat<br>
                • Have fire extinguishers accessible<br><br>
                <strong>If fire occurs:</strong><br>
                1. Alert everyone - yell 'FIRE!'<br>
                2. Get out immediately - don't stop to grab belongings<br>
                3. Stay low to avoid smoke<br>
                4. Feel doors before opening (if hot, use another exit)<br>
                5. Once out, stay out - call 911<br><br>
                <strong>If clothes catch fire:</strong><br>
                • <strong>Stop, Drop, and Roll</strong> - stop moving, drop to ground, cover face, roll until fire is out<br><br>
                Practice fire drills in Drill Mode!";
    }

    // Typhoon queries
    if (preg_match('/typhoon|bagyo|storm/i', $message)) {
        return "🌀 <strong>Typhoon Preparedness:</strong><br><br>
                <strong>Before typhoon arrives:</strong><br>
                • Monitor PAGASA weather updates<br>
                • Secure or bring indoors loose outdoor items<br>
                • Stock 3-day emergency supplies (water, food, medicine)<br>
                • Charge all devices and powerbanks<br>
                • Prepare flashlights and batteries<br><br>
                <strong>During typhoon:</strong><br>
                • Stay indoors away from windows<br>
                • Listen to battery-powered radio for updates<br>
                • Do NOT go out during the 'eye' of the storm<br>
                • Be ready to evacuate if ordered<br><br>
                <strong>Signal meanings:</strong><br>
                • Signal #1: Winds 30-60 kph<br>
                • Signal #2: Winds 61-120 kph<br>
                • Signal #3: Winds 121-170 kph<br>
                • Signal #4: Winds 171-220 kph<br>
                • Signal #5: Winds over 220 kph";
    }

    // Emergency kit queries
    if (preg_match('/emergency kit|go bag|supplies|prepare/i', $message)) {
        return "🎒 <strong>Emergency Kit Essentials:</strong><br><br>
                <strong>Water & Food (3-day supply):</strong><br>
                • 1 gallon of water per person per day<br>
                • Non-perishable food (canned goods, biscuits)<br>
                • Manual can opener<br><br>
                <strong>Tools & Supplies:</strong><br>
                • Flashlight with extra batteries<br>
                • Battery-powered or hand-crank radio<br>
                • First aid kit<br>
                • Whistle (to signal for help)<br>
                • Dust masks<br>
                • Plastic sheeting and duct tape<br><br>
                <strong>Documents & Money:</strong><br>
                • Copies of IDs and important papers<br>
                • Cash<br>
                • Insurance documents<br><br>
                <strong>Personal Items:</strong><br>
                • Prescription medications<br>
                • Personal hygiene items<br>
                • Change of clothes<br>
                • Phone charger/powerbank<br><br>
                Complete your emergency checklist in Drill Mode for points!";
    }

    // Contact/hotline queries
    if (preg_match('/contact|hotline|emergency number|call/i', $message)) {
        return "📞 <strong>Emergency Hotlines:</strong><br><br>
                • <strong>National Emergency: 911</strong><br>
                • NDRRMC: (02) 8911-1406 / (02) 8911-5061<br>
                • Philippine Red Cross: 143<br>
                • PAGASA Weather: (02) 8927-1335<br>
                • BFP Fire: (02) 8426-0219<br>
                • PNP: 117<br>
                • Coast Guard: (02) 8527-8481<br><br>
                Save these numbers in your phone!";
    }

    // Default response
    $defaultResponses = [
        "I can help you with information about evacuation centers, emergency procedures, and disaster preparedness. What would you like to know?",
        "I'm here to assist with disaster preparedness! You can ask me about earthquakes, floods, fires, typhoons, or emergency kits.",
        "Not sure what you're asking. Try asking about: evacuation centers, earthquake safety, flood tips, fire safety, typhoon preparedness, or emergency kits."
    ];

    return $defaultResponses[array_rand($defaultResponses)];
}
?>