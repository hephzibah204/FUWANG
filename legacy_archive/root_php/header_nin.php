<?php
// Use HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    die("Secure connection required. Please use HTTPS.");
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Ensure session data is stored securely
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// Function to generate a random transaction ID
function generateTransactionId() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $transaction_id = 'nin';
    for ($i = 0; $i < 20; $i++) {
        $transaction_id .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $transaction_id;
}
if (!isset($_SESSION["transaction_pin"])) {
    // Redirect to welcome_pin.php with reseller ID if transaction pin is not set

    header("location:welcome_pin.php");
    exit(); // Add exit to prevent further execution
}



// Initialize response data
$response_data = null;

if (empty($_SESSION['call_token'])) {
    $_SESSION['call_token'] = bin2hex(random_bytes(32));
}