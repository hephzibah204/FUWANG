<?php
include_once("db_conn.php");
// Check if POST data is received
if (isset($_POST['ip_address'])) {
    $ip_address = $conn->real_escape_string($_POST['ip_address']);
    
    // Delete record
    $sql = "DELETE FROM login_attempts WHERE ip_address = '$ip_address'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>