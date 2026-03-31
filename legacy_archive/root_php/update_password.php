<?php
session_start();
include_once("db_conn.php");

header('Content-Type: application/json');

if (!isset($_SESSION['verified_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please verify your email first.']);
    exit;
}

if (!isset($_POST['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Password is required.']);
    exit;
}

$email = $_SESSION['verified_email'];
$password = $_POST['password'];

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update password in customers2 (and customers1 if they are separate tables)
// Based on user_login.php, authentication is done against customers2
$stmt = $conn->prepare("UPDATE customers2 SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    // Also update customers1 if it exists and stores the same password
    $stmt2 = $conn->prepare("UPDATE customers1 SET password = ? WHERE email = ?");
    $stmt2->bind_param("ss", $hashed_password, $email);
    $stmt2->execute();

    // Delete the OTP record after successful reset
    $stmt3 = $conn->prepare("DELETE FROM verification_otp WHERE email = ?");
    $stmt3->bind_param("s", $email);
    $stmt3->execute();

    // Clear verification session
    unset($_SESSION['verified_email']);

    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
}

$conn->close();
?>