<?php
session_start();
include_once('db_conn.php'); // Ensure this includes your database connection

// Get the logged-in user's email from the session
$user_email = $_SESSION['email'];

// Check if the email is set in the session
if (!$user_email) {
    // Redirect to login page if the email is not set in the session
    header("Location: user_login.php");
    exit();
}

// Query the bank_details table to check if the user has a palmpay account
$sql = "SELECT palmpay FROM bank_details WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($palmpay_account);

// Check if the user record exists
if ($stmt->num_rows > 0) {
    // Fetch the result
    $stmt->fetch();

    // Check if the palmpay account is empty
    if (empty($palmpay_account)) {
        // Redirect to generate_palmpayaccount.php if palmpay account is empty
        header("Location: palmpay");
        exit();
    } else {
        // User has a palmpay account, proceed with the dashboard or other actions
        // You can load the dashboard or perform other actions here
    }
} else {
    // No entry found for the user in bank_details table
    // Redirect to generate_palmpayaccount.php or show an error message
    header("Location:palmpay");
    exit();
}


?>