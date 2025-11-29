<?php
/**
 * Application Configuration
 */

// Base URL - CHANGE THIS to match your setup
define('SITE_URL', 'http://localhost/disaster-prep');
define('SITE_NAME', 'Disaster Preparedness Platform');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Default Map Center (Philippines - adjust to your barangay)
define('DEFAULT_LAT', 14.5995);
define('DEFAULT_LNG', 120.9842);
define('DEFAULT_ZOOM', 13);

// AI Chatbot API Keys
// For NOW, leave as is - we'll implement chatbot later
// Get FREE API key from: https://platform.openai.com/api-keys
define('OPENAI_API_KEY', ''); // Leave empty for now

// OR use Claude (Anthropic)
// Get key from: https://console.anthropic.com/
define('ANTHROPIC_API_KEY', ''); // Leave empty for now

// You can also use FREE alternatives:
// 1. Dialogflow (Google) - Free tier available
// 2. Wit.ai (Meta) - Completely free
// 3. Build simple rule-based chatbot (no API needed)

// Error Reporting (Development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Upload Configuration
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Pagination
define('ITEMS_PER_PAGE', 10);
?>