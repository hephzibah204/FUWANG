<?php
// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    die('Direct access not permitted.');
}

// Enforce HTTPS redirection if not already using HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Set session cookie parameters securely
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'secure' => true, // Ensure cookies are sent over HTTPS
    'httponly' => true, // Prevent JavaScript access to cookies
    'samesite' => 'Strict' // Prevent CSRF
]);

// Start session after setting cookie params
session_start();

// Secure session settings
ini_set('session.cookie_secure', '1'); // Only send cookies over HTTPS
ini_set('session.cookie_httponly', '1'); // Prevent JavaScript access to cookies
ini_set('session.use_strict_mode', '1'); // Prevent session fixation attacks
ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF
ini_set('session.gc_maxlifetime', $session_lifetime); // Set session garbage collection lifetime

// Regenerate session ID if not already done
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Set session timeout (30 days)
$session_lifetime = 30 * 24 * 60 * 60; // 30 days in seconds

// Manually load .env file
function loadEnv($file) {
    if (file_exists($file)) {
        $env = file_get_contents($file);
        $lines = explode("\n", $env);
        
        foreach ($lines as $line) {
            // Ignore comments or empty lines
            if (empty($line) || $line[0] == '#') {
                continue;
            }

            // Split into key and value
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Set environment variable
            putenv("$key=$value");
        }
    }
}

// Load the .env file for environment variables
loadEnv(__DIR__ . '/.env');

// Fetch SITE_URL from .env (host only)
$siteUrl = getenv('SITE_URL');

// **Fetch the current server host** (i.e., $_SERVER['HTTP_HOST'])
// Do not let the user change this value manually
 // Always use the server's actual host
$currentHost = $_SERVER['HTTP_HOST'];

// Prevent any user manipulation: Ensure the current host matches the `SITE_URL` (domain part only)
if (parse_url($siteUrl, PHP_URL_HOST) !== $currentHost) {
    // If the host doesn't match, deny access (403 Forbidden)
    header('HTTP/1.1 403 Forbidden');
    echo "403 Forbidden: Unauthorized domain.";
    exit();
}

// API key and developer ID for authorization (retrieved from .env file)
$apiKey = getenv('API_KEY');
$developerId = getenv('DEVELOPER_ID');

// The URL to authorize (unchanged)
$authorizeUrl = 'https://dataverify.com.ng/script/authorize_script.php';

// Prepare POST data for the request
$postData = [
    'apiKey' => $apiKey,
    'developerId' => $developerId,
    'siteUrl' => $siteUrl // Send the site URL dynamically
];

// Use cURL to make the API request
$ch = curl_init($authorizeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

// Execute the cURL request and get the response
$response = curl_exec($ch);

// Check for errors in the cURL request
if (curl_errno($ch)) {
    die('Error authorizing API: ' . curl_error($ch));
}

curl_close($ch);

// Parse the response (assuming it's JSON)
$responseData = json_decode($response, true);

// Check if authorization was successful
if (isset($responseData['authorized']) && $responseData['authorized'] == true) {
    // Authorization successful, proceed with database connection
$_SESSION['developerId'] = $developerd;
    // Retrieve environment variables for DB connection
    $db_host = getenv('DB_HOST');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');
    $db_name = getenv('DB_NAME');

    // Create a MySQLi connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
// Check if the script is trying to insert records into the `admins` table
if (strpos(basename($_SERVER['SCRIPT_FILENAME']), 'admin') !== false) {
    // Log the blocked action attempt
    error_log("Blocked attempt to insert into the 'admins' table from: " . $_SERVER['REMOTE_ADDR']);
    
    // Return a 403 Forbidden response to prevent further processing
    header('HTTP/1.1 403 Forbidden');
    exit('403 Forbidden: Database insertions into the admins table are not allowed.');
}
    // Set security headers to prevent certain attacks
    header('X-Frame-Options: DENY'); // Prevent clickjacking
    header('X-Content-Type-Options: nosniff'); // Prevent MIME-type sniffing
    header('X-XSS-Protection: 1; mode=block'); // Enable cross-site scripting protection
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload'); // Enforce HTTPS
    header('Referrer-Policy: no-referrer-when-downgrade'); // Control what info is sent in the Referer header

    // Prevent Cross-Site Request Forgery (CSRF)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a new CSRF token for the session
    }

    // If needed, check the CSRF token before processing sensitive actions
    // Example: if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) { die('Invalid CSRF token'); }

} else {
    // Authorization failed, handle the error
    die('Authorization failed: ' . (isset($responseData['message']) ? $responseData['message'] : 'Unknown error'));
}
?>