<?php
// Include database connection
include_once("db_conn.php");

// Function to get visitor IP address
function getVisitorIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Function to insert visitor data into the database
function insertVisitorData($conn) {
    // Get visitor IP address
    $ip_address = getVisitorIP();

    // Dummy values for location, device model, browser type, and time on site
    $location = ''; // You can use a geolocation API to get the location based on the IP address
    $device_model = $_SERVER['HTTP_USER_AGENT'];
    $browser_type = $_SERVER['HTTP_USER_AGENT'];
    $time_on_site = 0; // You need JavaScript to track time spent on site

    // Get session data
    
    $reseller_id = $_SESSION['reseller_id'] ?? null;
    $fullname = $_SESSION['fullname'] ?? null;

    // Prepare SQL statement
    $sql = "INSERT INTO visitors (ip_address, location, device_model, browser_type, time_on_site, reseller_id, fullname) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check for SQL errors
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssssiss", $ip_address, $location, $device_model, $browser_type, $time_on_site, $reseller_id, $fullname);

    // Execute statement
    if (!$stmt->execute()) {
        die("SQL Error: " . $stmt->error);
    }

    // Close statement
    $stmt->close();
}

// Insert visitor data into the database
insertVisitorData($conn);

?>
