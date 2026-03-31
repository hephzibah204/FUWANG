<?php
session_start();
include 'db_conn.php';

header('Content-Type: application/json');
date_default_timezone_set('Africa/Lagos');

if (isset($_POST['email']) && isset($_POST['otp'])) {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $current_time = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT otp, expiry FROM verification_otp WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { 
        $row = $result->fetch_assoc();

        if ($row['otp'] === $otp) {
            // Fix: OTP is valid if current time is BEFORE or AT expiry
            if ($current_time <= $row['expiry']) {
                $_SESSION['verified_email'] = $email;
                echo json_encode(['status' => 'success', 'message' => 'OTP verified successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Security code has expired. Please request a new one.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security code. Please check and try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No reset request found for this email.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Email and code are required.']);
}

$conn->close();
?>