<?php
include_once("db_conn.php");
include_once("balance_catchers_security.php");
// Get data from POST request

$data = json_decode(file_get_contents('php://input'), true);
$email = $conn->real_escape_string($data['email']);

// Delete from balance_attackers
$sql = "DELETE FROM balance_attackers WHERE email = '$email'";
$success = $conn->query($sql);

if ($success) {
    // Check if the email is in the referral table
    $sql = "SELECT COUNT(*) AS count FROM referral WHERE email = '$email'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Delete from referral table
        $sql = "DELETE FROM referral WHERE email = '$email'";
        $conn->query($sql);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>