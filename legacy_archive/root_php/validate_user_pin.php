<?php
session_start(); 
include_once("db_conn.php");

if (isset($_POST['pin'])) {
    $email = $_SESSION['email']; // Get email from session
    $entered_pin = $_POST['pin']; // Get the entered transaction PIN from the request

    // Database connection
    require 'db_conn.php';

    // Query to fetch transaction PIN from the database
    $stmt = $conn->prepare("SELECT transaction_pin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($transaction_pin);
    $stmt->fetch();
    $stmt->close();

    // Check if the entered PIN matches the one from the database
    if ($entered_pin === $transaction_pin) {
        echo json_encode([
            'success' => true,
            'message' => 'PIN validated successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid transaction PIN.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No PIN provided.'
    ]);
}
?>