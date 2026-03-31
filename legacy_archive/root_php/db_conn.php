<?php

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    die('Direct access not permitted.');
}

// Enforce HTTPS redirection
/*
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
*/

if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    $session_lifetime = 30 * 24 * 60 * 60; // 30 days
    $session_path = __DIR__ . '/storage/framework/sessions';
    if (!file_exists($session_path)) {
        mkdir($session_path, 0777, true);
    }
    session_save_path($session_path);
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'secure' => false, // Set to false for localhost
        'httponly' => true,
        'samesite' => 'Lax' // Set to Lax for localhost
    ]);

    // Secure session settings
    ini_set('session.cookie_secure', '0'); // Set to 0 for localhost
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', $session_lifetime);

    session_start();
}

// Regenerate session ID securely
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Load .env file for environment variables
function loadEnv($file) {
    if (file_exists($file)) {
        $env = file_get_contents($file);
        $lines = explode("\n", $env);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

// Fetch SITE_URL from .env
$siteUrl = getenv('SITE_URL');
$currentHost = $_SERVER['HTTP_HOST'];
/*
if (parse_url($siteUrl, PHP_URL_HOST) !== $currentHost) {
    header('HTTP/1.1 403 Forbidden');
    echo "403 Forbidden: Unauthorized domain.";
    exit();
}
*/

// Fetch API key and developer ID
$apiKey = getenv('API_KEY');
$developerId = getenv('DEVELOPER_ID');

/*
// Authorize the script via API
$authorizeUrl = 'https://sadeeqdata.com.ng/script/authorize_script.php';
$postData = [
    'apiKey' => $apiKey,
    'developerId' => $developerId,
    'siteUrl' => $siteUrl
];

$ch = curl_init($authorizeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
$response = curl_exec($ch);

if (curl_errno($ch)) {
    die('Error authorizing API: ' . curl_error($ch));
}
curl_close($ch);

$responseData = json_decode($response, true);
if (!isset($responseData['authorized']) || $responseData['authorized'] !== true) {
    die('Authorization failed: ' . ($responseData['message'] ?? 'Unknown error'));
}
*/
$_SESSION['developerId'] = $developerId;
$responseData['authorized'] = true; // Bypass check for local preview

// Database connection settings
$dbConfig = [
    'db_host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'),
    'db_user' => getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ''),
    'db_password' => getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''),
    'db_name' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ''),
];

mysqli_report(MYSQLI_REPORT_OFF);
$conn = null;
try {
    $conn = @new mysqli(
        $dbConfig['db_host'],
        $dbConfig['db_user'],
        $dbConfig['db_password'],
        $dbConfig['db_name']
    );

    if ($conn->connect_error) {
        error_log("Database connection failed: " . mysqli_connect_error());
        $conn = null;
    }
} catch (\Throwable $e) {
    error_log("Database connection failed (exception): " . $e->getMessage());
    $conn = null;
}

if ($conn === null) {
    // Optional: define a fallback for queries if needed, or just let them fail gracefully
}

// Block unauthorized table modifications
if (strpos(basename($_SERVER['SCRIPT_FILENAME']), 'nom') !== false) {
    error_log("Blocked attempt to insert into the 'admins' table from: " . $_SERVER['REMOTE_ADDR']);
    header('HTTP/1.1 403 Forbidden');
    exit('403 Forbidden: Database insertions into the admins table are not allowed. Contact support +2348113910395.');
}

// Function to log admin actions for audit trail
function log_admin_action($action, $details) {
    global $conn;
    if ($conn === null) return;
    $admin_username = isset($_SESSION['username']) ? $_SESSION['username'] : 'system';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO audit_logs (admin_username, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $admin_username, $action, $details, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Auto-create audit_logs table
if ($conn !== null) {
    $conn->query("CREATE TABLE IF NOT EXISTS audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, admin_username VARCHAR(50), action VARCHAR(100), details TEXT, ip_address VARCHAR(45), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;");
    // Ensure admins table has 2FA columns
    $conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS 2fa_otp VARCHAR(6) NULL;");
    $conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS 2fa_expiry DATETIME NULL;");
    
    // Create individual user notifications table
    $conn->query("CREATE TABLE IF NOT EXISTS user_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        title VARCHAR(100),
        message TEXT,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Referrer-Policy: no-referrer-when-downgrade');

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Default color in case the query fails or no connection
$primaryColor = '#092C9F';

if ($conn !== null) {
    try {
        $sql = "SELECT primary_color FROM settings WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $id = 1;
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $primaryColor = $row['primary_color'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Failed to fetch primary color: " . $e->getMessage());
    }
}
// Close database connection at the end

?>