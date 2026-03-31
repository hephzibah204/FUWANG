<?php
session_start();
include_once("db_conn.php");

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}

// Get the logged-in username from the session
$username = $_SESSION['username']; // Assuming the session stores the username in 'username'

// Query the database to check if this username exists in the admins table
$sql = "SELECT * FROM admins WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// If no result is found, the user is not an admin
if ($result->num_rows === 0) {
    // Redirect or show an error message if the user is not an admin
    echo "You are not authorized to access this page.";
    exit();
}

// Optionally, you can proceed with the rest of your page logic here.
?>