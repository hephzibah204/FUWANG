<?php
include_once("db_conn.php");
session_start();

// Function to update user's online status
function updateUserOnlineStatus($id, $status) {
    global $conn;
    $query = "UPDATE customers1 SET online_status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Check if session email is set
if (isset($_SESSION["email"])) {
    // User is already logged in, redirect to welcome page
    header("Location: welcome_pin.php");
    exit();
}

// If session email is not set, check if cookies for email and fullname exist
if (isset($_COOKIE['email']) && isset($_COOKIE['fullname'])) {
    // Retrieve email and fullname from cookies
    $_SESSION['email'] = $_COOKIE['email'];
    $_SESSION['fullname'] = $_COOKIE['fullname'];

    // Optionally, you can also retrieve the user's ID from the database based on the email
    $query = "SELECT id FROM customers1 WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['id'] = $user['id'];

        // Update user's online status to online
        updateUserOnlineStatus($user['id'], "online");

        // Redirect to the welcome page
        header("Location: welcome_pin.php");
        exit();
    } else {
        // If the email is not found in the database, clear the cookies and session, then redirect to login page
        setcookie('email', '', time() - 3600, '/');
        setcookie('fullname', '', time() - 3600, '/');
        session_unset();
        session_destroy();
        header("Location: user_login.php");
        exit();
    }
} else {
    // If no session or cookies, redirect to the login page
    header("Location: user_login.php");
    exit();
}
?>